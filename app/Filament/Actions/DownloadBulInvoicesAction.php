<?php

namespace App\Filament\Actions;

use App\Services\GenerateInvoiceService;
use App\Services\ZipService;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadBulInvoicesAction
{
    public static function make()
    {
        return BulkAction::make('Download Bulk')
            ->label('Download Multiple Invoices')
            ->color(Color::Purple)
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->size('xs')
            // ->checkIfRecordIsSelectableUsing(
            //     fn (\Illuminate\Database\Eloquent\Model $record): bool => $record->amount_pending > 0,
            // )
            ->modalHeading('Download Multiple Invoices')
            ->modalDescription(
                'This action will download the all selected invoices as PDF files. Are you sure?'
            )
            ->deselectRecordsAfterCompletion()
            ->requiresConfirmation()
            ->action(function (
                Collection $records,
                GenerateInvoiceService $invoiceService,
                ZipService $zipService
            ): BinaryFileResponse|bool {
                $createdFilesPath = [];

                foreach ($records as $record) {
                    $pdf = $invoiceService
                        ->generate($record);

                    $createdFilesPath[] = \public_path('storage/'.$pdf->pdf->filename);
                }

                if (count($createdFilesPath) === 0) {
                    Notification::make('No files selected')
                        ->danger()
                        ->title('None of the selected files is downloadable! They may not have items')
                        ->send();

                    return false;
                }

                return $zipService
                    ->createZip(files: $createdFilesPath, removeFilesAfterCompletion: true)
                    ->download()
                    ->deleteFileAfterSend(true);
            }
            );
    }
}

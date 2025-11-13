<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource\Pages;

use Filament\Actions;
use App\Models\Invoice;
use Filament\Actions\Action;
use App\Enums\InvoiceStatuses;
use Filament\Support\Colors\Color;
use Filament\Resources\Pages\ViewRecord;
use App\Services\Filament\Forms\InvoicePaymentForm;
use App\Filament\Invoicing\Resources\InvoiceResource;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn ($record) => $record->status !== InvoiceStatuses::Paid),
            Action::make('Pay')
                ->visible(fn ($record) => $record->balance_pending > 0)
                ->color(Color::Purple)
                ->icon('heroicon-s-credit-card')
                ->form(InvoicePaymentForm::make())
                ->action(function (array $data, Invoice $record): void {
                    $record->payments()->create($data);
                }),
            Action::make(__('Pdf'))
                ->color('success')
                ->icon('heroicon-s-document-arrow-down')
                ->url(fn (Invoice $record) => route('generate-invoice', $record))
                ->openUrlInNewTab()

        ];
    }
}

<?php

namespace App\Filament\Invoicing\Resources\Invoices\Pages;

use App\Enums\InvoiceStatuses;
use App\Filament\Invoicing\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Services\Filament\Forms\InvoicePaymentForm;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->modalWidth('7xl')
                ->visible(fn ($record) => $record->status !== InvoiceStatuses::Paid),
            Action::make('Pay')
                ->visible(fn ($record) => $record->balance_pending > 0)
                ->color(Color::Purple)
                ->icon('heroicon-s-credit-card')
                ->schema(InvoicePaymentForm::make())
                ->action(function (array $data, Invoice $record): void {
                    $record->payments()->create($data);
                }),
            Action::make(__('Pdf'))
                ->color('success')
                ->icon('heroicon-s-document-arrow-down')
                ->url(fn (Invoice $record) => route('generate-invoice', $record))
                ->openUrlInNewTab(),

        ];
    }
}

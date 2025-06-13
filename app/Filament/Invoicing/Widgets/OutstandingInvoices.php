<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OutstandingInvoices extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'desc')
            ->query(
                Invoice::query()
                    ->where('status', '=', \App\Enums\InvoiceStatuses::Overdue)
                    ->with('project.client', 'agent', 'campaign')
                    ->where('balance_pending', '>', 0)
            )
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Expired')
                    ->date()
                    ->formatStateUsing(fn ($state) => $state?->diffForHumans())
                    ->sortable(),
            ]);
    }
}

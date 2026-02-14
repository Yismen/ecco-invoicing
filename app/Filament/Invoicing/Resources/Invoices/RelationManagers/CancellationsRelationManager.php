<?php

namespace App\Filament\Invoicing\Resources\Invoices\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CancellationsRelationManager extends RelationManager
{
    protected static string $relationship = 'cancellation';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice.number')
            ->columns([
                TextColumn::make('invoice.number'),
                TextColumn::make('date')
                    ->date(),
                TextColumn::make('comments')
                    ->limit(25)
                    ->tooltip(fn (?string $state) => $state),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->recordActions([
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ]);
    }
}

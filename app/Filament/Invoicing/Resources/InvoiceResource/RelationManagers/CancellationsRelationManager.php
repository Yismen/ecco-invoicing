<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class CancellationsRelationManager extends RelationManager
{
    protected static string $relationship = 'cancellation';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice.number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice.number'),
                Tables\Columns\TextColumn::make('date')
                    ->date(),
                Tables\Columns\TextColumn::make('comments')
                    ->limit(25)
                    ->tooltip(fn ($state) => $state),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ]);
    }
}

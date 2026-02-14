<?php

namespace App\Filament\Invoicing\Resources\Invoices\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Changes')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('Changes')
                    // ->getStateUsing(function ($record) {
                    //     dd($record);
                    //     return $record->changes;
                    // })
                    ->formatStateUsing(fn (Model $record): View => view('filament.partials.invoice-activities-column', [
                        'activity' => $record->load([
                            'causer',
                        ]),
                    ])),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

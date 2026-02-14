<?php

namespace App\Filament\Invoicing\Resources\Invoices\RelationManagers;

use App\Models\Payment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('date')
                    ->label('Date')
                    ->content(fn (Payment $record) => $record ? $record->date->toFormattedDateString() : 'N/A'),
                Placeholder::make('amount')
                    ->label('Amount')
                    ->content(fn (Payment $record) => $record ? $record->amount : 'N/A'),
                Placeholder::make('reference')
                    ->label('Reference')
                    ->content(fn (Payment $record) => $record ? $record->reference : 'N/A'),
                Placeholder::make('description')
                    ->label('Description')
                    ->content(fn (Payment $record) => $record ? $record->description : 'N/A'),
                FileUpload::make('images')
                    ->label('Attachments')
                    ->disabled()
                    ->multiple(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('date'),
                TextColumn::make('amount')->money('usd', true),
                TextColumn::make('reference')
                    ->limit(25)
                    ->tooltip(fn (?string $state) => $state),
                TextColumn::make('description')
                    ->limit(25)
                    ->tooltip(fn (?string $state) => $state),
                ImageColumn::make('images')
                    // ->multiple()
                    ->label('Attachments')
                    ->circular(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

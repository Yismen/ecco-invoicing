<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Payment;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('date')
                    ->label('Date')
                    ->content(fn (Payment $record) => $record ? $record->date->toFormattedDateString() : 'N/A'),
                Forms\Components\Placeholder::make('amount')
                    ->label('Amount')
                    ->content(fn (Payment $record) => $record ? $record->amount : 'N/A'),
                Forms\Components\Placeholder::make('reference')
                    ->label('Reference')
                    ->content(fn (Payment $record) => $record ? $record->reference : 'N/A'),
                Forms\Components\Placeholder::make('description')
                    ->label('Description')
                    ->content(fn (Payment $record) => $record ? $record->description : 'N/A'),
                Forms\Components\FileUpload::make('images')
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
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('amount')->money('usd', true),
                Tables\Columns\TextColumn::make('reference')
                    ->limit(25)
                    ->tooltip(fn ($state) => $state),
                Tables\Columns\TextColumn::make('description')
                    ->limit(25)
                    ->tooltip(fn ($state) => $state),
                Tables\Columns\ImageColumn::make('images')
                    // ->multiple()
                    ->label('Attachments')
                    ->circular(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

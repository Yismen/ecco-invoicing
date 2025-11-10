<?php

namespace App\Filament\Invoicing\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\Filament\Filters\InvoiceTableFilters;

class InvoiceNumberResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationIcon = 'heroicon-o-hashtag';

    protected static ?string $cluster = \App\Filament\Invoicing\Clusters\InvoicesCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationLabel = 'Numbers';

    protected static ?string $gridLabel = 'sdfadsf';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('number')
                    ->label('Original Invoice Number')
                    ->content(fn (?Invoice $record): string => $record->number),
                Forms\Components\TextInput::make('number')
                    ->label('New Invoice Number')
                    ->required()
                    ->unique(ignorable: fn ($record) => $record)
                    ->minLength(5)
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('project.client.name')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('project.name')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('agent.name')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('campaign.name')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_pending')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->badge()
                    ->sortable()
                    ->color(fn ($state) => $state->getColor()),
            ])
            ->deferFilters()
            ->filtersFormWidth('lg')
            ->filters(InvoiceTableFilters::make())

            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Invoicing\Resources\InvoiceResource\Pages\ManageInvoiceNumbers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('project.client', 'agent', 'campaign')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

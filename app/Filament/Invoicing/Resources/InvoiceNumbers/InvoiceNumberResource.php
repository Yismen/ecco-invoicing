<?php

namespace App\Filament\Invoicing\Resources\InvoiceNumbers;

use App\Filament\Invoicing\Clusters\InvoicesCluster\InvoicesCluster;
use App\Filament\Invoicing\Resources\InvoiceNumbers\Pages\ManageInvoiceNumbers;
use App\Models\Invoice;
use App\Services\Filament\Filters\InvoiceTableFilters;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceNumberResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';

    protected static ?string $cluster = InvoicesCluster::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationLabel = 'Numbers';

    protected static ?string $gridLabel = 'Invoice Numbers';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Placeholder::make('number')
                    ->label('Original Invoice Number')
                    ->content(fn (?Invoice $record): string => $record->number),
                TextInput::make('number')
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
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('number')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('project.client.name')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('project.name')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('agent.name')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('campaign.name')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balance_pending')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->badge()
                    ->sortable()
                    ->color(fn ($state) => $state->getColor()),
            ])
            ->deferFilters()
            ->filtersFormWidth('lg')
            ->filters(InvoiceTableFilters::make())

            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInvoiceNumbers::route('/'),
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

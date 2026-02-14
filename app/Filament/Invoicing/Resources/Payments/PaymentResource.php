<?php

namespace App\Filament\Invoicing\Resources\Payments;

use App\Filament\Invoicing\Clusters\InvoicesCluster\InvoicesCluster;
use App\Filament\Invoicing\Resources\PaymentResource\Pages;
use App\Filament\Invoicing\Resources\Payments\Pages\ListPayments;
use App\Models\Payment;
use App\Rules\PreventOverpayment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?string $title = 'Invoice Payments';

    // protected static ?string $navigationGroup = 'Invoicing';

    protected static ?string $cluster = InvoicesCluster::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Placeholder::make('Invoice Number')
                    ->content(fn ($record) => $record->invoice->number)
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    // ->maxValue(fn($record) => $record->balance_pending)
                    ->default(fn ($record) => $record->invoice->balance_pending)
                    ->rule(static function ($record) {
                        return new PreventOverpayment($record);
                    }),
                DatePicker::make('date')
                    ->required(),
                TextInput::make('reference')
                    ->maxLength(255),
                FileUpload::make('images')
                    ->image()
                    ->openable()
                    ->downloadable()
                    ->imageEditor()
                    ->multiple(),
                Textarea::make('description'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('invoice.number')
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money()
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                ImageColumn::make('images')
                    ->circular()
                    ->checkFileExistence(false)
                    ->disk('local')
                    ->stacked(),
                TextColumn::make('reference')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn (?string $state) => $state),
                TextColumn::make('description')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn (?string $state) => $state),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    // Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            // 'create' => Pages\CreatePayment::route('/create'),
            // 'view' => Pages\ViewPayment::route('/{record}'),
            // 'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

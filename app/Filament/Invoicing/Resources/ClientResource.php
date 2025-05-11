<?php

namespace App\Filament\Invoicing\Resources;

use App\Filament\Invoicing\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Invoicing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->autofocus()
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('address')
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\TextInput::make('invoice_net_days')
                            ->label('Invoice Net Days')
                            ->numeric()
                            ->minValue(0)
                            ->default(30)
                            ->required(),
                        Forms\Components\TextInput::make('tax_rate')
                            ->label('Tax Rate (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.01)
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('invoice_template')
                            ->label('Invoice Template')
                            ->required()
                            ->default('default'),
                        Forms\Components\Textarea::make('invoice_notes')
                            ->label('Invoice Notes')
                            ->maxLength(255)
                            ->columns(2),
                        Forms\Components\Textarea::make('invoice_terms')
                            ->label('Invoice Terms')
                            ->maxLength(255)
                            ->columns(2),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->limit(50)
                    ->html()
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('invoice_net_days'),
                Tables\Columns\TextColumn::make('tax_rate')
                    ->label('Tax Rate (%)')
                    ->formatStateUsing(fn ($state) => $state * 100),
                Tables\Columns\TextColumn::make('invoice_template'),
                Tables\Columns\TextColumn::make('invoice_notes')
                    ->limit(50)
                    ->html()
                    ->wrap()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('invoice_terms')
                    ->limit(50)
                    ->html()
                    ->wrap()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('agents_count')
                    ->counts('agents')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                // Tables\Columns\TextColumn::make('invoices_count')
                //     ->counts('invoices')
                //     ->sortable()
                //     ->toggleable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
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

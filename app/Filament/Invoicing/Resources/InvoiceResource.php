<?php

namespace App\Filament\Invoicing\Resources;

use Filament\Forms;
use App\Models\Item;
use Filament\Tables;
use App\Models\Invoice;
use App\Models\Project;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Services\Filament\Forms\ItemForm;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Filament\Forms\AgentForm;
use App\Services\Filament\Forms\ClientForm;
use App\Services\Filament\Forms\ProjectForm;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Invoicing\Resources\InvoiceResource\Pages;
use App\Models\Agent;
use App\Models\Client;
use App\Models\InvoiceItem;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationGroup = 'Invoicing';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Select::make('client_id')
                        ->relationship('client', 'name')
                        ->options(function() : array {
                            return Client::query()->pluck('name', 'id')->all();
                        })
                        ->afterStateUpdated(fn(Set $set) => $set('agent_id', null))
                        ->autofocus(fn (string $operation) => in_array($operation, ['create', 'edit']))
                        ->searchable()
                        ->required()
                        ->createOptionForm(ClientForm::make())
                        ->createOptionModalHeading('Create Client')
                        ->preload(10)
                        ->columnSpan(2)
                        ->live(),
                    Forms\Components\Select::make('agent_id')
                        ->relationship('agent', 'name')
                        ->options(function(Get $get) : array|null {
                            $client_id = $get('client_id');
                            return Agent::query()->where('client_id', $client_id)->pluck('name', 'id')->toArray();
                        })
                        ->afterStateUpdated(fn(Set $set) => $set('project_id', null))
                        ->required()
                        ->searchable()
                        ->disabled(fn(Get $get) => ! $get('client_id'))
                        ->createOptionForm(AgentForm::make())
                        ->createOptionModalHeading('Create Agent')
                        ->preload(10)
                        ->columnSpan(2)
                        ->live(),
                    Forms\Components\Select::make('project_id')
                        ->relationship('project', 'name')
                        ->required()
                        ->searchable()
                        ->createOptionForm(ProjectForm::make())
                        ->createOptionModalHeading('Create Project')
                        ->options(function(Get $get) : array|null {
                            $agent_id = $get('agent_id');
                            return Project::query()->where('agent_id', $agent_id)->pluck('name', 'id')->toArray();
                        })
                        ->preload(10)
                        ->disabled(fn(Get $get) => ! $get('agent_id'))
                        ->columnSpan(2)
                        ->live(),
                    Forms\Components\DatePicker::make('date')
                        ->default(today()->format('Y-m-d'))
                        ->maxDate(now()->format('Y-m-d'))
                        ->required(),
               ])->columns(7),
                Forms\Components\Section::make('Invoice Items')
                    ->schema([
                        Forms\Components\Repeater::make('invoiceItems')
                            ->relationship()
                            ->label('Items')
                            ->defaultItems(1)
                            ->reorderable()
                            ->reorderableWithButtons()
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->schema([
                                Forms\Components\Select::make('item_id')
                                    ->options(function(Get $get) : array|null {
                                        $project_id = $get('../../project_id');
                                        $project = $project_id ? Project::with(['items'])->findOrFail($project_id): null;

                                        return $project?->items?->pluck('name', 'id')->toArray();
                                    })
                                    ->distinct()
                                    ->afterStateUpdated(function($state, Get $get, Set $set) {
                                        $item = Item::find($state);
                                        if($item) {
                                            $set('item_price', $item->price);
                                            $set('subtotal', $item->price * (float) $get('quantity'));
                                        }
                                    } )
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('project')
                                            ->options(
                                                Project::query()
                                                    ->orderBy('name')
                                                    ->pluck('name', 'id')
                                                    ->toArray()
                                            )
                                            ->required()
                                            ->searchable()
                                            ->createOptionForm(ProjectForm::make())
                                            ->createOptionModalHeading('Create Project')
                                            ->preload(10)
                                            ->placeholder('Select a project'),
                                        Forms\Components\TextInput::make('price')
                                            ->minValue(0)
                                            ->required()
                                            ->numeric()
                                            ->prefix('$'),
                                    ])
                                    ->columnSpan(2)
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function($state, Set $set, Get $get) {
                                        $set('subtotal', $state * $get('item_price'));
                                    })
                                    ->minValue(0)
                                    ->default(1)
                                    ->required(),
                                Forms\Components\TextInput::make('item_price')
                                    ->disabled(),
                                Forms\Components\TextInput::make('subtotal')
                                    ->disabled()
                                    ->formatStateUsing(function(InvoiceItem $record) {
                                        return $record->item_price * $record->quantity;
                                    })
                                ,
                            ])
                            ->columns(5),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agent.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('template')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes')
                    ->searchable(),
                Tables\Columns\TextColumn::make('terms')
                    ->searchable(),
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
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getItems(null $component = null): array
    {
        // dump($component);

        return Item::pluck('name', 'id')
            ->toArray( );
    }
}

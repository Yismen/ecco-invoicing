<?php

namespace App\Filament\Invoicing\Resources;

use Filament\Forms;
use App\Models\Item;
use Filament\Tables;
use App\Models\Agent;
use App\Models\Invoice;
use App\Models\Project;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Campaign;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\InvoiceItem;
use App\Enums\InvoiceStatuses;
use Illuminate\Support\Number;
use Filament\Resources\Resource;
use App\Services\ModelListService;
use Filament\Support\Colors\Color;
use Illuminate\Validation\Rules\Unique;
use Filament\Notifications\Notification;
use App\Filament\Exports\InvoiceExporter;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Filament\Forms\ProjectForm;
use Illuminate\Database\Eloquent\Collection;
use App\Services\GenerateInvoiceNumberService;
use App\Filament\Actions\DownloadInvoiceAction;
use App\Services\Filament\Forms\InvoicePaymentForm;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\Builder as QueryBuilder;
use App\Filament\Invoicing\Resources\InvoiceResource\Pages;
use App\Rules\UniqueByParentRelationship;
use Closure;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    // protected static ?string $navigationGroup = 'Invoicing';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->options(
                                ModelListService::get(
                                    model: Project::class,
                                    key_field: 'id',
                                    value_field: 'name'
                                )
                            )
                            ->afterStateUpdated(fn (Set $set) => $set('agent_id', null))
                            ->autofocus(fn (string $operation) => in_array($operation, ['create', 'edit']))
                            ->searchable()
                            ->required()
                            ->createOptionForm(ProjectForm::make())
                            ->createOptionModalHeading('Create Project')
                            ->preload(10)
                            ->columnSpan(2)
                            ->live(),
                        Forms\Components\Select::make('agent_id')
                            ->relationship('agent', 'name')
                            ->options(function (Get $get): ?array {
                                $project_id = $get('project_id');

                                return ModelListService::get(
                                    model: Agent::query(),
                                    key_field: 'id',
                                    value_field: 'name',
                                    conditions: $project_id ? [['project_id' => $project_id]] : []
                                );
                            })
                            ->afterStateUpdated(fn (Set $set) => $set('campaign_id', null))
                            ->required()
                            ->searchable()
                            ->disabled(fn (Get $get) => ! $get('project_id'))
                            ->preload(10)
                            ->columnSpan(2)
                            ->createOptionForm([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->rules([
                                                function($livewire) {
                                                    return new UniqueByParentRelationship(
                                                        table: Agent::class,
                                                        uniqueField: 'name',
                                                        parentField: 'project_id',
                                                        parentId: $livewire->data['project_id'],
                                                    );
                                                },
                                            ])
                                            ->autofocus()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->createOptionModalHeading('Create Agent')
                            ->createOptionUsing(function (array $data, Get $get): int {
                                $data['project_id'] = $get('project_id');

                                if ($data['project_id'] === null) {
                                    Notification::make()
                                        ->title('Invalid Project')
                                        ->danger()
                                        ->persistent()
                                        ->body('Please select an agent to which this agent belongs.')
                                        ->send();

                                    return 0;
                                } else {
                                    $agent = Agent::create($data);

                                    return $agent->id;
                                }
                            })
                            ->live(),
                        Forms\Components\Select::make('campaign_id')
                            ->relationship('campaign', 'name')
                            ->required()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->autofocus()
                                    ->required()
                                    ->validationAttribute('name')
                                    ->rules([
                                        function($livewire) {
                                            return new UniqueByParentRelationship(
                                                table: Campaign::class,
                                                uniqueField: 'name',
                                                parentField: 'agent_id',
                                                parentId: $livewire->data['agent_id'],
                                            );
                                        },
                                    ])
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data, Get $get): int {
                                $data['agent_id'] = $get('agent_id');

                                if ($data['agent_id'] === null) {
                                    Notification::make()
                                        ->title('Invalid Agent')
                                        ->danger()
                                        ->persistent()
                                        ->body('Please select an agent to which this campaign belongs.')
                                        ->send();

                                    return 0;
                                } else {
                                    $campaign = Campaign::create($data);

                                    return $campaign->id;
                                }
                            })
                            ->createOptionModalHeading('Create Campaign')
                            ->options(function (Get $get): ?array {
                                $agent_id = $get('agent_id');

                                return ModelListService::get(
                                    model: Campaign::query(),
                                    key_field: 'id',
                                    value_field: 'name',
                                    conditions: $agent_id ? [['agent_id' => $agent_id]] : []
                                );
                            })
                            ->preload(10)
                            ->disabled(fn (Get $get) => ! $get('agent_id'))
                            ->columnSpan(2)
                            ->live(),
                        Forms\Components\DatePicker::make('date')
                            ->default(today()->format('Y-m-d'))
                            ->maxDate(now()->format('Y-m-d'))
                            ->required(),
                    ])->columns(7),
                Forms\Components\Section::make()
                    ->visible(fn ($record) => $record)
                    ->schema([
                        Forms\Components\Placeholder::make('')
                            ->content(function ($record) {
                                $record->load(['project', 'agent', 'campaign', 'items']);

                                return view('filament.partials.invoice-company-details', [
                                    'invoice' => $record,
                                ]);
                            }),
                    ]),

                Forms\Components\Section::make()
                    ->visible(fn ($record) => $record === null)
                    ->inlineLabel()
                    ->schema([
                        Forms\Components\Placeholder::make('New Invoice Numbrer')
                            ->content(function (Get $get) {
                                $project = Project::find($get('project_id'));

                                if ($project) {
                                    return GenerateInvoiceNumberService::generate($project);
                                }
                            }),
                    ]),

                Forms\Components\Section::make('Invoice Items')
                    ->schema([
                        Forms\Components\Repeater::make('invoiceItems')
                            ->relationship()
                            ->visible(fn ($get) => $get('campaign_id'))
                            ->label('Items')
                            ->defaultItems(1)
                            ->reorderable()
                            ->reorderableWithButtons()
                            ->reorderableWithDragAndDrop(false)
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->schema([
                                Forms\Components\Select::make('item_id')
                                    ->options(function (Get $get): ?array {
                                        $campaign_id = $get('../../campaign_id');
                                        if ($campaign_id === null) {
                                            return [];
                                        }

                                        return ModelListService::get(
                                            model: Item::query(),
                                            key_field: 'id',
                                            value_field: 'name',
                                            conditions: [['campaign_id' => $campaign_id]]
                                        );
                                    })
                                    ->searchable()
                                    ->preload(10)
                                    ->distinct()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $item = Item::find($state);

                                        if ($item) {
                                            $set('item_price', $item->price);
                                            $set('subtotal', $item->price * $get('quantity'));
                                        }
                                    })
                                    ->createOptionForm([
                                        Forms\Components\Section::make()
                                            ->columns(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->required(),
                                                Forms\Components\TextInput::make('price')
                                                    ->required()
                                                    ->numeric()
                                                    ->inputMode('decimal')
                                                    ->prefix('$'),
                                            ]),
                                    ])
                                    ->createOptionUsing(function (array $data, $get): int {
                                        $data['campaign_id'] = $get('../../campaign_id');

                                        if ($data['campaign_id'] === null) {
                                            Notification::make()
                                                ->title('Invalid Campaign')
                                                ->danger()
                                                ->persistent()
                                                ->body('Please select a campaign to which this campaign belongs.')
                                                ->send();

                                            return 0;
                                        } else {
                                            $item = Item::create($data);

                                            return $item->id;
                                        }
                                    })
                                    ->columnSpan(2)
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $set('subtotal', $state * $get('item_price'));
                                    })
                                    ->formatStateUsing(fn ($state) => (float) $state)
                                    ->minValue(0)
                                    ->default(1)
                                    ->required(),
                                Forms\Components\TextInput::make('item_price')
                                    ->disabled()
                                    ->dehydrated()
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->prefix('$'),
                                Forms\Components\TextInput::make('subtotal')
                                    ->disabled()
                                    ->numeric()
                                    ->dehydrated(false)
                                    ->prefix('$')
                                    ->formatStateUsing(function (?InvoiceItem $record) {
                                        return round($record?->item_price * $record?->quantity, 6);
                                    }),
                            ])
                            ->columns(5),
                    ]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('')
                            ->content(function ($record, $get) {
                                $subtotal = 0;

                                foreach ($get('invoiceItems') as $item) {
                                    $subtotal += $item['subtotal'] ?? 0;
                                }

                                return view('filament.partials.invoice-summary', [
                                    'invoice' => $record,
                                    'subtotal' => $subtotal,
                                ]);
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->copyable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agent.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal_amount')
                    ->numeric()
                    ->money()
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('subtotal_amount') / 100)))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->numeric()
                    ->money()
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('tax_amount') / 100)))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable()
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('total_amount') / 100)))
                    ->money(),
                Tables\Columns\TextColumn::make('total_paid')
                    ->numeric()
                    ->sortable()
                    ->color(Color::Blue)
                    ->formatStateUsing(fn ($state) => $state > 0 ? Number::currency($state) : '')
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('total_paid') / 100))),
                Tables\Columns\TextColumn::make('balance_pending')
                    ->label('Balance')
                    ->numeric()
                    ->color(Color::Red)
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('balance_pending') / 100)))
                    ->formatStateUsing(fn ($state) => $state > 0 ? Number::currency($state * (-1)) : ''),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->badge()
                    ->color(fn ($state) => $state->getColor()),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.client.invoice_template')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('project.invoice_notes')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('project.invoice_terms')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->options(
                        ModelListService::get(
                            model: Project::query(),
                            key_field: 'id',
                            value_field: 'name'
                        )
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('agent_id')
                    ->label('Agent')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(ModelListService::get(
                        model: Agent::query(),
                        key_field: 'id',
                        value_field: 'name'
                    )),
                Tables\Filters\SelectFilter::make('campaign_id')
                    ->label('Campaign')
                    ->options(ModelListService::get(
                        model: Campaign::query(),
                        key_field: 'id',
                        value_field: 'name'
                    ))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(InvoiceStatuses::toArray())
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->modalWidth('7xl')
                        ->stickyModalHeader()
                        ->closeModalByClickingAway(false)
                        ->closeModalByEscaping(),
                    Tables\Actions\Action::make('Pay')
                        ->visible(fn ($record) => $record->balance_pending > 0)
                        ->color(Color::Purple)
                        ->icon('heroicon-s-credit-card')
                        ->form(InvoicePaymentForm::make())
                        ->action(function (array $data, Invoice $record): void {
                            $record->payments()->create($data);
                        }),
                    DownloadInvoiceAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    // Tables\Actions\RestoreBulkAction::make(),

                ]),
                Tables\Actions\ExportBulkAction::make()
                    ->label('Export Selected')
                    // ->icon('heroicon-s-document-download')
                    ->color(Color::Blue)
                    ->deselectRecordsAfterCompletion()
                    ->exporter(InvoiceExporter::class),

                Tables\Actions\BulkAction::make('Pay Fully')
                    ->color(Color::Red)
                    ->icon('heroicon-s-credit-card')
                    // ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->modalDescription(
                        'This action will pay the all selected invoices fully using the pending balance. It only pays invoices with any pending amount. Are you sure?'
                    )
                    // ->checkIfRecordIsSelectableUsing(
                    //     fn (Invoice $record): bool => $record->balance_pending > 0,
                    // )
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('date')
                                    ->required()
                                    ->default(now())
                                    ->maxDate(now()->endOfDay()),
                                Forms\Components\TextInput::make('reference'),
                                Forms\Components\FileUpload::make('images')
                                    ->image()
                                    ->imageEditor()
                                    ->multiple()
                                    ->maxSize(1024)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        foreach ($records as $record) {
                            if ($record->balance_pending > 0) {
                                $record->payments()->create([
                                    'date' => $data['date'],
                                    'images' => $data['images'],
                                    'amount' => $record->balance_pending,
                                    'reference' => 'Paid using Bulk Action',
                                    'description' => 'Paid using Bulk Action',
                                ]);
                            }
                        }

                        Notification::make()
                            ->title('Bulk Payment Successful')
                            ->success()
                            ->body("All {$records->count()} selected invoices have been paid fully.")
                            ->send();
                    }),
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
            // 'view' => Pages\ViewInvoice::route('/{record}'),
            // 'edit' => Pages\EditInvoice::route('/{record}/edit'),
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

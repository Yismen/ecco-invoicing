<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource;
use Filament\Forms;
use App\Models\Item;
use App\Models\Agent;
use App\Models\Project;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Campaign;
use Filament\Forms\Form;
use App\Models\InvoiceItem;
use App\Services\ModelListService;
use Filament\Notifications\Notification;
use App\Rules\UniqueByParentRelationship;
use App\Services\Filament\Forms\ProjectForm;
use App\Services\GenerateInvoiceNumberService;

class InvoiceForm
{
    public static function make(Form $form): Form
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
                                                function($livewire, $record) {
                                                    $parentId = $livewire->data['project_id'] ?? $livewire->mountedActionsData[0]['project_id'] ?? $record->project_id ?? null;

                                                    return new UniqueByParentRelationship(
                                                        table: Agent::class,
                                                        uniqueField: 'name',
                                                        parentField: 'project_id',
                                                        parentId: $parentId,
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
                                        function($livewire, $record) {
                                            $agentId = $livewire->data['agent_id'] ?? $livewire->mountedActionsData[0]['agent_id'] ?? $record->agent_id ?? null;

                                            return new UniqueByParentRelationship(
                                                table: Campaign::class,
                                                uniqueField: 'name',
                                                parentField: 'agent_id',
                                                parentId: $agentId,
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
                                    ->label('Item')
                                    ->relationship('item', 'name')
                                    ->options(function (Get $get): ?array {
                                        $campaign_id = $get('../../campaign_id');
                                        if ($campaign_id === null) {
                                            return [];
                                        }

                                        return ModelListService::get(
                                            model: Item::query(),
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
}

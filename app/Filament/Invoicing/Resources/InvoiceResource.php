<?php

namespace App\Filament\Invoicing\Resources;

use Filament\Forms;
use App\Models\Item;
use Filament\Tables;
use App\Models\Agent;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Campaign;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\InvoiceItem;
use Illuminate\Support\Number;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Actions\PayInvoiceAction;
use App\Services\Filament\Forms\AgentForm;
use App\Services\Filament\Forms\ProjectForm;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Services\Filament\Forms\CampaignForm;
use App\Services\GenerateInvoiceNumberService;
use App\Filament\Actions\DownloadInvoiceAction;
use App\Services\Filament\Forms\InvoicePaymentForm;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Invoicing\Resources\InvoiceResource\Pages;
use Illuminate\Database\Eloquent\Collection;

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
                        ->options(function() : array {
                            return Project::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all();
                        })
                        ->afterStateUpdated(fn(Set $set) => $set('agent_id', null))
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
                        ->options(function(Get $get) : array|null {
                            $project_id = $get('project_id');
                            return Agent::query()
                                ->where('project_id', $project_id)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all();
                        })
                        ->afterStateUpdated(fn(Set $set) => $set('campaign_id', null))
                        ->required()
                        ->searchable()
                        ->disabled(fn(Get $get) => ! $get('project_id'))
                        ->createOptionForm(AgentForm::make())
                        ->createOptionModalHeading('Create Agent')
                        ->preload(10)
                        ->columnSpan(2)
                        ->live(),
                    Forms\Components\Select::make('campaign_id')
                        ->relationship('campaign', 'name')
                        ->required()
                        ->searchable()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->autofocus()
                                ->required()
                                ->unique(
                                    table: Campaign::class,
                                    column: 'name',
                                    ignorable: fn (Get $get) => $get('campaign_id') ? Campaign::find($get('campaign_id')) : null
                                )
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
                        ->options(function(Get $get) : array|null {
                            $agent_id = $get('agent_id');
                            return Campaign::query()
                                ->orderBy('name')
                                ->where('agent_id', $agent_id)
                                ->pluck('name', 'id')
                                ->all();
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
               Forms\Components\Section::make()
                    ->visible(fn($record) => $record)
                    ->schema([
                        Forms\Components\Placeholder::make('')
                            ->content(function($record) {
                                $record->load(['project', 'agent', 'campaign', 'items']);

                                return view('filament.partials.invoice-company-details', [
                                    'invoice' => $record
                                ]);
                            }),
                    ]),

               Forms\Components\Section::make()
                    ->visible(fn($record) => $record === null)
                    ->inlineLabel()
                    ->schema([
                        Forms\Components\Placeholder::make('New Invoice Numbrer')
                            ->content(function(Get $get) {
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
                            ->visible(fn($get) =>  $get('campaign_id'))
                            ->label('Items')
                            ->defaultItems(1)
                            ->reorderable()
                            ->reorderableWithButtons()
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->schema([
                                Forms\Components\Select::make('item_id')
                                    ->options(function(Get $get) : array|null {
                                        $campaign_id = $get('../../campaign_id');

                                        return Cache::rememberForever(
                                            'campaign_items_' . $campaign_id,
                                            function() use ($campaign_id) {
                                                $campaign = Campaign::find($campaign_id)->load(['items']);

                                                return $campaign?->items?->pluck('name', 'id')->toArray();
                                            }
                                        );
                                    })
                                    ->searchable()
                                    ->preload(10)
                                    ->distinct()
                                    ->afterStateUpdated(function($state, Get $get, Set $set) {
                                        $item = Item::find($state);

                                        if($item) {
                                            $set('item_price', $item->price);
                                            $set('subtotal', $item->price * (float) $get('quantity'));
                                        }
                                    } )
                                    ->createOptionForm([
                                        Forms\Components\Section::make()
                                            ->columns(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->required(),
                                                Forms\Components\TextInput::make('price')
                                                    ->minValue(0)
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('$'),
                                            ])
                                    ])
                                    ->createOptionUsing(function (array $data, $get): int {
                                        $data['campaign_id'] = $get('../../campaign_id');
                                        // dd($action);

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
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function($state, Set $set, Get $get) {
                                        $set('subtotal', $state * $get('item_price'));
                                    })
                                    ->minValue(0)
                                    ->default(1)
                                    ->required(),
                                Forms\Components\TextInput::make('item_price')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('subtotal')
                                    ->disabled()
                                    ->formatStateUsing(function(?InvoiceItem $record) {
                                        $subtotal = $record?->item_price * $record?->quantity;

                                        return Number::currency($subtotal);
                                    })
                                ,
                            ])
                            ->columns(5),
                    ]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('')
                            ->content(function($record, $get) {
                                $subtotal = 0;

                                foreach($get('invoiceItems') as $item) {
                                    $subtotal += (float)$item['subtotal'] ?? 0;
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
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal_amount')
                    ->numeric()
                    ->money()
                    ->summarize(Sum::make())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->numeric()
                    ->money()
                    ->summarize(Sum::make())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make())
                    ->money(),
                Tables\Columns\TextColumn::make('total_paid')
                    ->numeric()
                    ->sortable()
                    ->color(Color::Blue)
                    ->formatStateUsing(fn ($state) => $state > 0 ? Number::currency($state) : '')
                    ->summarize(Sum::make()),
                Tables\Columns\TextColumn::make('balance_pending')
                    ->label('Balance')
                    ->numeric()
                    ->color(Color::Red)
                    ->summarize(Sum::make())
                    ->formatStateUsing(fn ($state) => $state > 0 ? Number::currency($state * (-1)) : ''),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn($state) => $state->getLabel())
                    ->badge()
                    ->color(fn($state) => $state->getColor())
                    ,
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
                    ->options(Project::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                ),
                Tables\Filters\SelectFilter::make('agent_id')
                    ->label('Agent')
                    ->options(Agent::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('campaign_id')
                    ->label('Campaign')
                    ->options(Campaign::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('Pay')
                        ->visible(fn($record) => $record->balance_pending > 0)
                        ->color(Color::Purple)
                        ->icon('heroicon-s-credit-card')
                        ->form(InvoicePaymentForm::make())
                        ->action(function (array $data, Invoice $record): void {
                            $record->payments()->create($data);
                        }),
                    DownloadInvoiceAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    // Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('Pay Fully')
                        ->color(Color::Red)
                        ->icon('heroicon-s-credit-card')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->modalDescription(
                            'This action will pay the all selected invoices fully using the pending balance. Are you sure?'
                        )
                        // ->form(InvoicePaymentForm::make())
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                if ($record->balance_pending > 0) {
                                    $record->payments()->create([
                                        'date' => now(),
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
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Invoice $record): bool => $record->balance_pending > 0,
            );
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

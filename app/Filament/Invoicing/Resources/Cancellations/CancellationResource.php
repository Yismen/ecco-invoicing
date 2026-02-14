<?php

namespace App\Filament\Invoicing\Resources\Cancellations;

use App\Enums\InvoiceStatuses;
use App\Filament\Invoicing\Clusters\InvoicesCluster\InvoicesCluster;
use App\Filament\Invoicing\Resources\Cancellations\Pages\ManageCancellations;
use App\Models\Cancellation;
use App\Models\Invoice;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CancellationResource extends Resource
{
    protected static ?string $model = Cancellation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder-open';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Cancellations';

    protected static ?string $cluster = InvoicesCluster::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('invoice_id')
                    ->searchable()
                    ->preload()
                    ->relationship('invoice', 'number')
                    ->options(
                        Invoice::query()
                            ->whereIn('status', [
                                InvoiceStatuses::Pending,
                                InvoiceStatuses::Overdue,
                            ])
                            ->pluck('number', 'id')
                            ->toArray()
                    )
                    ->required(),
                DatePicker::make('date')
                    ->minDate(now()->subMonths(3))
                    ->maxDate(now())
                    ->default(now())
                    ->required(),
                Textarea::make('comments')
                    ->required()
                    ->minLength(15)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
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
                Filter::make('date')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('from')
                                    ->label('From Date'),
                                DatePicker::make('to')
                                    ->label('To Date')
                                    ->maxDate(now()),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if ($data['from']) {
                            $query->whereDate('date', '>=', $data['from']);
                        }

                        if ($data['to']) {
                            $query->whereDate('date', '<=', $data['to']);
                        }
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'From '.date('M j, Y', strtotime($data['from']));
                        }

                        if ($data['to'] ?? null) {
                            $indicators['to'] = 'To '.date('M j, Y', strtotime($data['to']));
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->label('Restore')
                    ->color(Color::Green)
                    ->icon(Heroicon::OutlinedArrowDown)
                    ->modalHeading('Remove Cancellation')
                    ->modalDescription('Are you sure you want to remove this cancellation and restore this invoice?')
                    ->modalSubmitActionLabel('Restore'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCancellations::route('/'),
        ];
    }
}

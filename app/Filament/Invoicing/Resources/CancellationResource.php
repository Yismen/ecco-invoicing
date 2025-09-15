<?php

namespace App\Filament\Invoicing\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\InvoiceStatuses;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use App\Models\Cancellation;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Invoicing\Resources\CancellationResource\Pages;

class CancellationResource extends Resource
{
    protected static ?string $model = Cancellation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 7;

    protected static ?string $label = 'Cancellations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('invoice_id')
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
                Forms\Components\DatePicker::make('date')
                    ->minDate(now()->subMonths(3))
                    ->maxDate(now())
                    ->default(now())
                    ->required(),
                Forms\Components\Textarea::make('comments')
                    ->required()
                    ->minLength(15)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice.number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
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
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('from')
                                ->label('From Date'),
                            Forms\Components\DatePicker::make('to')
                                ->label('To Date')
                                ->maxDate(now()),
                        ])
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
                            $indicators['from'] = 'From ' . date('M j, Y', strtotime($data['from']));
                        }

                        if ($data['to'] ?? null) {
                            $indicators['to'] = 'To ' . date('M j, Y', strtotime($data['to']));
                        }

                        return $indicators;
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Restore')
                    ->color(Color::Green)
                    ->icon('heroicon-s-arrow-path')
                    ->modalHeading('Remove Cancellation')
                    ->modalDescription('Are you sure you want to remove this cancellation and restore this invoice?')
                    ->modalSubmitActionLabel('Restore'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCancellations::route('/'),
        ];
    }
}

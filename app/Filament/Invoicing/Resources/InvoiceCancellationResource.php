<?php

namespace App\Filament\Invoicing\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\InvoiceStatuses;
use Filament\Resources\Resource;
use App\Models\InvoiceCancellation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Invoicing\Resources\InvoiceCancellationResource\Pages;
use App\Filament\Invoicing\Resources\InvoiceCancellationResource\RelationManagers;

class InvoiceCancellationResource extends Resource
{
    protected static ?string $model = InvoiceCancellation::class;

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
                Forms\Components\DatePicker::make('cancellation_date')
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
                Tables\Columns\TextColumn::make('cancellation_date')
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
                Tables\Filters\Filter::make('cancellation_date')
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
                            $query->whereDate('cancellation_date', '>=', $data['from']);
                        }

                        if ($data['to']) {
                            $query->whereDate('cancellation_date', '<=', $data['to']);
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInvoiceCancellations::route('/'),
        ];
    }
}

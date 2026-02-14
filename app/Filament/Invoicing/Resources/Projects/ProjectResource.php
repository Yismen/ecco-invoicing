<?php

namespace App\Filament\Invoicing\Resources\Projects;

use App\Filament\Invoicing\Resources\ProjectResource\Pages;
use App\Filament\Invoicing\Resources\Projects\Pages\ListProjects;
use App\Models\Project;
use App\Services\Filament\Forms\ProjectForm;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    // protected static ?string $navigationGroup = 'Invoicing';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(ProjectForm::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('client.name')
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('address')
                    ->limit(50)
                    ->searchable()
                    ->html()
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('invoice_net_days')
                    ->wrapHeader()
                    ->sortable(),
                TextColumn::make('tax_rate')
                    ->wrapHeader()
                    ->sortable()
                    ->label('Tax Rate (%)')
                    ->formatStateUsing(fn ($state) => $state * 100),
                TextColumn::make('invoice_notes')
                    ->wrapHeader()
                    ->searchable()
                    ->limit(50)
                    ->html()
                    ->wrap()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('invoice_terms')
                    ->limit(50)
                    ->html()
                    ->wrap()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('agents_count')
                    ->wrapHeader()
                    ->counts('agents')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('invoices_count')
                    ->wrapHeader()
                    ->counts('invoices')
                    ->sortable()
                    ->toggleable(),
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
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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
            'index' => ListProjects::route('/'),
            // 'create' => Pages\CreateProject::route('/create'),
            // 'edit' => Pages\EditProject::route('/{record}/edit'),
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

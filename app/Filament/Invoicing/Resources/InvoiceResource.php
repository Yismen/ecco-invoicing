<?php

namespace App\Filament\Invoicing\Resources;

use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Invoicing\Resources\InvoiceResource\Pages;

class InvoiceResource extends Resource
{

    protected static ?string $model = Invoice::class;

    // protected static ?string $navigationGroup = 'Invoicing';

    protected static ?string $cluster = \App\Filament\Invoicing\Clusters\InvoicesCluster::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return \App\Filament\Invoicing\Resources\InvoiceResource\InvoiceForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Invoicing\Resources\InvoiceResource\InvoiceTable::make($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Invoicing\Resources\InvoiceResource\RelationManagers\PaymentsRelationManager::class,
            \App\Filament\Invoicing\Resources\InvoiceResource\RelationManagers\CancellationsRelationManager::class,
            \App\Filament\Invoicing\Resources\InvoiceResource\RelationManagers\ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            // 'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            // 'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('project.client', 'agent', 'campaign', 'cancellation')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

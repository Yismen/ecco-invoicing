<?php

namespace App\Filament\Invoicing\Resources\Invoices;

use App\Filament\Invoicing\Clusters\InvoicesCluster\InvoicesCluster;
use App\Filament\Invoicing\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Invoicing\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Invoicing\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Invoicing\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Invoicing\Resources\Invoices\RelationManagers\ActivitiesRelationManager;
use App\Filament\Invoicing\Resources\Invoices\RelationManagers\CancellationsRelationManager;
use App\Filament\Invoicing\Resources\Invoices\RelationManagers\PaymentsRelationManager;
use App\Models\Invoice;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    // protected static ?string $navigationGroup = 'Invoicing';

    protected static ?string $cluster = InvoicesCluster::class;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoiceTable::make($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
            CancellationsRelationManager::class,
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            // 'create' => CreateInvoice::route('/create'),
            'view' => ViewInvoice::route('/{record}'),
            // 'edit' => EditInvoice::route('/{record}/edit'),
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

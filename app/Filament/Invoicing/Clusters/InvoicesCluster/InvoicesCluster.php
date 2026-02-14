<?php

namespace App\Filament\Invoicing\Clusters\InvoicesCluster;

use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class InvoicesCluster extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Invoices';

    protected static ?int $navigationSort = 6;
}

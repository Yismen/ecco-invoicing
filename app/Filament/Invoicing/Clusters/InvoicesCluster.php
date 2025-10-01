<?php

namespace App\Filament\Invoicing\Clusters;

use Filament\Clusters\Cluster;

class InvoicesCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Invoices';

    protected static ?int $navigationSort = 6;
}

<?php

namespace App\Filament\Invoicing\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.invoicing.pages.dashboard';

    protected static ?int $navigationSort = -1;
}

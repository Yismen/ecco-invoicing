<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenerateInvoiceController;
use Jeffgreco13\FilamentBreezy\Livewire\SanctumTokens;

Route::get('/', function () {
    return view('welcome');
});

Route::get('generate-invoice/{invoice}', GenerateInvoiceController::class)
    ->middleware('auth', 'can:view-invoice')
    ->name('generate-invoice');

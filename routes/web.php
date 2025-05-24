<?php

use App\Http\Controllers\GenerateInvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('generate-invoice/{invoice}', GenerateInvoiceController::class)
    ->middleware('auth', 'can:view-invoice')
    ->name('generate-invoice');

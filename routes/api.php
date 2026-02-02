<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\InvoiceController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/invoices/outstanding', [InvoiceController::class, 'outstanding'])->name('api.v1.invoices.outstanding');
});

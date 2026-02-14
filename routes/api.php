<?php

use App\Http\Controllers\Api\V1\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/invoices/outstanding', [InvoiceController::class, 'outstanding'])->name('api.v1.invoices.outstanding');
});

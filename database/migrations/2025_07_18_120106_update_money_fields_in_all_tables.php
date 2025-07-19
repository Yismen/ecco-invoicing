<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('price', 24, 16)->change();
        });

        Schema::table('invoice_item', function (Blueprint $table) {
            $table->decimal('item_price', 24, 16)->default(0)->change();
            $table->decimal('quantity', 24, 16)->default(0)->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('subtotal_amount', 24, 16)->default(0)->change();
            $table->decimal('tax_amount', 24, 16)->default(0)->change();
            $table->decimal('total_amount', 24, 16)->default(0)->change();
            $table->decimal('total_paid', 24, 16)->default(0)->change();
            $table->decimal('balance_pending', 24, 16)->default(0)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 24, 16)->change();
        });
    }
};

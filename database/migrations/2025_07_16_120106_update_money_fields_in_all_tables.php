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
            $table->decimal('price', 24, 18)->change();
        });

        Schema::table('invoice_item', function (Blueprint $table) {
            $table->decimal('item_price', 24, 18)->nullable()->change();
            $table->decimal('quantity', 24, 16)->nullable()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 24, 18)->nullable()->change();
        });
    }
};

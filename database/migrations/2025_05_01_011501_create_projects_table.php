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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignIdFor(\App\Models\Client::class)->constrained();
            $table->text('address');
            $table->integer('invoice_net_days')->default(0)->unsigned();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->double('tax_rate')->default(0);
            $table->string('invoice_notes')->nullable();
            $table->string('invoice_terms')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('name')->change();
            $table->dropUnique('campaigns_name_unique');
        });
        Schema::table('agents', function (Blueprint $table) {
            $table->string('name')->change();
            $table->dropUnique('agents_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};

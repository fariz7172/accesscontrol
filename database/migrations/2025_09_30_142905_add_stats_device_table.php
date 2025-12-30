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
        Schema::table('devicegate', function (Blueprint $table) {
            $table->tinyInteger('stat')->default(0)->comment('0=IN, 1=Out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devicegate', function (Blueprint $table) {
            $table->dropColumn('stat');
        });
    }
};

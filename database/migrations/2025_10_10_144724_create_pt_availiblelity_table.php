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
        Schema::create('pt_availability', function (Blueprint $table) {
            $table->id('ID');
            $table->foreignId('PT_ID');
            $table->tinyInteger('DOW')->unsigned()->comment('1=Senin ... 7=Minggu');
            $table->dateTime('START_TIME');
            $table->dateTime('END_TIME');
            $table->tinyInteger('IS_ACTIVE')->unsigned()->default(1)->comment('0=Nonaktif, 1=Aktif');
            $table->index('PT_ID', 'FK_PT_AVAILABILITY_PT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pt_availability');
    }
};

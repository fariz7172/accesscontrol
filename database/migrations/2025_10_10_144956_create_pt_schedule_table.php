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
        Schema::create('pt_schedule', function (Blueprint $table) {
            $table->id('ID');
            $table->foreignId('PT_ID');
            $table->foreignId('MEMBER_ID');
            $table->dateTime('START_TIME');
            $table->dateTime('END_TIME');
            $table->dateTime('BOOKED_AT')->comment('Tanggal & waktu booking dibuat');
            $table->tinyInteger('STATUS')->default(0)->comment('0 = booked, 1 = selesai, 2 = batal');
            $table->string('NOTE', 255)->nullable();
            $table->dateTime('CREATED_AT');
            $table->index('PT_ID', 'FK_PT_ID');
            $table->index('MEMBER_ID', 'FK_MEMBER_ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pt_schedule');
    }
};
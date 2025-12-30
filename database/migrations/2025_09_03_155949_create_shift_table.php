<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift', function (Blueprint $table) {
            $table->id();
            $table->string('ShiftNo');
            $table->string('ShiftName');
            $table->time('Begin_Time')->nullable();
            $table->time('Start_In')->nullable();
            $table->time('Range_In')->nullable();
            $table->time('Break_Time')->nullable();
            $table->time('Start_Break')->nullable();
            $table->time('Range_Break')->nullable();
            $table->time('Resume_Time')->nullable();
            $table->time('Start_Resume')->nullable();
            $table->time('Range_Resume')->nullable();
            $table->time('Out_time')->nullable();
            $table->time('Start_Out')->nullable();
            $table->time('Range_Out')->nullable();
            $table->integer('Tipe')->nullable();
            $table->integer('DDay')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift');
    }
};

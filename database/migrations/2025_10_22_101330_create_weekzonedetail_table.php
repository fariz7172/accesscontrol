<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekzonedetail', function (Blueprint $table) {
            $table->integer('ID')->primary();
            $table->integer('wz')->nullable()->comment('relasi ke weekzone id');
            $table->integer('day1')->default(0);
            $table->integer('day2')->default(0);
            $table->integer('day3')->default(0);
            $table->integer('day4')->default(0);
            $table->integer('day5')->default(0);
            $table->integer('day6')->default(0);
            $table->integer('day7')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekzonedetail');
    }
};

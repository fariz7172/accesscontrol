<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dayzonedetail', function (Blueprint $table) {
            $table->integer('ID')->primary();
            $table->integer('dz')->nullable()->comment('relasi ke dayzoneid');
            $table->time('Stz1')->default('00:00:00');
            $table->time('Etz1')->default('00:00:00');
            $table->time('Stz2')->default('00:00:00');
            $table->time('Etz2')->default('00:00:00');
            $table->time('Stz3')->default('00:00:00');
            $table->time('Etz3')->default('00:00:00');
            $table->time('Stz4')->default('00:00:00');
            $table->time('Etz4')->default('00:00:00');
            $table->time('Stz5')->default('00:00:00');
            $table->time('Etz5')->default('00:00:00');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dayzonedetail');
    }
};

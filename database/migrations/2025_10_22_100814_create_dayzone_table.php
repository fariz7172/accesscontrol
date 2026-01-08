<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dayzone', function (Blueprint $table) {
            $table->integer('ID')->primary();
            $table->string('Name', 15)->default('');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dayzone');
    }
};

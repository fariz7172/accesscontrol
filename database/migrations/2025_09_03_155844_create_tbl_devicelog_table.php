<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('devicelog', function (Blueprint $table) {
            $table->id();
            $table->dateTime('log_date');
            $table->string('modul')->nullable();
            $table->text('desc')->nullable();
            $table->string('status')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devicelog');
    }
    
};

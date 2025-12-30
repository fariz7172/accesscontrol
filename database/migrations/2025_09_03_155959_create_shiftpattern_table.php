<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('shiftpattern', function (Blueprint $table) {
            $table->id('Id');
            $table->string('PatternName');
            $table->string('PatternType')->nullable();
            $table->foreignId('pola1')->nullable()->constrained('shift')->onDelete('set null');
            $table->foreignId('pola2')->nullable()->constrained('shift')->onDelete('set null');
            $table->foreignId('pola3')->nullable()->constrained('shift')->onDelete('set null');
            $table->foreignId('pola4')->nullable()->constrained('shift')->onDelete('set null');
            $table->foreignId('pola5')->nullable()->constrained('shift')->onDelete('set null');
            $table->foreignId('pola6')->nullable()->constrained('shift')->onDelete('set null');
            $table->foreignId('pola7')->nullable()->constrained('shift')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shiftpattern');
    }
    
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('devicegate', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number');
            $table->string('flagstatus')->nullable();
            $table->string('type')->nullable();
            $table->string('sn')->unique();
            $table->string('ip')->nullable();
            $table->string('nodeid')->unique()->nullable();
            $table->text('description')->nullable();
            $table->foreignId('groupid')->nullable()->constrained('devicegroup')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devicegate');
    }
    
};

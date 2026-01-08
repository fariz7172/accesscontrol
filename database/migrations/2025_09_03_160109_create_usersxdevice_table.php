<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usersxdevice', function (Blueprint $table) {
            $table->id();
            $table->string('userId');
            $table->foreignId('gateId')->constrained('devicegate')->onDelete('cascade');
            $table->foreign('userId')->references('ID')->on('usersprofile')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usersxdevice');
    }
    
};

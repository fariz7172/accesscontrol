<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('leavetype', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name');
            $table->string('Type')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leavetype');
    }
    
};

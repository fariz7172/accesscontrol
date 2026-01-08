<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaveprocess', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('leaveid')->constrained('leavetype', 'Id')->onDelete('cascade');
            $table->string('EmplID');
            $table->date('FromDate');
            $table->date('ToDate');
            $table->text('Notes')->nullable();
            $table->foreign('EmplID')->references('ID')->on('usersprofile')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaveprocess');
    }
    
};

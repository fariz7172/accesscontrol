<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usersprofile', function (Blueprint $table) {
            $table->string('ID')->primary();
            $table->string('NAME');
            $table->string('PIN')->nullable();
            $table->integer('DOOR_GRP')->nullable(); // Removed length and auto_increment
            $table->dateTime('BEGIN_DATE')->nullable();
            $table->dateTime('END_DATE')->nullable();
            $table->date('BIRTHDAY')->nullable();
            $table->foreignId('Depid')->nullable()->constrained('departemen')->onDelete('set null');
            $table->string('MemberNo')->nullable();
            $table->foreignId('Branchid')->nullable()->constrained('branch')->onDelete('set null');
            $table->binary('photo')->nullable();
            $table->string('Card')->nullable();
            $table->string('NoIdentitas')->nullable();
            $table->string('timezone')->nullable();
            $table->integer('shiftpatternID')->nullable(); // Removed length and auto_increment
            $table->string('PASSWORD', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usersprofile');
    }
};

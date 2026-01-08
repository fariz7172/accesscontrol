<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('attend', function (Blueprint $table) {
            $table->id();
            $table->string('EmployeeID');
            $table->date('AttDate');
            $table->foreignId('PatternID')->nullable()->constrained('shiftpattern')->onDelete('set null');
            $table->foreignId('ShiftCode')->nullable()->constrained('shift')->onDelete('set null');
            $table->string('DayType')->nullable();
            $table->time('Time_In')->nullable();
            $table->time('Time_Break')->nullable();
            $table->time('Time_Resume')->nullable();
            $table->time('Time_Out')->nullable();
            $table->string('Time_InShort')->nullable();
            $table->string('Time_BreakShort')->nullable();
            $table->string('Time_ResumeShort')->nullable();
            $table->string('Time_OutShort')->nullable();
            $table->integer('WorkTime')->nullable();
            $table->integer('EarlyWork')->nullable();
            $table->integer('TotalWorkHour')->nullable();
            $table->integer('TotalOT')->nullable();
            $table->string('Remark')->nullable();
            $table->foreignId('DutyProcessID')->nullable()->constrained('leavetype', 'Id')->onDelete('set null');
            $table->boolean('Present')->default(false);
            $table->foreign('EmployeeID')->references('ID')->on('usersprofile')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attend');
    }
    
};

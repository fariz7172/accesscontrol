<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('attendsummary', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Period');
            $table->date('StartPeriod');
            $table->date('EndPeriod');
            $table->string('EmployeeID');
            $table->integer('WorkingDays')->nullable();
            $table->integer('Present')->nullable();
            $table->integer('Absent')->nullable();
            $table->integer('LateIn')->nullable();
            $table->integer('EarlyOut')->nullable();
            $table->integer('LateInMinute')->nullable();
            $table->integer('EarlyOutMinute')->nullable();
            $table->integer('TotalWorktime')->nullable();
            $table->integer('TotalWorkHour')->nullable();
            $table->integer('OT')->nullable();
            $table->integer('OTMinute')->nullable();
            $table->integer('EarlyWork')->nullable();
            $table->integer('EarlyWorkMinute')->nullable();
            $table->integer('Ncheckin')->nullable();
            $table->integer('Ncheckout')->nullable();
            $table->integer('LeaveTaken')->nullable();
            $table->integer('D1')->nullable();
            $table->integer('D2')->nullable();
            $table->integer('D3')->nullable();
            $table->integer('D4')->nullable();
            $table->integer('D5')->nullable();
            $table->integer('D6')->nullable();
            $table->integer('D7')->nullable();
            $table->integer('D8')->nullable();
            $table->integer('D9')->nullable();
            $table->integer('D10')->nullable();
            $table->integer('D11')->nullable();
            $table->integer('D12')->nullable();
            $table->integer('D13')->nullable();
            $table->integer('D14')->nullable();
            $table->integer('D15')->nullable();
            $table->integer('D16')->nullable();
            $table->integer('D17')->nullable();
            $table->integer('D18')->nullable();
            $table->integer('D19')->nullable();
            $table->integer('D20')->nullable();
            $table->foreign('EmployeeID')->references('ID')->on('usersprofile')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendsummary');
    }
    
};

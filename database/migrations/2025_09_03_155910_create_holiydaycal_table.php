<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('holidaycal', function (Blueprint $table) {
            $table->id('ID');
            $table->string('Name');
            $table->date('StartDate');
            $table->date('EndDate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidaycal');
    }
    
};

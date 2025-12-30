<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('devicegroup_devicegate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devicegroup_id')->constrained('devicegroup')->onDelete('cascade');
            $table->foreignId('devicegate_id')->constrained('devicegate')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devicegroup_devicegate');
    }

};

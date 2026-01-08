<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekzoneuser', function (Blueprint $table) {
            $table->id();
            $table->integer('userid')->nullable()->comment('User id');
            $table->integer('wzid')->nullable()->comment('weekzone Id');
            $table->integer('deviceid')->nullable()->comment('Device id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekzoneuser');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tbl_userlog', function (Blueprint $table) {
            $table->id();
            $table->string('USER_ADDR');
            $table->dateTime('TM_EVENT');
            $table->string('DEVICESN')->nullable();
            $table->string('IMGPATH')->nullable();
            $table->integer('devicetype')->nullable();
            $table->string('card')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_userlog');
    }
};

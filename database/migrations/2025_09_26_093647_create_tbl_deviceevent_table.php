<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_deviceevent', function (Blueprint $table) {
            $table->dateTime('TM_EVENT');
            $table->string('DEVICESN', 15)->default('');
            $table->string('MODELNAME', 30)->default('');
            $table->integer('useduser')->nullable();
            $table->integer('usedfp')->nullable();
            $table->integer('usedcard')->nullable();
            $table->integer('usedpwd')->nullable();
            $table->integer('usedlog')->nullable();
            $table->integer('usednewlog')->nullable();
            $table->string('firmware', 30)->default('');
            $table->dateTime('devicetime')->nullable();
            $table->integer('status')->nullable();
            $table->integer('lastsync')->nullable();

            // Menambahkan primary key gabungan
            $table->primary(['TM_EVENT', 'DEVICESN']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_deviceevent');
    }
};

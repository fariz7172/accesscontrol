<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_usersdata', function (Blueprint $table) {
            $table->string('fid')->primary();
            $table->string('Type')->nullable();
            $table->string('Picture')->nullable();
            $table->text('FaceNo')->nullable(); // Ubah dari varchar(9000) ke text
            $table->text('Fp')->nullable();     // Ubah dari varchar(9000) ke text
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_usersdata');
    }
};

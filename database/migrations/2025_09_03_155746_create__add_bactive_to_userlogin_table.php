<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('userlogin', function (Blueprint $table) {
            $table->text('bactive')->nullable();
        });
    }

    public function down()
    {
        Schema::table('userlogin', function (Blueprint $table) {
            $table->dropColumn('bactive');
        });
    }
};

// ('{"user": false, "device": false, "log": true, "setting": false}')
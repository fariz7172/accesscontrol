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
        Schema::table('usersprofile', function (Blueprint $table) {
            $table->enum('user_type', ['0', '1', '2'])->default('0')->after('NoIdentitas')->comment('0=Staff/User, 1=Member, 2=Personal Trainer');
        });
    }

    public function down()
    {
        Schema::table('usersprofile', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });
    }
};
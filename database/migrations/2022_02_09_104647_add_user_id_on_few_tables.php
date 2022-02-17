<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdOnFewTables extends Migration
{
    public $listTables = [
        'activities',
        'histories',
        'media_galleries',
        'point_transactions',
        'settings',
        'categories',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach($this->listTables as $tablename) {
            Schema::table($tablename, function(Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach($this->listTables as $tablename) {
            Schema::table($tablename, function(Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
}

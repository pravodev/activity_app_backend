<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPointSystemActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', function(Blueprint $table) {
            $table->bigInteger('bonus_value')->nullable();
            $table->bigInteger('penalty_value')->nullable();
            $table->bigInteger('point_weight')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', function(Blueprint $table){
            $table->dropColumn('bonus_value');
            $table->dropColumn('penalty_value');
            $table->dropColumn('point_weight');
        });
    }
}

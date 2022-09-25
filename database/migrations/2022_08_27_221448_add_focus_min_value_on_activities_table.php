<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFocusMinValueOnActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', function(Blueprint $table){
            if(!Schema::hasColumn('activities', 'focus_min_value')) {
                $table->unsignedInteger('focus_min_value')->after('is_focus_enabled')->default(1);
            }
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
            if(Schema::hasColumn('activities', 'focus_min_value')) {
                $table->dropColumn('focus_min_value');
            }
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMediaOnActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', function(Blueprint $table) {
            if(!Schema::hasColumn('activities', 'is_media_enabled')) {
                $table->boolean('is_media_enabled')->default(0);
            }

            if(!Schema::hasColumn('activities', 'media_type')) {
                $table->string('media_type')->nullable()->comment('image / video');
            }

            if(!Schema::hasColumn('activities', 'media_file')) {
                $table->string('media_file')->nullable();
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
        Schema::table('activities', function(Blueprint $table) {
            $table->dropColumn('is_media_enabled');
            $table->dropColumn('media_type');
            $table->dropColumn('media_file');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointFocusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_focus', function (Blueprint $table) {
            $table->id();
            $table->integer('activity_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('repeated_days_count');
            $table->integer('point');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('point_focus');
    }
}

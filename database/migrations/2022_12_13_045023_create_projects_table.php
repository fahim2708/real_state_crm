<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->integer('project_no')->nullable();
            $table->string('name')->nullable();
            $table->string('road_no')->nullable();
            $table->string('face_direction')->nullable();
            $table->string('location')->nullable();
            $table->integer('total_number_of_floor')->nullable();
            $table->integer('number_of_flat_or_plot')->nullable();
            $table->integer('land_size')->nullable();
            $table->date('work_start_date')->nullable();
            $table->date('work_end_date')->nullable();
            $table->date('work_complete_date')->nullable();
            $table->integer('type')->nullable();
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
        Schema::dropIfExists('projects');
    }
};

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
        Schema::create('flat_or_plots', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('project_id')->nullable();
            $table->string('file_no')->nullable();
            $table->string('flat_number')->nullable();
            $table->string('floor_no')->nullable();
            $table->string('plot_no')->nullable();
            $table->string('face_direction')->nullable();
            $table->string('size')->nullable();
            $table->integer('status')->default(0);
            $table->date('booking_date')->nullable();
            $table->integer('is_active')->default(1);
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
        Schema::dropIfExists('flat_or_plots');
    }
};

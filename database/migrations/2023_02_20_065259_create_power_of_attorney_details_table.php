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
        Schema::create('power_of_attorney_details', function (Blueprint $table) {
            $table->id();
            $table->date('registration_date');
            $table->string('sub_deed_no')->nullable();
            $table->string('land_size')->nullable();
            $table->string('mouza_name')->nullable();
            $table->string('cs_daag_no')->nullable();
            $table->string('sa_daag_no')->nullable();
            $table->string('rs_daag_no')->nullable();
            $table->string('bs_daag_no')->nullable();
            $table->string('cs_khatian_no')->nullable();
            $table->string('sa_khatian_no')->nullable();
            $table->string('rs_khatian_no')->nullable();
            $table->string('bs_khatian_no')->nullable();
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
        Schema::dropIfExists('power_of_attorney_details');
    }
};

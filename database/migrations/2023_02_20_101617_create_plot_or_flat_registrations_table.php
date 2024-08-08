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
        Schema::create('plot_or_flat_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('plot_or_flat_detailes_id')->nullable();
            $table->string('mutation_detailes_id')->nullable();
            $table->string('power_of_attorney_details_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->integer('flat_or_plots_id')->nullable();
            $table->string('project_type')->nullable();
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
        Schema::dropIfExists('plot_or_flat_registrations');
    }
};

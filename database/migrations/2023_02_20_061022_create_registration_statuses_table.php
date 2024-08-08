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
        Schema::create('registration_statuses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('flat_or_plot_id');
            $table->integer('flat_or_plot_registration_status')->nullable();
            $table->integer('mutation_cost_status')->nullable();
            $table->integer('power_of_attorney_cost_status')->nullable();
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
        Schema::dropIfExists('registration_statuses');
    }
};

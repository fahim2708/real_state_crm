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
        Schema::create('registration_amounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('flat_or_plot_id');
            $table->integer('registry_amount')->default(0);
            $table->date('registry_amount_schedule_date')->nullable();
            $table->integer('registry_payment')->default(0);
            $table->integer('mutation_cost_amount')->default(0);
            $table->date('mutation_cost_schedule_date')->nullable();
            $table->integer('mutation_cost_payment')->default(0);
            $table->integer('power_of_attorney_cost_amount')->default(0);
            $table->date('power_of_attorney_cost_schedule_date')->nullable();
            $table->integer('power_of_attorney_cost_payment')->default(0);
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
        Schema::dropIfExists('registration_amounts');
    }
};

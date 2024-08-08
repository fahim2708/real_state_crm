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
        Schema::create('price_information', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('flat_or_plot_id');
            $table->integer('project_type')->nullable();

            $table->integer('total_money')->default(0);
            $table->integer('booking_money')->default(0);
            $table->date('booking_money_date')->nullable();
            $table->integer('car_parking')->default(0);
            $table->date('car_parking_date')->nullable();
            $table->integer('utility_charge')->default(0);
            $table->date('utility_charge_date')->nullable();
            $table->integer('additional_work_amount')->default(0);
            $table->date('additional_work_amount_date')->nullable();
            $table->integer('total_installment_amount')->default(0);
            $table->integer('per_month_installment_amount')->default(0);
            $table->integer('number_of_installment')->default(0);
            $table->integer('total_downpayment_amount')->default(0);

            $table->integer('total_booking_money_paid')->default(0);
            $table->integer('total_car_parking_paid')->default(0);
            $table->integer('total_utility_charge_paid')->default(0);
            $table->integer('total_additional_work_amount_paid')->default(0);
            $table->integer('total_installment_amount_paid')->default(0);
            $table->integer('per_month_installment_amount_paid')->default(0);
            $table->integer('total_downpayment_amount_paid')->default(0);

            $table->integer('isActive')->default(1);

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
        Schema::dropIfExists('price_information');
    }
};

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
        Schema::create('after_reg_canceled_customers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('flat_or_plot_id');
            $table->string('new_address')->nullable(); 
            $table->date('canceled_application_date')->nullable();
            $table->integer('total_amount')->default(0);
            $table->integer('original_amount')->default(0);
            $table->integer('extra_amount')->default(0); 
            $table->date('canceled_file_reg_date')->nullable();  
            $table->string('canceled_file_reg_deed_no')->nullable(); 
            $table->string('canceled_file_reg_land_size')->nullable(); 
            $table->date('canceled_payment_start_date')->nullable();
            $table->string('authorized_person_name')->nullable(); 
            $table->string('authorized_phone_number')->nullable();
            $table->string('description')->nullable();
            $table->integer('total_canceled_amount_paid')->default(0);
            $table->date('buy_back_date')->nullable();
            $table->string('buy_back_deed_no')->nullable();
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
        Schema::dropIfExists('after_reg_canceled_customers');
    }
};

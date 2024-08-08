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
        Schema::create('cancel__customer__payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cancel_customer_id');
            $table->string('new_address')->nullable(); 
            $table->date('payment_date')->nullable();
            $table->integer('payment_amount')->default(0);          
            $table->string('amount_in_words')->nullable();                      
            $table->string('payment_method')->nullable();           
            $table->integer('invoice_no')->default(0);
            $table->string('received_by')->nullable();
            $table->string('staff_name')->nullable();  
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
        Schema::dropIfExists('cancel__customer__payments');
    }
};

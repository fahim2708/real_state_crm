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
        Schema::create('registration_payment_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('registration_amount_id');
            $table->date('payment_date');
            $table->string('pay_by');
            $table->string('money_receipt_no');
            $table->string('payment_against');
            $table->integer('payment_amount');
            $table->integer('payment_due');
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
        Schema::dropIfExists('registration_payment_histories');
    }
};

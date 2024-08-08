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
        Schema::create('registration_buy_backs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('after_reg_cancel_customer_id');
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
        Schema::dropIfExists('registration_buy_backs');
    }
};

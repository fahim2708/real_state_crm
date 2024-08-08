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
     *
     */


    public function up()
    {
        Schema::create('nominees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customer_id');
            $table->string('nominee_name')->nullable();
            $table->string('relation_with_nominee')->nullable();
            $table->string('nominee_contact_number')->nullable();
            $table->string('nominee_address')->nullable();
            $table->string('nominee_gets')->nullable();
            $table->string('nominee_image')->nullable();
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
        Schema::dropIfExists('nominees');
    }
};

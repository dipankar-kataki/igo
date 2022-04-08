<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookRidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_rides', function (Blueprint $table) {
            $table->id();
            $table->string('pickup_loc');
            $table->string('dest_loc');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('captain_id')->nullable();
            $table->boolean('is_ride_completed');
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
        Schema::dropIfExists('book_rides');
    }
}

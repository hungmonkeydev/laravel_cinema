<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Schema::create('bookings', function (Blueprint $table) {
        //     $table->bigIncrements('booking_id');
        //     $table->unsignedBigInteger('user_id')->nullable();
        //     $table->unsignedBigInteger('showtime_id')->index();
        //     $table->decimal('total_amount', 10, 2)->default(0);
        //     $table->string('payment_method')->nullable();
        //     $table->json('gateway_response')->nullable();
        //     $table->string('status')->default('pending');
        //     $table->timestamps();
        // });

        // Schema::create('booking_seats', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('booking_id')->index();
        //     $table->unsignedBigInteger('seat_id')->index();
        //     $table->decimal('price', 10, 2);
        //     $table->timestamps();
        // });
    }

    public function down()
    {
        Schema::dropIfExists('booking_seats');
        Schema::dropIfExists('bookings');
    }
};

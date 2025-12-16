<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Dòng này rất quan trọng: Nó kiểm tra nếu bảng có rồi thì BỎ QUA, không tạo lại nữa
        if (!Schema::hasTable('seat_status')) {
            Schema::create('seat_status', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('showtime_id');
                $table->unsignedBigInteger('seat_id');
                $table->string('status')->default('available');
                $table->timestamp('held_until')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('seat_status');
    }
};
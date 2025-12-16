<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Schema::table('bookings', function (Blueprint $table) {
        //     if (!Schema::hasColumn('bookings', 'booking_code')) {
        //         $table->string('booking_code')->nullable()->after('booking_id');
        //     }
        //     if (!Schema::hasColumn('bookings', 'final_amount')) {
        //         $table->decimal('final_amount', 10, 2)->default(0)->after('total_amount');
        //     }
        // });

        // Schema::table('seat_status', function (Blueprint $table) {
        //     if (!Schema::hasColumn('seat_status', 'held_by_user_id')) {
        //         $table->unsignedBigInteger('held_by_user_id')->nullable()->after('status');
        //     }
        // });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'final_amount')) {
                $table->dropColumn('final_amount');
            }
            if (Schema::hasColumn('bookings', 'booking_code')) {
                $table->dropColumn('booking_code');
            }
        });

        Schema::table('seat_status', function (Blueprint $table) {
            if (Schema::hasColumn('seat_status', 'held_by_user_id')) {
                $table->dropColumn('held_by_user_id');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Thêm các cột cho Social Login nếu chưa có
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->after('email');
                $table->string('provider_id')->nullable()->after('provider');
                $table->string('provider_token', 1000)->nullable()->after('provider_id'); // Token Google khá dài
                $table->string('provider_refresh_token', 1000)->nullable()->after('provider_token');
                $table->text('provider_avatar_url')->nullable()->after('provider_refresh_token');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_id', 'provider_token', 'provider_refresh_token', 'provider_avatar_url']);
        });
    }
};
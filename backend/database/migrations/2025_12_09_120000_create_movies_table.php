<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id('movie_id');
            $table->string('title');
            $table->string('title_vi');
            $table->text('description')->nullable();
            $table->text('description_vi')->nullable();
            $table->string('genre');
            $table->integer('duration_minutes');
            $table->date('release_date');
            $table->string('director')->nullable();
            $table->text('cast')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->string('poster_url', 500)->nullable();
            $table->string('trailer_url', 500)->nullable();
            $table->enum('status', ['showing', 'upcoming', 'ended'])->default('upcoming');
            $table->string('language')->nullable();
            $table->enum('age_rating', ['C13', 'C16', 'C18', 'P'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};

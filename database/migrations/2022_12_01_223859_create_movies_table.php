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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('movie_db_id')->nullable();
            $table->string('imdb_id')->nullable();
            $table->string('name');
            $table->string('sortable_name');
            $table->string('original_poster_id')->nullable();
            $table->string('active_poster_id')->nullable();
            $table->string('original_backdrop_id')->nullable();
            $table->string('active_backdrop_id')->nullable();
            $table->json('paths')->nullable();
            $table->date('release_date');
            $table->string('quality');
            $table->string('medium');
            $table->integer('runtime');
            $table->text('description')->nullable();
            $table->integer('revenue')->nullable();
            $table->integer('budget')->nullable();
            $table->text('tagline')->nullable();
            $table->unsignedBigInteger('boutique_id')->nullable();
            $table->foreign('boutique_id')->references('id')->on('boutiques');
            $table->json('filterable');
            $table->string('filter_on_quality')->nullable();
            $table->string('certification')->nullable();
            $table->json('videos')->nullable();
            $table->json('movie_genres')->nullable();
            $table->boolean('watched')->default(false);
            $table->integer('rating')->default(0);
            $table->boolean('manual')->default(false);
            $table->boolean('publicly_visible')->default(false);
            $table->boolean('wish_listed')->default(false);
            $table->string('film_boutique_url')->nullable();
            $table->boolean('auteur')->default(false);
            $table->date('date_added')->nullable();
            $table->json('notes')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('movies');
    }
};

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
        Schema::create('parametres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('nature_id')->references('id')->on('natures')->onDelete('cascade');
            $table->unsignedBigInteger('nature_id');
            $table->string('description')->nullable();
            $table->string('key_word')->nullable();
            $table->string('temps_travail')->nullable();
            $table->string('email')->nullable();
            $table->string('url_fb')->nullable();
            $table->string('url_insta')->nullable();
            $table->string('url_youtube')->nullable();
            $table->string('url_tiktok')->nullable();
            $table->string('url_twiter')->nullable();
            $table->string('mode_payement')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};

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
        Schema::create('command_product_attribute', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('commande_product_id');
                $table->foreign('commande_product_id')->references('id')->on('command_product')->onDelete('cascade');

                $table->unsignedBigInteger('attribute_id');
                $table->foreign('attribute_id')->references('attribute_id')->on('attributes')->onDelete('cascade');

                $table->unsignedBigInteger('value_id');
                $table->foreign('value_id')->references('value_id')->on('values')->onDelete('cascade');

                $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('command_product_attribute');
    }
};

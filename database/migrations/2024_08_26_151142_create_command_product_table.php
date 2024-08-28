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
        Schema::create('command_product', function (Blueprint $table) {
            $table->id();
            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('cascade');
            $table->unsignedBigInteger('commande_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(1); // Optional: If you need to track quantity
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('command_product');
    }
};

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
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();

            $table->unsignedBigInteger('admin_id');
            $table->foreign('admin_id')->references('admin_id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('commande_id');
            $table->foreign('commande_id')->references('commande_id')->on('commandes')->onDelete('cascade');

            $table->date('facture_date');
            $table->float('facture_tva');
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};

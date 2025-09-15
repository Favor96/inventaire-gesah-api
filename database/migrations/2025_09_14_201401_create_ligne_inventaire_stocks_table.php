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
        Schema::create('ligne_inventaire_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')->constrained('inventaire_stocks')->onDelete('cascade');
            $table->foreignId('produit_id')->constrained('produits')->onDelete('cascade');
            $table->integer('quantite_theorique');
            $table->integer('quantite_reelle');
            $table->integer('ecart')->nullable();
            $table->decimal('valeur_ecart', 10, 2)->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_inventaire_stocks');
    }
};

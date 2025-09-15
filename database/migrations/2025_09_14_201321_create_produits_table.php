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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained()->onDelete('cascade');
            $table->foreignId('categorie_id')->constrained('categories_produits')->onDelete('cascade');
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('code_produit')->unique();
            $table->decimal('prix_unitaire', 10, 2);
            $table->integer('stock_minimum')->default(0);
            $table->integer('stock_actuel')->default(0);
            $table->string('unite_mesure')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};

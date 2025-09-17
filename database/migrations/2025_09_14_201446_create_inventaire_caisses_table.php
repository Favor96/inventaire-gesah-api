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
        Schema::create('inventaire_caisses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->foreignId('caisse_id')->constrained('caisses')->onDelete('cascade');
            $table->string('numero_inventaire')->unique();
            $table->decimal('solde_theorique', 12, 2);
            $table->decimal('solde_reel', 12, 2);
            $table->decimal('ecart', 12, 2)->nullable();
            $table->dateTime('date_inventaire');
            $table->text('observations')->nullable();
            $table->dateTime('date_debut'); // date et heure début comptage
            $table->dateTime('date_fin');   // date et heure fin comptage
            $table->enum('statut', ['en_cours', 'valide', 'annule'])->default('en_cours');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaire_caisses');
    }
};

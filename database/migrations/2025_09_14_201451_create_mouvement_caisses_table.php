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
        Schema::create('mouvement_caisses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_caisse_id')->constrained('inventaire_caisses')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('client_entreprises')->onDelete('cascade');
            $table->enum('type_mouvement', ['entree', 'sortie']);
            $table->decimal('montant', 12, 2);
            $table->string('libelle')->nullable();
            $table->string('reference')->nullable();
            $table->dateTime('date_mouvement');
            $table->string('justificatif')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvement_caisses');
    }
};

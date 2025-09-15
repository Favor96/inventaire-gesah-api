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
        Schema::create('ligne_inventaire_immobilisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')->constrained('inventaire_immobilisations')->onDelete('cascade');
            $table->foreignId('immobilisation_id')->constrained('immobilisations')->onDelete('cascade');
            $table->string('etat_constate')->nullable();
            $table->string('localisation_constatee')->nullable();
            $table->decimal('valeur_estimee', 12, 2)->nullable();
            $table->text('observations')->nullable();
            $table->boolean('present')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_inventaire_immobilisations');
    }
};

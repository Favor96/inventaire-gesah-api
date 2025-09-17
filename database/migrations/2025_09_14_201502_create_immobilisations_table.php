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
        Schema::create('immobilisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('type_immobilisations')->onDelete('cascade');
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('numero_serie')->nullable();
            $table->decimal('valeur_acquisition', 15, 2);
            $table->dateTime('date_acquisition');
            $table->dateTime('date_mise_service')->nullable();
            $table->string('etat')->nullable();
            $table->string('localisation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('immobilisations');
    }
};

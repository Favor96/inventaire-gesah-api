<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_inventaire_caisses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')->constrained('inventaire_caisses')->onDelete('cascade');
            $table->string('type_billet');
            $table->integer('nombre')->default(0);
            $table->decimal('montant', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_inventaire_caisses');
    }
};

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
        Schema::table('achats', function (Blueprint $table) {
            // Supprimer la clé étrangère et la colonne
            $table->dropForeign(['inventaire_achat_id']);
            $table->dropColumn('inventaire_achat_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('achats', function (Blueprint $table) {
            // Recréer la colonne et la clé étrangère
            $table->foreignId('inventaire_achat_id')->constrained('inventaire_achats')->onDelete('cascade');
        });
    }
};

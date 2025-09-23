<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ligne_inventaire_stocks', function (Blueprint $table) {
            // 🔹 Supprimer l'ancienne clé étrangère si elle existe
            if (Schema::hasColumn('ligne_inventaire_stocks', 'produit_id')) {
                $table->dropForeign(['produit_id']);
                $table->dropColumn('produit_id');
            }

            // 🔹 Ajouter la nouvelle référence vers produit_packages
            $table->foreignId('produit_package_id')
                ->after('inventaire_id')
                ->constrained('produit_packages')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ligne_inventaire_stocks', function (Blueprint $table) {
            // rollback → enlever produit_package_id et remettre produit_id
            $table->dropForeign(['produit_package_id']);
            $table->dropColumn('produit_package_id');

            $table->foreignId('produit_id')
                ->constrained('produits')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};

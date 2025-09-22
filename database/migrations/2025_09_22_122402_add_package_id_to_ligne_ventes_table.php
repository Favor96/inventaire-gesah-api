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
        Schema::table('ligne_ventes', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable()->after('produit_id');

            // Optionnel : ajouter la clé étrangère
            $table->foreign('package_id')->references('id')->on('produit_packages')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('ligne_ventes', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
        });
    }
};

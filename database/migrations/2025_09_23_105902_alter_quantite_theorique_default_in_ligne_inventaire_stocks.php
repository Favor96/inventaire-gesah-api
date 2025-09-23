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
        Schema::table('ligne_inventaire_stocks', function (Blueprint $table) {
            $table->integer('quantite_theorique')
                ->default(0)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ligne_inventaire_stocks', function (Blueprint $table) {
            $table->integer('quantite_theorique')
                ->nullable(false)
                ->change();
        });
    }
};

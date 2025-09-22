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
            $table->string('statut')->default('active'); // active / annule
        });
    }

    public function down(): void
    {
        Schema::table('ligne_ventes', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};

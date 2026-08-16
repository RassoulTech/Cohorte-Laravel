<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Migration separee : publications pointe vers reponses, mais la table
     * reponses n'existe pas encore au moment ou l'on cree publications.
     * On ajoute donc la cle etrangere apres, dans une migration posterieure.
     */
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->foreignId('reponse_retenue_id')
                ->nullable()
                ->after('statut')
                ->constrained('reponses')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reponse_retenue_id');
        });
    }
};

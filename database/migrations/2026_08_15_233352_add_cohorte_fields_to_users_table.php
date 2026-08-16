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
        Schema::table('users', function (Blueprint $table) {
            // nullable : l'enseignant n'appartient a aucune promotion.
            // constrained() devine la table promotions a partir du nom promotion_id.
            // nullOnDelete() : si la promotion est supprimee, le membre reste mais sans promotion.
            $table->foreignId('promotion_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->string('role', 20)->default('apprenant')->after('email');
            $table->unsignedInteger('points')->default(0)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
            $table->dropColumn(['role', 'points']);
        });
    }
};

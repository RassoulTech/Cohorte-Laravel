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
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('post');      // post | question
            $table->string('titre')->nullable();
            $table->text('contenu');
            $table->string('statut', 20)->default('publie');  // publie | en_moderation | refuse | masque
            $table->text('motif_moderation')->nullable();
            $table->timestamp('epingle_le')->nullable();
            $table->timestamps();

            // Index de la requete du fil : filtrer par promotion + statut, trier par date.
            $table->index(['promotion_id', 'statut', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};

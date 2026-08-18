<?php

namespace Database\Factories;

use App\Models\Promotion;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * L'etat par defaut : un POST publie.
     * Les variantes (question, en moderation) sont declarees plus bas.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Une factory imbriquee se lit : "si personne ne me fournit de
            // promotion, fabrique-en une". Dans le seeder on fournit toujours
            // la valeur, donc ces deux lignes ne sont jamais executees la-bas.
            'promotion_id' => Promotion::factory(),
            'user_id' => User::factory(),

            'type' => 'post',
            'titre' => fake()->sentence(6),

            // Le second argument true demande une chaine, et non un tableau.
            'contenu' => fake()->paragraphs(2, true),

            'statut' => 'publie',

            // Des dates etalees sur 30 jours : sans cela toutes les
            // publications auraient le meme horodatage et le tri du fil
            // (phase 5) serait impossible a verifier.
            'created_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }

    /**
     * Un ETAT : il ecrase une partie de definition() sans la reecrire.
     * Usage : Publication::factory()->question()->create();
     */
    public function question(): static
    {
        return $this->state(fn () => [
            'type' => 'question',

            // rtrim enleve le point final de la phrase avant d'ajouter le "?".
            'titre' => rtrim(fake()->sentence(8), '.') . ' ?',
        ]);
    }

    /**
     * Utile des la phase 7 : alimenter la file de moderation du delegue.
     */
    public function enModeration(): static
    {
        return $this->state(fn () => [
            'statut' => 'en_moderation',
        ]);
    }
}

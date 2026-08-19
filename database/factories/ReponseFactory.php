<?php

namespace Database\Factories;

use App\Models\Publication;
use App\Models\Reponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reponse>
 */
class ReponseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // On repond a une QUESTION, jamais a un post : d'ou l'etat
            // ->question() sur la factory imbriquee. Utile si on appelle
            // Reponse::factory()->create() tout seul, hors du seeder.
            'publication_id' => Publication::factory()->question(),
            'user_id' => User::factory(),
            'contenu' => fake()->paragraph(),
        ];
    }
}

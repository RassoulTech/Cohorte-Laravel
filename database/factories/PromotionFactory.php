<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Le moule d'une promotion : a quoi ressemble une promotion "typique".
     * La factory ne decide PAS combien en creer : c'est le role du seeder.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // fake() est le generateur de donnees realistes (bibliotheque Faker).
            'nom' => 'Développement Web ' . fake()->year(),

            // bothify() remplace ? par une lettre et # par un chiffre : "ab1234".
            // strtoupper() met en majuscules  ->  "AB1234".
            // unique() garantit qu'on ne genere jamais deux fois le meme code,
            // sinon la contrainte unique de la migration ferait echouer le seed.
            'code_invitation' => strtoupper(fake()->unique()->bothify('??####')),

            'annee' => 2026,
            'ouverte' => true,
        ];
    }
}

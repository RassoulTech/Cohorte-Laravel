<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\Publication;
use App\Models\Reponse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Le seeder decide COMBIEN d'objets creer et POUR QUI.
     * Les factories, elles, decrivent seulement a quoi ressemble un objet.
     *
     * Lance par : php artisan db:seed
     * ou par    : php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        // ------------------------------------------------ Les deux promotions
        // Deux promotions DISTINCTES : c'est la condition pour pouvoir tester
        // le cloisonnement. Les codes d'invitation sont fixes (et non generes
        // au hasard) parce qu'ils doivent figurer dans le README et servir au
        // correcteur pour s'inscrire.
        $groupeA = Promotion::factory()->create([
            'nom' => 'Développement Web 2026 — Groupe A',
            'code_invitation' => 'DWA2026',
        ]);

        $groupeB = Promotion::factory()->create([
            'nom' => 'Développement Web 2026 — Groupe B',
            'code_invitation' => 'DWB2026',
        ]);

        // -------------------------------------------- Le contenu de chaque groupe
        // La meme recette appliquee aux deux promotions : 8 membres, 15 posts,
        // 6 questions, et de 0 a 3 reponses par question.
        foreach ([$groupeA, $groupeB] as $promotion) {
            $membres = User::factory()
                ->count(8)
                ->create(['promotion_id' => $promotion->id]);

            // recycle($membres) = "reutilise ces membres comme auteurs".
            // SANS cette ligne, la factory resoudrait sa ligne
            // 'user_id' => User::factory() et fabriquerait 15 utilisateurs
            // fantomes de plus par promotion.
            Publication::factory()
                ->count(15)
                ->recycle($membres)
                ->create(['promotion_id' => $promotion->id]);

            // Les questions, avec leurs reponses.
            Publication::factory()
                ->count(6)
                ->question()
                ->recycle($membres)
                ->create(['promotion_id' => $promotion->id])
                ->each(function (Publication $question) use ($membres) {
                    // rand(0, 3) : certaines questions restent sans reponse,
                    // c'est le cas reel qu'il faudra afficher en phase 6.
                    Reponse::factory()
                        ->count(rand(0, 3))
                        ->recycle($membres)
                        ->create(['publication_id' => $question->id]);
                });
        }

        $this->comptesDeDemonstration($groupeA, $groupeB);
    }

    /**
     * Les quatre comptes OBLIGATOIRES du cahier des charges.
     * Adresses et mot de passe imposes : le correcteur s'en sert tel quel.
     */
    private function comptesDeDemonstration(Promotion $a, Promotion $b): void
    {
        // Apprenante du groupe A : le point de depart du test de cloisonnement.
        User::factory()->create([
            'name' => 'Awa Diop',
            'email' => 'awa@cohorte.test',
            'password' => Hash::make('password'),
            'promotion_id' => $a->id,
            'role' => 'apprenant',
        ]);

        // Delegue du groupe A : il accedera a la file de moderation (phase 8).
        User::factory()->create([
            'name' => 'Moussa Ba',
            'email' => 'moussa@cohorte.test',
            'password' => Hash::make('password'),
            'promotion_id' => $a->id,
            'role' => 'delegue',
        ]);

        // Apprenante du groupe B : c'est avec elle que le correcteur essaiera
        // d'atteindre une publication d'Awa. Resultat attendu : 403.
        User::factory()->create([
            'name' => 'Fatou Sow',
            'email' => 'fatou@cohorte.test',
            'password' => Hash::make('password'),
            'promotion_id' => $b->id,
            'role' => 'apprenant',
        ]);

        // Enseignant : promotion_id volontairement null. C'est ce cas qui
        // justifie le middleware ExigePromotion de la phase 4.
        User::factory()->create([
            'name' => 'Formateur',
            'email' => 'formateur@cohorte.test',
            'password' => Hash::make('password'),
            'promotion_id' => null,
            'role' => 'enseignant',
        ]);
    }
}

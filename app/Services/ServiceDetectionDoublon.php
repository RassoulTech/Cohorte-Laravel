<?php

namespace App\Services;

use App\Models\AppelIa;
use App\Models\Publication;
use App\Models\User;

/**
 * La seconde fonctionnalite intelligente : prevenir un membre que sa question
 * a probablement deja ete posee dans sa promotion.
 *
 * Contrairement a la moderation, c'est une ASSISTANCE : on peut la retirer
 * sans empecher le membre de publier. C'est pourquoi elle est la seule soumise
 * au quota.
 */
class ServiceDetectionDoublon
{
    public function __construct(private OpenRouterClient $client) {}

    /**
     * Renvoie les questions existantes jugees similaires.
     *
     * @return list<Publication>
     */
    public function chercherSimilaires(string $titre, User $auteur): array
    {
        $existantes = Publication::query()
            ->questions()
            ->visibles()
            ->deLaPromotion($auteur->promotion_id)
            ->latest()

            // Limite volontaire. Envoyer trois cents titres couterait cher en
            // jetons, depasserait la fenetre de contexte des modeles gratuits,
            // et degraderait la qualite de la reponse.
            ->limit(40)

            // pluck('titre', 'id') renvoie une collection indexee par id :
            // une seule colonne remonte, pas les modeles entiers.
            ->pluck('titre', 'id');

        // Aucune question dans la promotion : inutile de depenser un appel.
        if ($existantes->isEmpty()) {
            return [];
        }

        $catalogue = $existantes
            ->map(fn ($t, $id) => "{$id}. {$t}")
            ->implode("\n");

        $texte = $this->client->discuter([
            ['role' => 'system', 'content' => $this->consigne()],
            ['role' => 'user', 'content' => "Questions existantes :\n{$catalogue}\n\nNouvelle question :\n{$titre}"],
        ]);

        AppelIa::create([
            'user_id' => $auteur->id,
            'contexte' => 'doublon',
            'modele' => config('services.openrouter.model'),
            'reussi' => $texte !== null,
        ]);

        $ids = $this->extraireIdentifiants($texte);

        if ($ids === []) {
            return [];
        }

        /*
         * ON REFILTRE SUR LA PROMOTION.
         *
         * Le modele pourrait inventer un identifiant, ou renvoyer celui d'une
         * question qui ne figurait pas dans le catalogue. Une sortie de modele
         * est une donnee utilisateur non fiable : on la traite comme telle.
         * Sans cette ligne, on afficherait le titre d'une question d'une autre
         * promotion — une fuite de cloisonnement par l'IA.
         */
        return Publication::query()
            ->whereIn('id', $ids)
            ->questions()
            ->visibles()
            ->deLaPromotion($auteur->promotion_id)

            // La vue affiche le nom de l'auteur de chaque question proche :
            // sans ce prechargement, preventLazyLoading fait planter la page.
            ->with('auteur')

            ->get()
            ->all();
    }

    private function consigne(): string
    {
        return <<<'TXT'
        On te donne une liste de questions deja posees, numerotees, et une nouvelle question.
        Identifie celles qui traitent du MEME probleme que la nouvelle question.
        Sois strict : une simple proximite de vocabulaire ne suffit pas.

        Reponds UNIQUEMENT par un objet JSON de la forme {"similaires": [12, 45]}.
        Si aucune question n'est similaire, reponds {"similaires": []}.
        TXT;
    }

    /**
     * Meme parsing defensif que la moderation : le modele peut encadrer sa
     * reponse, l'introduire par une phrase, ou ne rien renvoyer du tout.
     *
     * @return list<int>
     */
    private function extraireIdentifiants(?string $texte): array
    {
        if ($texte === null || trim($texte) === '') {
            return [];
        }

        if (preg_match('/\{.*\}/s', $texte, $trouve)) {
            $texte = $trouve[0];
        }

        $donnees = json_decode($texte, true);

        if (! is_array($donnees) || ! isset($donnees['similaires']) || ! is_array($donnees['similaires'])) {
            return [];
        }

        // intval convertit "12" en 12 ; array_filter retire les 0, c'est-a-dire
        // tout ce qui n'etait pas un nombre exploitable.
        return array_values(array_filter(array_map('intval', $donnees['similaires'])));
    }
}

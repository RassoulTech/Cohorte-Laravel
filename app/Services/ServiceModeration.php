<?php

namespace App\Services;

use App\Enums\VerdictModeration;
use App\Models\AppelIa;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * La couche METIER de la moderation. Elle decide, la ou OpenRouterClient se
 * contente de transporter.
 */
class ServiceModeration
{
    // Promotion de propriete du constructeur : Laravel construit et injecte
    // OpenRouterClient tout seul, sans qu'on l'instancie nulle part.
    public function __construct(private OpenRouterClient $client) {}

    public function evaluer(string $contenu, User $auteur): VerdictModeration
    {
        $texte = $this->client->discuter([
            ['role' => 'system', 'content' => $this->consigne()],
            ['role' => 'user', 'content' => "Publication à évaluer :\n\n" . $contenu],
        ]);

        $verdict = $this->interpreter($texte);

        // On journalise TOUS les appels, reussis ou non : c'est cette table qui
        // servira a calculer le quota quotidien en phase 9.
        AppelIa::create([
            'user_id' => $auteur->id,
            'contexte' => 'moderation',
            'modele' => config('services.openrouter.model'),
            'reussi' => $verdict !== VerdictModeration::Indisponible,
        ]);

        return $verdict;
    }

    private function consigne(): string
    {
        return <<<'TXT'
        Tu es le moderateur d'un reseau social interne a une ecole de developpement web.
        Tu evalues si une publication est acceptable dans un cadre scolaire.

        Classe la publication dans l'une de ces trois categories :
        - "acceptable" : contenu normal, meme maladroit ou hors sujet
        - "douteux" : moquerie ciblee, propos limites, publicite, contenu ambigu
        - "inacceptable" : insulte, harcelement, propos haineux, contenu sexuel

        Reponds UNIQUEMENT par un objet JSON valide, sans texte avant ni apres,
        de la forme exacte :
        {"verdict": "acceptable", "raison": "une phrase courte"}
        TXT;
    }

    /**
     * LE COEUR DE LA PHASE.
     *
     * Un modele de langage produit du TEXTE, pas une structure de donnees. Meme
     * en lui demandant du JSON, il ajoutera parfois une phrase d'introduction,
     * encadrera sa reponse par des accents graves, ou repondra 200 avec un
     * contenu vide. Le code doit survivre a tout cela.
     *
     * La regle generale, vraie pour toute la carriere : ne jamais faire
     * confiance a la sortie d'un modele, la parser defensivement, et prevoir
     * toujours le cas ou elle est inexploitable.
     */
    private function interpreter(?string $texte): VerdictModeration
    {
        // 1. Service injoignable, en erreur, ou contenu vide.
        if ($texte === null || trim($texte) === '') {
            return VerdictModeration::Indisponible;
        }

        // 2. Le modele encadre parfois sa reponse par ```json ... ``` ou ajoute
        //    une phrase avant. On extrait le premier bloc entre accolades.
        //    Le drapeau s fait correspondre le point aux sauts de ligne.
        if (preg_match('/\{.*\}/s', $texte, $trouve)) {
            $texte = $trouve[0];
        }

        $donnees = json_decode($texte, true);

        // 3. Ce n'etait pas du JSON exploitable.
        if (! is_array($donnees) || ! isset($donnees['verdict'])) {
            Log::warning('Reponse de moderation illisible', ['brut' => $texte]);

            return VerdictModeration::Indisponible;
        }

        // 4. tryFrom renvoie null si la valeur ne correspond a aucun cas de
        //    l'enumeration, au lieu de lever une exception comme from().
        //    Le modele pourrait inventer "problematique" ou repondre en anglais.
        $verdict = VerdictModeration::tryFrom(strtolower(trim($donnees['verdict'])));

        if ($verdict === null) {
            Log::warning('Verdict inconnu renvoye par le modele', [
                'verdict' => $donnees['verdict'],
            ]);

            return VerdictModeration::Indisponible;
        }

        return $verdict;
    }
}

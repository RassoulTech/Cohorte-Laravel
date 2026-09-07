<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Le seul endroit du projet qui parle a OpenRouter.
 *
 * Aucun controleur ne doit contenir d'appel Http::post() : le jour ou l'on
 * change de fournisseur, un seul fichier bouge. C'est aussi ce qui rend les
 * tests possibles, puisqu'il suffit de simuler cette couche.
 *
 * Cette classe ne prend AUCUNE decision metier. Elle renvoie du texte, ou null
 * si le service est indisponible. C'est a la couche metier de decider quoi en
 * faire : une classe technique ne tranche pas une regle de gestion.
 */
class OpenRouterClient
{
    /**
     * Envoie une conversation au modele et renvoie le texte de la reponse,
     * ou null si le service est injoignable, en erreur, ou muet.
     *
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function discuter(array $messages): ?string
    {
        $debut = microtime(true);

        try {
            $reponse = Http::withToken(config('services.openrouter.key'))
                ->withHeaders([
                    // OpenRouter demande ces deux en-tetes pour identifier
                    // l'application appelante dans ses statistiques.
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => 'Cohorte',
                ])
                // Sans timeout, la valeur par defaut de Guzzle laisse la
                // requete pendre tres longtemps et le formulaire semble plante.
                ->timeout(config('services.openrouter.timeout'))

                // Deux nouvelles tentatives a 400 ms d'intervalle : utile contre
                // un incident passager, inutile si le service est franchement en
                // panne, d'ou le timeout court. throw: false empeche retry() de
                // lever une exception a la derniere tentative.
                ->retry(2, 400, throw: false)

                ->post(config('services.openrouter.url'), [
                    'model' => config('services.openrouter.model'),
                    'messages' => $messages,

                    // temperature 0 : on veut la reponse la plus deterministe
                    // possible, pas de la creativite. Le meme contenu doit
                    // recevoir le meme verdict.
                    'temperature' => 0,
                    'max_tokens' => 300,
                ]);
        } catch (\Throwable $e) {
            // Reseau coupe, DNS injoignable, timeout depasse...
            Log::warning('OpenRouter injoignable', ['message' => $e->getMessage()]);

            return null;
        }

        if ($reponse->failed()) {
            Log::warning('OpenRouter a repondu en erreur', [
                'statut' => $reponse->status(),
                'corps' => $reponse->body(),
            ]);

            return null;
        }

        Log::info('Appel OpenRouter', [
            'modele' => config('services.openrouter.model'),
            'duree_ms' => (int) ((microtime(true) - $debut) * 1000),
        ]);

        // data_get traverse le tableau sans lever d'erreur si une cle manque :
        // il renvoie null. Constate en testant le catalogue, certains modeles
        // repondent 200 avec un content vide.
        return data_get($reponse->json(), 'choices.0.message.content');
    }
}

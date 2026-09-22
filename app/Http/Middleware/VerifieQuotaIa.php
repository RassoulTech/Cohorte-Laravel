<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuse une route qui consomme volontairement de l'IA quand le quota
 * quotidien du membre est epuise.
 *
 * POURQUOI CE MIDDLEWARE NE PROTEGE PAS LA PUBLICATION.
 *
 * La moderation est une CONTRAINTE imposee par l'application, pas un service
 * rendu au membre : la lui refuser parce qu'il a epuise son quota reviendrait
 * a l'empecher de s'exprimer. La detection de doublon, elle, est une
 * ASSISTANCE : on peut la lui retirer sans dommage.
 *
 * Les deux appels sont comptes dans appels_ia, mais seul le second passe par
 * ce middleware. C'est une decision de conception, consignee dans DECISIONS.md.
 */
class VerifieQuotaIa
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->peutAppelerIa()) {
            return back()
                // withInput conserve ce que le membre avait saisi : il ne
                // perd pas sa question parce que son quota est epuise.
                ->withInput()
                ->with('erreur', 'Vous avez épuisé votre quota d\'assistance IA pour aujourd\'hui. Réessayez demain.');
        }

        return $next($request);
    }
}

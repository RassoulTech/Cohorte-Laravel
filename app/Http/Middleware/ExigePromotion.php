<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantit qu'une route ne recoit JAMAIS un utilisateur sans promotion.
 *
 * C'est ce qu'on appelle une invariante : on verifie une fois, en amont, ce
 * que tout le code en aval tient ensuite pour acquis. Sans cette garantie,
 * l'appel ->deLaPromotion($request->user()->promotion_id) de la phase 5
 * recevrait null et leverait une TypeError, puisque le scope attend un int.
 *
 * Deux comptes echappent au rattachement automatique de l'inscription :
 *  - l'enseignant, qui n'appartient a aucune promotion par nature ;
 *  - un membre dont la promotion a ete supprimee (nullOnDelete, phase 1).
 */
class ExigePromotion
{
    public function handle(Request $request, Closure $next): Response
    {
        $utilisateur = $request->user();

        // Le middleware auth s'occupe deja des visiteurs non connectes.
        // Ici on ne fait que laisser passer, sans dupliquer sa responsabilite.
        if (! $utilisateur) {
            return $next($request);
        }

        // L'enseignant n'a pas de promotion_id : il ne doit jamais entrer dans
        // le fil d'une promotion, il passe par son propre module de consultation.
        if ($utilisateur->estEnseignant()) {
            return redirect()->route('enseignant.promotions.index');
        }

        if (! $utilisateur->promotion_id) {
            return redirect()
                ->route('promotion.rejoindre')
                ->with('erreur', "Saisissez le code d'invitation de votre promotion pour continuer.");
        }

        return $next($request);
    }
}

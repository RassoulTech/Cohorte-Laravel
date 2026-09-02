<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Le module de l'enseignant. Le cahier des charges lui donne un droit unique :
 * consulter TOUTES les promotions, sans jamais pouvoir publier.
 *
 * C'est vers cette page que le middleware ExigePromotion le redirige, puisqu'il
 * n'a pas de promotion_id et n'a donc rien a faire dans un fil de promotion.
 */
class PromotionController extends Controller
{
    public function index(Request $request): View
    {
        // abort_unless leve un 403 si la condition est fausse. Cacher le lien
        // dans une vue ne suffirait pas : il faut bloquer la ROUTE.
        abort_unless($request->user()->estEnseignant(), 403);

        // withCount evite le probleme N+1 : il ajoute une sous-requete COUNT
        // au lieu de charger toutes les lignes pour les compter en PHP.
        $promotions = Promotion::query()
            ->withCount(['membres', 'publications'])
            ->orderBy('nom')
            ->get();

        return view('promotion.index', compact('promotions'));
    }
}

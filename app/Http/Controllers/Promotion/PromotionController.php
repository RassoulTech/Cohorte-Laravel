<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
// Le controleur est lui-meme dans le namespace ...\Controllers\Promotion :
// sans alias, "Promotion" designerait CE dossier et non le modele.
use App\Models\Promotion as PromotionModel;
use App\Models\Publication;
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
        $promotions = PromotionModel::query()
            ->withCount(['membres', 'publications'])
            ->orderBy('nom')
            ->get();

        return view('promotion.index', compact('promotions'));
    }

    /**
     * Le fil d'une promotion, vu par l'enseignant.
     *
     * Cette route existe parce que l'enseignant n'a pas de promotion_id : le
     * middleware ExigePromotion le redirigerait avant d'atteindre
     * /publications. Elle est donc declaree hors du groupe 'promotion'.
     *
     * "Consulter toutes les promotions" ne signifie pas voir la liste de leurs
     * noms, mais consulter leur CONTENU : c'est ce que dit deja
     * PublicationPolicy::view(), qui renvoie true pour un enseignant.
     */
    public function fil(Request $request, PromotionModel $promotion): View
    {
        abort_unless($request->user()->estEnseignant(), 403);

        $publications = Publication::query()
            ->posts()
            ->deLaPromotion($promotion->id)

            // Pas de ->visibles() ici, volontairement : l'enseignant est un
            // observateur et voit aussi ce qui est masque, refuse ou en
            // moderation. C'est exactement ce qu'autorise sa policy.
            ->with('auteur')
            ->withCount('signalements')
            ->orderByRaw('epingle_le IS NULL')
            ->orderByDesc('epingle_le')
            ->latest()
            ->paginate(15);

        return view('promotion.fil', compact('promotion', 'publications'));
    }
}

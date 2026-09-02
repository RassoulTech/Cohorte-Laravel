<?php

namespace App\Http\Controllers\Promotion;

// Indispensable : depuis Laravel 11 le controleur de base est dans un autre
// namespace que le notre, donc extends Controller ne le trouverait pas seul.
use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Rattacher apres coup un membre a une promotion, via son code d'invitation.
 * Sert aux comptes que l'inscription n'a pas rattaches.
 */
class AdhesionController extends Controller
{
    public function create(Request $request): View
    {
        $this->refuserAuxEnseignants($request);

        return view('promotion.rejoindre');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->refuserAuxEnseignants($request);

        $donnees = $request->validate([
            'code_invitation' => ['required', 'string', 'max:12'],
        ]);

        // On filtre sur ouverte des la requete : une promotion fermee est
        // traitee exactement comme un code inconnu, et on ne revele donc pas
        // a un inconnu qu'une promotion porte ce code.
        $promotion = Promotion::where('code_invitation', $donnees['code_invitation'])
            ->where('ouverte', true)
            ->first();

        if (! $promotion) {
            // withInput() conserve la saisie, withErrors() rattache le message
            // au champ code_invitation pour que @error() l'affiche dessous.
            return back()
                ->withInput()
                ->withErrors(['code_invitation' => 'Code inconnu ou promotion fermée.']);
        }

        $request->user()->update(['promotion_id' => $promotion->id]);

        return redirect()
            ->route('profil.show')
            ->with('succes', 'Bienvenue dans la promotion ' . $promotion->nom . '.');
    }

    /**
     * Un enseignant n'appartient a aucune promotion, par nature : le cahier des
     * charges lui donne un seul droit, consulter toutes les promotions.
     *
     * Sans ce garde-fou, il pouvait saisir un code d'invitation et recevoir un
     * promotion_id qui ne lui servait a rien, puisque ExigePromotion teste son
     * role AVANT la promotion et le renvoie de toute facon vers son module. On
     * ecrivait donc en base une donnee fausse, en silence.
     *
     * La verification est ici, dans le controleur, et pas seulement dans la vue :
     * masquer un bouton n'empeche personne d'appeler la route directement.
     */
    private function refuserAuxEnseignants(Request $request): void
    {
        abort_if($request->user()->estEnseignant(), 403);
    }
}

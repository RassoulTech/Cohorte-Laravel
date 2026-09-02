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
    public function create(): View
    {
        return view('promotion.rejoindre');
    }

    public function store(Request $request): RedirectResponse
    {
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
}

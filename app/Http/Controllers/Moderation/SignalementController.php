<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Models\Signalement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Les trois interdits de la phase, testes un par un :
 *   1. on ne signale pas SA PROPRE publication        -> policy signaler()
 *   2. on ne signale pas DEUX FOIS la meme            -> contrainte unique + controle PHP
 *   3. une publication masquee reste visible POUR SON AUTEUR -> policy view()
 */
class SignalementController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Publication $publication): RedirectResponse
    {
        // signaler() refuse deja l'auto-signalement et le hors-promotion :
        // ecrite en phase 5, elle vit au meme endroit que les autres regles.
        $this->authorize('signaler', $publication);

        $donnees = $request->validate([
            'motif' => ['required', 'string', 'in:insulte,hors_sujet,publicite,autre'],
        ], [
            'motif.required' => 'Indiquez le motif de votre signalement.',
            'motif.in' => 'Ce motif de signalement n\'est pas valide.',
        ]);

        /*
         * Pourquoi verifier le doublon en PHP alors que la base a deja une
         * contrainte unique sur (publication_id, user_id) ?
         *
         * Parce que les deux ne servent pas au meme public. La contrainte de
         * base garantit l'INTEGRITE des donnees quoi qu'il arrive : code
         * contourne, requete forgee, ou deux requetes simultanees. La
         * verification PHP, elle, permet d'afficher un message comprehensible
         * plutot qu'une page d'erreur SQL.
         *
         * La base protege, le code explique. On garde les deux.
         */
        $dejaSignale = $publication->signalements()
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($dejaSignale) {
            return back()->with('erreur', 'Vous avez déjà signalé cette publication.');
        }

        Signalement::create([
            'publication_id' => $publication->id,
            'user_id' => $request->user()->id,
            'motif' => $donnees['motif'],
        ]);

        $this->masquerSiSeuilAtteint($publication);

        return back()->with('succes', 'Signalement enregistré. Merci.');
    }

    /**
     * Le masquage automatique de la regle metier 3.
     *
     * Le seuil vient de config/cohorte.php et jamais d'une valeur en dur : le
     * correcteur peut le passer a 1 pour tester ce comportement sans toucher
     * au code.
     */
    private function masquerSiSeuilAtteint(Publication $publication): void
    {
        $nombre = $publication->signalements()->count();

        // On ne masque que ce qui etait publie : une publication deja refusee
        // par la moderation IA, ou deja masquee, ne doit pas voir son motif
        // ecrase par celui du masquage automatique.
        if ($nombre >= config('cohorte.seuil_signalement') && $publication->statut === 'publie') {
            $publication->update([
                'statut' => 'masque',
                'motif_moderation' => "Masquée automatiquement après {$nombre} signalements.",
            ]);
        }
    }
}

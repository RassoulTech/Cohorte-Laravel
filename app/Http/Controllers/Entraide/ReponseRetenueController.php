<?php

namespace App\Http\Controllers\Entraide;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Models\Reponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Un controleur pour UNE SEULE action.
 *
 * Designer une reponse comme retenue n'est pas "modifier une question" : c'est
 * une action a part entiere, avec ses propres droits — seul l'auteur de la
 * question peut le faire. Plutot que d'ajouter une methode
 * marquerReponseRetenue() au controleur des questions, on cree un controleur
 * dedie a cette ressource. C'est le principe du controleur de ressource
 * singleton, tres courant en Laravel des que le projet grossit.
 */
class ReponseRetenueController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Publication $question): RedirectResponse
    {
        // Seul l'auteur de la question, et seulement sur une question.
        $this->authorize('designerReponse', $question);

        $donnees = $request->validate([
            'reponse_id' => ['required', 'integer', 'exists:reponses,id'],
        ]);

        $reponse = Reponse::findOrFail($donnees['reponse_id']);

        /*
         * LA LIGNE CRITIQUE DE LA PHASE.
         *
         * La regle exists:reponses,id garantit que la reponse EXISTE, pas
         * qu'elle appartient a CETTE question. Sans cette verification,
         * n'importe qui pourrait designer comme retenue une reponse ecrite sur
         * une question d'une AUTRE promotion, en modifiant simplement la valeur
         * du champ cache dans le formulaire.
         */
        abort_unless($reponse->publication_id === $question->id, 403);

        // On ne credite qu'une seule fois : si l'auteur change d'avis, on
        // retire les points a l'ancien elu avant de crediter le nouveau.
        $ancienne = $question->reponse_retenue_id;

        if ($ancienne === $reponse->id) {
            return back()->with('erreur', 'Cette réponse est déjà retenue.');
        }

        if ($ancienne) {
            Reponse::find($ancienne)?->auteur->decrement('points', 10);
        }

        $question->update(['reponse_retenue_id' => $reponse->id]);

        // increment() fait un UPDATE ... SET points = points + 10 en SQL :
        // pas de lecture puis ecriture, donc pas de valeur perdue si deux
        // requetes arrivent en meme temps.
        $reponse->auteur->increment('points', 10);

        return back()->with('succes', 'Réponse retenue. Merci pour votre contribution.');
    }

    public function destroy(Request $request, Publication $question): RedirectResponse
    {
        $this->authorize('designerReponse', $question);

        if ($question->reponse_retenue_id) {
            Reponse::find($question->reponse_retenue_id)?->auteur->decrement('points', 10);
            $question->update(['reponse_retenue_id' => null]);
        }

        return back()->with('succes', 'La réponse retenue a été retirée.');
    }
}

<?php

namespace App\Http\Controllers\Entraide;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReponseRequest;
use App\Models\Publication;
use App\Models\Reponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReponseController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreReponseRequest $request, Publication $question): RedirectResponse
    {
        // repondre() verifie trois choses d'un coup : c'est bien une question,
        // elle est publiee, et elle appartient a ma promotion.
        $this->authorize('repondre', $question);

        Reponse::create([
            'publication_id' => $question->id,
            'user_id' => $request->user()->id,
            'contenu' => $request->validated()['contenu'],
        ]);

        return back()->with('succes', 'Votre réponse est publiée.');
    }

    public function destroy(Request $request, Reponse $reponse): RedirectResponse
    {
        // Son auteur, ou le delegue de la promotion concernee. On remonte a la
        // publication pour connaitre la promotion : une reponse n'en porte pas.
        abort_unless(
            $reponse->user_id === $request->user()->id
                || ($request->user()->estDelegue()
                    && $request->user()->promotion_id === $reponse->publication->promotion_id),
            403
        );

        // Si cette reponse etait la reponse retenue, on retire la designation :
        // sinon publications.reponse_retenue_id pointerait vers une ligne
        // supprimee. La cle etrangere est en nullOnDelete, mais on retire aussi
        // les points attribues, ce que la base ne peut pas faire seule.
        if ($reponse->publication->reponse_retenue_id === $reponse->id) {
            $reponse->publication->update(['reponse_retenue_id' => null]);
            $reponse->auteur->decrement('points', 10);
        }

        $reponse->delete();

        return back()->with('succes', 'Réponse supprimée.');
    }
}

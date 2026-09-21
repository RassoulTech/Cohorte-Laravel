<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * La file de moderation du delegue.
 *
 * C'est ici que le role 'delegue', cree en phase 1 et attribue a Moussa Ba par
 * le seeder de la phase 2, prend enfin son sens.
 */
class FileModerationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->estDelegue(), 403);

        $publications = Publication::query()
            // Le delegue ne modere QUE sa promotion : le scope s'en charge.
            ->deLaPromotion($request->user()->promotion_id)

            // Les deux etats qui demandent une decision humaine : mis en
            // attente par l'IA (verdict douteux ou indisponible), ou masques
            // automatiquement apres le seuil de signalements.
            ->whereIn('statut', ['en_moderation', 'masque'])

            ->with('auteur')
            ->withCount('signalements')
            ->latest()
            ->paginate(20);

        return view('moderation.index', compact('publications'));
    }

    public function update(Request $request, Publication $publication): RedirectResponse
    {
        /*
         * DEUX verifications, et il faut les deux.
         *
         * Etre delegue ne suffit pas : il faut etre delegue DE CETTE
         * promotion. Le delegue du groupe A ne modere pas le groupe B. C'est
         * le genre de controle qu'on oublie tres facilement, parce que la
         * premiere ligne donne l'impression que le travail est fait.
         */
        abort_unless($request->user()->estDelegue(), 403);
        abort_unless($request->user()->promotion_id === $publication->promotion_id, 403);

        $donnees = $request->validate([
            'decision' => ['required', 'in:valider,refuser'],
        ], [
            'decision.required' => 'Indiquez votre décision.',
            'decision.in' => 'Décision inconnue.',
        ]);

        $valide = $donnees['decision'] === 'valider';

        $publication->update([
            'statut' => $valide ? 'publie' : 'refuse',
            'motif_moderation' => $valide
                ? null
                : 'Refusée par ' . $request->user()->name . ' le ' . now()->format('d/m/Y') . '.',
        ]);

        return back()->with(
            'succes',
            $valide ? 'Publication remise en ligne.' : 'Publication refusée.'
        );
    }
}

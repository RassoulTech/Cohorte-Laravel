<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

/**
 * Epingler une publication en tete du fil de sa promotion.
 *
 * Ressource singleton, comme la reponse retenue : une publication est epinglee
 * ou ne l'est pas, il n'y a pas d'identifiant d'epinglage.
 */
class EpinglageController extends Controller
{
    use AuthorizesRequests;

    public function store(Publication $publication): RedirectResponse
    {
        $this->authorize('epingler', $publication);

        // epingle_le est caste en datetime dans le modele : on y met un objet
        // Carbon, pas une chaine.
        $publication->update(['epingle_le' => now()]);

        return back()->with('succes', 'Publication épinglée en tête du fil.');
    }

    public function destroy(Publication $publication): RedirectResponse
    {
        $this->authorize('epingler', $publication);

        $publication->update(['epingle_le' => null]);

        return back()->with('succes', 'Publication désépinglée.');
    }
}

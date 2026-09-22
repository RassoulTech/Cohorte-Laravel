<?php

namespace App\Http\Controllers\Entraide;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Services\ServiceDetectionDoublon;
use Illuminate\View\View;

/**
 * Verifier si une question ressemble a une question deja posee.
 *
 * C'est la SEULE route de l'application soumise au middleware quota.ia :
 * la detection de doublon est une assistance, retirable sans dommage, alors
 * que la moderation est une contrainte imposee par l'application.
 */
class DetectionDoublonController extends Controller
{
    public function store(StoreQuestionRequest $request, ServiceDetectionDoublon $detection): View
    {
        $donnees = $request->validated();

        $similaires = $detection->chercherSimilaires($donnees['titre'], $request->user());

        // On reaffiche le formulaire avec la saisie conservee et, le cas
        // echeant, la liste des questions proches. Le champ cache
        // doublon_verifie passe a 1 : le second envoi publiera directement.
        return view('entraide.create', [
            'similaires' => $similaires,
            'donnees' => $donnees,
            'verifie' => true,
        ]);
    }
}

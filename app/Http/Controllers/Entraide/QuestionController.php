<?php

namespace App\Http\Controllers\Entraide;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Models\Publication;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Les questions vivent dans la MEME table que les posts : c'est la decision de
 * conception de la phase 1. Le scope questions() les separe, et un controleur
 * distinct leur donne leur propre module, leurs propres routes et leurs propres
 * vues, sans dupliquer la moderation ni le signalement.
 */
class QuestionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Publication::class);

        $questions = Publication::query()
            ->questions()                                    // type = 'question'
            ->visibles()                                     // statut = 'publie'
            ->deLaPromotion($request->user()->promotion_id)  // le cloisonnement
            ->with('auteur')
            ->withCount('reponses')                          // un COUNT, sans charger les lignes
            ->latest()
            ->paginate(15);

        return view('entraide.index', compact('questions'));
    }

    public function create(): View
    {
        $this->authorize('create', Publication::class);

        return view('entraide.create');
    }

    public function store(StoreQuestionRequest $request): RedirectResponse
    {
        $this->authorize('create', Publication::class);

        $question = Publication::create([
            ...$request->validated(),
            'type' => 'question',
            'user_id' => $request->user()->id,

            // La promotion vient de l'utilisateur connecte, jamais du formulaire.
            'promotion_id' => $request->user()->promotion_id,
            'statut' => 'publie',
        ]);

        return redirect()
            ->route('questions.show', $question)
            ->with('succes', 'Votre question est publiée.');
    }

    public function show(Publication $question): View
    {
        // Meme protection que le fil : la liaison de modele de route a trouve
        // l'enregistrement, elle n'a verifie aucun droit.
        $this->authorize('view', $question);

        // Une question et un post partagent la meme table : rien n'empeche de
        // saisir /questions/{id} avec l'identifiant d'un post. On refuse.
        abort_unless($question->type === 'question', 404);

        // reponses.auteur est une relation imbriquee : les auteurs des reponses
        // sont charges en une requete de plus, pas une par reponse.
        $question->load(['auteur', 'reponses.auteur', 'reponseRetenue']);

        return view('entraide.show', compact('question'));
    }
}

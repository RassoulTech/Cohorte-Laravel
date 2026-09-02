<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicationRequest;
use App\Models\Publication;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicationController extends Controller
{
    // Depuis Laravel 11, la classe Controller de base est VIDE : ce trait doit
    // etre importe explicitement. Son oubli produit l'erreur
    // "Call to undefined method authorizeResource()".
    use AuthorizesRequests;

    /*
     * POURQUOI PAS authorizeResource() DANS LE CONSTRUCTEUR ?
     *
     * Le guide propose $this->authorizeResource(Publication::class, 'publication')
     * dans le constructeur. Cette methode appelle en interne $this->middleware(),
     * qui N'EXISTE PLUS depuis Laravel 11 : la classe Controller de base est
     * desormais vide. Le resultat est une erreur 500 :
     *   Call to undefined method PublicationController::middleware()
     *
     * Deux remplacements possibles. Implementer l'interface HasMiddleware et sa
     * methode statique middleware(), qui reproduit le mecanisme ; ou appeler
     * $this->authorize() explicitement dans chaque methode. J'ai retenu la
     * seconde : l'autorisation est VISIBLE la ou elle s'applique, au lieu
     * d'etre deduite d'une convention de nommage.
     *
     * Le partage des roles reste le meme :
     *   - $this->authorize() protege LA PAGE DE DETAIL (l'URL tapee a la main) ;
     *   - le scope deLaPromotion() protege LA LISTE.
     * Proteger l'un sans l'autre laisse la porte ouverte.
     */

    public function index(Request $request): View
    {
        // Correspond a PublicationPolicy::viewAny()
        $this->authorize('viewAny', Publication::class);

        $publications = Publication::query()
            ->posts()                                        // type = 'post'
            ->visibles()                                     // statut = 'publie'
            ->deLaPromotion($request->user()->promotion_id)  // LE cloisonnement
            ->with('auteur')                                 // anti N+1 : 2 requetes, pas 16
            ->withCount('signalements')                      // un COUNT, sans charger les lignes
            ->orderByRaw('epingle_le IS NULL')               // 0 (epinglees) avant 1
            ->orderByDesc('epingle_le')
            ->latest()                                       // puis les plus recentes
            ->paginate(15);

        return view('feed.index', compact('publications'));
    }

    public function create(): View
    {
        // Correspond a PublicationPolicy::create() : l'enseignant ne publie pas.
        $this->authorize('create', Publication::class);

        return view('feed.create');
    }

    public function store(StorePublicationRequest $request): RedirectResponse
    {
        $this->authorize('create', Publication::class);

        // validated() ne renvoie QUE les champs passes par les regles du
        // FormRequest : un champ ajoute a la main dans le formulaire ne peut
        // pas se glisser dans le create().
        $publication = Publication::create([
            ...$request->validated(),
            'type' => 'post',
            'user_id' => $request->user()->id,

            // La promotion vient de l'utilisateur connecte, JAMAIS du
            // formulaire : sinon n'importe qui publierait chez les autres.
            'promotion_id' => $request->user()->promotion_id,

            // 'publie' pour l'instant ; la moderation IA decidera en phase 7.
            'statut' => 'publie',
        ]);

        return redirect()
            ->route('publications.show', $publication)
            ->with('succes', 'Votre publication est en ligne.');
    }

    public function show(Publication $publication): View
    {
        // LA ligne qui renvoie 403 quand Fatou (groupe B) saisit l'URL d'une
        // publication d'Awa (groupe A). La liaison de modele de route a deja
        // trouve l'enregistrement : elle ne verifie aucun droit.
        $this->authorize('view', $publication);

        // load() precharge sur un modele DEJA charge par la liaison de route.
        // reponses.auteur est une relation imbriquee : les auteurs des reponses
        // sont charges eux aussi, en une requete de plus et pas une par reponse.
        $publication->load('auteur', 'reponses.auteur');

        return view('feed.show', compact('publication'));
    }

    public function destroy(Publication $publication): RedirectResponse
    {
        // Son auteur, ou le delegue de la promotion concernee.
        $this->authorize('delete', $publication);

        $publication->delete();

        return redirect()
            ->route('publications.index')
            ->with('succes', 'Publication supprimée.');
    }
}

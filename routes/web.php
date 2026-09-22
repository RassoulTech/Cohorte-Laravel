<?php

use App\Http\Controllers\Entraide\DetectionDoublonController;
use App\Http\Controllers\Entraide\QuestionController;
use App\Http\Controllers\Entraide\ReponseController;
use App\Http\Controllers\Entraide\ReponseRetenueController;
use App\Http\Controllers\Feed\PublicationController;
use App\Http\Controllers\Moderation\FileModerationController;
use App\Http\Controllers\Moderation\SignalementController;
use App\Http\Controllers\Profil\ProfilController;
use App\Http\Controllers\Promotion\AdhesionController;
use App\Http\Controllers\Promotion\PromotionController;
use Illuminate\Support\Facades\Route;

// La seule page accessible sans etre connecte. Les routes d'authentification
// (login, register, forgot-password...) sont declarees par Fortify lui-meme :
// on ne les ecrit pas ici. Verifiable avec php artisan route:list.
Route::get('/', fn () => view('accueil'))->name('accueil');

/*
|--------------------------------------------------------------------------
| Connecte, mais PAS forcement rattache a une promotion
|--------------------------------------------------------------------------
| Ces routes sont volontairement hors du middleware 'promotion'. C'est ici
| qu'on envoie ceux qui n'ont pas de promotion : les proteger par 'promotion'
| creerait une boucle de redirection infinie.
*/
Route::middleware('auth')->group(function () {
    Route::get('/rejoindre', [AdhesionController::class, 'create'])->name('promotion.rejoindre');
    Route::post('/rejoindre', [AdhesionController::class, 'store'])->name('promotion.adherer');

    Route::get('/profil', [ProfilController::class, 'show'])->name('profil.show');

    // Le module de l'enseignant : c'est la destination vers laquelle le
    // middleware ExigePromotion le redirige.
    Route::get('/promotions', [PromotionController::class, 'index'])
        ->name('enseignant.promotions.index');

    // Le fil d'une promotion vu par l'enseignant. Volontairement hors du
    // groupe 'promotion' : n'ayant pas de promotion_id, il y serait redirige.
    Route::get('/promotions/{promotion}/fil', [PromotionController::class, 'fil'])
        ->name('enseignant.promotions.fil');
});

/*
|--------------------------------------------------------------------------
| Reserve aux membres rattaches a une promotion
|--------------------------------------------------------------------------
| Toute route de ce groupe est certaine de recevoir un utilisateur dont
| promotion_id n'est pas null. Le fil, l'entraide et la moderation des
| phases 5 a 10 viendront ici.
*/
Route::middleware(['auth', 'promotion'])->group(function () {
    // Route::resource declare les 7 routes conventionnelles d'un coup. On
    // restreint aux 5 utiles : une publication ne se modifie pas, elle se
    // supprime. Verifiable avec php artisan route:list --name=publications
    Route::resource('publications', PublicationController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    // ------------------------------------------------------------ Entraide
    Route::resource('questions', QuestionController::class)
        ->only(['index', 'create', 'store', 'show']);

    // LA SEULE route soumise au quota d'IA : la detection de doublon est une
    // assistance, retirable sans dommage. La moderation, elle, est une
    // contrainte imposee par l'application et n'y est pas soumise.
    Route::post('questions/verifier-doublon', [DetectionDoublonController::class, 'store'])
        ->middleware('quota.ia')
        ->name('questions.doublon');

    // Une reponse appartient a une question : son URL le dit.
    Route::post('questions/{question}/reponses', [ReponseController::class, 'store'])
        ->name('reponses.store');
    Route::delete('reponses/{reponse}', [ReponseController::class, 'destroy'])
        ->name('reponses.destroy');

    // Ressource singleton : une question a AU PLUS une reponse retenue, d'ou
    // l'absence d'identifiant dans l'URL. store() la designe, destroy() la retire.
    Route::post('questions/{question}/reponse-retenue', [ReponseRetenueController::class, 'store'])
        ->name('reponse-retenue.store');
    Route::delete('questions/{question}/reponse-retenue', [ReponseRetenueController::class, 'destroy'])
        ->name('reponse-retenue.destroy');

    // --------------------------------------------------------- Moderation
    // Signaler n'importe quelle publication : post ou question, meme table.
    Route::post('publications/{publication}/signalements', [SignalementController::class, 'store'])
        ->name('signalements.store');

    // La file du delegue. Reservee par abort_unless dans le controleur : on ne
    // cree pas un middleware pour une seule route.
    Route::get('moderation', [FileModerationController::class, 'index'])
        ->name('moderation.index');
    Route::patch('moderation/{publication}', [FileModerationController::class, 'update'])
        ->name('moderation.update');
});

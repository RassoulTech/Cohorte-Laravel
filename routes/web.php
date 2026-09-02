<?php

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
    //
});

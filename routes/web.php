<?php

use Illuminate\Support\Facades\Route;

// La seule page accessible sans etre connecte. Les routes d'authentification
// (login, register, forgot-password...) sont declarees par Fortify lui-meme :
// on ne les ecrit pas ici. Verifiable avec php artisan route:list.
Route::get('/', fn () => view('accueil'))->name('accueil');

// Tout le reste de l'application est derriere le middleware auth : un visiteur
// non connecte est redirige vers /login. Les routes metier des phases 4 a 10
// viendront toutes dans ce groupe.
Route::middleware('auth')->group(function () {
    //
});

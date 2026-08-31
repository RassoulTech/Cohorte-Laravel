<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ------------------------------------------------------- Les actions
        // Fortify expose les routes ; ce sont ces classes, publiees dans
        // app/Actions/Fortify/, qui executent reellement le travail. C'est
        // donc la que se branche la logique metier de Cohorte.
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // --------------------------------------------------------- Les vues
        // Fortify est "frontend agnostic" : il ne fournit aucune vue et ne fait
        // aucune hypothese sur l'interface. Chaque route GET d'authentification
        // a besoin qu'on lui indique quel fichier Blade afficher.
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));

        // Cette vue-ci recoit la requete : elle porte le jeton de
        // reinitialisation et l'adresse e-mail, a replacer en champs caches.
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', [
            'request' => $request,
        ]));

        // La route GET /user/confirm-password existe des que la fonctionnalite
        // updatePasswords est active. Sans vue declaree, l'ouvrir provoquerait
        // une erreur : on lui en donne une.
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));

        // ------------------------------------- La limitation des tentatives
        // Sans limitation, un attaquant peut essayer des milliers de mots de
        // passe a la seconde sur une adresse connue.
        //
        // La cle combine l'ADRESSE E-MAIL et l'ADRESSE IP :
        //  - e-mail seul : un attaquant bloquerait le compte d'un tiers en
        //    epuisant volontairement son quota depuis chez lui ;
        //  - IP seule : toute une promotion derriere le meme routeur se
        //    bloquerait mutuellement.
        // Fortify::username() renvoie 'email', valeur reglee dans config/fortify.php.
        RateLimiter::for('login', function (Request $request) {
            $cle = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($cle);
        });
    }
}

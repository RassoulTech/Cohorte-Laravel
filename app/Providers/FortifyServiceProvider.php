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

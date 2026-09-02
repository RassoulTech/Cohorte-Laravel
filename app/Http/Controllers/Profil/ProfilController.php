<?php

namespace App\Http\Controllers\Profil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function show(Request $request): View
    {
        // load() precharge la relation sur un modele DEJA charge, la ou with()
        // s'utilise sur une requete pas encore executee. Sans lui,
        // preventLazyLoading pourrait se declencher dans la vue.
        $membre = $request->user()->load('promotion');

        return view('profil.show', compact('membre'));
    }
}

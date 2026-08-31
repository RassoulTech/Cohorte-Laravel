@extends('layouts.app')
@section('titre', 'Cohorte — accueil')

@section('contenu')
    <h1>Cohorte</h1>

    <p>
        Le réseau social de votre promotion. On y entre sur invitation, et on n'y
        voit que le contenu de sa propre promotion.
    </p>

    @guest
        {{-- @guest ne s'affiche que pour un visiteur non connecte.
             Son contraire est @auth, utilise dans le gabarit. --}}
        <p class="liens">
            <a href="{{ route('login') }}" class="bouton">Se connecter</a>
            <a href="{{ route('register') }}">Créer un compte avec un code d'invitation</a>
        </p>
    @endguest

    @auth
        <p>Vous êtes connecté en tant que {{ auth()->user()->name }}.</p>
    @endauth
@endsection

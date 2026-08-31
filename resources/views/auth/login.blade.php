@extends('layouts.app')
@section('titre', 'Connexion')

@section('contenu')
    <h1>Se connecter à Cohorte</h1>

    {{-- action = la route POST login declaree par Fortify.
         On n'ecrit jamais l'URL en dur : route() la construit. --}}
    <form method="POST" action="{{ route('login') }}">
        {{-- @csrf insere le jeton anti-falsification de requete.
             Sans lui, Laravel renvoie une erreur 419 Page Expired. --}}
        @csrf

        <div class="champ">
            {{-- for et id relient l'etiquette au champ : cliquer sur le texte
                 place le curseur, et un lecteur d'ecran annonce correctement. --}}
            <label for="email">Adresse e-mail</label>

            {{-- old() reaffiche la valeur saisie quand la validation echoue :
                 l'utilisateur n'a pas a tout retaper. --}}
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}" required autofocus>

            @error('email')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" required>

            @error('password')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            <label class="case">
                <input type="checkbox" name="remember"> Se souvenir de moi
            </label>
        </div>

        <button type="submit">Connexion</button>
    </form>

    <p class="liens">
        <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
        —
        <a href="{{ route('register') }}">Créer un compte</a>
    </p>
@endsection

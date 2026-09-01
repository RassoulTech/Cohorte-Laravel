@extends('layouts.app')
@section('titre', 'Mot de passe oublié')

@section('contenu')
    <h1>Mot de passe oublié</h1>

    <p>
        Saisissez votre adresse e-mail : nous vous enverrons un lien permettant
        de choisir un nouveau mot de passe.
    </p>

    {{-- Fortify depose sa confirmation dans session('status'), et non dans
         session('succes') : le composant alerte ne la voit pas. --}}
    @if (session('status'))
        <div class="alerte alerte--succes">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="champ">
            <label for="email">Adresse e-mail</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}" required autofocus>

            @error('email')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">Envoyer le lien</button>
    </form>

    <p class="liens">
        <a href="{{ route('login') }}">Retour à la connexion</a>
    </p>
@endsection

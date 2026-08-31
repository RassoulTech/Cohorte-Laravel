@extends('layouts.app')
@section('titre', 'Créer un compte')

@section('contenu')
    <h1>Rejoindre Cohorte</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="champ">
            <label for="name">Nom complet</label>
            <input id="name" type="text" name="name"
                   value="{{ old('name') }}" required autofocus>

            @error('name')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            <label for="email">Adresse e-mail</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}" required>

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
            {{-- La regle 'confirmed' de PasswordValidationRules impose un second
                 champ nomme exactement password_confirmation. --}}
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password"
                   name="password_confirmation" required>
        </div>

        <button type="submit">Créer mon compte</button>
    </form>

    <p class="liens">
        Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a>
    </p>
@endsection

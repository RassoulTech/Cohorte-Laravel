@extends('layouts.app')
@section('titre', 'Nouveau mot de passe')

@section('contenu')
    <h1>Choisir un nouveau mot de passe</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        {{-- Le jeton vient de l'URL /reset-password/{token}. Il est a usage
             unique et a duree limitee : c'est lui qui prouve que la demande
             vient bien du titulaire de la boite e-mail. --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="champ">
            <label for="email">Adresse e-mail</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email', $request->email) }}" required>

            @error('email')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            <label for="password">Nouveau mot de passe</label>
            <input id="password" type="password" name="password" required autofocus>

            @error('password')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password"
                   name="password_confirmation" required>
        </div>

        <button type="submit">Enregistrer</button>
    </form>
@endsection

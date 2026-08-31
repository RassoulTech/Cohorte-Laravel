@extends('layouts.app')
@section('titre', 'Confirmer le mot de passe')

@section('contenu')
    <h1>Confirmation requise</h1>

    <p>Cette action est sensible : saisissez à nouveau votre mot de passe.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="champ">
            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" required autofocus>

            @error('password')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">Confirmer</button>
    </form>
@endsection

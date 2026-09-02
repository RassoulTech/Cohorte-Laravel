@extends('layouts.app')
@section('titre', 'Publier')

@section('contenu')
    <h1>Publier dans {{ auth()->user()->promotion->nom }}</h1>

    <form method="POST" action="{{ route('publications.store') }}">
        @csrf

        <div class="champ">
            <label for="titre">Titre <span class="facultatif">(facultatif)</span></label>
            <input id="titre" type="text" name="titre"
                   value="{{ old('titre') }}" maxlength="150" autofocus>

            @error('titre')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            <label for="contenu">Votre message</label>
            <textarea id="contenu" name="contenu" rows="8" required>{{ old('contenu') }}</textarea>

            @error('contenu')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">Publier</button>
    </form>

    <p class="liens">
        <a href="{{ route('publications.index') }}">Retour au fil</a>
    </p>
@endsection

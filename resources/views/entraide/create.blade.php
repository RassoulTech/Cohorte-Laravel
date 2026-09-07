@extends('layouts.app')
@section('titre', 'Poser une question')

@section('contenu')
    <h1>Poser une question</h1>

    <p>
        Un titre clair aide les autres à comprendre votre problème d'un coup
        d'œil, et permet de retrouver la question plus tard.
    </p>

    <form method="POST" action="{{ route('questions.store') }}">
        @csrf

        <div class="champ">
            {{-- Contrairement au post, le titre est OBLIGATOIRE ici : c'est lui
                 que la detection de doublon comparera en phase 9. --}}
            <label for="titre">Votre question en une phrase</label>
            <input id="titre" type="text" name="titre"
                   value="{{ old('titre') }}" required autofocus maxlength="150"
                   placeholder="ex. Comment éviter le problème des requêtes N+1 ?">

            @error('titre')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            <label for="contenu">Décrivez votre problème</label>
            <textarea id="contenu" name="contenu" rows="8" required
                      placeholder="Ce que vous avez essayé, le message d'erreur exact, ce que vous attendiez.">{{ old('contenu') }}</textarea>

            @error('contenu')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">Publier ma question</button>
    </form>

    <p class="liens">
        <a href="{{ route('questions.index') }}">Retour à l'entraide</a>
    </p>
@endsection

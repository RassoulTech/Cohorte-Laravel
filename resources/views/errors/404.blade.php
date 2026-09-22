@extends('layouts.app')
@section('titre', 'Page introuvable')

@section('contenu')
    <h1>Page introuvable</h1>

    <p>
        Cette adresse ne correspond à rien. La publication a peut-être été
        supprimée, ou l'adresse comporte une erreur.
    </p>

    <p class="liens">
        <a href="{{ url('/') }}" class="bouton">Retour à l'accueil</a>
    </p>
@endsection

@extends('layouts.app')
@section('titre', 'Fil — ' . $promotion->nom)

@section('contenu')
    <div class="entete-fil">
        <h1>{{ $promotion->nom }}</h1>
        <a href="{{ route('enseignant.promotions.index') }}">Retour aux promotions</a>
    </div>

    <p class="meta">
        Consultation en lecture seule : vous voyez aussi les publications
        masquées, refusées ou en attente de modération.
    </p>

    {{-- Le meme composant que le fil des apprenants : une carte de publication
         s'affiche de la meme facon partout, et il n'y a qu'un fichier a
         modifier le jour ou sa presentation change. --}}
    @forelse ($publications as $publication)
        <x-carte-publication :publication="$publication" />
    @empty
        <p class="vide">Cette promotion n'a encore rien publié.</p>
    @endforelse

    {{ $publications->links() }}
@endsection

@extends('layouts.app')
@section('titre', 'Les promotions')

@section('contenu')
    <h1>Les promotions</h1>

    <p>
        Vous consultez Cohorte en tant qu'enseignant : vous voyez toutes les
        promotions, sans appartenir à aucune.
    </p>

    {{-- @forelse gere la liste vide dans le meme bloc qu'un @foreach.
         Une page blanche quand il n'y a rien est sanctionnee en phase 10. --}}
    @forelse ($promotions as $promotion)
        <article class="carte">
            <h3>{{ $promotion->nom }}</h3>
            <p>
                Code d'invitation : <strong>{{ $promotion->code_invitation }}</strong>
                — {{ $promotion->ouverte ? 'inscriptions ouvertes' : 'inscriptions closes' }}
            </p>
            <p>
                {{-- membres_count et publications_count sont ajoutes par le
                     withCount() du controleur : aucune requete de plus ici. --}}
                {{ $promotion->membres_count }} membre(s) —
                {{ $promotion->publications_count }} publication(s)
            </p>
        </article>
    @empty
        <p class="vide">Aucune promotion enregistrée pour l'instant.</p>
    @endforelse
@endsection

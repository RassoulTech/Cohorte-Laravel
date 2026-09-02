@extends('layouts.app')
@section('titre', 'Fil de la promotion')

@section('contenu')
    <div class="entete-fil">
        <h1>{{ auth()->user()->promotion->nom }}</h1>

        {{-- @can interroge la POLICY : le bouton n'apparait que si la methode
             create() de PublicationPolicy renvoie true. Attention, cacher un
             bouton ne protege rien : c'est authorizeResource() dans le
             controleur qui bloque reellement la route. --}}
        @can('create', App\Models\Publication::class)
            <a href="{{ route('publications.create') }}" class="bouton">Publier</a>
        @endcan
    </div>

    {{-- @forelse gere la liste vide dans le meme bloc qu'un @foreach.
         Une page blanche quand il n'y a rien est sanctionnee. --}}
    @forelse ($publications as $publication)
        <x-carte-publication :publication="$publication" />
    @empty
        <p class="vide">Aucune publication pour l'instant. Lancez la conversation.</p>
    @endforelse

    {{-- links() n'affiche la pagination que si le controleur a utilise
         paginate() et non get(). --}}
    {{ $publications->links() }}
@endsection

@extends('layouts.app')
@section('titre', $publication->titre ?? 'Publication')

@section('contenu')
    <article class="carte">
        @if ($publication->epingle_le)
            <p class="etiquette">Épinglée</p>
        @endif

        @if ($publication->titre)
            <h1>{{ $publication->titre }}</h1>
        @endif

        {{-- nl2br conserve les retours a la ligne saisis par l'auteur.
             e() echappe le HTML AVANT : sans lui, quelqu'un pourrait injecter
             du script dans la page. On n'utilise donc jamais {!! !!} sur du
             contenu utilisateur brut. --}}
        <p>{!! nl2br(e($publication->contenu)) !!}</p>

        <footer class="meta">
            {{ $publication->auteur->name }}
            — {{ $publication->created_at->diffForHumans() }}

            @if ($publication->statut !== 'publie')
                — <strong>{{ $publication->statut }}</strong>
            @endif
        </footer>
    </article>

    {{-- Message affiche a son auteur quand sa publication n'est plus dans le
         fil : il doit comprendre pourquoi elle a disparu. --}}
    @if ($publication->statut !== 'publie' && $publication->user_id === auth()->id())
        <div class="alerte alerte--erreur">
            Cette publication n'apparaît pas dans le fil.
            @if ($publication->motif_moderation)
                Motif : {{ $publication->motif_moderation }}
            @endif
        </div>
    @endif

    {{-- @can appelle PublicationPolicy::epingler() : le bouton n'apparait que
         si la reputation atteint le seuil, ou si l'on est delegue. --}}
    @can('epingler', $publication)
        <p class="liens">
            @if ($publication->epingle_le)
                <form method="POST" action="{{ route('epinglage.destroy', $publication) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bouton-secondaire">Désépingler</button>
                </form>
            @else
                <form method="POST" action="{{ route('epinglage.store', $publication) }}">
                    @csrf
                    <button type="submit" class="bouton-secondaire">Épingler en tête du fil</button>
                </form>
            @endif
        </p>
    @endcan

    <x-formulaire-signalement :publication="$publication" />

    <p class="liens">
        <a href="{{ route('publications.index') }}">Retour au fil</a>

        {{-- @can appelle la methode delete() de la policy avec CETTE
             publication : le bouton n'apparait que pour son auteur ou le
             delegue de la promotion. --}}
        @can('delete', $publication)
            <form method="POST" action="{{ route('publications.destroy', $publication) }}"
                  onsubmit="return confirm('Supprimer cette publication ?');">
                @csrf
                {{-- Un navigateur ne sait envoyer que GET et POST : @method
                     ajoute un champ cache _method que Laravel interprete. --}}
                @method('DELETE')
                <button type="submit" class="bouton-danger">Supprimer</button>
            </form>
        @endcan
    </p>
@endsection

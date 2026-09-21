@extends('layouts.app')
@section('titre', 'File de modération')

@section('contenu')
    <h1>File de modération — {{ auth()->user()->promotion->nom }}</h1>

    <p class="meta">
        Publications mises en attente par la modération automatique, ou masquées
        après {{ config('cohorte.seuil_signalement') }} signalements.
        Vous ne voyez que celles de votre promotion.
    </p>

    @forelse ($publications as $publication)
        <article class="carte">
            <p class="etiquette">{{ $publication->statut }}</p>

            @if ($publication->titre)
                <h3>{{ $publication->titre }}</h3>
            @endif

            <p>{!! nl2br(e($publication->contenu)) !!}</p>

            <footer class="meta">
                {{ $publication->auteur->name }}
                — {{ $publication->created_at->diffForHumans() }}
                — {{ $publication->signalements_count }} signalement(s)

                @if ($publication->motif_moderation)
                    <br>Motif : {{ $publication->motif_moderation }}
                @endif
            </footer>

            <div class="decisions">
                {{-- PATCH est impossible depuis un navigateur : @method ajoute
                     un champ cache _method que Laravel interprete. --}}
                <form method="POST" action="{{ route('moderation.update', $publication) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="decision" value="valider">
                    <button type="submit">Remettre en ligne</button>
                </form>

                <form method="POST" action="{{ route('moderation.update', $publication) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="decision" value="refuser">
                    <button type="submit" class="bouton-danger">Refuser</button>
                </form>
            </div>
        </article>
    @empty
        <p class="vide">Rien à modérer. Tout est en ordre dans votre promotion.</p>
    @endforelse

    {{ $publications->links() }}
@endsection

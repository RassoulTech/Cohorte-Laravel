@extends('layouts.app')
@section('titre', 'Entraide')

@section('contenu')
    <div class="entete-fil">
        <h1>Entraide — {{ auth()->user()->promotion->nom }}</h1>

        @can('create', App\Models\Publication::class)
            <a href="{{ route('questions.create') }}" class="bouton">Poser une question</a>
        @endcan
    </div>

    @forelse ($questions as $question)
        <article class="carte">
            <h3>
                <a href="{{ route('questions.show', $question) }}">{{ $question->titre }}</a>
            </h3>

            <p>{{ Str::limit($question->contenu, 180) }}</p>

            <footer class="meta">
                {{ $question->auteur->name }}
                — {{ $question->created_at->diffForHumans() }}

                {{-- reponses_count vient du withCount() du controleur :
                     aucune requete supplementaire dans cette boucle. --}}
                — {{ $question->reponses_count }} réponse(s)

                @if ($question->reponse_retenue_id)
                    — <strong class="resolue">Résolue</strong>
                @endif
            </footer>
        </article>
    @empty
        <p class="vide">Aucune question pour l'instant. Soyez le premier à demander de l'aide.</p>
    @endforelse

    {{ $questions->links() }}
@endsection

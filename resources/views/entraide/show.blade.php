@extends('layouts.app')
@section('titre', $question->titre)

@section('contenu')
    <article class="carte">
        <h1>{{ $question->titre }}</h1>

        <p>{!! nl2br(e($question->contenu)) !!}</p>

        <footer class="meta">
            {{ $question->auteur->name }} — {{ $question->created_at->diffForHumans() }}
        </footer>
    </article>

    <h2>{{ $question->reponses->count() }} réponse(s)</h2>

    @forelse ($question->reponses as $reponse)
        {{-- La reponse retenue est mise en avant : c'est l'interet du module. --}}
        <article class="carte {{ $question->reponse_retenue_id === $reponse->id ? 'carte--retenue' : '' }}">
            @if ($question->reponse_retenue_id === $reponse->id)
                <p class="etiquette">Réponse retenue</p>
            @endif

            <p>{!! nl2br(e($reponse->contenu)) !!}</p>

            <footer class="meta">
                {{ $reponse->auteur->name }} — {{ $reponse->created_at->diffForHumans() }}
            </footer>

            {{-- Le bouton n'apparait que pour l'auteur de la QUESTION, et
                 seulement si cette reponse n'est pas deja retenue. La policy
                 designerReponse() est verifiee a nouveau cote controleur. --}}
            @can('designerReponse', $question)
                @if ($question->reponse_retenue_id !== $reponse->id)
                    <form method="POST" action="{{ route('reponse-retenue.store', $question) }}">
                        @csrf
                        {{-- Ce champ cache est modifiable par n'importe qui dans
                             le navigateur : c'est pourquoi le controleur verifie
                             avec abort_unless que la reponse appartient bien a
                             cette question. --}}
                        <input type="hidden" name="reponse_id" value="{{ $reponse->id }}">
                        <button type="submit">Retenir cette réponse</button>
                    </form>
                @endif
            @endcan
        </article>
    @empty
        <p class="vide">Personne n'a encore répondu. Vous pouvez être le premier.</p>
    @endforelse

    @can('repondre', $question)
        <h2>Répondre</h2>

        <form method="POST" action="{{ route('reponses.store', $question) }}">
            @csrf

            <div class="champ">
                <label for="contenu">Votre réponse</label>
                <textarea id="contenu" name="contenu" rows="6" required>{{ old('contenu') }}</textarea>

                @error('contenu')
                    <p class="erreur">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit">Publier ma réponse</button>
        </form>
    @endcan

    <p class="liens">
        <a href="{{ route('questions.index') }}">Retour à l'entraide</a>
    </p>
@endsection

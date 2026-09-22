@extends('layouts.app')
@section('titre', 'Poser une question')

@section('contenu')
    <h1>Poser une question</h1>

    {{-- $donnees et $similaires ne sont passes que par le controleur de
         detection. ?? garantit que la vue fonctionne aussi au premier
         affichage, quand elles n'existent pas. --}}
    @php
        $donnees = $donnees ?? [];
        $similaires = $similaires ?? null;
        $verifie = $verifie ?? false;
    @endphp

    @if ($similaires !== null)
        @if ($similaires === [])
            <div class="alerte alerte--succes">
                Aucune question similaire trouvée. Vous pouvez publier.
            </div>
        @else
            <div class="alerte alerte--erreur">
                <strong>Votre question a peut-être déjà été posée.</strong>
                Consultez ces questions avant de publier.
            </div>

            @foreach ($similaires as $proche)
                <article class="carte">
                    <h3>
                        <a href="{{ route('questions.show', $proche) }}" target="_blank">
                            {{ $proche->titre }}
                        </a>
                    </h3>
                    <p>{{ Str::limit($proche->contenu, 160) }}</p>
                    <footer class="meta">{{ $proche->auteur->name }}</footer>
                </article>
            @endforeach
        @endif
    @endif

    <form method="POST" action="{{ route('questions.store') }}">
        @csrf

        <div class="champ">
            <label for="titre">Votre question en une phrase</label>
            <input id="titre" type="text" name="titre"
                   value="{{ old('titre', $donnees['titre'] ?? '') }}"
                   required autofocus maxlength="150"
                   placeholder="ex. Comment éviter le problème des requêtes N+1 ?">

            @error('titre')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            <label for="contenu">Décrivez votre problème</label>
            <textarea id="contenu" name="contenu" rows="8" required
                      placeholder="Ce que vous avez essayé, le message d'erreur exact, ce que vous attendiez.">{{ old('contenu', $donnees['contenu'] ?? '') }}</textarea>

            @error('contenu')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">{{ $verifie ? 'Publier quand même' : 'Publier ma question' }}</button>
    </form>

    {{-- Second formulaire, vers la route soumise au quota. Il n'apparait que
         si le membre a encore des appels disponibles, et une seule fois :
         apres verification, le bouton de publication suffit. --}}
    @if (! $verifie && auth()->user()->peutAppelerIa())
        <form method="POST" action="{{ route('questions.doublon') }}" class="verif-doublon">
            @csrf
            <input type="hidden" name="titre" id="copie-titre">
            <input type="hidden" name="contenu" id="copie-contenu">
            <button type="submit" class="bouton-secondaire">
                Vérifier si la question existe déjà
                ({{ auth()->user()->quotaIaRestant() }} appel(s) restant(s))
            </button>
        </form>

        {{-- Recopie la saisie dans le second formulaire au moment de l'envoi :
             un champ HTML ne peut appartenir qu'a un seul formulaire. --}}
        <script>
            document.querySelector('.verif-doublon').addEventListener('submit', function () {
                document.getElementById('copie-titre').value = document.getElementById('titre').value;
                document.getElementById('copie-contenu').value = document.getElementById('contenu').value;
            });
        </script>
    @endif

    <p class="liens">
        <a href="{{ route('questions.index') }}">Retour à l'entraide</a>
    </p>
@endsection

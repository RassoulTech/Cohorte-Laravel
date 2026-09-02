@props(['publication'])

{{-- @props declare les donnees attendues par le composant. On l'appelle
     ensuite avec <x-carte-publication :publication="$publication" />
     Le deux-points devant l'attribut passe une VARIABLE, pas une chaine. --}}

<article class="carte">
    @if ($publication->epingle_le)
        <p class="etiquette">Épinglée</p>
    @endif

    @if ($publication->titre)
        <h3>
            <a href="{{ route('publications.show', $publication) }}">{{ $publication->titre }}</a>
        </h3>
    @endif

    {{-- Str::limit coupe proprement le texte du fil ; la publication entiere
         est visible sur sa page de detail. --}}
    <p>{{ Str::limit($publication->contenu, 200) }}</p>

    <footer class="meta">
        {{-- auteur est prechargee par le with() du controleur : aucune requete
             supplementaire ici, malgre la boucle de la vue parente. --}}
        {{ $publication->auteur->name }}
        — {{ $publication->created_at->diffForHumans() }}

        @if ($publication->signalements_count > 0)
            — {{ $publication->signalements_count }} signalement(s)
        @endif

        @if ($publication->statut !== 'publie')
            — <strong>{{ $publication->statut }}</strong>
        @endif
    </footer>

    <p class="liens">
        <a href="{{ route('publications.show', $publication) }}">Lire la suite</a>
    </p>
</article>

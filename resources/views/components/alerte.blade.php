{{-- Messages flash poses par un controleur avec ->with('succes', '...').
     Ils ne survivent qu'a UNE redirection, puis disparaissent. --}}

@if (session('succes'))
    <div class="alerte alerte--succes">{{ session('succes') }}</div>
@endif

@if (session('erreur'))
    <div class="alerte alerte--erreur">{{ session('erreur') }}</div>
@endif

{{-- Les erreurs de validation ne sont PAS listees ici : chaque formulaire les
     affiche sous le champ concerne avec @error(), la ou l'utilisateur les
     attend. Les lister aussi en haut de page les afficherait deux fois. --}}

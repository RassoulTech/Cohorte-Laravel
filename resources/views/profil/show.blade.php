@extends('layouts.app')
@section('titre', 'Mon profil')

@section('contenu')
    <h1>Mon profil</h1>

    <article class="carte">
        <p><strong>Nom :</strong> {{ $membre->name }}</p>
        <p><strong>Adresse e-mail :</strong> {{ $membre->email }}</p>

        <p>
            <strong>Rôle :</strong>
            {{-- match() est plus sur qu'une suite de @if : si un role inconnu
                 apparaissait un jour, le cas par defaut le rendrait visible. --}}
            @php
                $libelleRole = match ($membre->role) {
                    'enseignant' => 'Enseignant',
                    'delegue' => 'Délégué',
                    'apprenant' => 'Apprenant',
                    default => $membre->role,
                };
            @endphp
            {{ $libelleRole }}
        </p>

        <p>
            <strong>Promotion :</strong>
            {{-- L'operateur ?-> evite l'erreur "Attempt to read property on null"
                 pour l'enseignant, qui n'a pas de promotion. --}}
            {{ $membre->promotion?->nom ?? 'aucune' }}
        </p>

    </article>

    {{-- L'enseignant ne publie pas : la reputation n'a pas de sens pour lui. --}}
    @unless ($membre->estEnseignant())
        <p class="meta">
            Réputation : <strong>{{ $membre->points }}</strong> point(s).
            @if ($membre->points >= config('cohorte.seuil_epinglage'))
                Vous pouvez épingler une publication en tête du fil.
            @else
                Il vous faut {{ config('cohorte.seuil_epinglage') }} points pour
                pouvoir épingler une publication.
            @endif
        </p>
    @endunless

    {{-- L'enseignant n'appartient a aucune promotion et ne doit pas en
         rejoindre une : on ne lui propose pas le lien. La regle est aussi
         appliquee cote controleur, l'affichage ne protege rien a lui seul. --}}
    @unless ($membre->promotion_id || $membre->estEnseignant())
        <p class="liens">
            <a href="{{ route('promotion.rejoindre') }}" class="bouton">Rejoindre une promotion</a>
        </p>
    @endunless
@endsection

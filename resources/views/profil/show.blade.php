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

        <p><strong>Points de contribution :</strong> {{ $membre->points }}</p>
    </article>

    @unless ($membre->promotion_id)
        <p class="liens">
            <a href="{{ route('promotion.rejoindre') }}" class="bouton">Rejoindre une promotion</a>
        </p>
    @endunless
@endsection

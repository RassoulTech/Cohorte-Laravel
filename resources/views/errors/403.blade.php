@extends('layouts.app')
@section('titre', 'Accès refusé')

@section('contenu')
    <h1>Accès refusé</h1>

    <p>
        Cette page appartient à une autre promotion, ou demande un rôle que vous
        n'avez pas. Le contenu de Cohorte est cloisonné par promotion : vous ne
        voyez que celui de la vôtre.
    </p>

    @if ($exception?->getMessage())
        <p class="meta">{{ $exception->getMessage() }}</p>
    @endif

    <p class="liens">
        @auth
            @if (auth()->user()->estEnseignant())
                <a href="{{ route('enseignant.promotions.index') }}" class="bouton">Les promotions</a>
            @elseif (auth()->user()->promotion_id)
                <a href="{{ route('publications.index') }}" class="bouton">Retour au fil</a>
            @endif
        @else
            <a href="{{ route('login') }}" class="bouton">Se connecter</a>
        @endauth
    </p>
@endsection

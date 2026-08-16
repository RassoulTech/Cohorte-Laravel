<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Cohorte')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="barre">
        <a href="{{ url('/') }}" class="logo">Cohorte</a>

        @auth
            <nav>
                {{-- Les liens Fil et Entraide seront ajoutes ici avec leurs
                     routes, en phases 5 et 6. --}}
                <span>{{ auth()->user()->name }}</span>

                @if (Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Deconnexion</button>
                    </form>
                @endif
            </nav>
        @endauth
    </header>

    <main class="conteneur">
        <x-alerte />

        @yield('contenu')
    </main>
</body>
</html>

@extends('layouts.app')
@section('titre', 'Créer un compte')

@section('contenu')
    <h1>Rejoindre Cohorte</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="champ">
            <label for="name">Nom complet</label>
            <input id="name" type="text" name="name"
                   value="{{ old('name') }}" required autofocus>

            @error('name')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            <label for="email">Adresse e-mail</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}" required>

            @error('email')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            {{-- C'est ce champ qui rattache le nouveau membre a sa promotion.
                 Sa verification a lieu dans app/Actions/Fortify/CreateNewUser.php. --}}
            <label for="code_invitation">Code d'invitation de votre promotion</label>
            <input id="code_invitation" type="text" name="code_invitation"
                   value="{{ old('code_invitation') }}" required
                   placeholder="ex. DWA2026">

            {{-- ValidationException::withMessages() rattache l'erreur a ce champ
                 precis : elle s'affiche donc ici, et non en haut de page. --}}
            @error('code_invitation')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" required>

            @error('password')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <div class="champ">
            {{-- La regle 'confirmed' de PasswordValidationRules impose un second
                 champ nomme exactement password_confirmation. --}}
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password"
                   name="password_confirmation" required>
        </div>

        <button type="submit">Créer mon compte</button>
    </form>

    <p class="liens">
        Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a>
    </p>
@endsection

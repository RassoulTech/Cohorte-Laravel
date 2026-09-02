@extends('layouts.app')
@section('titre', 'Rejoindre une promotion')

@section('contenu')
    <h1>Rejoindre une promotion</h1>

    <p>
        Votre compte n'est rattaché à aucune promotion. Saisissez le code
        d'invitation que votre formateur vous a communiqué pour accéder au fil
        de votre groupe.
    </p>

    <form method="POST" action="{{ route('promotion.adherer') }}">
        @csrf

        <div class="champ">
            <label for="code_invitation">Code d'invitation</label>
            <input id="code_invitation" type="text" name="code_invitation"
                   value="{{ old('code_invitation') }}" required autofocus
                   placeholder="ex. DWA2026">

            @error('code_invitation')
                <p class="erreur">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">Rejoindre</button>
    </form>
@endsection

@props(['publication'])

{{-- @can appelle la policy signaler() : le formulaire disparait pour l'auteur
     de la publication et pour toute personne d'une autre promotion. La regle
     est aussi appliquee cote controleur — cacher un formulaire ne protege rien. --}}
@can('signaler', $publication)
    <details class="signalement">
        <summary>Signaler cette publication</summary>

        <form method="POST" action="{{ route('signalements.store', $publication) }}">
            @csrf

            <div class="champ">
                <label for="motif-{{ $publication->id }}">Motif</label>
                <select id="motif-{{ $publication->id }}" name="motif" required>
                    <option value="">Choisir…</option>
                    <option value="insulte">Insulte ou harcèlement</option>
                    <option value="hors_sujet">Hors sujet</option>
                    <option value="publicite">Publicité</option>
                    <option value="autre">Autre</option>
                </select>

                @error('motif')
                    <p class="erreur">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bouton-danger">Envoyer le signalement</button>
        </form>
    </details>
@endcan

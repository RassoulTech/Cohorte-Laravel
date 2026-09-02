<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Un FormRequest sort la validation du controleur : elle devient reutilisable,
 * et on voit en un coup d'oeil ce que la route accepte. Si la validation
 * echoue, Laravel redirige automatiquement en arriere avec les erreurs et les
 * anciennes valeurs, sans qu'on ecrive une ligne pour cela.
 */
class StorePublicationRequest extends FormRequest
{
    /**
     * Premiere barriere : sans promotion, on ne publie pas. La policy create()
     * verifie la meme chose du cote du controleur ; les deux se completent.
     */
    public function authorize(): bool
    {
        return $this->user()->promotion_id !== null
            && ! $this->user()->estEnseignant();
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            // Le titre est facultatif sur un post, obligatoire sur une question
            // (phase 6) : d'ou le nullable ici.
            'titre' => ['nullable', 'string', 'max:150'],
            'contenu' => ['required', 'string', 'min:10', 'max:3000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contenu.required' => 'Votre publication ne peut pas être vide.',
            'contenu.min' => 'Votre publication doit faire au moins :min caractères.',
            'contenu.max' => 'Votre publication ne peut pas dépasser :max caractères.',
            'titre.max' => 'Le titre ne peut pas dépasser :max caractères.',
        ];
    }
}

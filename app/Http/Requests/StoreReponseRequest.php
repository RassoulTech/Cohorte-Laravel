<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReponseRequest extends FormRequest
{
    /**
     * Le droit de repondre a CETTE question est verifie par la policy dans le
     * controleur : ici on ne controle que l'etat general de l'utilisateur.
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
            'contenu' => ['required', 'string', 'min:10', 'max:3000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contenu.required' => 'Votre réponse ne peut pas être vide.',
            'contenu.min' => 'Votre réponse doit faire au moins :min caractères.',
            'contenu.max' => 'Votre réponse ne peut pas dépasser :max caractères.',
        ];
    }
}

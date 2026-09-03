<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Une question n'est pas un post : son titre est OBLIGATOIRE.
 * C'est lui qui sera compare aux questions existantes par la detection de
 * doublon de la phase 9 ; une question sans titre y serait invisible.
 */
class StoreQuestionRequest extends FormRequest
{
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
            'titre' => ['required', 'string', 'min:10', 'max:150'],
            'contenu' => ['required', 'string', 'min:10', 'max:3000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titre.required' => 'Donnez un titre à votre question.',
            'titre.min' => 'Le titre doit faire au moins :min caractères pour être compréhensible.',
            'titre.max' => 'Le titre ne peut pas dépasser :max caractères.',
            'contenu.required' => 'Décrivez votre problème.',
            'contenu.min' => 'Votre question doit faire au moins :min caractères.',
            'contenu.max' => 'Votre question ne peut pas dépasser :max caractères.',
        ];
    }
}

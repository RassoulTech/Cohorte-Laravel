<?php

namespace App\Actions\Fortify;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Fortify appelle cette classe pour creer un compte. C'est donc l'endroit
     * exact ou brancher la logique metier de Cohorte : on n'entre pas dans
     * l'application sans le code d'invitation d'une promotion ouverte.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),

            // max:12 correspond a la taille de la colonne code_invitation.
            'code_invitation' => ['required', 'string', 'max:12'],
        ], [
            // Laravel n'est pas traduit en francais par defaut : on fournit nos
            // propres messages pour que le formulaire reste coherent.
            'name.required' => 'Votre nom est obligatoire.',
            'email.required' => 'Votre adresse e-mail est obligatoire.',
            'email.email' => 'Cette adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est deja utilisee.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'code_invitation.required' => 'Le code d\'invitation de votre promotion est obligatoire.',
        ])->validate();

        $promotion = Promotion::where('code_invitation', $input['code_invitation'])->first();

        // On distingue les deux refus : un code inconnu et une promotion fermee
        // ne se corrigent pas de la meme facon cote utilisateur.
        //
        // withMessages() plutot qu'un message flash : l'erreur est ainsi
        // rattachee au CHAMP code_invitation et remonte dans $errors, donc la
        // vue l'affiche sous le bon champ avec @error(), sans code en plus.
        if (! $promotion) {
            throw ValidationException::withMessages([
                'code_invitation' => 'Ce code d\'invitation n\'existe pas.',
            ]);
        }

        // 'ouverte' est castee en booleen dans le modele (phase 1) : sans ce
        // cast, MySQL renverrait la chaine "0", qui n'est pas falsy en PHP.
        if (! $promotion->ouverte) {
            throw ValidationException::withMessages([
                'code_invitation' => 'Les inscriptions a cette promotion sont closes.',
            ]);
        }

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),

            // Le rattachement automatique : c'est lui qui fait fonctionner tout
            // le cloisonnement des phases suivantes.
            'promotion_id' => $promotion->id,
            'role' => 'apprenant',
        ]);
    }
}

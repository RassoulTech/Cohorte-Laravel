<?php

// Messages de validation en francais.
//
// Laravel construit chaque message a partir de la REGLE qui a echoue :
// la regle 'required' sur le champ 'titre' cherche la cle 'required' ci-dessous,
// et remplace :attribute par le nom du champ, traduit via le tableau
// 'attributes' place en bas de ce fichier.
//
// Les messages ecrits directement dans un Validator::make() ou dans la methode
// messages() d'un FormRequest restent PRIORITAIRES sur ceux-ci.

return [

    'accepted' => 'Le champ :attribute doit être accepté.',
    'active_url' => "Le champ :attribute n'est pas une URL valide.",
    'after' => 'Le champ :attribute doit être une date postérieure au :date.',
    'after_or_equal' => 'Le champ :attribute doit être une date postérieure ou égale au :date.',
    'alpha' => 'Le champ :attribute ne peut contenir que des lettres.',
    'alpha_dash' => 'Le champ :attribute ne peut contenir que des lettres, des chiffres et des tirets.',
    'alpha_num' => 'Le champ :attribute ne peut contenir que des lettres et des chiffres.',
    'array' => 'Le champ :attribute doit être un tableau.',
    'before' => 'Le champ :attribute doit être une date antérieure au :date.',
    'before_or_equal' => 'Le champ :attribute doit être une date antérieure ou égale au :date.',

    'between' => [
        'array' => 'Le champ :attribute doit contenir entre :min et :max éléments.',
        'file' => 'Le fichier :attribute doit peser entre :min et :max kilo-octets.',
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'string' => 'Le champ :attribute doit contenir entre :min et :max caractères.',
    ],

    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => "Le champ :attribute n'est pas une date valide.",
    'date_equals' => 'Le champ :attribute doit être une date égale au :date.',
    'date_format' => 'Le champ :attribute ne correspond pas au format :format.',
    'different' => 'Les champs :attribute et :other doivent être différents.',
    'digits' => 'Le champ :attribute doit contenir :digits chiffres.',
    'digits_between' => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'email' => "Le champ :attribute n'est pas une adresse e-mail valide.",
    'ends_with' => 'Le champ :attribute doit se terminer par une de ces valeurs : :values.',
    'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
    'file' => 'Le champ :attribute doit être un fichier.',
    'filled' => 'Le champ :attribute doit avoir une valeur.',

    'gt' => [
        'array' => 'Le champ :attribute doit contenir plus de :value éléments.',
        'file' => 'Le fichier :attribute doit peser plus de :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur à :value.',
        'string' => 'Le champ :attribute doit contenir plus de :value caractères.',
    ],

    'gte' => [
        'array' => 'Le champ :attribute doit contenir au moins :value éléments.',
        'file' => 'Le fichier :attribute doit peser au moins :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :value.',
        'string' => 'Le champ :attribute doit contenir au moins :value caractères.',
    ],

    'image' => 'Le champ :attribute doit être une image.',
    'in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'ip' => 'Le champ :attribute doit être une adresse IP valide.',
    'json' => 'Le champ :attribute doit être un document JSON valide.',

    'lt' => [
        'array' => 'Le champ :attribute doit contenir moins de :value éléments.',
        'file' => 'Le fichier :attribute doit peser moins de :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
        'string' => 'Le champ :attribute doit contenir moins de :value caractères.',
    ],

    'lte' => [
        'array' => 'Le champ :attribute doit contenir au plus :value éléments.',
        'file' => 'Le fichier :attribute doit peser au plus :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :value.',
        'string' => 'Le champ :attribute doit contenir au plus :value caractères.',
    ],

    'max' => [
        'array' => 'Le champ :attribute ne peut pas contenir plus de :max éléments.',
        'file' => 'Le fichier :attribute ne peut pas peser plus de :max kilo-octets.',
        'numeric' => 'Le champ :attribute ne peut pas être supérieur à :max.',
        'string' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
    ],

    'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'mimetypes' => 'Le champ :attribute doit être un fichier de type : :values.',

    'min' => [
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file' => 'Le fichier :attribute doit peser au moins :min kilo-octets.',
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],

    'not_in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'numeric' => 'Le champ :attribute doit être un nombre.',

    // Messages de la regle Password::default() utilisee par Fortify.
    'password' => [
        'letters' => 'Le champ :attribute doit contenir au moins une lettre.',
        'mixed' => 'Le champ :attribute doit contenir au moins une majuscule et une minuscule.',
        'numbers' => 'Le champ :attribute doit contenir au moins un chiffre.',
        'symbols' => 'Le champ :attribute doit contenir au moins un caractère spécial.',
        'uncompromised' => 'Ce :attribute est apparu dans une fuite de données. Merci d\'en choisir un autre.',
    ],

    'present' => 'Le champ :attribute doit être présent.',
    'prohibited' => 'Le champ :attribute est interdit.',
    'regex' => 'Le format du champ :attribute est invalide.',
    'required' => 'Le champ :attribute est obligatoire.',
    'required_if' => 'Le champ :attribute est obligatoire quand :other vaut :value.',
    'required_unless' => 'Le champ :attribute est obligatoire sauf si :other est dans :values.',
    'required_with' => 'Le champ :attribute est obligatoire quand :values est présent.',
    'required_without' => 'Le champ :attribute est obligatoire quand :values est absent.',
    'same' => 'Les champs :attribute et :other doivent être identiques.',

    'size' => [
        'array' => 'Le champ :attribute doit contenir :size éléments.',
        'file' => 'Le fichier :attribute doit peser :size kilo-octets.',
        'numeric' => 'Le champ :attribute doit valoir :size.',
        'string' => 'Le champ :attribute doit contenir :size caractères.',
    ],

    'starts_with' => 'Le champ :attribute doit commencer par une de ces valeurs : :values.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'timezone' => 'Le champ :attribute doit être un fuseau horaire valide.',
    // Formule volontairement sans :attribute : « Cette valeur de adresse
    // e-mail » serait fautif, et le message s'affiche deja sous le bon champ.
    'unique' => 'Cette valeur est déjà utilisée.',
    'uploaded' => 'Le téléversement du fichier :attribute a échoué.',
    'url' => "Le champ :attribute n'est pas une URL valide.",

    /*
    |--------------------------------------------------------------------------
    | Messages sur mesure
    |--------------------------------------------------------------------------
    | Format : 'champ.regle' => 'message'. Utile quand un champ merite une
    | formulation particuliere sans encombrer le controleur.
    */

    'custom' => [
        'code_invitation' => [
            'required' => "Le code d'invitation de votre promotion est obligatoire.",
        ],
        'contenu' => [
            'required' => 'Votre publication ne peut pas être vide.',
            'min' => 'Votre publication doit faire au moins :min caractères.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Noms des champs
    |--------------------------------------------------------------------------
    | Remplace :attribute dans tous les messages ci-dessus. Sans ce tableau,
    | Laravel afficherait le nom technique de la colonne, par exemple
    | « Le champ code_invitation est obligatoire ».
    */

    'attributes' => [
        'name' => 'nom',
        'email' => 'adresse e-mail',
        'password' => 'mot de passe',
        'password_confirmation' => 'confirmation du mot de passe',
        'code_invitation' => "code d'invitation",
        'titre' => 'titre',
        'contenu' => 'contenu',
        'motif' => 'motif',
        'decision' => 'décision',
        'reponse_id' => 'réponse',
    ],

];

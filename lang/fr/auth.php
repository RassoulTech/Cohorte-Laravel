<?php

// Messages d'authentification. Laravel les cherche via la cle auth.failed,
// auth.password, auth.throttle. C'est Fortify qui les declenche.
return [
    'failed' => 'Ces identifiants ne correspondent à aucun compte.',
    'password' => 'Le mot de passe est incorrect.',
    'throttle' => 'Trop de tentatives de connexion. Merci de réessayer dans :seconds secondes.',
];

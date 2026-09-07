<?php

namespace App\Enums;

/**
 * Le verdict de la moderation, exprime par une enumeration plutot qu'une chaine.
 *
 * Une enumeration interdit d'ecrire 'acceptble' par erreur, garantit que tous
 * les cas sont traites dans un match(), et l'editeur propose les valeurs
 * possibles. Le cas Indisponible est le plus important : il represente la panne
 * d'OpenRouter, et c'est la configuration qui decide de la conduite a tenir.
 */
enum VerdictModeration: string
{
    case Acceptable = 'acceptable';
    case Douteux = 'douteux';
    case Inacceptable = 'inacceptable';
    case Indisponible = 'indisponible';

    /**
     * Traduit un verdict en statut de publication.
     *
     * Le match() est exhaustif : si l'on ajoutait un cas a l'enumeration sans
     * le traiter ici, PHP leverait une UnhandledMatchError au lieu de laisser
     * passer silencieusement une valeur inconnue.
     */
    public function statutPublication(): string
    {
        return match ($this) {
            self::Acceptable => 'publie',
            self::Douteux => 'en_moderation',
            self::Inacceptable => 'refuse',

            // La decision fail-open / fail-closed, pilotee par la configuration
            // et argumentee dans docs/DECISIONS.md.
            self::Indisponible => config('cohorte.moderation_fail_open')
                ? 'publie'
                : 'en_moderation',
        };
    }

    /**
     * Le message affiche a l'auteur apres sa publication.
     */
    public function message(): string
    {
        return match ($this) {
            self::Acceptable => 'Votre publication est en ligne.',
            self::Inacceptable => 'Votre publication a été refusée par la modération automatique.',
            self::Douteux => 'Votre publication est en attente de validation par un délégué.',
            self::Indisponible => config('cohorte.moderation_fail_open')
                ? 'Votre publication est en ligne.'
                : 'La modération est momentanément indisponible : votre publication sera validée par un délégué.',
        };
    }
}

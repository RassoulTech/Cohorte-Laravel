<?php

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;

/**
 * La gardienne du cloisonnement.
 *
 * ATTENTION au partage des roles, c'est LE point de la phase :
 *  - le SCOPE deLaPromotion() protege LA LISTE (ce qu'on affiche) ;
 *  - cette POLICY protege LA PAGE DE DETAIL (l'URL tapee a la main).
 * Proteger l'un sans l'autre laisse la porte ouverte. La liaison de modele de
 * route ne verifie AUCUN droit : elle trouve la publication n. 12 et la donne,
 * qu'on ait le droit de la voir ou non.
 *
 * Depuis Laravel 11 cette classe est decouverte automatiquement : nom du modele
 * suivi de Policy, dans app/Policies. Aucun enregistrement manuel.
 */
class PublicationPolicy
{
    /**
     * Peut-on afficher une liste de publications ?
     * Un membre rattache voit sa promotion ; l'enseignant les voit toutes.
     */
    public function viewAny(User $user): bool
    {
        return $user->promotion_id !== null || $user->estEnseignant();
    }

    /**
     * Peut-on afficher CETTE publication ? C'est la methode qui decide de la
     * note : c'est elle qui renvoie 403 quand Fatou (groupe B) saisit l'URL
     * d'une publication d'Awa (groupe A).
     */
    public function view(User $user, Publication $publication): bool
    {
        // L'enseignant est un observateur en lecture seule, au-dessus du
        // cloisonnement : il consulte le contenu de toutes les promotions.
        if ($user->estEnseignant()) {
            return true;
        }

        // Une publication qui n'est pas publiee (refusee, masquee, en attente)
        // reste visible pour SON auteur, qui doit comprendre ce qui lui arrive.
        // Pour les autres, seul le delegue de la meme promotion y accede.
        if ($publication->statut !== 'publie' && $publication->user_id !== $user->id) {
            return $user->estDelegue() && $user->promotion_id === $publication->promotion_id;
        }

        // La regle de cloisonnement, dans sa forme la plus simple.
        return $user->promotion_id === $publication->promotion_id;
    }

    /**
     * L'enseignant ne publie jamais : c'est ce qui definit son role.
     */
    public function create(User $user): bool
    {
        return $user->promotion_id !== null && ! $user->estEnseignant();
    }

    /**
     * Son auteur, ou le delegue de la promotion concernee. Etre delegue ne
     * suffit pas : il faut etre delegue DE CETTE promotion.
     */
    public function delete(User $user, Publication $publication): bool
    {
        return $user->id === $publication->user_id
            || ($user->estDelegue() && $user->promotion_id === $publication->promotion_id);
    }

    /**
     * On ne signale pas sa propre publication, ni celle d'une autre promotion.
     * Utilisee des la phase 8 ; ecrite ici pour que la regle vive au meme
     * endroit que les autres.
     */
    public function signaler(User $user, Publication $publication): bool
    {
        return $user->promotion_id === $publication->promotion_id
            && $user->id !== $publication->user_id;
    }
}

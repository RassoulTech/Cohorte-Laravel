<?php

namespace Database\Factories;

use App\Models\Publication;
use App\Models\Reponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reponse>
 */
class ReponseFactory extends Factory
{
    /**
     * Reponses d'entraide en francais. Volontairement generiques pour rester
     * plausibles quelle que soit la question tiree au sort par la factory des
     * publications.
     *
     * @var list<string>
     */
    protected array $reponses = [
        "J'ai eu exactement le meme souci la semaine derniere. Regarde du cote de la documentation officielle, la section correspondante est courte et repond precisement a ce cas.",
        "Le plus simple est de tester dans tinker avant de passer par le navigateur : tu vois tout de suite si le probleme vient de la requete ou de l'affichage.",
        "Attention, ca vient souvent d'un cache qui n'a pas ete vide. Lance php artisan optimize:clear et reessaie avant de chercher plus loin.",
        "Chez moi le probleme venait du fillable du modele : la colonne n'y figurait pas, donc Laravel l'ignorait sans lever la moindre erreur.",
        "Pense a verifier tes routes avec php artisan route:list. Neuf fois sur dix le nom de la route ne correspond pas a ce qu'on croit.",
        "Ne le fais surtout pas uniquement dans la vue : cacher un bouton n'empeche personne d'appeler l'adresse directement. La verification doit etre cote serveur.",
        "Je te conseille de commencer par ecrire ce que la methode doit faire en francais, en commentaire, avant de coder. Ca m'a evite beaucoup d'allers-retours.",
        "Regarde la trace de l'erreur jusqu'au fichier concerne, meme si c'est dans le dossier vendor. C'est souvent la qu'on comprend ce qui manque vraiment.",
        "Il faut creer une nouvelle migration : modifier un fichier deja execute ne change rien a la base, sauf a tout rejouer avec migrate:fresh.",
        "La difference se joue sur l'emplacement de la cle etrangere. Si elle est sur ta table, c'est belongsTo ; si elle est sur l'autre, c'est hasMany.",
        "Ajoute le chargement anticipe sur ta requete, avec with, et le nombre de requetes tombe de seize a deux. La difference est immediatement visible.",
        "Merci d'avoir pose la question, je bloquais dessus depuis deux jours sans oser demander. La reponse au-dessus a resolu mon cas aussi.",
        "Attention a la casse dans les noms de fichiers : ca fonctionne sous Windows et ca casse sous Linux, donc chez le correcteur.",
        "Chez moi c'etait la directive csrf oubliee dans le formulaire. L'erreur ne le dit pas clairement, mais c'est presque toujours ca.",
        "Ce dossier ne doit pas etre envoye sur le depot : il se reconstruit avec composer install a partir des fichiers de dependances.",
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // On repond a une QUESTION, jamais a un post : d'ou l'etat
            // ->question() sur la factory imbriquee. Utile si on appelle
            // Reponse::factory()->create() tout seul, hors du seeder.
            'publication_id' => Publication::factory()->question(),
            'user_id' => User::factory(),
            'contenu' => fake()->randomElement($this->reponses),
        ];
    }
}

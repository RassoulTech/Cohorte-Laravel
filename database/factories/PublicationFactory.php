<?php

namespace Database\Factories;

use App\Models\Promotion;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * Contenus de demonstration en francais.
     *
     * Le lorem ipsum de Faker est du faux latin : il ne permet pas de juger si
     * une carte est lisible, si un titre passe sur une ligne, ni si le fil
     * ressemble a quelque chose. Des contenus reels d'ecole de developpement
     * web rendent la demonstration credible devant un jury.
     *
     * @var list<array{titre: string, contenu: string}>
     */
    protected array $posts = [
        ['titre' => 'Retour sur le TP de la semaine',
         'contenu' => "J'ai enfin termine le TP sur les formulaires. Le plus dur a ete de comprendre pourquoi mes donnees ne s'enregistraient pas : il manquait simplement une colonne dans le fillable du modele. Si ca bloque chez vous, commencez par la."],

        ['titre' => 'Ressource utile sur les migrations',
         'contenu' => "La documentation officielle de Laravel sur les migrations est beaucoup plus claire que les tutoriels que j'avais trouves. La partie sur les cles etrangeres m'a debloque en dix minutes."],

        ['titre' => 'Attention a la casse sous Linux',
         'contenu' => "Mon projet marchait sur mon portable et plantait sur celui d'un camarade. La cause : j'avais nomme une vue Index.blade.php avec une majuscule. Windows ne fait pas la difference, Linux si. Pensez-y avant la soutenance."],

        ['titre' => 'Seance de revision samedi',
         'contenu' => "On se retrouve samedi matin en salle 3 pour reviser les relations Eloquent. Apportez vos projets, on debuggera ensemble. Tout le monde est le bienvenu, meme si vous etes en retard sur le programme."],

        ['titre' => 'Git : ce que j\'aurais aime savoir plus tot',
         'contenu' => "Faites des commits petits et frequents. J'ai passe deux heures a essayer de comprendre un commit ou j'avais tout modifie d'un coup. Depuis que je commite une idee a la fois, je relis mon historique sans effort."],

        ['titre' => 'Petit rappel sur les tableaux blancs',
         'contenu' => "Merci d'effacer le tableau apres vos sessions de travail en salle 2. La promo du soir arrive juste apres nous et repart de zero a chaque fois."],

        ['titre' => 'Mon erreur du jour : le cache de configuration',
         'contenu' => "Je modifiais mon fichier .env sans aucun effet. En fait j'avais lance php artisan config:cache la veille. Un php artisan optimize:clear a tout regle. A retenir quand une modification semble ignoree."],

        ['titre' => 'Comment vous organisez vos branches ?',
         'contenu' => "Je commence a m'y perdre entre mes branches de fonctionnalites. J'ai adopte le format feat/numero-sujet et ca va deja beaucoup mieux. Curieux de savoir ce que vous faites de votre cote."],

        ['titre' => 'Le debogueur de Laravel est vraiment pratique',
         'contenu' => "J'utilisais des echo partout pour comprendre mes bugs. La page d'erreur de Laravel donne la ligne exacte et la pile d'appels : on gagne un temps fou en prenant le temps de la lire au lieu de la fermer."],

        ['titre' => 'Stage : retour d\'experience',
         'contenu' => "Je termine deux mois de stage dans une agence a Dakar. Ce qui m'a le plus servi, ce n'est pas la technique mais l'habitude de poser des questions precises et de documenter ce que je faisais."],

        ['titre' => 'Pensez a sauvegarder votre base',
         'contenu' => "J'ai lance un migrate:fresh sans reflechir et perdu toutes mes donnees de test. Heureusement mes seeders etaient a jour et tout est revenu en une commande. C'est exactement a ca qu'ils servent."],

        ['titre' => 'Un point sur les conventions de nommage',
         'contenu' => "Nommer une relation auteur() plutot que user() change vraiment la lisibilite des vues. On lit publication auteur name comme une phrase. Ca ne coute rien et ca se voit dans toute l'application."],

        ['titre' => 'Salle informatique fermee mardi',
         'contenu' => "Maintenance du reseau toute la journee de mardi. Prevoyez de travailler depuis chez vous ou a la bibliotheque. La connexion sera retablie mercredi matin."],

        ['titre' => 'Retour sur la presentation d\'hier',
         'contenu' => "Merci a ceux qui sont venus assister aux presentations. Les questions du public etaient tres pertinentes et m'ont fait voir plusieurs angles morts dans mon projet."],

        ['titre' => 'Les raccourcis qui m\'ont fait gagner du temps',
         'contenu' => "php artisan route:list pour verifier mes routes, php artisan tinker pour tester une requete sans passer par le navigateur, et php artisan make:model avec l'option -mf pour tout generer d'un coup."],

        ['titre' => 'Question de methode plus que de code',
         'contenu' => "Je me suis rendu compte que je codais avant d'avoir compris le probleme. Depuis que j'ecris en francais ce que la fonction doit faire avant de l'ecrire en PHP, je fais beaucoup moins d'allers-retours."],

        ['titre' => 'Groupe d\'entraide le soir',
         'contenu' => "On a monte un petit groupe qui se retrouve en visio en semaine vers vingt heures. On avance chacun sur son projet et on s'aide quand quelqu'un bloque. Dites-moi si vous voulez en etre."],

        ['titre' => 'Ne codez pas les valeurs en dur',
         'contenu' => "J'avais ecrit le seuil de trois signalements directement dans mon controleur. Le jour ou j'ai voulu le tester avec une autre valeur, j'ai du modifier trois fichiers. Depuis, tout va dans un fichier de configuration."],
    ];

    /**
     * @var list<array{titre: string, contenu: string}>
     */
    protected array $questions = [
        ['titre' => 'Comment eviter le probleme des requetes N+1 ?',
         'contenu' => "Ma page met plusieurs secondes a s'afficher des que j'ai une trentaine d'elements dans ma liste. On m'a parle de chargement anticipe mais je ne vois pas ou le placer exactement. Quelqu'un peut m'expliquer simplement ?"],

        ['titre' => 'Difference entre belongsTo et hasMany ?',
         'contenu' => "Je confonds systematiquement les deux et je finis par essayer au hasard jusqu'a ce que ca marche. Y a-t-il une regle simple pour savoir laquelle utiliser de quel cote ?"],

        ['titre' => 'Erreur 419 Page Expired au moment de valider mon formulaire',
         'contenu' => "Mon formulaire de connexion renvoie une erreur 419 des que je clique sur envoyer. Les champs sont pourtant tous remplis. J'ai vide le cache du navigateur, sans resultat."],

        ['titre' => 'Ma migration ne modifie plus la base',
         'contenu' => "J'ai ajoute une colonne dans un fichier de migration deja execute, mais elle n'apparait pas dans la table. Faut-il creer une nouvelle migration a chaque fois ou existe-t-il une commande pour rejouer ?"],

        ['titre' => 'Ou placer la verification des droits ?',
         'contenu' => "Je cache mes boutons avec une condition dans la vue selon le role de l'utilisateur. Est-ce suffisant ou faut-il verifier ailleurs aussi ?"],

        ['titre' => 'Comment passer une variable du controleur a la vue ?',
         'contenu' => "J'ai une erreur qui dit que ma variable n'est pas definie dans la vue, alors que je la remplis bien dans le controleur. Je dois rater une etape entre les deux."],

        ['titre' => 'Une valeur ne s\'enregistre pas, sans aucun message d\'erreur',
         'contenu' => "Quand j'enregistre mon formulaire, tous les champs passent sauf un, qui reste vide en base. Aucune erreur ne s'affiche, ce qui rend le probleme difficile a chercher."],

        ['titre' => 'Comment tester une page qui appelle un service externe ?',
         'contenu' => "Chaque essai consomme un appel a une API limitee en nombre de requetes. Existe-t-il un moyen de simuler la reponse pour developper tranquillement ?"],

        ['titre' => 'Quelle difference entre git revert et git reset ?',
         'contenu' => "J'ai pousse un commit qui casse mon application. On m'a dit de ne surtout pas utiliser reset mais je ne comprends pas bien pourquoi, ni ce que revert fait de different."],

        ['titre' => 'Comment nommer proprement mes routes ?',
         'contenu' => "J'ecris mes liens avec des URL completes dans mes vues, et des que je change une adresse je dois corriger partout. Il parait qu'on peut faire autrement."],

        ['titre' => 'Mes accents s\'affichent mal en base de donnees',
         'contenu' => "Les caracteres accentues apparaissent sous forme de symboles bizarres dans phpMyAdmin alors que tout est correct dans le formulaire. Est-ce un probleme de configuration de la base ?"],

        ['titre' => 'Comment organiser ses controleurs quand le projet grossit ?',
         'contenu' => "J'ai maintenant une douzaine de controleurs tous au meme endroit et je mets du temps a retrouver le bon. Comment structurez-vous les votres ?"],

        ['titre' => 'A quoi sert exactement un middleware ?',
         'contenu' => "J'ai compris que ca s'execute avant le controleur, mais je ne vois pas dans quels cas concrets en creer un plutot que de faire la verification directement dans la methode."],

        ['titre' => 'Faut-il commiter le dossier vendor ?',
         'contenu' => "Mon depot fait plusieurs centaines de megaoctets et le push prend un temps enorme. Un camarade m'a dit que je ne devrais pas envoyer ce dossier, mais alors comment mon projet fonctionne-t-il chez quelqu'un d'autre ?"],
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $post = fake()->randomElement($this->posts);

        return [
            // Une factory imbriquee se lit : "si personne ne me fournit de
            // promotion, fabrique-en une". Dans le seeder on fournit toujours
            // la valeur, donc ces deux lignes ne sont jamais executees la-bas.
            'promotion_id' => Promotion::factory(),
            'user_id' => User::factory(),

            'type' => 'post',
            'titre' => $post['titre'],
            'contenu' => $post['contenu'],
            'statut' => 'publie',

            // Des dates etalees sur 30 jours : sans cela toutes les
            // publications auraient le meme horodatage et le tri du fil
            // serait impossible a verifier.
            'created_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }

    /**
     * Un ETAT : il ecrase une partie de definition() sans la reecrire.
     * Usage : Publication::factory()->question()->create();
     */
    public function question(): static
    {
        return $this->state(function () {
            $question = fake()->randomElement($this->questions);

            return [
                'type' => 'question',
                'titre' => $question['titre'],
                'contenu' => $question['contenu'],
            ];
        });
    }

    /**
     * Utile des la phase 7 : alimenter la file de moderation du delegue.
     */
    public function enModeration(): static
    {
        return $this->state(fn () => [
            'statut' => 'en_moderation',
        ]);
    }
}

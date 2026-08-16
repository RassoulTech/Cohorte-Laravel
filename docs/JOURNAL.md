# Journal de bord — Projet Cohorte

> Une entrée par phase, dans l'ordre. Quatre paragraphes courts :
> ce que j'ai fait, pourquoi je l'ai fait ainsi, la difficulté rencontrée,
> comment je l'ai résolue.

---

## Phase 0 — Installation et mise en place du dépôt

### Ce que j'ai fait

J'ai installé le projet Laravel 12, généré la clé applicative et branché la
base de données sur MySQL/MariaDB lancé par XAMPP, avec une base `cohorte` en
`utf8mb4_unicode_ci`. J'ai vérifié que `php artisan migrate` et
`php artisan serve` fonctionnaient. J'ai ensuite créé le dépôt Git, vérifié que
`.env` était bien ignoré, mis `.env.example` à jour avec les variables MySQL et
les variables métier, ajouté `config/cohorte.php`, le gabarit
`resources/views/layouts/app.blade.php`, le composant `components/alerte.blade.php`
et la feuille de style `public/css/app.css`.

### Pourquoi je l'ai fait ainsi

Le guide recommande SQLite mais autorise MySQL : j'ai choisi MySQL parce que
c'est ce que j'utilise en cours avec XAMPP, et parce que je veux vérifier mes
clés étrangères et mes index sur le même moteur qu'en production. Les quatre
valeurs de règles métier (quota IA, seuil de signalement, seuil d'épinglage,
comportement en cas de panne de l'IA) sont dans `config/cohorte.php` et non en
dur dans le code : on peut changer un seuil pour tester une règle sans toucher
à une seule ligne de logique, et `config()` continue de fonctionner même après
un `php artisan config:cache`, ce qui n'est pas le cas d'un `env()` appelé
depuis un contrôleur.

### Difficulté rencontrée

Sous Git Bash, `mysql --version` répondait `command not found`, et la commande
`net start MySQL80` échouait puisqu'aucun service Windows de ce nom n'existe :
mon MySQL est démarré par le panneau de contrôle XAMPP, pas par un service.

### Comment je l'ai résolue

J'ai démarré MySQL depuis XAMPP Control Panel, vérifié l'écoute du port avec
`netstat -an | grep 3306`, puis ajouté `/c/xampp/mysql/bin` au `PATH` dans
`~/.bashrc`. J'ai compris au passage que le `PATH` est simplement la liste des
dossiers dans lesquels le shell cherche un exécutable.

---

## Phase 1 — Le modèle de données et les relations Eloquent

### Ce que j'ai fait

J'ai écrit les six migrations du schéma : `promotions`, l'ajout de
`promotion_id`, `role` et `points` sur `users`, `publications`, `reponses`,
`signalements`, `appels_ia`, plus une migration séparée pour la colonne
`reponse_retenue_id`. J'ai ensuite renseigné les `$fillable`, les `casts()`, les
relations Eloquent (`membres`, `publications`, `auteur`, `promotion`,
`reponses`, `signalements`, `reponseRetenue`, `appelsIa`) et les quatre scopes
`visibles`, `deLaPromotion`, `questions`, `posts`. J'ai enfin activé
`Model::preventLazyLoading()` en développement dans `AppServiceProvider`.

### Pourquoi je l'ai fait ainsi

Les posts et les questions partagent le même auteur, la même promotion, le même
statut de modération et la même possibilité d'être signalés : je les garde dans
une seule table `publications` distinguée par une colonne `type`, et j'isole les
questions avec le scope `questions()`. Deux tables m'auraient obligé à écrire
deux fois la logique de modération et de signalement. Les règles de lecture sont
dans des scopes et non recopiées dans les contrôleurs : le jour où la règle de
cloisonnement change, je n'ai qu'un seul endroit à modifier. Enfin, l'unicité
`(publication_id, user_id)` sur `signalements` est posée en base et pas seulement
en PHP : la base doit rendre le doublon impossible même si le contrôle applicatif
est contourné.

### Difficulté rencontrée

Trois choses m'ont bloqué. D'abord `php artisan make:model AppelIa -m` a généré
une table nommée `appel_ias`, alors que le cahier des charges demande
`appels_ia`. Ensuite, la colonne `reponse_retenue_id` de `publications` pointe
vers `reponses`, table qui n'existe pas encore au moment où `publications` est
créée : la migration échouait sur la clé étrangère. Enfin, mes migrations vides
avaient déjà été jouées, donc les modifier ne changeait plus rien à la base.

### Comment je l'ai résolue

J'ai renommé le fichier de migration en `create_appels_ia_table` et déclaré
`protected $table = 'appels_ia';` dans le modèle, pour que le nom de table reste
celui du cahier des charges même si Laravel en devinait un autre. Pour la
réponse retenue, j'ai créé une migration postérieure
`add_reponse_retenue_to_publications_table` qui ajoute la clé étrangère une fois
`reponses` créée — c'est le seul moyen de sortir d'une référence circulaire entre
deux tables. Et j'ai rejoué toute la base avec `php artisan migrate:fresh`, qui
supprime les tables et rejoue les migrations dans l'ordre des horodatages.

J'ai vérifié le tout dans Tinker : `$p->membres()->count()` renvoie bien `1`,
`$u->promotion->nom` renvoie `"Test"`, un second signalement identique lève une
`UniqueConstraintViolationException`, et parcourir des publications sans
`with('auteur')` lève une `LazyLoadingViolationException`. J'ai découvert à cette
occasion que ce garde-fou ne se déclenche que sur une collection de plusieurs
enregistrements : sur un modèle seul, il n'y a pas de N+1 possible, donc Laravel
laisse passer.

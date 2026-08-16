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

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

---

## Phase 2 — Factories et seeders

Branche : `feat/02-seeders`
Dates : 18 au 19 août 2026

### Ce que j'ai fait

J'ai rempli les trois factories générées au jour 1 par `make:model -mf`
(`PromotionFactory`, `PublicationFactory`, `ReponseFactory`), puis réécrit
`DatabaseSeeder` pour construire un jeu de démonstration complet. Le seeder crée
deux promotions distinctes, `DWA2026` et `DWB2026`, et applique à chacune la même
recette : huit membres, quinze publications, six questions avec de zéro à trois
réponses. Il ajoute ensuite les quatre comptes obligatoires du cahier des charges,
dont deux dans des promotions différentes et un enseignant sans promotion. Enfin
j'ai documenté ces comptes et les deux codes d'invitation dans le README.

### Pourquoi je l'ai fait ainsi

La factory décrit à quoi ressemble un objet typique, le seeder décide combien en
créer et pour qui : je n'ai donc mis aucune quantité dans les factories, et aucune
description d'objet dans le seeder. Les deux codes d'invitation sont écrits en dur
dans le seeder plutôt que générés au hasard, parce qu'ils doivent figurer dans le
README et servir au correcteur pour tester l'inscription. J'ai ajouté
`'created_at' => fake()->dateTimeBetween('-30 days')` dans la factory des
publications : sans cela, les quarante-deux publications auraient le même
horodatage à la seconde près et le tri du fil de la phase 5 serait invérifiable.
J'ai aussi mis les réponses à un nombre aléatoire entre zéro et trois pour que
certaines questions restent sans réponse, cas que l'affichage devra gérer.

### Difficulté rencontrée

Ma première version du seeder produisait plus de cent utilisateurs au lieu de
vingt. Chaque publication créait son propre auteur, si bien que personne n'avait
écrit deux publications et que le jeu de démonstration n'avait aucun sens.

### Comment je l'ai résolue

Le coupable était la ligne `'user_id' => User::factory()` du `definition()` : elle
signifie « si personne ne me fournit d'auteur, fabrique-en un », et c'est ce
qu'elle faisait quarante-deux fois. La solution est `->recycle($membres)`, qui
fournit à la factory un vivier de modèles déjà créés dans lequel elle pioche au
lieu d'en fabriquer. J'ai vérifié en comptant : vingt utilisateurs exactement,
soit huit plus huit membres et les quatre comptes de démonstration.

J'ai découvert au passage que les factories court-circuitent volontairement
`$fillable` : `created_at` n'y figure pas et se remplit quand même. C'est logique,
puisqu'une factory vient du code du projet et non d'un formulaire — la protection
contre l'assignation de masse n'a pas lieu de s'appliquer là.

`php artisan migrate:fresh --seed` passe sans erreur et produit deux promotions,
vingt utilisateurs, quarante-deux publications dont trente posts et douze
questions, et dix-sept réponses. Le scope `deLaPromotion()` renvoie bien vingt et
une publications de chaque côté, jamais quarante-deux : les deux jeux de contenu
sont strictement séparés, ce qui rendra possible le test d'accès direct par URL de
la phase 5.

---

## Phase 3 — L'authentification avec Laravel Fortify

Branche : `feat/03-authentification-fortify`
Dates : 24 août 2026

### Ce que j'ai fait

J'ai installé Laravel Fortify, réduit ses fonctionnalités à l'inscription, la
réinitialisation de mot de passe et la mise à jour du profil, puis écrit
moi-même les six vues Blade correspondantes. J'ai déclaré ces vues dans
`FortifyServiceProvider`, branché la vérification du code d'invitation dans
`app/Actions/Fortify/CreateNewUser.php`, et placé toute l'application derrière le
middleware `auth` en ne laissant public que l'accueil. Un nouveau membre qui
saisit `DWA2026` est désormais créé avec le rôle `apprenant` et rattaché
automatiquement à sa promotion.

### Pourquoi je l'ai fait ainsi

Fortify plutôt que Breeze parce qu'il fournit les mécanismes de sécurité qu'il ne
faut jamais réécrire — hachage, régénération de l'identifiant de session contre la
fixation, limitation des tentatives, jetons de réinitialisation à usage unique —
sans fournir les vues. Breeze m'aurait donné les deux et je n'aurais rien appris
du jeton CSRF, de `old()` ni de l'affichage des erreurs.

Pour refuser un code d'invitation, j'utilise
`ValidationException::withMessages()` plutôt qu'une redirection avec un message
flash : l'erreur est ainsi rattachée au champ `code_invitation` et remonte dans
`$errors`, donc la vue l'affiche sous le bon champ avec `@error()` sans code
supplémentaire. J'ai aussi distingué deux refus, le code inconnu et la promotion
fermée, parce qu'ils ne se corrigent pas de la même façon côté utilisateur.

### La difficulté rencontrée

Le guide décrit Fortify tel qu'il était dans une version antérieure, et la
version installée, la 1.39, en diffère sur quatre points. Elle tire
`laravel/passkeys` et active `twoFactorAuthentication()` et `passkeys()` par
défaut, deux fonctionnalités hors périmètre. Elle fournit déjà le
`RateLimiter::for('login')` à cinq tentatives par minute que le guide me demande
d'écrire. Sa valeur `home` par défaut est `/home` et non `/publications`. Et elle
déclare une route `GET /user/confirm-password` pour laquelle aucune vue n'est
prévue.

### Comment je l'ai résolue

J'ai lu les fichiers publiés avant de suivre le guide, au lieu de recopier son
code par-dessus. J'ai commenté `twoFactorAuthentication()` et `passkeys()` dans
`features`, puis vérifié avec `php artisan route:list` que les routes
`passkeys/login` et `two-factor-challenge` avaient bien disparu ; leurs limiteurs
étant devenus du code mort, je les ai supprimés du provider dans un commit
`refactor` distinct. Je n'ai pas réécrit le limiteur `login` déjà présent : je
l'ai relu, vérifié qu'il correspondait à l'exigence, et commenté le choix de sa
clé, qui combine l'adresse e-mail et l'adresse IP — l'e-mail seul permettrait de
bloquer volontairement le compte d'un tiers, l'IP seule bloquerait toute une
promotion derrière le même routeur.

Pour `home`, j'ai mis `/` provisoirement, avec un commentaire : `/publications`
n'existera qu'en phase 5 et se connecter donnerait un 404. Et j'ai écrit une
sixième vue, `confirm-password`, que le guide ne mentionne pas, pour qu'un
correcteur qui ouvre cette route après avoir lu `route:list` ne tombe pas sur une
erreur.

J'ai vérifié les six situations demandées. L'inscription avec `DWA2026` crée bien
un apprenant rattaché au groupe A ; un code inexistant et une promotion fermée
affichent chacun leur message sous le champ ; une adresse déjà utilisée est
refusée ; la connexion d'Awa redirige vers l'accueil qui affiche son nom ; et
`/user/confirm-password` renvoie une redirection vers `/login` quand on n'est pas
connecté, ce qui confirme que le middleware `auth` fait son travail. Sur la
limitation, j'ai observé un décalage d'un cran par rapport au guide : avec
`Limit::perMinute(5)`, c'est la septième tentative qui reçoit un 429, pas la
sixième. La règle exigée est bien en place, c'est la mécanique interne du
compteur de Laravel qui décale le seuil.

J'ai enfin traduit les messages de validation, Laravel n'étant pas livré en
français : un formulaire francophone qui répond « The email has already been
taken » n'est pas acceptable.

---

## Phase 4 — Rejoindre une promotion et le profil

Branche : `feat/04-adhesion-promotion`
Dates : 2 septembre 2026

### Ce que j'ai fait

J'ai créé le middleware `ExigePromotion`, déclaré son alias `promotion` dans
`bootstrap/app.php`, et ouvert un groupe de routes `['auth', 'promotion']` qui
accueillera le fil et l'entraide des phases suivantes. J'ai écrit la page
`/rejoindre` qui permet de saisir un code d'invitation après coup, la page de
profil, et un module de consultation pour l'enseignant listant toutes les
promotions avec leur nombre de membres et de publications.

### Pourquoi je l'ai fait ainsi

Le middleware existe pour établir une invariante : toute route qu'il traverse est
certaine de recevoir un utilisateur dont `promotion_id` n'est pas `null`. Sans
cette garantie, l'appel `->deLaPromotion($request->user()->promotion_id)` de la
phase 5 recevrait `null` et lèverait une `TypeError`, puisque le scope est typé
`int`. On vérifie une fois, en amont, ce que tout le code en aval tient pour
acquis.

Les routes de `/rejoindre` sont volontairement placées hors du groupe
`promotion` : c'est la page vers laquelle on envoie ceux qui n'ont pas de
promotion, la protéger par ce middleware créerait une boucle de redirection
infinie.

Dans `AdhesionController::store()`, je filtre sur `ouverte` dès la requête, si
bien qu'une promotion fermée et un code inconnu produisent le même message. Ce
n'est pas de la paresse : distinguer les deux révélerait à un inconnu qu'une
promotion porte ce code.

### La difficulté rencontrée

Le middleware du guide redirige l'enseignant vers
`route('enseignant.promotions.index')`, mais aucune section du guide ne fait
créer cette route. Un enseignant qui se connectait déclenchait donc une
`RouteNotFoundException`. C'est le troisième endroit où le guide référence du
code d'une étape ultérieure, après les liens du gabarit en phase 0 et
`home => '/publications'` en phase 3.

Mes tests en ligne de commande échouaient par ailleurs sur une erreur 419, alors
que les mêmes actions fonctionnaient dans le navigateur.

### Comment je l'ai résolue

J'ai écrit le module manquant : un `PromotionController` réservé à l'enseignant,
protégé par `abort_unless($request->user()->estEnseignant(), 403)`, et sa vue.
Le cahier des charges le demande de toute façon, puisque l'enseignant doit
pouvoir consulter toutes les promotions sans jamais publier. J'ai vérifié qu'Awa,
simple apprenante, reçoit bien un 403 sur cette route.

Le 419 venait de mon script de test, pas de l'application : depuis que
l'utilisateur est connecté, le gabarit affiche le formulaire de déconnexion, qui
porte son propre `@csrf`. Mon extraction du jeton en récupérait donc deux, collés
l'un à l'autre, et le jeton envoyé était invalide. Ajouter `head -1` a suffi.
La leçon est qu'une erreur 419 ne signifie pas toujours un `@csrf` manquant :
elle signifie que le jeton reçu ne correspond pas à la session, ce qui inclut le
cas d'un jeton mal transmis.

J'ai vérifié les sept situations. Le formateur, qui n'a pas de promotion, est
dirigé vers la liste des promotions et voit ses deux groupes avec leurs
compteurs. Awa passe normalement et reçoit un 403 sur le module enseignant. Un
visiteur non connecté est redirigé vers `/login` sur les trois routes. Un membre
sans promotion qui saisit `ZZZZ0000` ou le code d'une promotion fermée obtient le
même refus, et `DWA2026` le rattache avec un message de bienvenue visible sur son
profil.

---

## Phase 5 — Le fil de promotion et le cloisonnement

Branche : `feat/05-fil-promotion`
Dates : 2 septembre 2026

### Ce que j'ai fait

J'ai écrit la `PublicationPolicy`, un contrôleur de ressource restreint à cinq
méthodes, un `FormRequest` de validation, le composant `carte-publication` et
les trois vues du fil. Le fil affiche les publications de la promotion du membre
connecté, paginées par quinze, les épinglées en tête. J'ai également ouvert à
l'enseignant l'accès au fil de chaque promotion, via une route dédiée.

### Pourquoi je l'ai fait ainsi

Le cloisonnement repose sur deux protections complémentaires, et il faut les
deux. Le scope `deLaPromotion()` filtre la liste ; la policy protège la page de
détail. La liaison de modèle de route ne vérifie aucun droit : elle trouve la
publication numéro 3 et la donne, que j'aie le droit de la voir ou non. Protéger
seulement la liste laisserait passer une URL saisie à la main, protéger seulement
le détail laisserait fuiter la liste.

Dans `store()`, `promotion_id` vient de l'utilisateur connecté et jamais du
formulaire. Un champ caché ajouté à la main permettrait sinon de publier dans une
autre promotion.

J'ai restreint `Route::resource` à cinq routes avec `->only()` : une publication
ne se modifie pas, elle se supprime. Déclarer `edit` et `update` sans les écrire
créerait deux routes mortes visibles dans `route:list`.

### La difficulté rencontrée

Le fil renvoyait une erreur 500 systématique :
`Call to undefined method PublicationController::middleware()`, levée depuis
`vendor/laravel/framework/.../AuthorizesRequests.php` ligne 104.

Le guide fait pourtant écrire exactement ce code :
`$this->authorizeResource(Publication::class, 'publication')` dans le
constructeur.

### Comment je l'ai résolue

En remontant jusqu'au fichier de `vendor/` indiqué par la trace, j'ai vu que
`authorizeResource()` construit des chaînes de middleware puis appelle
`$this->middleware(...)`. Or depuis Laravel 11 la classe `Controller` de base est
vide : cette méthode n'existe plus. Le guide signale lui-même que cette classe
est vide, à propos du trait `AuthorizesRequests` à importer, sans voir que sa
propre solution en dépendait.

Deux remplacements étaient possibles : implémenter l'interface `HasMiddleware` et
sa méthode statique `middleware()`, qui reproduit le mécanisme, ou appeler
`$this->authorize()` explicitement dans chaque méthode. J'ai retenu le second.
L'autorisation devient visible là où elle s'applique, au lieu d'être déduite
d'une convention de nommage, et je peux montrer la ligne exacte qui bloque un
accès.

J'ai ensuite exécuté le test qui décide de la note. Awa, du groupe A, ouvre sa
publication numéro 3 et reçoit 200 ; elle demande la publication 31, du groupe B,
et reçoit 403. Fatou obtient l'inverse. Une suppression tentée par Fatou sur une
publication du groupe A renvoie 403, et une publication inexistante renvoie 404 —
la distinction est correcte : 404 signifie qu'elle n'existe pas, 403 qu'elle
existe mais ne me regarde pas.

J'ai enfin traité un point que le guide laisse en suspens. L'enseignant n'ayant
pas de `promotion_id`, le middleware `ExigePromotion` le redirigeait avant qu'il
n'atteigne `/publications`, alors que sa policy `view()` renvoie `true` : il
devait donc pouvoir tout consulter sans jamais y accéder. J'ai ajouté une route
`/promotions/{promotion}/fil`, hors du groupe `promotion`, réservée par
`abort_unless(...->estEnseignant(), 403)`. Elle n'applique pas le scope
`visibles()` : l'enseignant est un observateur et voit aussi ce qui est masqué ou
en attente, ce qu'autorise déjà sa policy. Awa reçoit bien 403 sur cette route.

---

## Phase 6 — L'entraide, questions et réponses

Branche : `feat/06-entraide`
Dates : 3 au 7 septembre 2026

### Ce que j'ai fait

J'ai écrit le module d'entraide : un contrôleur de ressource pour les questions,
un contrôleur pour les réponses, et un troisième dédié à la réponse retenue.
J'ai ajouté deux méthodes à la `PublicationPolicy`, `designerReponse()` et
`repondre()`, deux `FormRequest`, et les trois vues du module. Retenir une
réponse crédite son auteur de dix points ; retirer la désignation les lui reprend.

### Pourquoi je l'ai fait ainsi

Désigner une réponse comme retenue n'est pas modifier une question : c'est une
action distincte, avec ses propres droits, puisque seul l'auteur de la question
peut la faire. Plutôt qu'une méthode de plus dans le contrôleur des questions,
j'ai créé un contrôleur dédié à cette ressource. Une question a au plus une
réponse retenue, d'où l'absence d'identifiant dans l'URL : c'est un contrôleur de
ressource singleton.

Le titre est facultatif pour un post mais obligatoire pour une question, avec un
minimum de dix caractères. C'est lui que la détection de doublon de la phase 9
comparera aux questions existantes : une question sans titre y serait invisible.

Pour créditer les points j'utilise `increment('points', 10)` et non une lecture
suivie d'une écriture. La méthode génère `UPDATE users SET points = points + 10`
en SQL : si deux requêtes arrivent en même temps, aucune des deux additions n'est
perdue.

### La difficulté rencontrée

Deux problèmes, l'un de conception, l'autre de manipulation.

La règle `exists:reponses,id` garantit qu'une réponse existe, pas qu'elle
appartient à la question qu'on est en train de traiter. Le formulaire transmet
l'identifiant de la réponse dans un champ caché, modifiable par n'importe qui
depuis le navigateur.

Par ailleurs, en préparant les commits, mon fichier `layouts/app.blade.php` est
revenu à sa version de la phase 0 : les liens vers le fil, l'entraide, les
promotions et le profil avaient disparu du fichier de travail.

### Comment je l'ai résolue

Pour la première, j'ai ajouté
`abort_unless($reponse->publication_id === $question->id, 403)` après la
validation. Je l'ai vérifié en envoyant volontairement l'identifiant d'une
réponse écrite sur une autre question : la requête est refusée avec 403, alors
qu'elle aurait abouti sans cette ligne. J'ai également testé qu'un membre d'une
autre promotion ne peut ni consulter la question, ni y répondre, ni désigner sa
réponse retenue.

Pour la seconde, `git status` montrait le gabarit en modifié, ce qui était
attendu puisque j'y ajoutais le lien Entraide. Mais `git diff` révélait une
suppression de trois phases de navigation. La version correcte était dans le
dernier commit : `git restore resources/views/layouts/app.blade.php` l'a
récupérée, et j'ai réappliqué ma seule modification par-dessus. Sans la
relecture du diff avant le commit, j'aurais supprimé toute ma navigation sans
m'en apercevoir. C'est exactement le risque que fait courir `git add .`.

J'ai enfin ajouté une possibilité que le guide ne prévoit pas : retirer la
réponse retenue. Sans elle, l'auteur d'une question ne pouvait jamais se
raviser, et changer d'avis aurait crédité deux personnes de dix points chacune
au lieu d'une.

---

## Phase 7 — OpenRouter et la modération automatique

Branche : `feat/07-moderation-ia`
Dates : 7 au 10 septembre 2026

### Ce que j'ai fait

J'ai isolé toute la communication avec OpenRouter dans une classe unique,
`OpenRouterClient`, ajouté une énumération `VerdictModeration` à quatre cas, écrit
`ServiceModeration` qui interroge le modèle et interprète sa réponse, et branché
le tout sur la création d'une publication. Chaque appel est tracé dans la table
`appels_ia` créée en phase 1, qui servira au calcul du quota en phase 9. La
configuration d'accès vit dans `config/services.php`, la clé et le modèle dans le
`.env`.

### Pourquoi je l'ai fait ainsi

Aucun contrôleur ne contient d'appel `Http::post()` : le jour où l'on change de
fournisseur, un seul fichier bouge. `OpenRouterClient` ne lève jamais
d'exception et ne prend aucune décision métier : il renvoie du texte ou `null`,
et c'est `ServiceModeration` qui tranche. Une classe technique ne décide pas
d'une règle de gestion.

Le verdict est une énumération et non une chaîne libre. Elle interdit d'écrire
`acceptble` par erreur, garantit l'exhaustivité du `match()`, et le cas
`Indisponible` donne un nom au cas « je n'ai pas d'avis ».

J'ai retenu le fail-closed, argumenté dans `DECISIONS.md` : dans un réseau
scolaire, une insulte publiée cause un préjudice sans commune mesure avec une
publication retardée, la promotion compte une vingtaine de membres donc la file
reste soutenable, et le délégué qui la traitera existe déjà au cahier des
charges. Le choix est piloté par `COHORTE_MODERATION_FAIL_OPEN` : le correcteur
peut tester l'autre comportement sans toucher au code.

### La difficulté rencontrée

Le modèle retenu a cessé de fonctionner deux jours après avoir été choisi. Les
trois publications de test se sont toutes retrouvées en `en_moderation` avec le
motif `indisponible`. Le journal donnait la réponse exacte du serveur :
`This model is unavailable for free. The paid version is available now`.
`minimax/minimax-m3:free`, qui répondait parfaitement le 7 septembre, était
devenu payant le 9.

### Comment je l'ai résolue

J'ai interrogé le catalogue d'OpenRouter et testé les dix-huit modèles annoncés
gratuits, un par un, sur les trois verdicts attendus. Le résultat est instructif :
quatre renvoient une erreur du fournisseur, cinq répondent HTTP 200 avec un
contenu vide, deux exigent un compte vérifié, deux exposent leur raisonnement
sans produire de JSON, et plusieurs classent tout en `inacceptable` sans
discriminer. Un seul, `nvidia/nemotron-3-super-120b-a12b:free`, rend les trois
verdicts corrects : `acceptable` pour un remerciement, `douteux` pour de la
publicité, `inacceptable` pour une insulte.

La correction a consisté en une seule ligne du `.env`. Aucun fichier PHP modifié,
aucun commit. C'est exactement l'intérêt de la règle du guide qui interdit de
coder un identifiant de modèle en dur, et je l'ai vérifiée dans les faits plutôt
qu'en théorie. Il faut également noter que pendant toute la panne, rien n'a fui :
le fail-closed a envoyé les trois publications en file de modération au lieu de
les laisser passer sans contrôle.

J'ai vérifié le parsing défensif sur neuf situations simulées avec `Http::fake()` :
JSON propre, JSON encadré par des accents graves, phrase d'introduction avant le
JSON, texte sans aucun JSON, JSON sans la clé attendue, verdict inventé, verdict
en majuscules, contenu `null`, et service en panne 503. Toutes produisent un
statut valide, aucune ne lève d'exception. Les cas du contenu `null` et du
verdict en majuscules ne figurent pas dans le guide : je les ai ajoutés après les
avoir réellement observés en interrogeant le catalogue.

Le test de bout en bout avec de vrais appels confirme la chaîne complète : un
remerciement est publié et apparaît dans le fil, une publicité part en
`en_moderation`, une insulte est `refuse`, et les trois appels sont enregistrés
dans `appels_ia`.

---

## Phase 8 — Les signalements et le masquage automatique

Branche : `feat/08-signalements`
Dates : 21 septembre 2026

### Ce que j'ai fait

J'ai écrit le `SignalementController`, sa méthode privée `masquerSiSeuilAtteint()`,
le `FileModerationController` du délégué, un composant Blade de signalement
réutilisé par le fil et par l'entraide, et la vue de la file. Le lien
« Modération » n'apparaît dans la barre que pour un délégué.

### Pourquoi je l'ai fait ainsi

Le seuil vient de `config('cohorte.seuil_signalement')` et jamais d'une valeur
écrite en dur : le correcteur peut le passer à 1 pour vérifier le masquage sans
toucher au code.

Le masquage ne s'applique qu'à une publication dont le statut est `publie`. Sans
cette condition, une publication déjà refusée par la modération automatique
verrait son motif écrasé par celui du masquage, et l'historique de la décision
serait perdu.

Le formulaire de signalement est un composant, pas un bloc recopié dans deux
vues. Posts et questions partagent la même table : le même composant sert aux
deux, et le jour où il change, un seul fichier bouge.

### La difficulté rencontrée

La vérification du doublon posait une question que je ne m'étais pas posée : la
base porte déjà une contrainte unique sur `(publication_id, user_id)` depuis la
phase 1. Fallait-il vraiment re-vérifier en PHP, ou était-ce de la duplication ?

Par ailleurs, en testant que Fatou ne peut pas signaler une publication du
groupe A, j'obtenais une erreur 419 au lieu du 403 attendu.

### Comment je l'ai résolue

Sur le doublon, j'ai gardé les deux contrôles, parce qu'ils ne s'adressent pas
au même public. La contrainte de base garantit l'intégrité des données quoi
qu'il arrive : code contourné, requête forgée, ou deux requêtes simultanées qui
passeraient toutes deux le test PHP avant que l'une ait écrit. La vérification
PHP, elle, sert à afficher un message compréhensible plutôt qu'une page d'erreur
SQL. La base protège, le code explique.

Le 419 venait de mon script de test et non de l'application : pour envoyer le
formulaire, j'extrayais le jeton CSRF depuis la page de la publication — or
Fatou reçoit précisément un 403 sur cette page, donc aucun jeton n'était
récupéré. En prenant le jeton depuis une page qu'elle peut ouvrir, le
signalement renvoie bien 403. C'est la deuxième fois de ce projet qu'un 419
masque autre chose : une erreur 419 ne signifie pas « `@csrf` manquant » mais
« le jeton reçu ne correspond pas à la session », ce qui inclut le cas d'un
jeton absent.

J'ai vérifié les dix situations. Les trois interdits sont respectés :
auto-signalement et signalement hors promotion renvoient 403, un second
signalement du même membre est refusé sans incrémenter le compteur. Au troisième
signalement par trois membres différents, la publication passe en `masque` avec
le motif « Masquée automatiquement après 3 signalements ». Le délégué du groupe
A ouvre sa file et y trouve cette publication, la remet en ligne, et son motif
est effacé. Une apprenante reçoit 403 sur la file, et surtout le délégué du
groupe A reçoit 403 quand il tente de modérer une publication du groupe B :
être délégué ne suffit pas, il faut être délégué de cette promotion.

---

## Phase 9 — Le quota d'IA et la détection de doublon

Branche : `feat/09-quota-et-doublon`
Dates : 22 septembre 2026

### Ce que j'ai fait

J'ai ajouté trois méthodes de quota au modèle `User`, un middleware
`VerifieQuotaIa`, le `ServiceDetectionDoublon` et son contrôleur. Le quota
restant est affiché en permanence dans la barre de navigation. Le formulaire de
question propose un bouton de vérification qui, le cas échéant, réaffiche la
saisie accompagnée des questions proches et d'un bouton « Publier quand même ».

### Pourquoi je l'ai fait ainsi

Seule la détection de doublon est soumise au quota, et c'est une décision de
conception que j'ai consignée dans `DECISIONS.md`. La modération est une
contrainte imposée par l'application, pas un service rendu au membre : bloquer
son appel reviendrait à l'empêcher de publier. La détection est une assistance,
qu'on peut retirer sans dommage. Les deux appels sont comptés dans `appels_ia`,
mais une seule route porte le middleware.

J'ai réglé `config/app.php` sur `Africa/Dakar`. `now()` utilise ce fuseau, et
laissé à UTC, minuit serait tombé en pleine journée pour une promotion de Dakar,
réinitialisant le quota au mauvais moment. Je n'utilise pas `whereDate()`, qui
délègue la comparaison à MySQL et n'emploie pas le même fuseau.

La requête finale du service refiltre sur la promotion. Le modèle pourrait
inventer un identifiant ou renvoyer celui d'une question d'un autre groupe :
sans ce refiltrage, l'IA ouvrirait une brèche dans le cloisonnement.

### La difficulté rencontrée

La page de vérification renvoyait une erreur 500. Le journal indiquait :
`Attempted to lazy load [auteur] on model [App\Models\Publication] but lazy
loading is disabled`, dans la vue `entraide/create.blade.php`.

### Comment je l'ai résolue

C'est le garde-fou `preventLazyLoading`, activé dès la phase 0, qui faisait son
travail : la vue affiche le nom de l'auteur de chaque question proche, mais la
requête du service ne préchargeait pas la relation. J'ai ajouté `->with('auteur')`
à la requête finale.

L'erreur elle-même m'a appris quelque chose : si la vue essayait de charger les
auteurs, c'est que la détection avait bel et bien trouvé des questions
similaires. Le plantage prouvait le fonctionnement de la fonctionnalité.

J'ai ensuite vérifié le service directement : sur la question « Quelle est la
différence entre git revert et git reset ? », il retrouve les deux questions
identiques de la promotion 1 et aucune de la promotion 2. Un appel HTTP formulé
autrement a renvoyé zéro similarité, ce qui est le comportement normal d'un
modèle : il propose, mon code dispose.

J'ai enfin testé le quota. Après dix appels, `peutAppelerIa()` passe à faux, le
bouton de vérification disparaît, le middleware renvoie en arrière avec un
message explicite, et la publication d'une question reste possible. En antidatant
les appels d'un jour, le compteur repart à dix : la réinitialisation à minuit
fonctionne.

---

## Phase 10 — La réputation et les finitions

Branche : `feat/10-reputation`
Dates : 22 septembre 2026

### Ce que j'ai fait

J'ai ajouté la méthode `epingler()` à la `PublicationPolicy`, un
`EpinglageController` en ressource singleton, la commande
`cohorte:recalculer-reputation`, l'affichage de la réputation sur le profil, et
les pages d'erreur 403 et 404 personnalisées. `APP_DEBUG` est passé à `false`
dans le fichier d'exemple.

### Pourquoi je l'ai fait ainsi

Le score est stocké dans `users.points` et incrémenté au fil de l'eau, mais
accompagné d'une commande de recalcul. Le stockage seul serait fragile — un
incrément oublié fait dériver le compteur en silence ; le recalcul permanent
serait juste mais coûterait une agrégation à chaque vérification du droit
d'épingler, donc à chaque affichage du fil. Le compromis est la réponse
professionnelle courante, et il est argumenté dans `DECISIONS.md`.

L'épinglage est une ressource singleton, comme la réponse retenue : une
publication est épinglée ou ne l'est pas, il n'y a pas d'identifiant
d'épinglage. `store()` épingle, `destroy()` retire.

La page 403 ne se contente pas d'annoncer le refus : elle explique que le
contenu est cloisonné par promotion et propose un retour adapté au rôle. C'est
la page que le correcteur verra en exécutant son test d'accès direct par URL.

### La difficulté rencontrée

Le tri du fil place les publications épinglées en tête grâce à
`orderByDesc('epingle_le')`, qui repose sur le fait que MySQL considère `NULL`
comme la plus petite valeur. Ce comportement n'est pas universel : PostgreSQL
fait l'inverse en tri descendant et remonterait les publications non épinglées
en tête.

### Comment je l'ai résolue

J'avais anticipé le problème dès la phase 5 en écrivant
`orderByRaw('epingle_le IS NULL')` avant `orderByDesc('epingle_le')` : la
première expression vaut 0 pour les épinglées et 1 pour les autres, ce qui donne
un tri identique quel que soit le moteur. Le guide ne le mentionne qu'en phase
10, mais la ligne concernée était déjà écrite en phase 5.

J'ai vérifié la commande de recalcul sur un jeu fraîchement semé : elle met
treize membres à jour et n'écrit que ceux dont le score avait dérivé. Sur les
droits, un délégué épingle quel que soit son score, une apprenante à zéro point
ne le peut pas, et une membre d'une autre promotion non plus.

---

## Retour arrière documenté — `git revert`

Branche : `fix/revert-documente`
Date : 22 septembre 2026

### Ce que j'ai fait, et pourquoi c'était une erreur

En relisant le contrôleur du fil, j'ai trouvé que ces deux lignes faisaient
double emploi :

```php
->orderByRaw('epingle_le IS NULL')
->orderByDesc('epingle_le')
```

Le raisonnement semblait juste : sous MySQL, `NULL` est considéré comme la plus
petite valeur, donc un tri décroissant sur `epingle_le` place déjà les
publications épinglées en tête. J'ai supprimé la première ligne et commité le
changement sous `refactor(feed): simplifier le tri des publications epinglees`,
commit `5952ab5`.

C'était une erreur. Ce comportement n'est pas universel : PostgreSQL traite
`NULL` comme la plus grande valeur en tri décroissant et remonterait les
publications **non** épinglées en tête. Mon « doublon » était en réalité la
seule chose qui rendait le tri indépendant du moteur de base de données. Le code
aurait continué à fonctionner chez moi sous MySQL et se serait cassé
silencieusement lors d'un changement de moteur — le pire type de bug, celui qui
ne se manifeste pas sur la machine du développeur.

### Comment je l'ai corrigé, et pourquoi avec `revert`

Le commit était déjà poussé. J'ai donc utilisé :

```
git revert 5952ab5
```

qui a créé le commit `386d257`, *Revert "refactor(feed): simplifier le tri..."*,
annulant exactement les modifications du premier.

J'aurais pu utiliser `git reset --hard HEAD~1`, et le résultat visible dans les
fichiers aurait été identique. La différence est dans l'historique.

`revert` **ajoute** un commit qui applique l'inverse d'un ancien : rien n'est
supprimé, l'historique s'allonge et reste honnête. On voit que j'ai cru bien
faire, puis que je me suis corrigé — et le journal explique pourquoi. C'est la
seule méthode acceptable sur une branche déjà poussée.

`reset --hard` **déplace le pointeur de branche en arrière** et fait disparaître
des commits. C'est utile en local, sur du travail non partagé et non poussé.
Mais dès qu'une branche est partagée, pousser le résultat exigerait un
`push --force`, qui réécrirait l'historique distant et détruirait le travail de
toute personne l'ayant récupéré.

La règle que j'en retiens : si je crois avoir besoin de `--force`, c'est presque
toujours que `revert` était la bonne réponse.

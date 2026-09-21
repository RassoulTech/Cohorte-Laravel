# Utilisation de l'IA générative

> Phase par phase : ce que j'ai demandé, ce que j'ai retenu, ce que j'ai rejeté
> et pourquoi.

---

## Phases 0 et 1

### Ce que j'ai demandé

J'ai utilisé un assistant IA (Claude Code) pour relire le guide d'évaluation et
le plan de travail, puis écrire les migrations, les modèles, les relations et
les scopes de la phase 1 à partir des extraits de code du guide. Je lui ai aussi
demandé de m'expliquer le rôle de chaque commit attendu.

### Ce que j'ai retenu

- La structure des six migrations et l'ordre imposé par les clés étrangères.
- La migration séparée pour `reponse_retenue_id`, seule façon de sortir de la
  référence circulaire entre `publications` et `reponses`.
- Le nommage `auteur()` plutôt que `user()` sur `Publication` et `Reponse`, avec
  la clé `'user_id'` passée en deuxième argument de `belongsTo()`.
- Les méthodes `estEnseignant()` et `estDelegue()` sur `User`, pour ne pas
  comparer des chaînes de rôle un peu partout dans le code.

### Ce que j'ai rejeté, et pourquoi

- **Le gabarit du guide recopié tel quel.** Il contient
  `route('feed.index')`, `route('entraide.index')` et `route('profil.show')`,
  qui sont des routes des phases 5 et 6. Les écrire aujourd'hui ferait planter
  la première page qui utiliserait le gabarit avec une erreur *Route not
  defined*. J'ai mis un lien `url('/')` et un commentaire indiquant où ajouter
  ces liens le moment venu.
- **Le nom de table `appel_ias` généré automatiquement.** Je l'ai remplacé par
  `appels_ia`, le nom du cahier des charges, en déclarant explicitement
  `protected $table` dans le modèle. Un nom de table faux se paie en phase 9,
  quand le quota lira cette table.
- **Une première vérification erronée du garde-fou N+1.** L'assistant testait
  `Publication::first()->auteur` et concluait que `preventLazyLoading` ne
  fonctionnait pas. En lisant le code de `Builder::hydrate()` dans `vendor/`,
  j'ai vu que le garde-fou ne s'applique qu'aux collections de plus d'un
  enregistrement — ce qui est logique, puisqu'un modèle seul ne peut pas
  provoquer de N+1. Le test correct parcourt une collection.

### Ce que j'en retiens

L'IA va vite sur le code répétitif (migrations, relations), mais elle propose
volontiers du code qui « a l'air » du guide sans tenir compte de l'état réel du
projet : routes qui n'existent pas encore, noms de tables approximatifs, tests
mal construits. Chaque bloc a été relu et vérifié dans Tinker avant d'être
commité.

---

## Phase 2 — Factories et seeders

### Ce que j'ai demandé

Le remplissage des trois `definition()`, les états `question()` et
`enModeration()`, et la structure du seeder à partir de l'exemple du guide.

### Ce que j'ai retenu

- La structure générale du seeder du guide : deux promotions, une boucle
  appliquant la même recette à chacune, puis les comptes de démonstration
  isolés dans une méthode privée.
- `strtoupper(fake()->unique()->bothify('??####'))` pour le code d'invitation.
  Le `unique()` est indispensable : la colonne porte une contrainte unique en
  base, et deux tirages identiques feraient échouer le seed.
- `recycle($membres)` pour éviter les utilisateurs fantômes.

### Ce que j'ai rejeté

- **Un seeder qui n'utilisait pas `recycle()`.** La version proposée créait un
  auteur par publication, soit plus de cent utilisateurs. Le jeu de
  démonstration devenait inutilisable : aucun membre n'avait publié deux fois,
  et le futur calcul de réputation de la phase 10 n'aurait rien mesuré.
- **Des codes d'invitation générés aléatoirement pour les deux promotions du
  seeder.** Ils doivent être écrits en dur : le README les publie et le
  correcteur s'en sert pour tester l'inscription par code. Un code différent à
  chaque `migrate:fresh --seed` rendrait le README faux.
- **Un `PublicationSeeder` séparé.** Le guide le cite dans un exemple de
  commande, mais tout le jeu de données tient dans `DatabaseSeeder` sans
  duplication. Créer une classe de plus aurait ajouté un fichier sans ajouter
  de clarté.
- **La suppression de `'created_at'` de la factory**, au motif que Laravel gère
  déjà les horodatages. C'est vrai, mais il les met tous à l'instant présent :
  le fil de la phase 5 serait alors trié au hasard et je ne pourrais pas
  vérifier mon `latest()`.

---

## Phase 3 — L'authentification avec Fortify

### Ce que j'ai demandé

La structure des vues Blade d'authentification, la configuration de Fortify, et
la vérification du code d'invitation dans `CreateNewUser`.

### Ce que j'ai retenu

- La logique de `CreateNewUser` telle que le guide la décrit : valider, chercher
  la promotion, refuser avec `ValidationException::withMessages()` pour que
  l'erreur se rattache au champ, puis créer l'utilisateur rattaché.
- Les trois éléments obligatoires de chaque formulaire : `@csrf`, `old()` et le
  couple `for` / `id`.
- La distinction entre les deux refus, code inconnu et promotion fermée.

### Ce que j'ai rejeté

- **Le code du guide recopié sur `config/fortify.php` sans lire le fichier
  publié.** Fortify 1.39 active `twoFactorAuthentication()` et `passkeys()` par
  défaut, deux fonctionnalités hors périmètre que le guide ne mentionne pas
  parce qu'il décrit une version antérieure. Je les ai désactivées et vérifié
  avec `route:list` que leurs routes disparaissaient.
- **Réécrire le `RateLimiter::for('login')` que le guide fait écrire.** Il est
  déjà présent dans le fichier publié par `fortify:install`, avec exactement la
  bonne valeur. Le recopier par-dessus aurait produit un commit mensonger : je
  ne l'ai pas écrit, je l'ai lu, vérifié, commenté et testé. En revanche j'ai
  supprimé les limiteurs `two-factor` et `passkeys`, devenus du code mort après
  la désactivation de leurs fonctionnalités.
- **`'home' => '/publications'`.** Le guide le demande, mais cette route
  n'existera qu'en phase 5 : se connecter aujourd'hui renverrait un 404. J'ai mis
  `/` avec un commentaire indiquant quand le changer. C'est le même défaut que le
  gabarit de la phase 0, qui renvoyait vers des routes futures.
- **S'en tenir aux quatre vues du guide.** La fonctionnalité `updatePasswords`
  déclare aussi une route `GET /user/confirm-password`. Sans vue déclarée, elle
  provoque une erreur — et c'est une route qu'un correcteur voit dans
  `route:list`. J'ai écrit la cinquième vue.
- **Laisser les messages de validation en anglais.** Ils s'affichent dans un
  formulaire entièrement en français.

---

## Phase 4 — Adhésion à une promotion

### Ce que j'ai retenu

- La structure du middleware `ExigePromotion` telle que le guide la décrit, et
  la déclaration de son alias dans `bootstrap/app.php`, `Kernel.php` n'existant
  plus depuis Laravel 11.
- Le placement des routes de `/rejoindre` hors du groupe `promotion`.

### Ce que j'ai rejeté

- **Le middleware du guide recopié tel quel.** Il redirige l'enseignant vers
  `route('enseignant.promotions.index')`, une route que le guide ne fait jamais
  créer : un enseignant qui se connecte déclenche une `RouteNotFoundException`.
  J'ai écrit le contrôleur et la vue manquants.
- **Protéger la page de l'enseignant par un simple `@if` dans le gabarit.**
  Cacher un lien n'empêche personne d'appeler la route directement. J'ai mis
  `abort_unless(...->estEnseignant(), 403)` dans le contrôleur, et vérifié
  qu'Awa reçoit bien un 403.
- **Distinguer « code inconnu » et « promotion fermée » dans les messages.**
  Deux messages différents indiqueraient à un inconnu qu'une promotion porte ce
  code. Un seul refus commun ne coûte rien à l'utilisateur légitime.
- **Afficher `$membre->promotion->nom` sans précaution sur la page de profil.**
  L'enseignant n'a pas de promotion : sans l'opérateur `?->`, la page lève
  « Attempt to read property on null ».

---

## Phase 5 — Le fil et le cloisonnement

### Ce que j'ai retenu

- La `PublicationPolicy` du guide, y compris le cas d'une publication non
  publiée qui reste visible pour son auteur.
- La combinaison scope pour la liste, policy pour le détail.
- Le `FormRequest` plutôt qu'une validation dans le contrôleur.

### Ce que j'ai rejeté

- **`$this->authorizeResource()` dans le constructeur**, que le guide fait
  écrire. Sur Laravel 12, il lève `Call to undefined method
  PublicationController::middleware()` : cette méthode a disparu de la classe
  `Controller` de base en Laravel 11. Remplacé par des `$this->authorize()`
  explicites, plus lisibles et démontrables ligne à ligne.
- **`orderByDesc('epingle_le')` seul**, comme dans le corps du guide. Il repose
  sur le fait que MySQL traite `NULL` comme la plus petite valeur ; PostgreSQL
  fait l'inverse. J'ai ajouté `orderByRaw('epingle_le IS NULL')` avant, comme le
  guide le suggère lui-même plus loin, pour un tri qui ne dépende pas du moteur.
- **Afficher le contenu avec `{!! $publication->contenu !!}`** pour conserver les
  retours à la ligne. C'est une faille XSS directe. J'utilise
  `nl2br(e($publication->contenu))` : l'échappement a lieu avant la conversion
  des sauts de ligne.
- **Se contenter de `@can` dans la vue pour masquer le bouton Supprimer.**
  Cacher un bouton n'empêche personne d'appeler la route. La vérification réelle
  est `$this->authorize('delete', $publication)` dans le contrôleur.
- **Laisser l'enseignant sans accès au fil.** Le guide ne prévoit pas de route
  pour lui, alors que sa policy l'autorise à tout voir. Son rôle n'aurait eu
  aucun sens.

---

## Phase 6 — L'entraide

### Ce que j'ai retenu

- Le contrôleur de ressource singleton pour la réponse retenue, et la
  justification du guide : c'est une action à part entière, pas une
  modification de la question.
- La vérification `abort_unless($reponse->publication_id === $question->id, 403)`,
  que le guide signale comme le genre de faille qu'une IA laisse passer quand on
  lui demande simplement « écris-moi le contrôleur ».

### Ce que j'ai rejeté

- **Une version du contrôleur qui se contentait de `exists:reponses,id`.** La
  règle prouve que la réponse existe, pas qu'elle appartient à cette question.
  J'ai vérifié la faille avant de la corriger : en envoyant l'identifiant d'une
  réponse d'une autre question, la désignation aboutissait.
- **Créditer les points avec `$user->points = $user->points + 10`.** Deux
  requêtes simultanées liraient la même valeur et une addition serait perdue.
  `increment()` délègue le calcul à SQL.
- **Ne pas prévoir le retrait de la réponse retenue.** Le guide ne l'évoque pas,
  mais sans lui l'auteur d'une question ne peut jamais se raviser, et un
  changement d'avis créditerait deux auteurs de dix points.
- **Laisser `/questions/{id}` accepter l'identifiant d'un post.** Questions et
  posts partagent la même table : sans
  `abort_unless($question->type === 'question', 404)`, on pouvait ouvrir un post
  dans la vue des questions et voir un formulaire de réponse sur un contenu qui
  n'en attend pas.

---

## Phase 7 — Modération automatique

### Ce que j'ai retenu

- L'architecture en couches du guide : un client technique qui ne décide rien,
  un service métier qui tranche, une énumération pour le verdict.
- Le `timeout`, le `retry(2, 400, throw: false)` et le `try/catch` qui renvoie
  `null` plutôt que de propager une exception.
- La méthode `interpreter()` et son extraction du premier bloc entre accolades.

### Ce que j'ai rejeté

- **Écrire l'identifiant du modèle dans le service.** Le guide l'interdit ; j'en
  ai eu la démonstration en deux jours, `minimax/minimax-m3:free` étant devenu
  payant entre le moment où je l'ai choisi et celui où je l'ai testé de bout en
  bout. La correction a été une ligne de `.env`.
- **Un `interpreter()` limité aux trois cas du guide.** En interrogeant
  réellement le catalogue, j'ai observé deux comportements qu'il ne mentionne
  pas : un modèle qui répond HTTP 200 avec `content: null`, et un verdict rendu
  en majuscules. J'ai ajouté le test de chaîne vide et un `strtolower(trim(...))`
  avant le `tryFrom()`.
- **`VerdictModeration::from()`.** Il lève une `ValueError` si la valeur ne
  correspond à aucun cas. `tryFrom()` renvoie `null`, ce qui me permet de
  journaliser le verdict inventé et de retomber sur `Indisponible`.
- **Rediriger vers `publications.show` dans tous les cas.** Une publication
  refusée ou en attente n'apparaît pas dans le fil : j'ai séparé les deux
  destinations et le canal du message, vert ou rouge selon le verdict.
- **Tester les neuf cas dans un seul processus.** `Http::fake()` accumule les
  stubs et le premier enregistré l'emporte : les neuf cas renvoyaient le même
  verdict, ce qui m'a d'abord fait croire à un bug du parsing. Chaque cas est
  désormais exécuté dans son propre processus.

---

## Phase 8 — Signalements

### Ce que j'ai retenu

- La structure du contrôleur du guide et la méthode privée de masquage.
- La double vérification de `update()` : être délégué, et être délégué de la
  promotion concernée.

### Ce que j'ai rejeté

- **Supprimer le contrôle PHP du doublon** sous prétexte que la contrainte
  unique existe en base. Elle produirait une page d'erreur SQL au lieu d'un
  message lisible.
- **Masquer sans vérifier le statut courant.** Le guide le fait, mais sans la
  condition `statut === 'publie'` une publication déjà refusée par l'IA verrait
  son motif écrasé par celui du masquage automatique.
- **Recopier le formulaire de signalement dans `feed/show` et
  `entraide/show`.** J'en ai fait un composant : les deux vues affichent des
  lignes de la même table.
- **Créer un middleware pour la file du délégué.** Pour deux routes, un
  `abort_unless` dans le contrôleur est plus lisible et se montre en soutenance.

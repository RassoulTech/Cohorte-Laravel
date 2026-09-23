# Préparation de la soutenance — Cohorte

25 minutes, en trois temps : **démo 7 min · lecture de code 10 min · décisions 8 min.**

---

## 0. Avant d'entrer dans la salle

```bash
# MySQL démarré dans XAMPP
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan serve
```

Navigateur sur `http://127.0.0.1:8000`, **deux fenêtres** : une normale, une en navigation privée — pour être connecté avec deux comptes à la fois sans se déconnecter.

| Compte | Mot de passe | Rôle |
|---|---|---|
| `aminata@cohorte.test` | `password` | apprenante, groupe A |
| `ibrahima@cohorte.test` | `password` | **délégué**, groupe A |
| `coumba@cohorte.test` | `password` | apprenante, **groupe B** |
| `professeur@cohorte.test` | `password` | enseignant, aucune promotion |

Codes d'invitation : **`DWA2026`** (groupe A) · **`DWB2026`** (groupe B)

---

## 1. La démonstration — 7 minutes chrono

> **Ne commente pas ton code. Montre le produit.**

| Temps | Action | Ce que tu dis |
|---|---|---|
| 0:00 | `/register` → nom, e-mail, mot de passe, code **`DWA2026`** | « On entre sur invitation : sans le code d'une promotion ouverte, l'inscription est refusée. » |
| 0:45 | Ressaie avec un code bidon `ZZZZ0000` | « Le message s'affiche sous le champ concerné, pas en haut de page. » |
| 1:15 | Connexion **Aminata** → **Le fil** | « Chaque membre ne voit que sa promotion. » |
| 2:00 | **Publier** un message normal | « Chaque publication passe par un modèle de langage avant d'être enregistrée. » |
| 2:45 | **Publier** « Tu es complètement nul… » | « Refusée par la modération automatique. Elle n'apparaît pas dans le fil. » |
| 3:30 | **Entraide** → une question → **répondre** → **Retenir cette réponse** | « L'auteur désigne la meilleure réponse ; son auteur gagne 10 points. » |
| 4:30 | Sur la publication d'un autre → **Signaler** | « Au 3ᵉ signalement elle est masquée automatiquement. Le seuil est en configuration. » |
| 5:15 | Fenêtre privée → **Ibrahima** → **Modération** | « Le délégué voit la file de **sa** promotion, et remet en ligne ou refuse. » |
| 6:00 | **LE TEST** — Aminata ouvre une publication, note l'ID → fenêtre privée **Coumba** → `/publications/{ID}` | « **403.** Le contenu est cloisonné, même en tapant l'URL à la main. » |
| 6:45 | **Professeur** → **Les promotions** → **Voir le fil** | « L'enseignant consulte toutes les promotions, sans jamais pouvoir publier. » |

---

## 2. Lecture de code — 10 minutes

Le jury ouvre `git log --graph`, **choisit un commit au hasard**, demande ce qu'il fait, puis une modification en direct.

```bash
git log --oneline --graph --decorate --all
```

**Les 5 commits qu'il choisira probablement :**

| Commit | Ce que tu dis |
|---|---|
| `feat(models): declarer les relations eloquent` **puis** `ajouter les scopes` | « Le **même fichier** dans deux commits : un commit prend un *changement*, pas un fichier. Relations et scopes sont deux idées. » |
| `feat(feed): ajouter la policy de cloisonnement` | « Le cœur du projet. Elle protège la page de détail ; le scope protège la liste. Il faut les deux. » |
| `fix(entraide): verifier que la reponse retenue appartient a la question` | « `exists:` prouve que la réponse existe, pas qu'elle est ici. Sans `abort_unless`, on retenait une réponse d'une autre promotion via le champ caché. » |
| `refactor(auth): retirer du provider les fonctionnalites hors perimetre` | « `refactor` et non `feat` : le comportement ne change pas, je retire du code mort après avoir désactivé 2FA et passkeys. » |
| `Revert "refactor(feed): simplifier le tri..."` | voir Q1 ci-dessous |

**Tu as droit à la documentation Laravel. Pas à un assistant IA.**

---

## 3. Les 5 questions annoncées par le guide

### Q1 — Différence entre `git revert` et `git reset --hard` ? Pourquoi le premier ?

> `revert` **ajoute** un commit qui applique l'inverse d'un ancien : rien n'est supprimé, l'historique reste complet et honnête. C'est la seule méthode acceptable sur une branche déjà poussée.
>
> `reset --hard` **déplace le pointeur de branche en arrière** et supprime des commits. Utile en local sur du travail non partagé, mais destructeur dès que la branche est partagée : pousser le résultat exigerait un `--force` qui écraserait le travail des autres.
>
> **Je l'ai utilisé.** J'avais supprimé `orderByRaw('epingle_le IS NULL')` en croyant à un doublon. C'est vrai sous MySQL, faux sous PostgreSQL qui remonte les `NULL` en tête d'un tri décroissant. Le commit était poussé, donc `git revert`. C'est documenté dans `docs/JOURNAL.md`.

### Q2 — Pourquoi Fortify plutôt que Breeze ?

> Fortify fournit les mécanismes de sécurité qu'il ne faut jamais réécrire — hachage, **régénération de l'identifiant de session** contre la fixation, limitation des tentatives, jetons de réinitialisation à usage unique — **sans fournir les vues**.
>
> Breeze fournit aussi les vues : j'aurais eu un formulaire de connexion sans écrire une ligne de Blade, donc sans rien apprendre du jeton CSRF, de `old()` ni de l'affichage des erreurs. Jetstream imposerait Livewire ou Inertia, hors périmètre.
>
> Le mot-clé : Fortify est ***frontend agnostic***.

### Q3 — Que se passe-t-il si OpenRouter renvoie du texte au lieu de JSON ?

> Rien ne casse. `OpenRouterClient` ne lève jamais d'exception : il renvoie une chaîne ou `null`. `ServiceModeration::interpreter()` traite **quatre cas** — pas de réponse, JSON emballé dans du texte, JSON sans la clé attendue, valeur inventée — et tous mènent au verdict `Indisponible`.
>
> C'est alors ma configuration `moderation_fail_open` qui décide : j'ai choisi **fail-closed**, la publication part en file de modération humaine.
>
> Vérifié sur **9 cas simulés** avec `Http::fake()`. Et ce n'est pas théorique : sur **18 modèles gratuits testés**, un seul renvoyait un JSON exploitable — l'un répondait HTTP 200 avec un contenu **vide**.

### Q4 — Montrez-moi la ligne qui empêche un membre du groupe B de lire une publication du groupe A.

> **Il y en a deux, et il faut les deux.**
>
> ```php
> // app/Models/Publication.php — protège LA LISTE
> public function scopeDeLaPromotion(Builder $query, int $promotionId): void
> { $query->where('promotion_id', $promotionId); }
>
> // app/Policies/PublicationPolicy.php — protège LA PAGE DE DÉTAIL
> return $user->promotion_id === $publication->promotion_id;
>
> // app/Http/Controllers/Feed/PublicationController.php::show()
> $this->authorize('view', $publication);
> ```
>
> Le scope seul laisserait passer `/publications/12`, parce que **la liaison de modèle de route ne vérifie aucun droit** : elle trouve la publication et la donne. La policy seule laisserait fuiter la liste.

### Q5 — Pourquoi la contrainte unique en base alors que vous vérifiez déjà en PHP ?

> **Elles ne s'adressent pas au même public.** La contrainte `unique(['publication_id','user_id'])` garantit l'**intégrité des données** quoi qu'il arrive : code contourné, requête forgée, ou deux requêtes simultanées qui passeraient toutes deux le test PHP avant que l'une ait écrit. La vérification PHP affiche **un message compréhensible** plutôt qu'une page d'erreur SQL.
>
> **La base protège, le code explique.**

---

## 4. Les questions techniques probables

| Question | Réponse courte |
|---|---|
| **Pourquoi une seule table pour posts et questions ?** | Ils partagent auteur, promotion, contenu, statut, signalements. Deux tables dupliqueraient la modération et le signalement. Colonne `type` + scope `questions()`. |
| **Pourquoi `promotion_id` sur `publications` alors qu'on peut le déduire de l'auteur ?** | Dénormalisation assumée : le cloisonnement devient une condition sur une colonne **indexée**, sans jointure. Et la publication reste rattachée à la promotion **où elle a été écrite**, même si l'auteur change de groupe. |
| **À quoi sert `preventLazyLoading` ?** | Faire **planter** le développement dès qu'une relation non préchargée est utilisée, pour rendre le N+1 visible. Désactivé en production. Il m'a attrapé une vraie erreur en phase 9. |
| **Différence entre `$p->reponses` et `$p->reponses()` ?** | Sans parenthèses → la **Collection** déjà chargée. Avec → le **Builder**, sur lequel on ajoute des conditions SQL. `signalements()->where(...)->exists()` fait un seul `SELECT` ; `signalements->count()` rapatrierait toutes les lignes pour les compter en PHP. |
| **Qu'est-ce qu'un middleware ?** | Une couche traversée **avant** le contrôleur. `ExigePromotion` établit une **invariante** : en aval, `promotion_id` n'est jamais `null` — sans quoi mon scope typé `int` lèverait une `TypeError`. |
| **Pourquoi un contrôleur dédié pour la réponse retenue ?** | Ce n'est pas « modifier une question » : c'est une action avec ses propres droits. **Contrôleur de ressource singleton** — une question a au plus une réponse retenue, d'où l'absence d'identifiant dans l'URL. |
| **Pourquoi le quota ne bloque pas la publication ?** | La modération est une **contrainte** imposée par l'application ; la refuser reviendrait à empêcher quelqu'un de s'exprimer. La détection de doublon est une **assistance**, retirable sans dommage. On retire une assistance, pas une protection. |
| **Pourquoi `config/cohorte.php` ?** | Le correcteur peut changer un seuil sans toucher au code. Et `env()` renvoie `null` après `config:cache` s'il est appelé hors de `config/`. |
| **Pourquoi `increment('points', 10)` ?** | Génère `UPDATE ... SET points = points + 10`. Avec lire-puis-écrire, deux requêtes simultanées liraient la même valeur et une addition serait perdue. |
| **Score stocké ou recalculé ?** | Stocké, **plus** une commande `cohorte:recalculer-reputation`. Le stockage seul dérive en silence ; le recalcul permanent coûte une agrégation à chaque affichage du fil. |
| **Pourquoi `authorize()` et pas `authorizeResource()` ?** | `authorizeResource()` appelle `$this->middleware()`, **supprimé de la classe `Controller` en Laravel 11**. Le code du guide produit une erreur 500. J'ai mis des `$this->authorize()` explicites : l'autorisation est visible là où elle s'applique. |

---

## 5. La logique métier — les 4 règles

```
① CLOISONNEMENT
   Un apprenant ne voit que sa promotion. Y COMPRIS en tapant une URL.
   → scope deLaPromotion() pour les listes + policy view() pour le détail

② QUOTA D'IA
   10 appels/membre/jour, remis à zéro à minuit (Africa/Dakar).
   → seule la détection de doublon est bloquée ; publier reste toujours possible

③ MODÉRATION COMMUNAUTAIRE
   3 signalements → masquage automatique.
   Interdits : son propre contenu · deux fois · l'auteur voit toujours sa publication

④ RÉPUTATION
   +10 réponse retenue · +3 réponse · +1 question · −5 publication refusée
   Au-delà de 50 points → droit d'épingler
```

### Les 4 rôles

| | Visiteur | Apprenant | Délégué | Enseignant |
|---|:---:|:---:|:---:|:---:|
| A une promotion | — | ✅ | ✅ | ❌ **jamais** |
| Publie | ❌ | ✅ | ✅ | ❌ |
| Voit **sa** promotion | ❌ | ✅ | ✅ | — |
| Voit **toutes** les promotions | ❌ | ❌ | ❌ | ✅ |
| File de modération | ❌ | ❌ | ✅ **sa promo** | ❌ |
| Épingle | ❌ | si ≥ 50 pts | ✅ toujours | ❌ |

### Les trois axes qui se croisent

```
1. AI-JE UNE PROMOTION ?   → apprenant/délégué oui, enseignant non
2. QUEL EST MON RÔLE ?     → détermine mes pouvoirs supplémentaires
3. EST-CE MA PROMOTION ?   → limite ces pouvoirs à mon groupe
```

**Le point 3 est celui qu'on oublie.** C'est lui qui distingue « je suis délégué » de « je suis délégué **ici** » :

```php
abort_unless($request->user()->estDelegue(), 403);
abort_unless($request->user()->promotion_id === $publication->promotion_id, 403);
```

---

## 6. Les 4 divergences du guide — ton meilleur matériau

Le guide a été écrit pour des versions antérieures. Les avoir **détectées et corrigées** vaut mieux que de l'avoir suivi aveuglément.

| Divergence | Ce qui se serait passé | Ce que j'ai fait |
|---|---|---|
| `authorizeResource()` dans le constructeur | **Erreur 500** — `middleware()` supprimé en Laravel 11 | `$this->authorize()` explicite dans chaque méthode |
| Fortify 1.39 active 2FA et **passkeys** | routes hors périmètre, code mort | désactivés, limiteurs morts retirés dans un commit `refactor` |
| `'home' => '/publications'` | **404 après connexion** — la route n'existe qu'en phase 5 | `/` provisoirement, avec un commentaire |
| Middleware redirigeant vers `enseignant.promotions.index` | **`RouteNotFoundException`** — route jamais créée | j'ai écrit le module enseignant manquant |

**Et une preuve vécue :** `minimax/minimax-m3:free` fonctionnait le 7 septembre, **devenu payant le 9**. Correction : **une ligne de `.env`**, aucun fichier PHP, aucun commit. C'est exactement ce que le guide vise en interdisant de coder un identifiant de modèle en dur. Et pendant la panne, le fail-closed a envoyé les publications en modération au lieu de les laisser passer.

> **Si on te demande comment ton code absorberait une demande de changement :**
>
> | Changement | Fichiers à modifier |
> |---|---|
> | La règle de cloisonnement | `scopeDeLaPromotion()` — **1 ligne** |
> | Les droits d'un rôle | `PublicationPolicy` — **1 méthode** |
> | Un seuil | `config/cohorte.php`, voire le `.env` — **0 ligne de code** |
> | Le modèle d'IA disparaît | le `.env` — **et c'est arrivé** |

---

## 7. Checklist J-0

```
□ MySQL démarré dans XAMPP
□ php artisan migrate:fresh --seed        → aucune erreur
□ php artisan serve                       → un seul serveur, port 8000
□ Deux fenêtres navigateur (normale + privée)
□ Le test 403 refait UNE FOIS avant d'entrer
□ git log --oneline --graph --decorate --all   → 16 bosses, 11 tags
□ git status                              → clean, up to date with origin/main
□ Les 5 réponses de la section 3 relues à voix haute
□ La démo de 7 min répétée une fois, chrono en main
```

**Si OpenRouter tombe pendant la démo** : ne panique pas, explique.

> *« Le service est indisponible. Mon fail-closed envoie la publication en file de modération au lieu de la laisser passer sans contrôle. C'est le comportement attendu, et il est argumenté dans `DECISIONS.md`. »*

**Une panne pendant la démo est une occasion de montrer que tu l'avais prévue.**

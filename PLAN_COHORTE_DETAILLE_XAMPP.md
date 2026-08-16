# PLAN DE RÉALISATION — PROJET COHORTE

> **Source :** `Projet_Cohorte_Guide_Evaluation.pdf` — Wommate Technology, v1.0, août 2026 (69 pages).
> **Environnement :** Windows 11 · Git Bash (MINGW64) · XAMPP (Apache + MySQL/MariaDB) · PHP · Composer · Node/npm · **Laravel 12** · Blade · Fortify · OpenRouter.
> **Dépôt :** https://github.com/RassoulTech/Cohorte-Laravel
>
> Ce document est un **manuel d'exécution** : pour chaque étape, la commande exacte, son **rôle**, le **pourquoi**, le fichier concerné, le test, et le commit.
> Le guide PDF reste la référence en cas de doute — ce plan ne fait que l'ordonner et l'expliquer.

---

## TABLE DES MATIÈRES

**PARTIE I — COMPRENDRE LE PROJET**
1. Le cahier des charges
2. Les quatre règles métier
3. Ce que le correcteur évalue
4. L'environnement de travail
5. L'architecture imposée

**PARTIE II — GIT, EN PROFONDEUR**
6. Le modèle mental de Git
7. Le commit
8. La branche
9. La fusion (merge)
10. Le tag
11. `revert` contre `reset`
12. Le cycle complet d'une phase
13. GitHub : ce qui s'y passe vraiment
14. Git de secours

**PARTIE III — LE PLAN JOUR PAR JOUR**
15. Calendrier des 14 jours
16. Jour 1 — Phase 0 + Phase 1 ✅
17. Jour 2 — Phase 2
18. Jour 3 — Phase 3 (Fortify)
19. Jour 4 — Phase 3 + Phase 4
20. Jour 5 — Phase 5 (fil)
21. Jour 6 — Phase 5 (sécurité)
22. Jour 7 — Phase 6 (entraide)
23. Jour 8 — Phase 7 (OpenRouter)
24. Jour 9 — Phase 7 (modération)
25. Jour 10 — Phase 11 (demande de changement)
26. Jour 11 — Phase 8 (signalements)
27. Jour 12 — Phase 9 (quota + doublon)
28. Jour 13 — Phase 10 (réputation)
29. Jour 14 — finalisation

**PARTIE IV — LA SOUTENANCE**
30. Déroulé des 25 minutes
31. Les questions annoncées, et leurs réponses
32. Checklist J-1

**PARTIE V — ANNEXES**
33. Aide-mémoire Artisan
34. Erreurs fréquentes
35. Trame du journal
36. Checklist finale

---
---

# PARTIE I — COMPRENDRE LE PROJET

## 1. Le cahier des charges

**Cohorte** est un réseau social **privé, par promotion**. On y entre uniquement sur invitation, on n'y voit que le contenu de sa propre promotion, et un modèle de langage modère les publications et évite que la même question soit posée quinze fois.

L'application est **volontairement sobre**. Le guide le dit noir sur blanc : *« Aucune prouesse graphique n'est attendue. »* Blade + un peu de CSS suffisent. **Toute la difficulté est dans les règles métier.**

### Les quatre rôles

| Rôle | Ce qu'il peut faire |
|---|---|
| **Visiteur** | Voir l'accueil, s'inscrire avec un code d'invitation, se connecter |
| **Apprenant** | Publier, poser des questions, répondre, signaler, consulter le fil de **sa** promotion |
| **Délégué** | Tout ce que fait un apprenant + la file de modération de **sa** promotion |
| **Enseignant** | Consulter **toutes** les promotions, **sans pouvoir publier** |

### Ce qui est HORS PÉRIMÈTRE — à ne pas faire

```
✗ messagerie privée        ✗ API REST
✗ notifications temps réel ✗ framework JavaScript
✗ upload d'images          ✗ recherche plein texte
✗ thème sombre
```

> Le guide : *« Toute fonctionnalité hors périmètre ajoutée au détriment des règles métier sera considérée comme une **dispersion** et non comme un bonus. »*

---

## 2. Les quatre règles métier

Ce sont **le cœur de la note**. Elles seront testées une par une.

### Règle 1 — Le cloisonnement
Un apprenant appartient à une promotion et ne doit accéder **qu'aux** contenus de sa promotion. Cette règle vaut pour **toutes** les pages, **y compris quand on tape directement une URL**.

> Un apprenant du groupe A qui saisit l'URL d'une publication du groupe B doit recevoir **403**, pas le contenu.

C'est le test qui décide de la note. Il se protège en **deux** endroits :
- le **scope** `deLaPromotion()` filtre les **listes** ;
- la **policy** `view()` protège les **pages de détail**.

Protéger l'un sans l'autre laisse la porte ouverte.

### Règle 2 — Le quota d'IA
Chaque membre a un nombre limité d'appels IA **par jour**, remis à zéro à minuit. Au-delà, les fonctionnalités intelligentes se désactivent **proprement** : l'application ne plante pas, et le membre peut **toujours publier**, simplement sans assistance.

### Règle 3 — La modération communautaire
Tout membre peut signaler une publication. À partir d'un seuil, elle est **automatiquement masquée** du fil. Trois interdits :
1. on ne signale pas **sa propre** publication ;
2. on ne signale pas **deux fois** la même publication ;
3. une publication masquée **reste visible pour son auteur**, avec un message expliquant son état.

### Règle 4 — La réputation
Chaque membre a un score de contribution calculé sur son activité **utile**. Au-delà d'un seuil, il obtient le droit d'**épingler** une publication en haut du fil de sa promotion.

### Les deux fonctionnalités intelligentes

**La modération automatique.** Avant enregistrement en base, le contenu part vers un modèle via OpenRouter qui rend un verdict en trois niveaux : *acceptable* → publié · *inacceptable* → refusé avec message · *douteux* → file d'attente du délégué.

**La détection de doublon.** Quand un membre s'apprête à poser une question, l'application soumet au modèle sa question **et les titres des questions déjà posées dans sa promotion**, et lui montre les questions similaires avant de le laisser publier.

---

## 3. Ce que le correcteur évalue

### La règle des trois obligations

Pour **chaque** phase, sans exception :

```
1. une BRANCHE dédiée, créée AVANT d'écrire la moindre ligne
2. au MINIMUM 2 COMMITS poussés sur cette branche
3. une ENTRÉE DE JOURNAL : ce que j'ai fait / pourquoi ainsi /
   quelle difficulté / comment résolue
```

> *« Une phase livrée sans branche, ou avec un seul commit géant en fin de journée, est considérée comme non conforme **même si le code fonctionne parfaitement**. »*

### La grille — 100 points

| Critère | Ce qui est regardé | Pts |
|---|---|---:|
| **Git et traçabilité** | Une branche par phase, fusion `--no-ff`, commits atomiques, messages conformes, **dates réparties**, un `revert` documenté | **20** |
| **Cloisonnement / sécurité** | Le test d'accès direct par URL, la policy, les scopes, `.env` absent de l'historique, validation systématique | **15** |
| **Modèle de données** | Migrations correctes, clés étrangères, index, contrainte unique, relations nommées, absence de N+1 | 12 |
| **Conventions d'architecture** | Contrôleurs et vues en sous-dossiers, namespaces corrects, contrôleurs de ressource, routes nommées | 10 |
| **Authentification** | Fortify installé/configuré, vues écrites, code d'invitation validé dans `CreateNewUser`, limitation des tentatives | 10 |
| **Fonctionnalités IA** | Client isolé, timeout, parsing défensif, gestion de la panne, quota fonctionnel | 10 |
| **Soutenance** | Explication d'un commit au hasard, modification en direct, réponses aux questions de conception | 10 |
| **Documentation** | README installable, journal sincère, cinq décisions argumentées, `IA.md` rempli | 8 |
| **Seeders** | `migrate:fresh --seed` fonctionne, 2 promotions, 4 comptes conformes | 5 |

**Git + sécurité = 35 points.** C'est plus que toutes les fonctionnalités réunies.

### Les pénalités

| Situation | Pénalité |
|---|---:|
| `.env` présent dans l'historique Git | **−10** |
| Tous les commits poussés le même jour | **−10** |
| Clé OpenRouter écrite en dur dans le code | **−10** |
| `php artisan migrate:fresh --seed` échoue | −8 |
| Aucune branche de fonctionnalité, tout sur `main` | −8 |
| Journal absent ou réduit à une liste de titres | −6 |
| Contrôleurs ou vues laissés à plat | −5 |

### L'IA générative : ce qui est autorisé

Tu **peux** utiliser une IA. Mais tu dois tenir `docs/IA.md` avec, **par phase** : ce que tu as demandé, ce que tu as retenu, et **surtout ce que tu as rejeté et pourquoi**.

> C'est ce dernier point qui compte. Le guide donne l'exemple : *« l'IA me proposait de filtrer la promotion dans la vue Blade, je l'ai refusé parce que la sécurité ne doit jamais dépendre de l'affichage. »*

Un `IA.md` vide alors que le code raconte autre chose est **lourdement pénalisé**.

---

## 4. L'environnement de travail

```
Windows 11
├── XAMPP Control Panel
│   ├── Apache   (pas indispensable : on utilise php artisan serve)
│   └── MySQL    ← à démarrer AVANT de travailler
├── Git Bash (MINGW64)
├── PHP 8.2+
├── Composer
├── Node / npm
└── Laravel 12
```

### Démarrer une séance de travail

```bash
# 1. Démarrer MySQL depuis XAMPP Control Panel (bouton Start)
# 2. Vérifier qu'il écoute
netstat -an | grep 3306

# 3. Aller dans le projet
cd ~/Documents/perso/PROJECTS/cohorte

# 4. Voir où on en est
git status
git branch
git log --oneline --graph --decorate -5
```

### ⚠️ Ne PAS utiliser

```bash
net start MySQL80        # cherche un service Windows qui n'existe pas ici
```

Notre MySQL est lancé par **XAMPP**, pas par un service Windows.

### Si `mysql` n'est pas trouvé

```bash
/c/xampp/mysql/bin/mysql.exe --version     # test avec le chemin complet

# Ajouter XAMPP au PATH une fois pour toutes :
printf '\nexport PATH="$PATH:/c/xampp/mysql/bin"\n' >> ~/.bashrc
source ~/.bashrc
mysql --version
```

**Le PATH**, c'est simplement la liste des dossiers dans lesquels le shell cherche un exécutable quand tu tapes son nom.

### Se connecter à MySQL depuis Git Bash

```bash
winpty mysql -u root            # sans mot de passe
winpty mysql -u root -p         # avec mot de passe
```

`winpty` est nécessaire sous Git Bash pour les programmes qui attendent une saisie interactive.

---

## 5. L'architecture imposée

> Ces conventions **sont notées**. Le non-respect coûte des points même si l'application fonctionne.

### Les contrôleurs, groupés par module métier

```
app/Http/Controllers/
├── Controller.php
├── Promotion/
│   ├── AdhesionController.php
│   └── PromotionController.php
├── Feed/
│   ├── PublicationController.php
│   └── EpinglageController.php
├── Entraide/
│   ├── QuestionController.php
│   ├── ReponseController.php
│   └── ReponseRetenueController.php
├── Moderation/
│   ├── SignalementController.php
│   └── FileModerationController.php
└── Profil/
    └── ProfilController.php
```

**Le piège qui fait perdre une heure à tout le monde :** le `namespace` doit refléter le chemin du dossier.

```php
<?php
namespace App\Http\Controllers\Feed;      // ← reflète app/Http/Controllers/Feed/

use App\Http\Controllers\Controller;      // ← INDISPENSABLE
use App\Models\Publication;

class PublicationController extends Controller
{
}
```

La ligne `use App\Http\Controllers\Controller;` est obligatoire : le contrôleur de base n'est plus dans le même namespace que le tien, donc `extends Controller` ne le trouverait pas tout seul.

> `Target class [PublicationController] does not exist` → 9 fois sur 10, c'est le namespace ou le `use` manquant dans `routes/web.php`. Le 10ᵉ cas se règle avec `composer dump-autoload`.

### Les vues, même découpage

```
resources/views/
├── layouts/
│   └── app.blade.php
├── components/
│   ├── carte-publication.blade.php
│   └── alerte.blade.php
├── auth/
│   ├── login.blade.php
│   ├── register.blade.php
│   ├── forgot-password.blade.php
│   └── reset-password.blade.php
├── promotion/
│   ├── rejoindre.blade.php
│   └── show.blade.php
├── feed/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── show.blade.php
├── entraide/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── show.blade.php
├── moderation/
│   └── index.blade.php
└── profil/
    └── show.blade.php
```

On désigne une vue en remplaçant les `/` par des `.`, sans extension :

```php
return view('feed.index', ['publications' => $publications]);
return view('entraide.show', compact('question'));
```

Un composant de `components/` s'utilise comme une balise :

```blade
<x-carte-publication :publication="$publication" />
```

```blade
{{-- components/carte-publication.blade.php --}}
@props(['publication'])

<article class="carte">
    <h3>{{ $publication->titre }}</h3>
    <p>{{ Str::limit($publication->contenu, 200) }}</p>
    <footer>Publié par {{ $publication->auteur->name }}</footer>
</article>
```

> **Pourquoi c'est noté :** un correcteur doit trouver la vue du fil en moins de dix secondes, sans chercher, en devinant qu'elle est dans `feed/`. C'est ce que fera un collègue qui reprend ton code dans six mois.

---
---

# PARTIE II — GIT, EN PROFONDEUR

> C'est **20 points sur 100**, plus que n'importe quelle fonctionnalité. Cette partie explique le *fonctionnement*, pas seulement les commandes.

## 6. Le modèle mental de Git

Git n'a pas deux endroits (mon PC / GitHub) mais **quatre**.

```
 RÉPERTOIRE DE TRAVAIL      INDEX             DÉPÔT LOCAL          DÉPÔT DISTANT
    (tes fichiers)      (zone de préparation)  (.git/ chez toi)      (GitHub)
          │                     │                    │                    │
          │──── git add ───────>│                    │                    │
          │                     │──── git commit ───>│                    │
          │                     │                    │──── git push ─────>│
          │<──── git restore ───│                    │                    │
          │<──────────────────────── git pull ───────────────────────────│
```

| Zone | Ce que c'est | Comment la regarder |
|---|---|---|
| **Répertoire de travail** | Les fichiers que tu édites dans VS Code | `git status` · `git diff` |
| **Index** (*staging area*) | La **vitrine** : ce que tu as choisi de mettre dans le prochain commit | `git diff --staged` |
| **Dépôt local** | L'historique complet, dans le dossier `.git/` | `git log` |
| **Dépôt distant** | La copie sur GitHub, nommée `origin` | `git log origin/main` |

### Lire `git status`

```
 M .env.example        ← Modifié dans le répertoire de travail, PAS dans l'index
M  config/cohorte.php  ← Modifié ET ajouté à l'index (prêt à être commité)
MM app/Models/User.php ← Ajouté à l'index PUIS modifié à nouveau
?? docs/               ← Non suivi : Git ne le connaît pas encore
 D ancien-fichier.md   ← Supprimé du répertoire de travail
```

**Deux colonnes.** La 1ʳᵉ = l'index. La 2ᵉ = le répertoire de travail. Comprendre ces deux colonnes, c'est comprendre Git.

### Les trois commandes de lecture, à faire AVANT chaque commit

```bash
git status              # quels fichiers ont bougé ?
git diff                # qu'est-ce qui a changé et n'est PAS encore dans l'index ?
git diff --staged       # qu'est-ce que je m'apprête EXACTEMENT à commiter ?
```

> **Ne jamais commiter sans avoir lu `git diff --staged`.** C'est le seul moment où tu peux encore attraper un `dd()`, un mot de passe ou un fichier oublié.

---

## 7. Le commit

### Ce qu'est un commit

Un commit est une **photo de l'ensemble du projet** à un instant donné, plus :
- un **identifiant** (SHA-1 : `3f9a1c2...`) ;
- un **parent** (le commit précédent) — c'est ce qui forme la chaîne ;
- un **auteur** et une **date** ;
- un **message**.

Un commit est **immuable**. On ne le modifie jamais : on en ajoute un nouveau par-dessus.

### Ce qu'un commit prend : un CHANGEMENT, pas un fichier

C'est le point que tout le monde comprend de travers.

```
Commit A : app/Models/Publication.php  (les relations Eloquent)
Commit B : app/Models/Publication.php  (les scopes ajoutés ensuite)
```

**Le même fichier dans deux commits différents.** Un commit ne capture pas « un fichier », il capture « une idée ». C'est pour ça que la phase 1 attend 8 commits alors qu'elle ne touche que 11 fichiers.

### Le commit atomique

> **Un commit fait une chose, et une seule.**
> Le test du guide : *si ton message contient le mot « et », coupe-le en deux.*

Le geste concret :

```bash
git add chemin/precis/du/fichier.php     # ✅ ce que tu veux, et rien d'autre
git add .                                # ❌ tout, y compris ce que tu n'as pas relu
```

### La convention de message — Conventional Commits

```
type(portee): description à l'impératif, en minuscule, sans point final
```

| Type | Quand | Exemple |
|---|---|---|
| `feat` | Nouvelle fonctionnalité visible | `feat(feed): afficher le fil de la promotion` |
| `fix` | Correction de bug | `fix(feed): corriger la fuite entre promotions sur la page detail` |
| `refactor` | Réorganisation **sans** changement de comportement | `refactor(feed): extraire le scope deLaPromotion` |
| `docs` | Documentation, README, journal | `docs: journal de la phase 5` |
| `chore` | Configuration, dépendances, outillage | `chore: installer laravel/fortify` |
| `test` | Ajout ou modification de tests | `test(feed): verifier le 403 hors promotion` |
| `wip` | Travail en cours — **uniquement sur une branche** | `wip(feed): pagination en cours` |

**Messages sanctionnés :** `update`, `modif`, `test`, `ça marche`, `final`, `finalfinal`, `.`

> *« Ils ne coûtent rien à écrire et ne servent à rien à personne, vous compris, dans trois jours. »*

### La répartition des dates — piège à −10

> *« Un dépôt dont tous les commits ont été poussés le dernier jour est immédiatement repérable et sera sanctionné. »*

**Pousse à la fin de chaque séance**, même si la fonctionnalité n'est pas finie. Un `wip:` sur une branche de fonctionnalité est **parfaitement acceptable**.

Pour vérifier la répartition :

```bash
git log --date=short --pretty=format:'%ad %h %s'
```

---

## 8. La branche

### Ce qu'est vraiment une branche

Une branche n'est **pas** une copie du projet. C'est un **simple pointeur** (un fichier de 41 octets) qui contient l'identifiant d'un commit.

```
                        main
                         ↓
  A ─── B ─── C ─────── M
         \             /
          D ─── E ─── F
                      ↑
              feat/05-fil-promotion
```

Quand tu commites, le pointeur de la branche courante **avance**. `HEAD` est un autre pointeur qui dit « sur quelle branche je suis ».

C'est pour ça que créer une branche est **instantané** et **gratuit**.

### Les noms imposés par le guide

```
feat/00-installation             feat/06-entraide
feat/01-modele-donnees           feat/07-moderation-ia
feat/02-seeders                  feat/08-signalements
feat/03-authentification-fortify feat/09-quota-et-doublon
feat/04-adhesion-promotion       feat/10-reputation
feat/05-fil-promotion            feat/11-demande-changement
```

> ⚠️ **Ce sont les noms du PDF, et ils font foi.** Ils sont légèrement différents dans certaines listes abrégées — utilise toujours ceux ci-dessus.

### Créer et changer de branche

```bash
git switch main                       # revenir sur main
git pull                              # récupérer ce qui est sur GitHub
git switch -c feat/05-fil-promotion   # -c = create : créer ET basculer dessus

git switch main                       # revenir
git switch -                          # revenir à la branche précédente
git branch                            # lister les branches locales
git branch -a                         # + les branches distantes
git branch --show-current             # sur quelle branche suis-je ?
```

> `git switch` est la commande moderne. `git checkout` fait la même chose **et dix autres choses**, ce qui la rend ambiguë. Utilise `switch` pour les branches et `restore` pour les fichiers.

### Ce qui suit quand tu changes de branche

Les fichiers **non suivis** (`??`) et les modifications **non commitées** te suivent d'une branche à l'autre. C'est normal : Git ne touche qu'aux fichiers qu'il connaît. Si ça te gêne, utilise `git stash` (§14).

---

## 9. La fusion (merge)

### Le problème du *fast-forward*

Quand `main` n'a pas bougé depuis que tu as créé ta branche, Git peut simplement **faire glisser le pointeur** `main` en avant. C'est l'« avance rapide » (*fast-forward*).

```
AVANT :
  A ─── B                     ← main
         \
          C ─── D ─── E       ← feat/05

APRÈS un merge en fast-forward :
  A ─── B ─── C ─── D ─── E   ← main
                            ↑ la branche a totalement disparu de l'historique
```

**Une ligne droite.** Impossible de savoir que tu as travaillé en branches. C'est exactement ce qui te ferait perdre les 20 points.

### La solution : `--no-ff`

`--no-ff` = *no fast-forward*. Git est **forcé** de créer un **commit de fusion** (un commit à deux parents).

```
APRÈS un merge --no-ff :

  A ─── B ─────────────── M   ← main   (M a DEUX parents : B et E)
         \               /
          C ─── D ─── E      ← feat/05
```

La structure **reste visible** dans `git log --graph`. C'est précisément ce que le jury regardera.

```bash
git switch main
git pull
git merge --no-ff feat/05-fil-promotion -m "merge: fil de promotion avec cloisonnement"
```

> **`--no-ff` est obligatoire sur ce projet.** Sans lui, ton travail en branches est invisible.

### Après la fusion

**Ne supprime pas la branche.** Elle reste comme trace du découpage, en local et sur GitHub. Le correcteur veut la voir dans `git branch -a`.

---

## 10. Le tag

### Ce qu'est un tag

Un tag est une **étiquette permanente** posée sur un commit précis. Contrairement à une branche, il **ne bouge jamais**. C'est un marque-page dans l'histoire du projet.

Dans la vraie vie, on tague les **versions livrées** (`v1.0.0`, `v1.2.3`). Ici, on tague les **phases terminées**.

```bash
git tag phase-05                       # tag léger, sur le commit courant
git tag                                # lister tous les tags
git show phase-05                      # voir le commit taggé
git push origin main --tags            # ⚠️ les tags ne partent PAS avec un git push simple
```

> **Le piège :** `git push` **ne pousse pas les tags**. Il faut `--tags` (ou `git push origin phase-05`). Un tag posé mais non poussé n'existe pas pour le correcteur.

### Le tag dans le cycle

```bash
git switch main
git merge --no-ff feat/05-fil-promotion -m "merge: fil de promotion avec cloisonnement"
git tag phase-05
git push origin main --tags
```

Sur GitHub, chaque tag apparaît dans l'onglet **Tags** / **Releases** : le correcteur peut télécharger le projet **exactement tel qu'il était à la fin de chaque phase**. C'est très parlant.

---

## 11. `revert` contre `reset`

> **Obligation du guide :** au moins **un retour arrière documenté** dans le journal, avec `git revert` et **jamais** `git reset`. La différence **sera demandée en soutenance**.

### `git revert` — la seule méthode acceptable sur du code déjà poussé

`revert` crée un **nouveau commit** qui applique l'inverse d'un ancien commit.

```
AVANT :  A ─── B ─── C(bug) ─── D
APRÈS :  A ─── B ─── C(bug) ─── D ─── E(annule C)
```

**L'historique s'allonge, rien n'est perdu, rien n'est réécrit.**

```bash
git log --oneline -10          # identifier le commit fautif
git revert 3f9a1c2             # crée le commit d'annulation
git push
```

### `git reset --hard` — destructeur

`reset` **déplace le pointeur de branche en arrière** et supprime des commits de l'historique.

```
AVANT :  A ─── B ─── C ─── D      ← main
APRÈS :  A ─── B                  ← main    (C et D n'existent plus)
```

C'est utile **en local**, sur du travail **non partagé**. Dès que quelqu'un a récupéré ta branche, c'est destructeur — et pousser le résultat exige `--force`, ce qui écraserait le travail des autres.

### La réponse à donner en soutenance

> « `revert` **ajoute** un commit qui annule un ancien : l'historique reste complet et honnête, c'est la seule méthode acceptable sur une branche déjà poussée. `reset --hard` **réécrit** l'historique en supprimant des commits : c'est pratique en local sur du travail non partagé, mais destructeur dès que la branche est partagée, parce que pousser le résultat obligerait à un `--force` qui écrase le travail des autres. »

### Les autres formes de reset

```bash
git reset --soft HEAD~1     # annule le commit, GARDE les modifications dans l'index
git reset HEAD~1            # annule le commit, garde les modifications (hors index)
git reset --hard HEAD~1     # annule le commit ET jette les modifications ⚠️
```

> ⚠️ **NE JAMAIS `git push --force` sur `main`.** Le guide est explicite : *« Si vous croyez avoir besoin de `--force`, c'est presque toujours que `git revert` était la bonne réponse. »*

---

## 12. Le cycle complet d'une phase

**À appliquer identiquement pour chaque phase.** C'est la recette à connaître par cœur.

```bash
# ── 1. PARTIR D'UN MAIN À JOUR ─────────────────────────────
git switch main
git pull

# ── 2. CRÉER LA BRANCHE, AVANT D'ÉCRIRE LA MOINDRE LIGNE ───
git switch -c feat/05-fil-promotion

# ── 3. TRAVAILLER PAR PETITS INCRÉMENTS ────────────────────
#    (écrire → tester → relire → commiter → recommencer)
git status
git diff
git add app/Http/Controllers/Feed/PublicationController.php
git diff --staged                       # ← relecture obligatoire
git commit -m "feat(feed): creer le controleur de ressource des publications"

#    ... 4 à 7 commits comme celui-ci ...

# ── 4. POUSSER (à chaque fin de séance !) ──────────────────
git push -u origin feat/05-fil-promotion    # la 1re fois : -u crée le suivi
git push                                     # les fois suivantes

# ── 5. TESTER POUR DE VRAI ─────────────────────────────────
php artisan migrate:fresh --seed
#    + le test manuel de la phase

# ── 6. ÉCRIRE LE JOURNAL ───────────────────────────────────
#    docs/JOURNAL.md → entrée de la phase
git add docs/JOURNAL.md
git commit -m "docs: journal de la phase 5"
git push

# ── 7. FUSIONNER ET TAGUER ─────────────────────────────────
git switch main
git pull
git merge --no-ff feat/05-fil-promotion -m "merge: fil de promotion avec cloisonnement"
git tag phase-05
git push origin main --tags

# ── 8. VÉRIFIER LE RÉSULTAT ────────────────────────────────
git log --oneline --graph --decorate --all
```

**À ne jamais inclure dans un commit :**

```
.env  ·  une clé d'API  ·  dd()  ·  var_dump()  ·  dump()
fichiers temporaires  ·  /vendor  ·  /node_modules
```

---

## 13. GitHub : ce qui s'y passe vraiment

### `origin`, c'est quoi

`origin` est simplement le **nom court** que Git donne à l'URL de ton dépôt distant. Rien de magique.

```bash
git remote -v
# origin  https://github.com/RassoulTech/Cohorte-Laravel.git (fetch)
# origin  https://github.com/RassoulTech/Cohorte-Laravel.git (push)
```

### Les branches de suivi (*tracking branches*)

Quand tu fais `git push -u origin feat/05-fil-promotion`, le `-u` (*upstream*) crée un lien permanent entre ta branche locale et sa jumelle sur GitHub. Après ça, un simple `git push` ou `git pull` sait où aller.

Git garde aussi une copie locale de l'état distant, visible sous `origin/…` :

```bash
git branch -a
#   feat/05-fil-promotion
# * main
#   remotes/origin/feat/05-fil-promotion   ← ce que GitHub avait au dernier contact
#   remotes/origin/main
```

`origin/main` n'est **pas** GitHub en direct : c'est une **photo** de GitHub prise lors du dernier `fetch`/`pull`/`push`.

```bash
git fetch                  # met à jour les origin/* SANS toucher à ton travail
git pull                   # = git fetch + git merge origin/<branche>
```

> **Conseil :** en cas de doute, fais `git fetch` puis `git log --oneline --graph --all`. Tu vois la situation complète sans rien modifier.

### Ce que le correcteur voit sur GitHub

| Sur GitHub | Ce qu'il en déduit |
|---|---|
| Onglet **Code → branch selector** | Tu as bien 12 branches, une par phase |
| Onglet **Commits** | Les messages, et surtout **les dates** — étalées ou toutes le même jour |
| **Insights → Network** | Le graphe visuel des branches et des fusions : c'est là que `--no-ff` se voit |
| Onglet **Tags** | `phase-00` … `phase-10` : il peut télécharger chaque étape |
| Recherche de fichier | Il tape `.env` : il ne doit **rien** trouver |
| `README.md` | Il essaie d'installer ton projet en le suivant |

### Les Pull Requests : utiles, mais pas obligatoires ici

Une **Pull Request** (PR) est une fonctionnalité **de GitHub**, pas de Git. C'est une demande de fusion accompagnée d'une discussion, faite pour la **revue de code en équipe**.

Sur ce projet tu travailles **seul** et le guide demande une fusion **en ligne de commande** avec `--no-ff`. Tu n'as donc **pas besoin** de PR.

Mais si tu veux montrer que tu sais faire (et ça fait bonne impression) :
1. pousse ta branche ;
2. GitHub affiche « Compare & pull request » ;
3. ouvre la PR, décris ce qu'elle contient ;
4. **fusionne-la avec l'option « Create a merge commit »** — c'est l'équivalent de `--no-ff` (surtout **pas** « Squash and merge », qui écrase tous tes commits atomiques en un seul !) ;
5. puis en local : `git switch main && git pull`.

> ⚠️ **« Squash and merge » détruirait exactement ce sur quoi tu es noté.** Si tu utilises les PR, c'est **« Create a merge commit »** et rien d'autre.

### Si tu as déjà commité `.env` par erreur

Le guide est formel : **ne le supprime pas en silence.**

```
1. Signale-le dans docs/JOURNAL.md
2. Régénère la clé d'API compromise (elle est publique, considère-la comme perdue)
3. Ajoute .env au .gitignore
4. git rm --cached .env
5. git commit -m "chore: retirer le fichier .env du suivi git"
```

Le fichier reste dans l'**historique** (c'est pour ça que la clé est perdue), mais la démarche honnête et documentée vaut mieux qu'une dissimulation.

---

## 14. Git de secours

| Situation | Commande |
|---|---|
| Bêtise dans le dernier commit, **pas encore poussé** | `git reset --soft HEAD~1` |
| Commit **déjà poussé** qui casse tout | `git revert 3f9a1c2` puis `git push` |
| Revenir à la version commitée d'un fichier | `git restore chemin/fichier.php` |
| Retirer un fichier de l'index (sans perdre les modifs) | `git restore --staged chemin/fichier.php` |
| Changer de branche, travail non fini | `git stash push -m "formulaire en cours"` … `git stash pop` |
| Voir ce que j'ai vraiment livré | `git log --oneline --graph --all --decorate` |
| Voir le contenu d'un commit | `git show 3f9a1c2` |
| Comparer une branche à main | `git diff main..feat/05-fil-promotion` |
| **J'ai perdu un commit après un `reset --hard`** | `git reflog` puis `git switch -c recuperation 8a2b1f9` |

> **`git reflog` est ton filet de sécurité ultime.** Il liste **tout** ce que `HEAD` a touché ces derniers jours, y compris les commits « supprimés ». Tant que tu n'as pas attendu des semaines, presque rien n'est vraiment perdu dans Git.

---
---

# PARTIE III — LE PLAN JOUR PAR JOUR

## 15. Calendrier des 14 jours

| Jour | Phase | Branche | Livraison de fin de journée |
|---:|---|---|---|
| **1** ✅ | 0 + 1 | `feat/00-installation` · `feat/01-modele-donnees` | Projet installé, migrations et modèles écrits |
| 2 | 2 | `feat/02-seeders` | Factories, seeders, `migrate:fresh --seed` opérationnel |
| 3 | 3 | `feat/03-authentification-fortify` | Fortify installé, connexion fonctionnelle |
| 4 | 3 + 4 | `feat/03-…` · `feat/04-adhesion-promotion` | Inscription par code, middleware promotion |
| 5 | 5 | `feat/05-fil-promotion` | Fil affiché et cloisonné |
| 6 | 5 | `feat/05-fil-promotion` | Policy, FormRequest, test d'accès direct validé |
| 7 | 6 | `feat/06-entraide` | Questions, réponses, réponse retenue |
| 8 | 7 | `feat/07-moderation-ia` | Client OpenRouter opérationnel — **réception de la demande de changement** |
| 9 | 7 | `feat/07-moderation-ia` | Modération branchée, panne gérée |
| 10 | **11** | `feat/11-demande-changement` | Changement intégré |
| 11 | 8 | `feat/08-signalements` | Signalements et masquage |
| 12 | 9 | `feat/09-quota-et-doublon` | Quota et détection de doublon |
| 13 | 10 | `feat/10-reputation` | Réputation, finitions |
| 14 | — | — | Documentation, dernier `migrate:fresh --seed`, répétition de la soutenance |

> ⚠️ **Attention à l'ordre :** la demande de changement (phase **11**) se traite au **jour 10**, **avant** les phases 8, 9 et 10. Ce n'est pas une erreur du guide : c'est fait exprès, pour que le changement tombe sur un code déjà conséquent mais encore vivant.

### Règle de fin de journée — à faire **chaque soir**

```bash
git status                              # rien d'oublié ?
php artisan migrate:fresh --seed        # ça repart de zéro ?
git diff && git diff --staged           # relire
git commit -m "..."                     # commiter
git push                                # POUSSER (répartition des dates !)
```

Puis écrire dans `docs/JOURNAL.md` :
```
Ce qui fonctionne · Ce qui reste · Le prochain objectif
```

---

## 16. JOUR 1 — Phase 0 + Phase 1 ✅ *(fait)*

**Branches :** `feat/00-installation` · `feat/01-modele-donnees`

### 16.1 Phase 0 — installation et mise en place

| # | Action | Rôle / Pourquoi |
|---|---|---|
| 1 | `composer create-project laravel/laravel cohorte` | Créer le squelette Laravel 12 |
| 2 | `php artisan key:generate` | Génère `APP_KEY`, la clé de chiffrement des sessions et cookies |
| 3 | Démarrer **MySQL** dans XAMPP | Le serveur de base de données |
| 4 | `CREATE DATABASE cohorte CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;` | `utf8mb4` gère les accents **et** les emojis, contrairement à `utf8` de MySQL qui est incomplet |
| 5 | Configurer `.env` (`DB_CONNECTION=mysql`, `DB_DATABASE=cohorte`…) | Laravel lit sa configuration ici |
| 6 | `php artisan migrate` puis `php artisan serve` | Vérifier que tout démarre |
| 7 | Mettre `.env.example` à jour | **C'est lui qui est commité.** Il permet à un inconnu d'installer le projet |
| 8 | Créer `config/cohorte.php` | Centraliser les valeurs métier |
| 9 | Créer `layouts/app.blade.php` + `components/alerte.blade.php` | Gabarit commun + affichage des messages flash |
| 10 | Créer `README.md` et `docs/` | Documentation |

**Le point à comprendre — pourquoi `config/cohorte.php` :**

```php
// config/cohorte.php
return [
    'quota_ia_quotidien'   => (int) env('COHORTE_QUOTA_IA', 10),
    'seuil_signalement'    => (int) env('COHORTE_SEUIL_SIGNALEMENT', 3),
    'seuil_epinglage'      => (int) env('COHORTE_SEUIL_EPINGLAGE', 50),
    'moderation_fail_open' => (bool) env('COHORTE_MODERATION_FAIL_OPEN', false),
];
```

On lit ensuite partout `config('cohorte.seuil_signalement')`. **Double intérêt :** le correcteur peut changer un seuil sans toucher au code pour tester ta logique, et tu n'auras pas à chercher dans quinze fichiers le jour où la règle change.

> ⚠️ **Ne jamais appeler `env()` en dehors de `config/`.** Si tu exécutes un jour `php artisan config:cache`, tous les `env()` situés dans un contrôleur ou un modèle renvoient `null`. C'est la raison technique de cette règle.

**Commits attendus :**
```
chore: initialiser le projet laravel 12
chore: configurer la base mysql et le fichier .env.example
feat(config): ajouter la configuration metier cohorte
feat(layout): ajouter le gabarit de base et le composant alerte
docs: initialiser le readme et le journal de bord
```

### 16.2 Phase 1 — le modèle de données

**Six tables**, dont une fournie par Laravel.

| Table | Rôle |
|---|---|
| `users` | Fournie par Laravel, **enrichie** de `promotion_id`, `role`, `points` |
| `promotions` | Une promotion, avec son code d'invitation **unique** |
| `publications` | Un **post ou une question** — les deux dans la même table |
| `reponses` | Les réponses apportées à une question |
| `signalements` | Le lien entre un membre et une publication signalée |
| `appels_ia` | La trace de **chaque** appel OpenRouter — base du calcul de quota |

**Pourquoi une seule table pour les posts et les questions ?** Parce qu'ils partagent presque tout : un auteur, une promotion, un contenu, un statut de modération, la possibilité d'être signalés. Deux tables obligeraient à **dupliquer** la logique de modération et de signalement. On les distingue par une colonne `type` et on isole les questions avec un **scope**. *C'est une décision à savoir défendre en soutenance.*

**Génération :**
```bash
php artisan make:model Promotion -mf     # -m = migration, -f = factory
php artisan make:model Publication -mf
php artisan make:model Reponse -mf
php artisan make:model Signalement -m    # pas de factory : journalisation d'actions réelles
php artisan make:model AppelIa -m        # pas de factory : appels réels
php artisan make:migration add_cohorte_fields_to_users_table --table=users
php artisan make:migration add_reponse_retenue_to_publications_table --table=publications
```

**L'ordre des migrations compte.** Elles s'exécutent dans l'ordre de leur **horodatage**, et une table référencée par une clé étrangère doit exister **avant** celle qui la référence :

```
users (Laravel) → promotions → users.promotion_id → publications
                → reponses → publications.reponse_retenue_id
                → signalements → appels_ia
```

**La référence circulaire.** `publications.reponse_retenue_id` pointe vers `reponses`, mais `reponses.publication_id` pointe vers `publications`. Chacune a besoin de l'autre. La seule sortie : créer `publications` **sans** la colonne, créer `reponses`, puis **ajouter** la clé étrangère dans une migration postérieure. C'est le rôle de `add_reponse_retenue_to_publications_table`.

**Les pièges rencontrés (documentés dans le journal) :**
- `make:model AppelIa -m` génère une table `appel_ias`. Le cahier des charges veut `appels_ia` → renommer la migration **et** déclarer `protected $table = 'appels_ia';` dans le modèle.
- Modifier une migration **déjà jouée** ne change rien à la base → il faut `php artisan migrate:fresh`.

**Les relations à connaître :**

| Relation | Sens | Où est la clé étrangère |
|---|---|---|
| `belongsTo` | le modèle **appartient à** un autre | sur **sa propre** table |
| `hasMany` | le modèle **possède plusieurs** autres | sur la table de **l'autre** modèle |

```php
// Publication
public function auteur(): BelongsTo      { return $this->belongsTo(User::class, 'user_id'); }
public function promotion(): BelongsTo   { return $this->belongsTo(Promotion::class); }
public function reponses(): HasMany      { return $this->hasMany(Reponse::class); }
public function signalements(): HasMany  { return $this->hasMany(Signalement::class); }
public function reponseRetenue(): BelongsTo { return $this->belongsTo(Reponse::class, 'reponse_retenue_id'); }
```

On l'appelle `auteur()` et non `user()` : `$publication->auteur->name` se lit tout seul dans une vue. Comme le nom ne suit plus la convention, **on précise la clé** `'user_id'` en 2ᵉ argument. **Un même concept porte le même nom partout** — `Reponse` a aussi un `auteur()`.

**Les scopes — le point le plus important de la phase :**

```php
public function scopeDeLaPromotion(Builder $query, int $promotionId): void
{ $query->where('promotion_id', $promotionId); }

public function scopeVisibles(Builder $query): void
{ $query->where('statut', 'publie'); }

public function scopeQuestions(Builder $query): void
{ $query->where('type', 'question'); }
```

```php
Publication::query()
    ->visibles()
    ->deLaPromotion($user->promotion_id)
    ->with('auteur')
    ->latest()
    ->paginate(15);
```

> **L'intérêt n'est pas esthétique.** Le jour où la règle de cloisonnement change — *et elle changera, c'est la demande de changement du jour 10* — tu n'as **qu'un seul endroit** à modifier.

**Le `$fillable` oublié — bug numéro un du semestre :**
- **aucun** `$fillable` → `create()` lève une `MassAssignmentException` bien visible ;
- `$fillable` **incomplet** → Laravel **ignore l'attribut en silence**, aucune erreur, la colonne reste vide.

> Chaque fois qu'une valeur « ne s'enregistre pas » sans message d'erreur : **relis le `$fillable`.**

**`preventLazyLoading` — le professeur brutal :**

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    Model::preventLazyLoading(! app()->isProduction());
}
```

Cette ligne fait **planter** l'application, en développement uniquement, dès que tu accèdes à une relation non préchargée.

```php
// ❌ 1 + 15 requêtes (problème du « N+1 »)
$publications = Publication::visibles()->latest()->paginate(15);
// puis dans la vue : $publication->auteur->name

// ✅ 2 requêtes
$publications = Publication::visibles()->with('auteur')->latest()->paginate(15);
```

**Test de la phase :**
```bash
php artisan migrate:fresh
php artisan tinker
```
```php
$p = App\Models\Promotion::create(['nom' => 'Test', 'code_invitation' => 'TEST01', 'annee' => 2026]);
$u = App\Models\User::factory()->create(['promotion_id' => $p->id]);
$p->membres()->count();      // doit renvoyer 1
$u->promotion->nom;          // doit renvoyer "Test"
```

**Commits attendus :**
```
feat(bdd): creer la migration des promotions
feat(bdd): ajouter promotion_id role et points aux utilisateurs
feat(bdd): creer les migrations publications reponses et signalements
feat(bdd): creer la migration des appels ia
feat(models): declarer les relations eloquent entre les modeles
feat(models): ajouter les scopes visibles delapromotion et questions
chore: activer preventLazyLoading en developpement
docs: journal de la phase 1
```

---

## 17. JOUR 2 — Phase 2 : factories et seeders

**Branche :** `feat/02-seeders` — Durée : une demi-journée

### Objectif
Pouvoir reconstruire **en une seule commande** une base de démonstration complète, cohérente, contenant **deux promotions distinctes**.

> *« Un correcteur qui ne peut pas se connecter à votre application en trente secondes ne pourra rien évaluer. »*

### 17.1 Factory contre seeder

- Une **factory** décrit **à quoi ressemble** un objet typique (un générateur d'objets factices mais réalistes).
- Un **seeder** décide **combien** en créer et **pour qui**.

Trois raisons d'avoir des seeders : **travailler** (sans données on ne voit rien), **démontrer** (l'app doit être présentable immédiatement après installation), **tester** (chaque test repart d'une base connue).

### 17.2 Les factories

```php
// database/factories/PromotionFactory.php
public function definition(): array
{
    return [
        'nom' => 'Développement Web ' . fake()->year(),
        'code_invitation' => strtoupper(fake()->unique()->bothify('??####')),
        'annee' => 2026,
        'ouverte' => true,
    ];
}
```

```php
// database/factories/PublicationFactory.php
public function definition(): array
{
    return [
        'promotion_id' => Promotion::factory(),
        'user_id' => User::factory(),
        'type' => 'post',
        'titre' => fake()->sentence(6),
        'contenu' => fake()->paragraphs(2, true),
        'statut' => 'publie',
        'created_at' => fake()->dateTimeBetween('-30 days'),
    ];
}

public function question(): static
{
    return $this->state(fn () => [
        'type' => 'question',
        'titre' => rtrim(fake()->sentence(8), '.') . ' ?',
    ]);
}

public function enModeration(): static
{
    return $this->state(fn () => ['statut' => 'en_moderation']);
}
```

```php
// database/factories/ReponseFactory.php
public function definition(): array
{
    return [
        'publication_id' => Publication::factory()->question(),
        'user_id' => User::factory(),
        'contenu' => fake()->paragraph(),
    ];
}
```

**Les états** (`question()`, `enModeration()`) permettent d'écrire `Publication::factory()->question()->create()` sans dupliquer la définition de base. C'est exactement l'usage attendu.

**`'user_id' => User::factory()`** signifie : *« si personne ne me fournit d'auteur, fabrique-en un »*. C'est cette déclaration qui rend possible `recycle()`.

### 17.3 Le seeder principal

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    $groupeA = Promotion::factory()->create([
        'nom' => 'Développement Web 2026 — Groupe A',
        'code_invitation' => 'DWA2026',
    ]);

    $groupeB = Promotion::factory()->create([
        'nom' => 'Développement Web 2026 — Groupe B',
        'code_invitation' => 'DWB2026',
    ]);

    foreach ([$groupeA, $groupeB] as $promotion) {
        $membres = User::factory()->count(8)
            ->create(['promotion_id' => $promotion->id]);

        Publication::factory()->count(15)
            ->recycle($membres)
            ->create(['promotion_id' => $promotion->id]);

        Publication::factory()->count(6)->question()
            ->recycle($membres)
            ->create(['promotion_id' => $promotion->id])
            ->each(function (Publication $question) use ($membres) {
                Reponse::factory()->count(rand(0, 3))
                    ->recycle($membres)
                    ->create(['publication_id' => $question->id]);
            });
    }

    $this->comptesDeDemonstration($groupeA, $groupeB);
}
```

**`recycle($membres)`** dit à la factory de **réutiliser** les membres déjà créés au lieu d'en fabriquer un nouveau pour chaque publication. Sans elle : **cent vingt utilisateurs fantômes**.

### 17.4 Les quatre comptes de démonstration — OBLIGATOIRES

Avec **exactement** ces adresses et le mot de passe `password` :

| Nom | E-mail | Rôle | Promotion |
|---|---|---|---|
| Awa Diop | `awa@cohorte.test` | apprenant | **Groupe A** |
| Moussa Ba | `moussa@cohorte.test` | **délégué** | Groupe A |
| Fatou Sow | `fatou@cohorte.test` | apprenant | **Groupe B** |
| Formateur | `formateur@cohorte.test` | **enseignant** | *aucune* (`null`) |

```php
User::factory()->create([
    'name' => 'Awa Diop',
    'email' => 'awa@cohorte.test',
    'password' => Hash::make('password'),
    'promotion_id' => $a->id,
    'role' => 'apprenant',
]);
// … idem pour Moussa (delegue, A), Fatou (apprenant, B), Formateur (enseignant, null)
```

> **Pourquoi Awa et Fatou dans des groupes différents ?** C'est **exactement** comme ça que le correcteur testera ton cloisonnement : il se connecte avec Awa, relève l'ID d'une publication, se connecte avec Fatou, tape l'URL. **Ces comptes doivent figurer dans le README.**

### 17.5 Utiliser les seeders

```bash
php artisan migrate:fresh --seed              # tout reconstruire ET remplir  ← ton réflexe quotidien
php artisan db:seed                           # rejouer les seeders seulement
php artisan db:seed --class=PublicationSeeder # un seeder précis
```

### 17.6 Commits attendus
```
feat(factories): definir les factories promotion publication et reponse
feat(seeders): generer deux promotions cloisonnees avec leurs membres
feat(seeders): ajouter les quatre comptes de demonstration
docs: documenter les comptes de demonstration dans le README
docs: journal de la phase 2
```
Puis : `merge --no-ff` + `git tag phase-02` + `push --tags`.

---

## 18. JOUR 3 — Phase 3 : l'authentification avec Fortify

**Branche :** `feat/03-authentification-fortify` — Durée : un jour et demi (jours 3 et 4)

### 18.1 Pourquoi Fortify, et pas autre chose — *question de soutenance*

| Solution | Fournit | Ne fournit pas | Verdict |
|---|---|---|---|
| `Auth::attempt()` à la main | rien | limitation, régénération de session, jetons de reset | **trop risqué** : on oublie toujours une protection |
| **Breeze** | routes + contrôleurs **+ vues** | rien | **trop généreux** : les vues sont fournies, tu n'apprends rien |
| **Jetstream** | Breeze + équipes + 2FA | impose Livewire ou Inertia | **hors périmètre** |
| **Fortify** | routes + contrôleurs + actions, **sans aucune vue** | les vues, que **tu** écris | ✅ **notre choix** |
| Socialite | connexion Google/GitHub | tout le reste | hors périmètre |

**Le raisonnement à savoir redire :** l'authentification contient des mécanismes de sécurité que personne ne devrait réécrire — hachage du mot de passe, **régénération de l'identifiant de session après connexion** (contre la fixation de session), limitation des tentatives, jetons de réinitialisation à usage unique et à durée limitée. Fortify les offre, éprouvés et maintenus. En revanche, tout ce qui est **pédagogiquement intéressant** reste à ta charge : écrire les formulaires, gérer le CSRF, afficher les erreurs, brancher la logique métier. *Breeze t'aurait donné les deux, et tu aurais appris la moitié.*

> **Le mot-clé : Fortify est *frontend agnostic*.** Il ne fait aucune hypothèse sur ton interface. Il expose des routes qui attendent des POST et renvoient des redirections ; à toi de lui dire **quelle vue** afficher. C'est le rôle du `FortifyServiceProvider`.

### 18.2 Installation

```bash
composer require laravel/fortify
php artisan fortify:install
php artisan migrate
```

`fortify:install` publie **trois choses** :
- `config/fortify.php` — la configuration ;
- `app/Providers/FortifyServiceProvider.php` — enregistré automatiquement dans `bootstrap/providers.php` ;
- `app/Actions/Fortify/` — **les classes qui exécutent réellement** l'inscription et le changement de mot de passe.

```bash
php artisan route:list --name=login       # vérifier ce que Fortify a ajouté
php artisan route:list --name=register
```

### 18.3 Choisir les fonctionnalités

```php
// config/fortify.php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    // Features::emailVerification(),        // hors périmètre
    // Features::twoFactorAuthentication(),  // hors périmètre
],

'home' => '/publications',
'prefix' => '',
'domain' => null,
'username' => 'email',
```

> **Chaque ligne activée crée des routes.** Les laisser toutes ouvertes sans écrire les vues correspondantes **provoquera des erreurs**.

### 18.4 Indiquer les vues à Fortify

```php
// app/Providers/FortifyServiceProvider.php
public function boot(): void
{
    Fortify::createUsersUsing(CreateNewUser::class);

    Fortify::loginView(fn () => view('auth.login'));
    Fortify::registerView(fn () => view('auth.register'));
    Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
    Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', [
        'request' => $request,
    ]));

    // Limitation des tentatives : 5 par minute
    RateLimiter::for('login', function (Request $request) {
        $cle = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        return Limit::perMinute(5)->by($cle);
    });
}
```

**Pourquoi la clé combine e-mail ET adresse IP ?** Pour qu'un attaquant ne puisse pas **bloquer le compte d'un tiers** en épuisant volontairement son quota depuis sa propre machine. Sans limitation du tout, un attaquant essaie des milliers de mots de passe à la seconde.

### 18.5 Les vues — tu les écris toi-même

```blade
{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')
@section('titre', 'Connexion')

@section('contenu')
    <h1>Se connecter à Cohorte</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">Adresse e-mail</label>
        <input id="email" type="email" name="email"
               value="{{ old('email') }}" required autofocus>

        <label for="password">Mot de passe</label>
        <input id="password" type="password" name="password" required>

        <label><input type="checkbox" name="remember"> Se souvenir de moi</label>

        <button type="submit">Connexion</button>
    </form>

    <p>
        <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
        — <a href="{{ route('register') }}">Créer un compte</a>
    </p>
@endsection
```

**Trois éléments obligatoires dans chaque formulaire**, leur absence est sanctionnée :

| Élément | Rôle |
|---|---|
| `@csrf` | Insère le jeton anti-falsification. Sans lui : **419 Page Expired** |
| `old('email')` | Réaffiche la valeur saisie quand la validation échoue — sinon l'utilisateur retape tout |
| `for` / `id` | Relient l'étiquette au champ → utilisable au clavier et par un lecteur d'écran |

### 18.6 Ce que tu dois tester à la main

```
✓ inscription avec un code valide
✓ inscription avec un code inexistant
✓ inscription avec une adresse déjà utilisée
✓ connexion réussie
✓ six tentatives ratées d'affilée → la sixième doit être bloquée
✓ accès à une route protégée sans être connecté → redirection vers /login
```

### 18.7 Commits attendus
```
chore: installer laravel fortify
feat(auth): activer les fonctionnalites fortify utiles au projet
feat(auth): declarer les vues login register et reset dans le provider
feat(auth): ecrire les vues blade d authentification
feat(auth): limiter les tentatives de connexion a cinq par minute
```

---

## 19. JOUR 4 — fin Phase 3 + Phase 4 : rejoindre une promotion

### 19.1 Fin de la phase 3 — le code d'invitation

**C'est le point le plus intéressant de la phase.** `app/Actions/Fortify/CreateNewUser.php` est la classe que Fortify appelle pour créer un compte. C'est donc **l'endroit exact** où l'on vérifie le code d'invitation.

```php
// app/Actions/Fortify/CreateNewUser.php
public function create(array $input): User
{
    Validator::make($input, [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
        'password' => $this->passwordRules(),
        'code_invitation' => ['required', 'string', 'max:12'],
    ], [
        'code_invitation.required' => 'Le code d\'invitation de votre promotion est obligatoire.',
    ])->validate();

    $promotion = Promotion::where('code_invitation', $input['code_invitation'])->first();

    if (! $promotion) {
        throw ValidationException::withMessages([
            'code_invitation' => 'Ce code d\'invitation n\'existe pas.',
        ]);
    }

    if (! $promotion->ouverte) {
        throw ValidationException::withMessages([
            'code_invitation' => 'Les inscriptions à cette promotion sont closes.',
        ]);
    }

    return User::create([
        'name' => $input['name'],
        'email' => $input['email'],
        'password' => Hash::make($input['password']),
        'promotion_id' => $promotion->id,
        'role' => 'apprenant',
    ]);
}
```

> **Pourquoi `ValidationException::withMessages()` et pas une redirection avec un message flash ?** Parce que l'erreur est ainsi **rattachée au champ** `code_invitation` et remonte dans `$errors`, exactement comme les erreurs de validation classiques. Ta vue peut donc l'afficher **sous le bon champ** avec `@error('code_invitation')`, sans code supplémentaire. C'est le comportement qu'un utilisateur attend.

```blade
<label for="code_invitation">Code d'invitation de votre promotion</label>
<input id="code_invitation" type="text" name="code_invitation"
       value="{{ old('code_invitation') }}" required placeholder="ex. DWA2026">

@error('code_invitation')
    <p class="erreur">{{ $message }}</p>
@enderror
```

**Protéger les routes :**
```php
// routes/web.php
Route::get('/', fn () => view('accueil'))->name('accueil');

Route::middleware('auth')->group(function () {
    // toutes tes routes métier viennent ici
});
```

**Commits :**
```
feat(auth): valider le code d invitation a l inscription
docs: journal de la phase 3
```
→ `merge --no-ff` + `tag phase-03`.

### 19.2 Phase 4 — l'adhésion

**Branche :** `feat/04-adhesion-promotion` — Durée : une demi-journée

**Le problème à résoudre.** L'inscription rattache automatiquement le nouveau membre à une promotion. Mais **deux comptes y échappent** : l'enseignant (qui n'appartient à aucune promotion) et un membre dont la promotion aurait été supprimée. L'application ne doit **pas planter**.

**La règle :** un utilisateur connecté **sans** promotion est redirigé vers une page lui demandant un code, et ne peut accéder à **aucune** autre page. **Sauf** s'il est enseignant, auquel cas il va vers une page listant toutes les promotions.

```bash
php artisan make:middleware ExigePromotion
```

```php
// app/Http/Middleware/ExigePromotion.php
public function handle(Request $request, Closure $next): Response
{
    $utilisateur = $request->user();

    if (! $utilisateur) {
        return $next($request);
    }

    if ($utilisateur->estEnseignant()) {
        return redirect()->route('enseignant.promotions.index');
    }

    if (! $utilisateur->promotion_id) {
        return redirect()
            ->route('promotion.rejoindre')
            ->with('erreur', 'Saisissez le code d\'invitation de votre promotion pour continuer.');
    }

    return $next($request);
}
```

> **La notion clé : l'invariante.** Ce middleware garantit **une chose précise** : toute route qui le traverse est **certaine** de recevoir un utilisateur dont `promotion_id` n'est pas `null`. Sans cette garantie, `->deLaPromotion($request->user()->promotion_id)` recevrait `null` et lèverait une `TypeError`, puisque le scope attend un `int`. **On vérifie une fois, en amont, ce que tout le code en aval tient ensuite pour acquis.**

**Déclarer le middleware.** Depuis Laravel 11, **`Kernel.php` n'existe plus** — tout se passe dans `bootstrap/app.php` :

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'promotion' => \App\Http\Middleware\ExigePromotion::class,
    ]);
})
```

```php
Route::middleware(['auth', 'promotion'])->group(function () {
    // routes réservées aux membres rattachés à une promotion
});
```

**Le contrôleur d'adhésion :**
```bash
php artisan make:controller Promotion/AdhesionController
```

```php
public function store(Request $request): RedirectResponse
{
    $donnees = $request->validate([
        'code_invitation' => ['required', 'string', 'max:12'],
    ]);

    $promotion = Promotion::where('code_invitation', $donnees['code_invitation'])
        ->where('ouverte', true)
        ->first();

    if (! $promotion) {
        return back()->withInput()
            ->withErrors(['code_invitation' => 'Code inconnu ou promotion fermée.']);
    }

    $request->user()->update(['promotion_id' => $promotion->id]);

    return redirect()->route('publications.index')
        ->with('succes', 'Bienvenue dans la promotion ' . $promotion->nom . '.');
}
```

Les routes d'adhésion sont **en dehors** du groupe `promotion` — c'est précisément la page où l'on envoie ceux qui n'en ont pas :

```php
Route::middleware('auth')->group(function () {
    Route::get('/rejoindre', [AdhesionController::class, 'create'])->name('promotion.rejoindre');
    Route::post('/rejoindre', [AdhesionController::class, 'store'])->name('promotion.adherer');
});
```

**Commits attendus :**
```
feat(promotion): creer le middleware exigepromotion
feat(promotion): permettre de rejoindre une promotion par code
feat(profil): afficher la page de profil du membre
docs: journal de la phase 4
```
→ `merge --no-ff` + `tag phase-04`.

---

## 20. JOUR 5 — Phase 5 : le fil de promotion

**Branche :** `feat/05-fil-promotion` — Durée : un jour et demi (jours 5 et 6)

> **C'est la phase la plus lourdement notée du projet.**

### 20.1 Le contrôleur de ressource

```bash
php artisan make:controller Feed/PublicationController --resource --model=Publication
```

`--model` est important : il **type automatiquement** les paramètres des méthodes, ce qui active la **liaison de modèle de route**.

| Méthode | Verbe | URL | Nom de route | Rôle |
|---|---|---|---|---|
| `index` | GET | `/publications` | `publications.index` | Lister |
| `create` | GET | `/publications/create` | `publications.create` | Formulaire de création |
| `store` | POST | `/publications` | `publications.store` | Enregistrer |
| `show` | GET | `/publications/{publication}` | `publications.show` | Afficher une ressource |
| `edit` | GET | `/publications/{publication}/edit` | `publications.edit` | Formulaire de modification |
| `update` | PUT/PATCH | `/publications/{publication}` | `publications.update` | Enregistrer la modification |
| `destroy` | DELETE | `/publications/{publication}` | `publications.destroy` | Supprimer |

```php
Route::resource('publications', PublicationController::class);

// ou en restreignant :
Route::resource('publications', PublicationController::class)
     ->only(['index', 'create', 'store', 'show', 'destroy']);
```

> **Pourquoi c'est imposé :** les noms générés (`publications.show`) sont utilisables partout avec `route('publications.show', $publication)`. Tu n'écris **plus jamais** d'URL en dur, donc tu peux changer l'adresse d'une page sans casser vingt liens.

```bash
php artisan route:list --name=publications     # toujours vérifier
```

**Un navigateur ne sait envoyer que GET et POST.** Pour atteindre une route en DELETE ou PUT :

```blade
<form method="POST" action="{{ route('publications.destroy', $publication) }}">
    @csrf
    @method('DELETE')
    <button type="submit">Supprimer</button>
</form>
```

### 20.2 La liaison de modèle de route

```php
// ❌ Sans liaison : verbeux
public function show($id)
{
    $publication = Publication::findOrFail($id);
    return view('feed.show', compact('publication'));
}

// ✅ Avec liaison : Laravel s'en charge, et renvoie 404 tout seul
public function show(Publication $publication)
{
    return view('feed.show', compact('publication'));
}
```

> ⚠️ **LA LIAISON DE MODÈLE NE VÉRIFIE AUCUN DROIT.** Elle trouve la publication n° 42, un point c'est tout — **que tu aies le droit de la voir ou non**. C'est exactement là que se produit la fuite entre promotions. La liaison répond à *« cette publication existe-t-elle ? »* ; il te reste à répondre à *« ai-je le droit de la voir ? »*.

### 20.3 La policy, gardienne du cloisonnement

```bash
php artisan make:policy PublicationPolicy --model=Publication
```

```php
// app/Policies/PublicationPolicy.php
public function viewAny(User $user): bool
{
    return $user->promotion_id !== null || $user->estEnseignant();
}

public function view(User $user, Publication $publication): bool
{
    if ($user->estEnseignant()) {
        return true;
    }

    // Une publication masquée reste visible pour SON auteur
    if ($publication->statut !== 'publie' && $publication->user_id !== $user->id) {
        return $user->estDelegue() && $user->promotion_id === $publication->promotion_id;
    }

    return $user->promotion_id === $publication->promotion_id;
}

public function create(User $user): bool
{
    return $user->promotion_id !== null && ! $user->estEnseignant();
}

public function delete(User $user, Publication $publication): bool
{
    return $user->id === $publication->user_id
        || ($user->estDelegue() && $user->promotion_id === $publication->promotion_id);
}

public function signaler(User $user, Publication $publication): bool
{
    return $user->promotion_id === $publication->promotion_id
        && $user->id !== $publication->user_id;
}
```

> Depuis Laravel 11, les policies sont **découvertes automatiquement** dès lors qu'elles portent le nom du modèle suivi de `Policy` et se trouvent dans `app/Policies`. **Aucun enregistrement manuel.**

### 20.4 Le contrôleur

```php
namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicationRequest;
use App\Models\Publication;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PublicationController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Publication::class, 'publication');
    }

    public function index(Request $request): View
    {
        $publications = Publication::query()
            ->posts()
            ->visibles()
            ->deLaPromotion($request->user()->promotion_id)
            ->with('auteur')
            ->withCount('signalements')
            ->orderByDesc('epingle_le')
            ->latest()
            ->paginate(15);

        return view('feed.index', compact('publications'));
    }

    public function store(StorePublicationRequest $request): RedirectResponse
    {
        $publication = Publication::create([
            ...$request->validated(),
            'type' => 'post',
            'user_id' => $request->user()->id,
            'promotion_id' => $request->user()->promotion_id,
            'statut' => 'publie',
        ]);

        return redirect()->route('publications.show', $publication)
            ->with('succes', 'Votre publication est en ligne.');
    }

    public function show(Publication $publication): View
    {
        $publication->load('auteur', 'reponses.auteur');

        return view('feed.show', compact('publication'));
    }
}
```

**Deux mécanismes se combinent — il faut savoir lequel fait quoi :**

| Mécanisme | Ce qu'il protège |
|---|---|
| `authorizeResource()` + policy | La **page de détail** (accès direct par URL) |
| Le scope `deLaPromotion()` | La **liste** |

> **Les deux sont nécessaires. Protéger l'un sans l'autre laisse la porte ouverte.**

`authorizeResource()` relie automatiquement chaque méthode du contrôleur à la policy : `index`→`viewAny`, `show`→`view`, `store`→`create`, `destroy`→`delete`.

> ⚠️ Le trait `AuthorizesRequests` **doit être importé explicitement**. Depuis Laravel 11, la classe `Controller` de base est **vide**. Son oubli produit `Call to undefined method authorizeResource()`.

### 20.5 La validation par FormRequest

```bash
php artisan make:request StorePublicationRequest
```

```php
public function authorize(): bool
{
    return $this->user()->promotion_id !== null;
}

public function rules(): array
{
    return [
        'titre' => ['nullable', 'string', 'max:150'],
        'contenu' => ['required', 'string', 'min:10', 'max:3000'],
    ];
}

public function messages(): array
{
    return [
        'contenu.required' => 'Votre publication ne peut pas être vide.',
        'contenu.min' => 'Votre publication doit faire au moins 10 caractères.',
    ];
}
```

**Pourquoi un FormRequest plutôt que valider dans le contrôleur ?** Il **sort la validation du contrôleur**, il est **réutilisable**, et il **documente en un coup d'œil** ce que la route accepte. Si la validation échoue, Laravel redirige automatiquement en arrière avec les erreurs et les anciennes valeurs — **tu n'écris pas une ligne pour ça**.

### 20.6 La vue

```blade
{{-- resources/views/feed/index.blade.php --}}
@extends('layouts.app')
@section('titre', 'Fil de la promotion')

@section('contenu')
    <div class="entete-fil">
        <h1>{{ auth()->user()->promotion->nom }}</h1>
        <a href="{{ route('publications.create') }}" class="bouton">Publier</a>
    </div>

    @forelse ($publications as $publication)
        <x-carte-publication :publication="$publication" />
    @empty
        <p class="vide">Aucune publication pour l'instant. Lancez la conversation.</p>
    @endforelse

    {{ $publications->links() }}
@endsection
```

`@forelse` remplace avantageusement un `@if` suivi d'un `@foreach` : il gère le cas de la liste vide dans le même bloc. `$publications->links()` affiche la pagination — **à condition** que le contrôleur ait utilisé `paginate()` et non `get()`.

---

## 21. JOUR 6 — Phase 5 : le test qui décide de ta note

### Le protocole exact

> **Le correcteur exécutera ce test. Exécute-le avant lui.**

```
1. Se connecter avec awa@cohorte.test        (Groupe A)
2. Relever l'ID d'une publication visible, par exemple 12
3. Se déconnecter
4. Se connecter avec fatou@cohorte.test      (Groupe B)
5. Saisir directement l'adresse /publications/12 dans le navigateur
6. Résultat attendu : ERREUR 403
```

> *« Tout autre résultat est une faute grave. »*

**Refais la même vérification sur :** la page de détail d'une question, la suppression, et **toute** autre route acceptant un identifiant en paramètre.

### Vérifier l'absence de N+1

Parcours le fil avec `preventLazyLoading` actif. S'il lève `LazyLoadingViolationException`, ajoute `->with('auteur')` à la requête.

### Commits attendus (phase 5 complète)
```
feat(feed): creer le controleur de ressource des publications
feat(feed): afficher le fil filtre par promotion avec pagination
feat(feed): ajouter la policy de cloisonnement des publications
feat(feed): valider la creation via un formrequest dedie
feat(feed): ajouter le composant blade carte-publication
fix(feed): bloquer l acces direct a une publication hors promotion
docs: journal de la phase 5
```
→ `merge --no-ff` + `tag phase-05`.

---

## 22. JOUR 7 — Phase 6 : l'entraide

**Branche :** `feat/06-entraide` — Durée : un jour

> Rien de conceptuellement nouveau : cette phase **consolide** ce que tu viens d'apprendre, avec une subtilité sur la réponse retenue.

### 22.1 Trois contrôleurs, une responsabilité chacun

```bash
php artisan make:controller Entraide/QuestionController --resource --model=Publication
php artisan make:controller Entraide/ReponseController --only=store,destroy
php artisan make:controller Entraide/ReponseRetenueController --only=store,destroy
```

**Pourquoi un contrôleur dédié pour la réponse retenue ?** Désigner une réponse comme retenue **n'est pas « modifier une question »** : c'est une action à part entière, avec ses propres droits — seul l'auteur de la question peut le faire. Plutôt qu'une méthode `marquerReponseRetenue()` dans le contrôleur des questions, on crée un contrôleur dédié à **cette ressource**. C'est le principe du **contrôleur de ressource singleton**, très courant en Laravel dès que le projet grossit.

```php
Route::resource('questions', QuestionController::class)
     ->only(['index', 'create', 'store', 'show']);

Route::post('questions/{question}/reponses', [ReponseController::class, 'store'])
     ->name('reponses.store');

Route::post('questions/{question}/reponse-retenue', [ReponseRetenueController::class, 'store'])
     ->name('reponse-retenue.store');
```

### 22.2 Le contrôleur de la réponse retenue

```php
public function store(Request $request, Publication $question): RedirectResponse
{
    $this->authorize('designerReponse', $question);

    $donnees = $request->validate([
        'reponse_id' => ['required', 'integer', 'exists:reponses,id'],
    ]);

    $reponse = Reponse::findOrFail($donnees['reponse_id']);

    // Une réponse ne peut être retenue que si elle appartient bien à CETTE question
    abort_unless($reponse->publication_id === $question->id, 403);

    $question->update(['reponse_retenue_id' => $reponse->id]);

    $reponse->auteur->increment('points', 10);

    return back()->with('succes', 'Réponse retenue. Merci pour votre contribution.');
}
```

```php
// dans PublicationPolicy
public function designerReponse(User $user, Publication $publication): bool
{
    return $user->id === $publication->user_id
        && $publication->type === 'question';
}
```

> ⚠️ **`abort_unless` est indispensable.** La règle `exists:reponses,id` garantit que la réponse **existe**, pas qu'elle **appartient à cette question**. Sans cette ligne, n'importe qui pourrait désigner comme retenue une réponse écrite sur une question **d'une autre promotion**, en modifiant simplement la valeur du champ caché dans le formulaire.
>
> Le guide ajoute : *« C'est exactement le genre de faille que génère une IA à qui l'on demande "écris-moi le contrôleur". »*

### 22.3 Commits attendus
```
feat(entraide): creer le controleur de ressource des questions
feat(entraide): permettre de repondre a une question
feat(entraide): designer une reponse retenue et crediter son auteur
fix(entraide): verifier que la reponse retenue appartient a la question
docs: journal de la phase 6
```
→ `merge --no-ff` + `tag phase-06`.

---

## 23. JOUR 8 — Phase 7 : le client OpenRouter

**Branche :** `feat/07-moderation-ia` — Durée : un jour et demi (jours 8 et 9)
**⚠️ C'est aujourd'hui que tu reçois la demande de changement (à traiter au jour 10).**

> *« C'est la première fois que votre application dépend d'un service extérieur qui peut être lent, indisponible, ou renvoyer n'importe quoi. C'est tout l'intérêt de la phase. »*

### 23.1 La clé OpenRouter

Créer un compte sur **openrouter.ai**, puis une clé d'API. Aucune carte bancaire tant que tu utilises des **modèles gratuits** — ceux dont l'identifiant se termine par `:free`.

**La contrainte à connaître :** l'offre gratuite est limitée à environ **200 requêtes par jour** et **20 par minute**. Ce n'est pas un détail de configuration : **c'est la justification directe de la règle métier du quota** (phase 9). Une application qui appellerait le modèle sans compter serait hors service au bout d'une heure.

> ⚠️ **Le catalogue des modèles gratuits change souvent.** Ne code **jamais** un identifiant de modèle en dur : mets-le dans `.env`. Si ton application cesse de fonctionner, c'est la **première** chose à vérifier.

```ini
# .env  (JAMAIS commité)
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxxxxxxxxxx
OPENROUTER_MODEL=identifiant-du-modele-gratuit:free
OPENROUTER_TIMEOUT=12
```

```ini
# .env.example  (commité, valeurs VIDES)
OPENROUTER_API_KEY=
OPENROUTER_MODEL=
OPENROUTER_TIMEOUT=12
```

```php
// config/services.php
'openrouter' => [
    'key' => env('OPENROUTER_API_KEY'),
    'url' => 'https://openrouter.ai/api/v1/chat/completions',
    'model' => env('OPENROUTER_MODEL'),
    'timeout' => (int) env('OPENROUTER_TIMEOUT', 12),
],
```

### 23.2 Le client — toute la communication extérieure dans une seule classe

> **Aucun contrôleur ne doit contenir d'appel `Http::post()`.** Le jour où tu changes de fournisseur, **un seul fichier bouge**.

```php
// app/Services/OpenRouterClient.php
public function discuter(array $messages): ?string
{
    $debut = microtime(true);

    try {
        $reponse = Http::withToken(config('services.openrouter.key'))
            ->withHeaders([
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'Cohorte',
            ])
            ->timeout(config('services.openrouter.timeout'))
            ->retry(2, 400, throw: false)
            ->post(config('services.openrouter.url'), [
                'model' => config('services.openrouter.model'),
                'messages' => $messages,
                'temperature' => 0,
                'max_tokens' => 300,
            ]);
    } catch (\Throwable $e) {
        Log::warning('OpenRouter injoignable', ['message' => $e->getMessage()]);

        return null;
    }

    if ($reponse->failed()) {
        Log::warning('OpenRouter a repondu en erreur', [
            'statut' => $reponse->status(),
            'corps' => $reponse->body(),
        ]);

        return null;
    }

    Log::info('Appel OpenRouter', [
        'duree_ms' => (int) ((microtime(true) - $debut) * 1000),
    ]);

    return data_get($reponse->json(), 'choices.0.message.content');
}
```

**Trois précautions à comprendre plutôt qu'à recopier :**

| Précaution | Pourquoi |
|---|---|
| `timeout(12)` | Sans lui, la valeur par défaut de Guzzle laisse la requête pendre très longtemps et ton formulaire semble planté. 12 s est déjà généreux. |
| `retry(2, 400)` | Réessaie 2 fois à 400 ms d'intervalle. Utile contre un incident **passager**, inutile et coûteux si le service est franchement en panne — d'où le timeout court. |
| `try/catch` + retour `null` | Cette classe **ne lève jamais d'exception** vers l'appelant. Elle renvoie soit du texte, soit `null`. **Une classe technique ne prend pas de décision métier.** |

### 23.3 Commits du jour
```
chore: configurer l acces a openrouter dans services.php
feat(ia): creer le client openrouter avec timeout et journalisation
```

### 23.4 La demande de changement — à documenter dès aujourd'hui

Tu la reçois aujourd'hui, tu l'implémenteras au **jour 10**. Dès réception, crée dans `docs/DECISIONS.md` (ou un fichier dédié) :

```markdown
## Demande de changement
### La demande (recopiée telle quelle)
### Impact analysé : quels fichiers, quelles règles
### Le choix retenu, et l'alternative écartée
### L'implémentation
### Les tests de non-régression
```

---

## 24. JOUR 9 — Phase 7 : la modération automatique

### 24.1 Le verdict, exprimé par une énumération

```php
// app/Enums/VerdictModeration.php
enum VerdictModeration: string
{
    case Acceptable = 'acceptable';
    case Douteux = 'douteux';
    case Inacceptable = 'inacceptable';
    case Indisponible = 'indisponible';

    public function statutPublication(): string
    {
        return match ($this) {
            self::Acceptable => 'publie',
            self::Douteux => 'en_moderation',
            self::Inacceptable => 'refuse',
            self::Indisponible => config('cohorte.moderation_fail_open') ? 'publie' : 'en_moderation',
        };
    }
}
```

**Pourquoi une énumération plutôt qu'une chaîne libre ?** Elle t'**interdit** d'écrire `'acceptble'` par erreur, elle te **garantit** d'avoir traité tous les cas dans le `match`, et l'éditeur te propose les valeurs possibles.

Le cas `Indisponible` est le plus important : il représente **la panne d'OpenRouter**.

### 24.2 Le service de modération

```php
// app/Services/ServiceModeration.php
public function evaluer(string $contenu, User $auteur): VerdictModeration
{
    $texte = $this->client->discuter([
        ['role' => 'system', 'content' => $this->consigne()],
        ['role' => 'user', 'content' => "Publication à évaluer :\n\n" . $contenu],
    ]);

    $verdict = $this->interpreter($texte);

    AppelIa::create([
        'user_id' => $auteur->id,
        'contexte' => 'moderation',
        'modele' => config('services.openrouter.model'),
        'reussi' => $verdict !== VerdictModeration::Indisponible,
    ]);

    return $verdict;
}

private function interpreter(?string $texte): VerdictModeration
{
    if ($texte === null) {
        return VerdictModeration::Indisponible;
    }

    // Le modèle encadre parfois sa réponse par ```json ... ```
    if (preg_match('/\{.*\}/s', $texte, $trouve)) {
        $texte = $trouve[0];
    }

    $donnees = json_decode($texte, true);

    if (! is_array($donnees) || ! isset($donnees['verdict'])) {
        Log::warning('Reponse de moderation illisible', ['brut' => $texte]);

        return VerdictModeration::Indisponible;
    }

    return VerdictModeration::tryFrom($donnees['verdict']) ?? VerdictModeration::Indisponible;
}
```

La consigne système, en *heredoc* :

```
Tu es le modérateur d'un réseau social interne à une école de développement web.
Tu évalues si une publication est acceptable dans un cadre scolaire.

Classe la publication dans l'une de ces trois catégories :
- "acceptable" : contenu normal, même maladroit ou hors sujet
- "douteux" : moquerie ciblée, propos limites, publicité, contenu ambigu
- "inacceptable" : insulte, harcèlement, propos haineux, contenu sexuel

Réponds UNIQUEMENT par un objet JSON valide, sans texte avant ni après,
de la forme exacte :
{"verdict": "acceptable", "raison": "une phrase courte"}
```

> ⚠️ **`interpreter()` est le cœur de cette phase.** Un modèle de langage produit du **texte**, pas une structure de données. Même en lui demandant du JSON, il ajoutera parfois une phrase d'introduction ou encadrera sa réponse par des accents graves.
>
> **La règle générale, vraie pour toute ta carrière :** *ne fais jamais confiance à la sortie d'un modèle, parse-la défensivement, et prévois toujours le cas où elle est inexploitable.*

### 24.3 Brancher sur la publication

```php
public function store(
    StorePublicationRequest $request,
    ServiceModeration $moderation
): RedirectResponse {
    $verdict = $moderation->evaluer($request->validated()['contenu'], $request->user());

    $publication = Publication::create([
        ...$request->validated(),
        'type' => 'post',
        'user_id' => $request->user()->id,
        'promotion_id' => $request->user()->promotion_id,
        'statut' => $verdict->statutPublication(),
        'motif_moderation' => $verdict->value,
    ]);

    $message = match ($verdict) {
        VerdictModeration::Acceptable => 'Votre publication est en ligne.',
        VerdictModeration::Inacceptable => 'Votre publication a été refusée par la modération.',
        default => 'Votre publication est en attente de validation par un délégué.',
    };

    return redirect()->route('publications.index')
        ->with($verdict === VerdictModeration::Inacceptable ? 'erreur' : 'succes', $message);
}
```

Le service **s'injecte directement** dans la méthode ; Laravel s'occupe de le construire (*injection de dépendances*).

### 24.4 La décision à prendre et défendre — *décision n° 3 de `DECISIONS.md`*

**Que se passe-t-il si OpenRouter est en panne au moment où un membre publie ?**

| Position | Avantage | Inconvénient |
|---|---|---|
| **fail-open** (publier quand même) | l'application reste utilisable | un contenu problématique passe sans contrôle |
| **fail-closed** (file de modération) | rien ne passe sans contrôle | si la panne dure, le délégué a 200 publications à traiter et les membres croient l'app cassée |

> *« Il n'y a pas de bonne réponse universelle. Il y a une réponse adaptée à un contexte, et vous devez énoncer le vôtre. »*
> Une réponse du type *« j'ai mis ce que l'IA avait proposé »* **sera notée comme telle**.

### 24.5 Tester sans dépendre du réseau

```php
use Illuminate\Support\Facades\Http;

// Verdict forcé
Http::fake([
    'openrouter.ai/*' => Http::response([
        'choices' => [['message' => ['content' => '{"verdict":"inacceptable","raison":"insulte"}']]],
    ], 200),
]);

// Panne
Http::fake(['openrouter.ai/*' => Http::response(null, 503)]);
```

**Vérifie au minimum ces quatre situations :** verdict acceptable · verdict inacceptable · service en panne · **réponse illisible** (par ex. `Bonjour ! Voici mon analyse...`).

### 24.6 Commits attendus
```
feat(ia): definir l enumeration des verdicts de moderation
feat(ia): implementer le service de moderation et le parsing defensif
feat(feed): soumettre chaque publication a la moderation automatique
feat(ia): gerer l indisponibilite du service selon la configuration
docs: justifier le choix fail-open ou fail-closed dans DECISIONS.md
docs: journal de la phase 7
```
→ `merge --no-ff` + `tag phase-07`.

---

## 25. JOUR 10 — Phase 11 : la demande de changement

**Branche :** `feat/11-demande-changement` — 48 h pour l'intégrer

> *« Ce n'est pas une brimade. C'est la situation la plus fréquente de la vie professionnelle, et c'est le meilleur révélateur de la qualité réelle d'un code. »*

**Une application dont la règle de cloisonnement est centralisée dans un scope et une policy absorbera le changement en une heure. La même application, avec la même apparence, mais dont la règle est recopiée dans quinze contrôleurs, demandera une journée et cassera trois choses au passage.**

### Comment tu t'y es préparé (trois réflexes déjà appliqués)

```
1. Ne JAMAIS dupliquer une règle métier
   → si tu copies une condition d'un contrôleur à l'autre, arrête-toi
     et extrais-la dans un scope, une policy ou une méthode de modèle.

2. Ne JAMAIS mettre une valeur seuil en dur
   → elle va dans config/cohorte.php.

3. Ne JAMAIS mettre une règle de sécurité dans une vue Blade
   → un @if qui cache un bouton n'empêche personne d'appeler la route.
```

### Le protocole du jour

```bash
git switch main && git pull
git status
git log --oneline --decorate -10
git switch -c feat/11-demande-changement
```

Puis : implémenter → `php artisan migrate:fresh --seed` → **tester la non-régression** sur :

```
auth · inscription · promotion · feed · questions · réponses
réponse retenue · modération · panne IA · cloisonnement (403)
```

Mettre à jour `docs/JOURNAL.md` **et** `docs/DECISIONS.md`.

### Ce qui sera évalué sur cette branche

**Non pas la rapidité, mais la propreté :**
- Combien de fichiers as-tu dû modifier ?
- As-tu cassé une fonctionnalité existante ?
- Ton historique de commits raconte-t-il une **démarche réfléchie** ou une **suite de tâtonnements** ?
- Ton journal explique-t-il **ce que tu as découvert sur ton propre code** en le modifiant ?

→ `merge --no-ff` + `tag phase-11`.

---

## 26. JOUR 11 — Phase 8 : signalements et masquage

**Branche :** `feat/08-signalements` — Durée : un jour

### 26.1 Les trois interdits

```
1. On ne signale pas SA PROPRE publication          → policy signaler()
2. On ne signale pas DEUX FOIS la même publication  → contrainte unique + contrôle PHP
3. Une publication masquée reste VISIBLE POUR SON AUTEUR, avec un message
```

### 26.2 Le contrôleur

```php
public function store(Request $request, Publication $publication): RedirectResponse
{
    $this->authorize('signaler', $publication);

    $donnees = $request->validate([
        'motif' => ['required', 'string', 'in:insulte,hors_sujet,publicite,autre'],
    ]);

    $dejaSignale = $publication->signalements()
        ->where('user_id', $request->user()->id)
        ->exists();

    if ($dejaSignale) {
        return back()->with('erreur', 'Vous avez déjà signalé cette publication.');
    }

    Signalement::create([
        'publication_id' => $publication->id,
        'user_id' => $request->user()->id,
        'motif' => $donnees['motif'],
    ]);

    $this->masquerSiSeuilAtteint($publication);

    return back()->with('succes', 'Signalement enregistré. Merci.');
}

private function masquerSiSeuilAtteint(Publication $publication): void
{
    $nombre = $publication->signalements()->count();

    if ($nombre >= config('cohorte.seuil_signalement') && $publication->statut === 'publie') {
        $publication->update([
            'statut' => 'masque',
            'motif_moderation' => "Masquée automatiquement après {$nombre} signalements.",
        ]);
    }
}
```

La policy `signaler()` écrite en phase 5 refuse déjà l'auto-signalement et le hors-promotion. Le contrôleur n'a donc à traiter **que** le double signalement.

> **Question de soutenance : pourquoi vérifier le doublon en PHP alors que la base a déjà une contrainte unique ?**
> Parce que **les deux ne servent pas au même public**. La contrainte de base garantit **l'intégrité des données** quoi qu'il arrive — y compris si le code est contourné, ou si deux requêtes arrivent en même temps. La vérification PHP permet d'afficher **un message compréhensible** plutôt qu'une page d'erreur SQL.
> **La base protège, le code explique.**

### 26.3 La file de modération du délégué

```bash
php artisan make:controller Moderation/FileModerationController --only=index,update
```

```php
public function index(Request $request): View
{
    abort_unless($request->user()->estDelegue(), 403);

    $publications = Publication::query()
        ->deLaPromotion($request->user()->promotion_id)
        ->whereIn('statut', ['en_moderation', 'masque'])
        ->with('auteur')
        ->withCount('signalements')
        ->latest()
        ->paginate(20);

    return view('moderation.index', compact('publications'));
}

public function update(Request $request, Publication $publication): RedirectResponse
{
    abort_unless($request->user()->estDelegue(), 403);
    abort_unless($request->user()->promotion_id === $publication->promotion_id, 403);   // ← la 2e !

    $donnees = $request->validate(['decision' => ['required', 'in:valider,refuser']]);

    $publication->update([
        'statut' => $donnees['decision'] === 'valider' ? 'publie' : 'refuse',
    ]);

    return back()->with('succes', 'Décision enregistrée.');
}
```

> ⚠️ **Remarque la double vérification dans `update()`** : être délégué **ne suffit pas**, il faut être délégué **de la promotion concernée**. Le délégué du groupe A ne modère pas le groupe B. *« C'est le genre de contrôle qu'on oublie très facilement. »*

### 26.4 Commits attendus
```
feat(moderation): permettre de signaler une publication
feat(moderation): interdire le double signalement et l auto-signalement
feat(moderation): masquer automatiquement au seuil configure
feat(moderation): ajouter la file de moderation du delegue
fix(moderation): verifier que le delegue modere bien sa propre promotion
docs: journal de la phase 8
```
→ `merge --no-ff` + `tag phase-08`.

---

## 27. JOUR 12 — Phase 9 : quota d'IA et détection de doublon

**Branche :** `feat/09-quota-et-doublon` — Durée : un jour et demi

### 27.1 Calculer le quota

```php
// app/Models/User.php
public function appelsIaAujourdhui(): int
{
    return $this->appelsIa()
        ->where('created_at', '>=', now()->startOfDay())
        ->count();
}

public function quotaIaRestant(): int
{
    return max(0, config('cohorte.quota_ia_quotidien') - $this->appelsIaAujourdhui());
}

public function peutAppelerIa(): bool
{
    return $this->quotaIaRestant() > 0;
}
```

> ⚠️ **Attention au fuseau horaire.** `now()` utilise le fuseau de `config/app.php`, qui vaut **UTC** par défaut. Si ta promotion travaille à Dakar, « minuit » ne tombera pas au bon moment et le quota se réinitialisera en pleine journée.
> **Règle `'timezone' => 'Africa/Dakar'` dans `config/app.php` et note-le dans ton journal.**
> **N'utilise pas `whereDate()`**, qui délègue la comparaison à la base et n'utilise pas le même fuseau.

### 27.2 Le middleware de quota

```bash
php artisan make:middleware VerifieQuotaIa
```

```php
public function handle(Request $request, Closure $next): Response
{
    if (! $request->user()?->peutAppelerIa()) {
        return back()->withInput()
            ->with('erreur', 'Vous avez épuisé votre quota d\'assistance IA pour aujourd\'hui. Réessayez demain.');
    }

    return $next($request);
}
```

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'promotion' => \App\Http\Middleware\ExigePromotion::class,
        'quota.ia'  => \App\Http\Middleware\VerifieQuotaIa::class,
    ]);
})
```

```php
Route::post('questions/verifier-doublon', [DetectionDoublonController::class, 'store'])
     ->middleware('quota.ia')
     ->name('questions.doublon');
```

> **Pourquoi le quota ne bloque PAS la publication — *décision n° 5 de `DECISIONS.md`*.**
> La **modération** est une contrainte imposée par l'application, **pas un service rendu au membre** : la lui refuser parce qu'il a épuisé son quota reviendrait à **l'empêcher de s'exprimer**. La **détection de doublon**, elle, est une **assistance** : on peut la retirer sans dommage.
> **Compte les deux dans `appels_ia`, mais n'applique le middleware qu'à la seconde.**

**Affiche toujours le quota restant :**
```blade
@auth
    <span class="quota">
        Assistance IA : {{ auth()->user()->quotaIaRestant() }} / {{ config('cohorte.quota_ia_quotidien') }}
    </span>
@endauth
```

### 27.3 La détection de doublon

```php
public function chercherSimilaires(string $titre, User $auteur): array
{
    $existantes = Publication::query()
        ->questions()->visibles()
        ->deLaPromotion($auteur->promotion_id)
        ->latest()->limit(40)               // ← limite volontaire
        ->pluck('titre', 'id');

    if ($existantes->isEmpty()) {
        return [];
    }

    $catalogue = $existantes->map(fn ($t, $id) => "{$id}. {$t}")->implode("\n");

    $texte = $this->client->discuter([
        ['role' => 'system', 'content' => $this->consigne()],
        ['role' => 'user', 'content' => "Questions existantes :\n{$catalogue}\n\nNouvelle question :\n{$titre}"],
    ]);

    AppelIa::create([
        'user_id' => $auteur->id,
        'contexte' => 'doublon',
        'modele' => config('services.openrouter.model'),
        'reussi' => $texte !== null,
    ]);

    $ids = $this->extraireIdentifiants($texte);

    return Publication::whereIn('id', $ids)
        ->deLaPromotion($auteur->promotion_id)   // ← ON REFILTRE
        ->get()->all();
}
```

> ⚠️ **Remarque le `->deLaPromotion()` sur la requête finale.** Le modèle pourrait très bien **inventer** un identifiant, ou renvoyer celui d'une question qui ne figurait pas dans le catalogue.
> **Une sortie de modèle est une donnée utilisateur non fiable — on la traite comme telle.**

**Pourquoi la limite à 40 questions ?** Envoyer 300 titres coûterait cher en jetons, dépasserait la fenêtre de contexte des modèles gratuits, et **dégraderait la qualité** de la réponse.

### 27.4 L'enchaînement côté interface

```
Le membre remplit le formulaire → soumet
   → questions similaires détectées ?
        → OUI : on réaffiche le formulaire + la liste + bouton « Publier quand même »
                (champ caché doublon_verifie=1)
        → NON : publication normale
```

```php
public function store(StoreQuestionRequest $request, ServiceDetectionDoublon $detection): RedirectResponse|View
{
    $donnees = $request->validated();

    if (! $request->boolean('doublon_verifie') && $request->user()->peutAppelerIa()) {
        $similaires = $detection->chercherSimilaires($donnees['titre'], $request->user());

        if ($similaires !== []) {
            return view('entraide.create', ['similaires' => $similaires, 'donnees' => $donnees]);
        }
    }

    // ... création de la question comme en phase 6
}
```

### 27.5 Commits attendus
```
feat(ia): calculer le quota quotidien d appels a partir de la table appels_ia
feat(ia): creer le middleware de verification du quota
feat(ia): afficher le quota restant dans le gabarit
feat(entraide): detecter les questions similaires avant publication
feat(entraide): proposer de publier quand meme apres verification
fix(ia): refiltrer les identifiants renvoyes par le modele sur la promotion
docs: journal de la phase 9
```
→ `merge --no-ff` + `tag phase-09`.

---

## 28. JOUR 13 — Phase 10 : réputation et finitions

**Branche :** `feat/10-reputation` — Durée : un jour

### 28.1 Le barème

Il doit récompenser **l'utilité, pas le volume**. Proposition du guide :

```
+10  une réponse retenue
 +3  une réponse écrite
 +1  une question posée
 -5  une publication refusée par la modération
```

### 28.2 La décision à trancher — *décision n° 4 de `DECISIONS.md`*

**Le score est-il stocké dans `users.points`, ou recalculé à chaque affichage ?**

| Option | Avantage | Inconvénient |
|---|---|---|
| **Stocké** | rapide à lire | peut se **désynchroniser** si un incrément est oublié |
| **Recalculé** | toujours juste | une requête d'agrégation à chaque affichage, lent quand le volume grossit |

> **La réponse professionnelle courante — et la recommandation du guide :** stocker le compteur **tout en fournissant une commande de recalcul** qui remet les compteurs d'aplomb.

```bash
php artisan make:command RecalculerReputation
```

```php
protected $signature = 'cohorte:recalculer-reputation';
protected $description = 'Recalcule le score de contribution de tous les membres';

public function handle(): int
{
    User::query()->chunkById(100, function ($membres) {
        foreach ($membres as $membre) {
            $reponsesRetenues = Publication::query()
                ->whereIn('reponse_retenue_id', $membre->reponses()->select('id'))
                ->count();

            $score = $reponsesRetenues * 10
                + $membre->reponses()->count() * 3
                + $membre->publications()->questions()->count()
                - $membre->publications()->where('statut', 'refuse')->count() * 5;

            $membre->update(['points' => max(0, $score)]);
        }
    });

    $this->info('Réputation recalculée.');

    return self::SUCCESS;
}
```

**Deux notions à retenir :**
- **`chunkById(100)`** traite les utilisateurs par paquets de cent au lieu de tous les charger en mémoire. *Réflexe à prendre pour toute commande qui parcourt une table entière.*
- **La sous-requête** `whereIn('reponse_retenue_id', $membre->reponses()->select('id'))` se lit : *« compte les questions dont la réponse retenue figure parmi les réponses écrites par ce membre »*. Écrite comme une sous-requête, elle **ne ramène aucun identifiant en PHP** : c'est la base qui fait tout le travail, **en une seule requête**.

### 28.3 Le droit d'épingler

```php
// app/Policies/PublicationPolicy.php
public function epingler(User $user, Publication $publication): bool
{
    return $user->promotion_id === $publication->promotion_id
        && ($user->points >= config('cohorte.seuil_epinglage') || $user->estDelegue());
}
```

Le tri du fil (phase 5) place déjà les épinglées en tête grâce à `orderByDesc('epingle_le')` : sous **MySQL comme SQLite**, un `NULL` est la plus petite valeur, donc les non-épinglées passent derrière.

> ⚠️ **Ce comportement n'est pas universel.** PostgreSQL fait l'inverse en tri descendant. Pour un tri indépendant du moteur :
> ```php
> ->orderByRaw('epingle_le IS NULL')   // 0 (épinglées) avant 1
> ->orderByDesc('epingle_le')
> ->latest()
> ```

### 28.4 Les sept finitions attendues

```
□ Chaque page a un titre correct dans l'onglet du navigateur
□ Les listes vides affichent un message, pas une page blanche
□ Chaque action produit un message de confirmation ou d'erreur visible
□ Page d'erreur 403 personnalisée : resources/views/errors/403.blade.php
□ APP_DEBUG=false dans .env.example
□ README relu : un inconnu pourrait-il installer le projet en le suivant ?
□ php artisan migrate:fresh --seed une dernière fois, sans erreur
```

### 28.5 Commits attendus
```
feat(reputation): calculer le score de contribution des membres
feat(reputation): creer la commande de recalcul de la reputation
feat(feed): autoriser l epinglage au dela du seuil de reputation
feat(ui): ajouter la page d erreur 403 personnalisee
docs: completer le README avec l installation et les comptes de demo
docs: journal de la phase 10
```
→ `merge --no-ff` + `tag phase-10`.

---

## 29. JOUR 14 — finalisation

### 29.1 Reconstruction complète

```bash
php artisan optimize:clear
composer dump-autoload
php artisan migrate:fresh --seed
php artisan serve
```

### 29.2 Le test final, dans l'ordre

```
□ inscription avec code valide          □ réponse retenue (par l'auteur seulement)
□ inscription avec code invalide        □ signalement
□ connexion / déconnexion               □ double signalement refusé
□ mot de passe oublié                   □ auto-signalement refusé
□ 6 tentatives → blocage                □ masquage au seuil
□ redirection sans promotion            □ file de modération du délégué
□ fil cloisonné                         □ délégué A ne modère pas B
□ URL directe hors promotion → 403      □ modération IA (verdict OK / KO)
□ questions                             □ panne IA gérée
□ réponses                              □ quota épuisé → refus propre
                                        □ détection de doublon
                                        □ réputation + épinglage
```

### 29.3 Vérification Git

```bash
git status                                  # doit être propre
git log --oneline --graph --decorate --all  # 12 bosses de merge
git branch -a                               # toutes les branches, locales et distantes
git tag                                     # phase-00 … phase-11
git ls-files .env                           # ⚠️ NE DOIT RIEN RENVOYER
git log --date=short --pretty=format:'%ad %h %s' | head -50   # dates étalées ?
```

### 29.4 Les quatre livrables

| Fichier | Contenu exigé |
|---|---|
| **`README.md`** | Prérequis · suite exacte des commandes d'installation · comment renseigner les clés du `.env` · **les 4 comptes de démo avec rôle et promotion** · **quel modèle OpenRouter** tu as utilisé |
| **`docs/JOURNAL.md`** | Une entrée **par phase**, dans l'ordre, **10 à 20 lignes** chacune, 4 paragraphes. ⚠️ *Le journal n'est pas un résumé du guide : recopier les titres des phases ne rapporte aucun point.* |
| **`docs/DECISIONS.md`** | **Exactement cinq** décisions argumentées (liste ci-dessous), chacune avec **l'alternative écartée** et pourquoi |
| **`docs/IA.md`** | Par phase : ce que tu as demandé · ce que tu as retenu · **ce que tu as rejeté et pourquoi** |

**Les cinq décisions attendues — elles sont nommées par le guide :**
1. Le choix **fail-open ou fail-closed** en cas de panne d'OpenRouter *(phase 7)*
2. **Stocker ou recalculer** le score de réputation *(phase 10)*
3. La justification du **`promotion_id` dénormalisé** sur les publications *(phase 1)*
4. Le choix du **seuil de signalement** *(phase 8)*
5. Le choix de **ne pas soumettre la modération au quota d'IA** *(phase 9)*

### 29.5 L'objectif final

```
git clone
  → composer install
  → cp .env.example .env && php artisan key:generate
  → créer la base MySQL dans XAMPP
  → php artisan migrate:fresh --seed
  → php artisan serve
  → connexion avec awa@cohorte.test / password
  → démonstration
```

---
---

# PARTIE IV — LA SOUTENANCE

## 30. Déroulé des 25 minutes

### ① Démonstration — 7 minutes

Tu montres **l'application en fonctionnement**, en suivant le parcours d'un membre :

```
inscription avec un code → publication → question → signalement → file de modération
```

> **Ne commente pas ton code. Montre le produit.**

**Prépare-la comme un scénario minuté**, avec la base fraîchement seedée et les onglets déjà ouverts. Répète-la au moins deux fois la veille.

### ② Lecture de code — 10 minutes

```
Le jury ouvre `git log --graph`, CHOISIT UN COMMIT AU HASARD,
et te demande d'expliquer ce qu'il fait et pourquoi.
Puis il te demande UNE MODIFICATION EN DIRECT (~10 min de travail).
```

**Tu as le droit à la documentation Laravel. Pas à un assistant IA.**

> C'est la raison d'être des commits atomiques : un commit qui fait **une** chose s'explique en une phrase. Un commit fourre-tout de 40 fichiers est **inexplicable** — et ça se voit immédiatement.

**Comment t'y préparer :** relis ton propre `git log`, et pour **chaque** commit, demande-toi *« saurais-je expliquer ça en 30 secondes ? »*. Si non, relis le code **maintenant**, pas le jour J.

### ③ Échange sur tes décisions — 8 minutes

Voir §31.

---

## 31. Les questions annoncées, et leurs réponses

Le guide **liste explicitement** ces questions. Prépare-les par écrit.

### Q1. Quelle est la différence entre `git revert` et `git reset --hard`, et pourquoi imposons-nous le premier ?

> `revert` **ajoute** un commit qui applique l'inverse d'un commit fautif : l'historique s'allonge, rien n'est perdu, rien n'est réécrit. C'est la seule méthode acceptable sur une branche **déjà poussée**.
> `reset --hard` **déplace le pointeur de branche en arrière** et supprime des commits : utile en local sur du travail non partagé, mais destructeur dès que la branche est partagée, parce que la pousser exigerait un `--force` qui écraserait le travail des autres.

*Sache montrer ton `revert` dans le journal et dans `git log`.*

### Q2. Pourquoi Fortify plutôt que Breeze ?

> Fortify fournit les **mécanismes de sécurité** qu'il ne faut jamais réécrire (hachage, régénération de session contre la fixation, limitation des tentatives, jetons de reset à usage unique) **sans fournir les vues**. Breeze fournit **aussi** les vues : j'aurais eu un formulaire de connexion sans avoir écrit une ligne de Blade, donc sans rien apprendre sur le CSRF, `old()` ou l'affichage des erreurs. Fortify laisse à ma charge exactement la partie pédagogiquement intéressante. Jetstream imposerait Livewire ou Inertia, hors périmètre.

### Q3. Que se passe-t-il exactement si OpenRouter renvoie du texte au lieu de JSON ?

> Rien ne casse. Mon `OpenRouterClient` ne lève jamais d'exception : il renvoie une chaîne ou `null`. Ma méthode `interpreter()` extrait d'abord le premier bloc `{...}` avec une regex, parce que le modèle encadre parfois sa réponse par des accents graves. Puis `json_decode`. Si le résultat n'est pas un tableau, ou si la clé `verdict` manque, je journalise le texte brut et je renvoie `VerdictModeration::Indisponible`. Enfin `tryFrom()` renvoie `null` — donc `Indisponible` — si la valeur n'est pas une de mes quatre constantes. **À aucun moment je ne fais confiance à la sortie du modèle.**

### Q4. Montre-moi la ligne de code qui empêche un membre du groupe B de lire une publication du groupe A.

> Il y en a **deux**, et il faut les deux :
> ```php
> // 1. Le scope, qui protège LA LISTE
> public function scopeDeLaPromotion(Builder $query, int $promotionId): void
> { $query->where('promotion_id', $promotionId); }
>
> // 2. La policy, qui protège LA PAGE DE DÉTAIL et l'accès direct par URL
> public function view(User $user, Publication $publication): bool
> { return $user->promotion_id === $publication->promotion_id; }
> ```
> Le scope seul laisserait passer `/publications/12`, parce que la **liaison de modèle de route ne vérifie aucun droit** : elle trouve la publication n° 12 et la donne. La policy seule laisserait fuiter la liste. `authorizeResource()` dans le constructeur du contrôleur relie `show` à `view`.

### Q5. Pourquoi la contrainte unique en base alors que vous vérifiez déjà en PHP ?

> **Elles ne servent pas au même public.** La contrainte `unique(['publication_id','user_id'])` garantit **l'intégrité des données** quoi qu'il arrive : code contourné, requête forgée, ou deux requêtes simultanées qui passeraient toutes deux le test PHP avant que l'une ait écrit. La vérification PHP, elle, sert à afficher **un message compréhensible** à l'utilisateur plutôt qu'une page d'erreur SQL. **La base protège, le code explique.**

### Questions probables supplémentaires

| Question | Axe de réponse |
|---|---|
| Pourquoi une seule table pour les posts et les questions ? | Ils partagent auteur, promotion, contenu, statut, signalements. Deux tables → duplication de la modération et du signalement. Colonne `type` + scope `questions()`. |
| Pourquoi `promotion_id` sur `publications` alors qu'on pourrait le déduire de l'auteur ? | Dénormalisation assumée : la requête de cloisonnement devient une condition sur une colonne **indexée**, sans jointure ; et la publication reste rattachée à la promotion **où elle a été écrite** même si l'auteur change de promotion. |
| À quoi sert `preventLazyLoading` ? | Faire planter le développement dès qu'une relation non préchargée est utilisée, pour rendre le N+1 **visible immédiatement**. Désactivé en production. |
| Pourquoi `config/cohorte.php` et pas des valeurs en dur ? | Le correcteur peut changer un seuil sans toucher au code ; et `env()` renvoie `null` après `config:cache` s'il est appelé hors de `config/`. |
| Qu'est-ce qu'un middleware, concrètement ? | Une couche traversée **avant** le contrôleur. `ExigePromotion` établit une **invariante** : en aval, `promotion_id` n'est jamais `null`. |
| Pourquoi un contrôleur dédié pour la réponse retenue ? | Ce n'est pas « modifier une question » : c'est une action avec ses propres droits. Contrôleur de ressource singleton. |
| Pourquoi le quota ne bloque pas la publication ? | La modération est une **contrainte** imposée par l'app, pas un service au membre ; la lui refuser reviendrait à l'empêcher de s'exprimer. Le doublon est une **assistance**, retirable sans dommage. |

---

## 32. Checklist J-1

```
□ php artisan optimize:clear && php artisan migrate:fresh --seed  → 0 erreur
□ Les 4 comptes de démo fonctionnent (mot de passe : password)
□ Le test 403 refait une dernière fois, en direct
□ git ls-files .env  → vide
□ git log --oneline --graph --all --decorate  → 12 bosses visibles
□ git tag  → phase-00 … phase-11
□ Tout est POUSSÉ : git status dit "up to date with origin/main"
□ Le git revert est visible dans le log ET expliqué dans le journal
□ Les 5 décisions sont rédigées dans DECISIONS.md
□ J'ai relu MON PROPRE git log commit par commit
□ Les 5 questions annoncées : réponses préparées à l'oral
□ La démo de 7 min répétée deux fois, chrono en main
□ Base seedée fraîche + navigateur prêt AVANT d'entrer dans la salle
```

---
---

# PARTIE V — ANNEXES

## 33. Aide-mémoire Artisan

```bash
# ── GÉNÉRATION ────────────────────────────────────────────────────
php artisan make:model Publication -mf              # modèle + migration + factory
php artisan make:migration nom_de_la_migration --table=users
php artisan make:controller Feed/PublicationController --resource --model=Publication
php artisan make:controller Moderation/SignalementController --only=store
php artisan make:request StorePublicationRequest
php artisan make:policy PublicationPolicy --model=Publication
php artisan make:middleware VerifieQuotaIa
php artisan make:seeder PublicationSeeder
php artisan make:command RecalculerReputation

# ── BASE DE DONNÉES ───────────────────────────────────────────────
php artisan migrate                     # jouer les migrations en attente
php artisan migrate:status              # lesquelles sont passées ?
php artisan migrate:rollback            # annuler le DERNIER lot
php artisan migrate:fresh               # ⚠️ supprime TOUT et rejoue
php artisan migrate:fresh --seed        # ⚠️ + remplit
php artisan db:seed --class=DatabaseSeeder

# ── INSPECTION ────────────────────────────────────────────────────
php artisan route:list --name=publications
php artisan tinker                      # console interactive
php artisan about                       # état de l'installation

# ── QUAND QUELQUE CHOSE SE COMPORTE BIZARREMENT ───────────────────
php artisan optimize:clear
composer dump-autoload

# ── MySQL depuis Git Bash ─────────────────────────────────────────
netstat -an | grep 3306
winpty mysql -u root -p cohorte -e "SHOW TABLES;"
```

---

## 34. Erreurs fréquentes

| Message | Cause la plus fréquente | Correction |
|---|---|---|
| `Target class [X] does not exist` | Namespace non mis à jour après déplacement, ou `use` manquant dans les routes | Vérifier le namespace du contrôleur puis `composer dump-autoload` |
| `Call to undefined method authorizeResource()` | Le trait `AuthorizesRequests` n'est pas importé | `use Illuminate\Foundation\Auth\Access\AuthorizesRequests;` **et** `use AuthorizesRequests;` dans la classe |
| `419 Page Expired` | `@csrf` absent du formulaire | Ajouter `@csrf` juste après la balise `<form>` |
| `Attempt to read property "name" on null` | Une relation vaut `null` — souvent un membre sans promotion | Opérateur `?->` ou traiter le cas en amont (middleware) |
| `Attempted to lazy load [auteur]` | `preventLazyLoading` fait son travail | Ajouter `->with('auteur')` à la requête |
| `SQLSTATE… Integrity constraint violation` | Clé étrangère vers un enregistrement inexistant, ou violation d'unicité | Vérifier l'ordre des migrations et les données du seeder |
| `View [feed.index] not found` | Fichier pas dans le bon sous-dossier, ou point mal placé | Vérifier `resources/views/feed/index.blade.php` |
| `Undefined variable $publications` | La variable n'est pas passée depuis le contrôleur | Vérifier le `compact()` ou le tableau passé à `view()` |
| `MassAssignmentException` | `$fillable` absent | L'ajouter au modèle |
| Une valeur ne s'enregistre pas, **sans erreur** | `$fillable` **incomplet** — Laravel ignore l'attribut en silence | Relire le `$fillable` |
| Réponse OpenRouter vide ou `null` | Clé absente, modèle délisté, ou quota gratuit épuisé | Vérifier `.env`, tester la clé, changer de modèle dans le catalogue |
| `mysql: command not found` | XAMPP absent du `PATH` | `export PATH="$PATH:/c/xampp/mysql/bin"` dans `~/.bashrc` |
| `net start MySQL80` échoue | Il n'y a pas de service Windows de ce nom | Démarrer MySQL depuis **XAMPP Control Panel** |

---

## 35. Trame du journal

À recopier pour **chaque** phase dans `docs/JOURNAL.md`.

```markdown
## Phase 5 — Le fil de promotion
Branche : feat/05-fil-promotion
Dates : 18 au 19 août 2026

### Ce que j'ai fait
...

### Pourquoi je l'ai fait ainsi
...

### La difficulté rencontrée
...

### Comment je l'ai résolue
...
```

**10 à 20 lignes par entrée.** Le journal n'est **pas** un résumé du guide : recopier les titres des phases **ne rapporte aucun point**. Ce qui rapporte : la difficulté réelle et la façon dont tu t'en es sorti.

---

## 36. Checklist finale

### Installation
`□ PHP` `□ Composer` `□ Node/npm` `□ Git` `□ XAMPP` `□ MySQL` `□ Laravel 12` `□ .env` `□ .env.example à jour`

### Git — 20 pts
`□ 12 branches aux noms imposés` `□ commits atomiques` `□ messages conformes` `□ dates étalées` `□ merges --no-ff` `□ tags phase-00…phase-11 poussés` `□ un revert documenté` `□ .env absent de l'historique`

### Base de données — 12 pts
`□ promotions` `□ users enrichie` `□ publications` `□ reponses` `□ signalements` `□ appels_ia` `□ clés étrangères` `□ index` `□ contrainte unique` `□ relations nommées` `□ scopes` `□ preventLazyLoading`

### Seed — 5 pts
`□ 3 factories` `□ états question() et enModeration()` `□ recycle()` `□ 2 promotions` `□ 4 comptes conformes` `□ migrate:fresh --seed`

### Auth — 10 pts
`□ Fortify` `□ features réduites` `□ vues écrites` `□ @csrf` `□ old()` `□ for/id` `□ code d'invitation dans CreateNewUser` `□ limitation 5/min`

### Sécurité — 15 pts
`□ Policy` `□ FormRequest` `□ scopes` `□ authorizeResource` `□ test 403 par URL` `□ délégué limité à sa promotion` `□ abort_unless réponse retenue` `□ validation serveur systématique`

### Fonctionnalités
`□ feed` `□ publications` `□ questions` `□ réponses` `□ réponse retenue` `□ signalements` `□ masquage au seuil` `□ file de modération` `□ quota` `□ doublons` `□ réputation` `□ épinglage`

### IA — 10 pts
`□ client isolé dans app/Services` `□ clé dans .env uniquement` `□ modèle configurable` `□ timeout` `□ retry` `□ parsing défensif` `□ enum de verdicts` `□ panne gérée` `□ journalisation dans appels_ia` `□ quota`

### Documentation — 8 pts
`□ README installable` `□ 4 comptes documentés` `□ modèle OpenRouter mentionné` `□ JOURNAL une entrée par phase` `□ 5 décisions argumentées` `□ IA.md avec les rejets`

### Soutenance — 10 pts
`□ démo de 7 min répétée` `□ chaque commit explicable` `□ 5 questions préparées` `□ prêt à modifier le code en direct`

---

> **Règle de travail :** commande comprise → fichier compris → code compris → test réussi → commit → documentation.
>
> **Le mot de la fin du guide :** *« Ce projet vous demandera plus de rigueur que de virtuosité : c'est exactement ce que l'on attend d'un développeur en début de carrière. »*

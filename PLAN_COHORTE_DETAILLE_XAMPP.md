# PLAN DE RÉALISATION DÉTAILLÉ — PROJET COHORTE

> **Source principale :** `Projet_Cohorte_Guide_Evaluation.pdf` — Wommate Technology, version 1.0, août 2026.  
> **Adaptation locale :** Windows + Git Bash (MINGW64) + XAMPP + MySQL/MariaDB + PHP + Composer + Laravel 12 + Blade + Fortify + OpenRouter.  
> **But :** transformer le guide d’évaluation en manuel d’exécution : quoi faire, pourquoi, commande exacte, fichiers concernés, vérification, erreurs possibles et commits.

---

# TABLE DES MATIÈRES

1. Comment utiliser ce plan
2. Ce que le correcteur évalue
3. Environnement réel
4. Règles Git obligatoires
5. Architecture et conventions
6. Calendrier des 14 jours
7. Jour 1 — Phase 0 + début Phase 1
8. Jour 2 — fin Phase 1 + Phase 2
9. Jour 3 — Phase 3 Fortify
10. Jour 4 — fin Phase 3 + Phase 4
11. Jour 5 — Phase 5, fil de promotion
12. Jour 6 — Phase 5, sécurité
13. Jour 7 — Phase 6, entraide
14. Jour 8 — Phase 7, OpenRouter + changement
15. Jour 9 — Phase 7, modération
16. Jour 10 — demande de changement
17. Jour 11 — Phase 8, signalements
18. Jour 12 — Phase 9, quota et doublons
19. Jour 13 — Phase 10, réputation
20. Jour 14 — finalisation et soutenance
21. Diagnostic
22. Checklist finale

---

# 1. COMMENT UTILISER CE PLAN

Pour chaque étape :

```text
Lire l’objectif
→ comprendre pourquoi
→ exécuter la commande
→ modifier le fichier indiqué
→ tester
→ corriger
→ relire le diff Git
→ commit atomique
→ journal
```

Ne pas exécuter toutes les commandes d'une section sans vérifier les résultats intermédiaires.

## Commande destructive

```bash
php artisan migrate:fresh
```

Supprime toutes les tables puis rejoue les migrations. Elle est destinée au développement et détruit les données de la base concernée.

---

# 2. CE QUE LE CORRECTEUR ÉVALUE

Le guide indique que l'évaluation porte notamment sur Git, la compréhension du code, l'architecture imposée et la conformité fonctionnelle.

La grille finale comprend notamment :

| Critère | Points |
|---|---:|
| Modèle de données et relations | 12 |
| Architecture | 10 |
| Authentification | 10 |
| IA | 10 |
| Documentation | 8 |
| Seeders / démonstration | 5 |
| Cloisonnement / sécurité | 15 |
| Git | 20 |
| Soutenance | 10 |
| **Total** | **100** |

Pénalités importantes :

```text
.env dans l'historique                 → -10
Tous les commits poussés le même jour → -10
migrate:fresh --seed échoue            → -8
Tout sur main                          → -8
Contrôleurs/vues à plat                → -5
Clé OpenRouter en dur                  → -10
Journal absent/pauvre                  → -6
```

---

# 3. ENVIRONNEMENT RÉEL

```text
Windows
├── XAMPP
│   ├── Apache
│   └── MySQL/MariaDB
├── Git Bash / MINGW64
├── PHP
├── Composer
├── Node / npm
└── Laravel 12
```

Le projet cible Laravel 12, Blade, Fortify et OpenRouter.

## Projet

```bash
cd ~/Documents/perso/PROJECTS/cohorte
```

Vérifier :

```bash
pwd
git status
```

---

# 4. RÈGLES GIT OBLIGATOIRES

## 4.1 Branches

Exemples :

```text
feat/01-modele-donnees
feat/02-seeders
feat/03-fortify
feat/04-invitation
feat/05-fil-promotion
feat/06-entraide
feat/07-openrouter
feat/08-signalements
feat/09-quota-doublon
feat/10-reputation
```

## 4.2 Créer une branche

```bash
git switch main
git pull origin main
git switch -c feat/01-modele-donnees
```

## 4.3 Vérifier avant commit

```bash
git status
git diff
git diff --staged
```

Ne jamais inclure :

```text
.env
clé API
dd()
var_dump()
fichiers temporaires
```

## 4.4 Push

```bash
git push -u origin feat/01-modele-donnees
```

Puis :

```bash
git push
```

## 4.5 Merge (signifie fusionner les branches entre elles pour qu'elles n'existent plus)

```bash
git switch main
git pull origin main
git merge --no-ff feat/01-modele-donnees -m "merge: phase 1 modele de donnees"
git push origin main
```

---

# 5. ARCHITECTURE ET CONVENTIONS

Les contrôleurs et vues doivent être organisés en sous-dossiers.

Exemple :

```text
app/Http/Controllers/Feed/PostController.php
resources/views/feed/index.blade.php
```

Commandes utiles du guide :

```bash
php artisan make:controller Feed/PostController --resource --model=Publication
php artisan make:request StorePublicationRequest 
php artisan make:policy PublicationPolicy --model=Publication
php artisan make:middleware VerifieQuotaIa
php artisan make:seeder PublicationSeeder
php artisan make:command RecalculerReputation
```

---

# 6. CALENDRIER DES 14 JOURS

| Jour | Travail | Livraison |
|---:|---|---|
| 1 | Phase 0 + Phase 1 | Projet installé + migrations |
| 2 | Phase 1 + Phase 2 | Relations + factories + seeders |
| 3 | Phase 3 | Fortify |
| 4 | Phase 3 + Phase 4 | Inscription par code |
| 5 | Phase 5 | Fil cloisonné |
| 6 | Phase 5 | Policy + FormRequest + 403 |
| 7 | Phase 6 | Questions/réponses |
| 8 | Phase 7 | Client OpenRouter |
| 9 | Phase 7 | Modération + panne |
| 10 | Demande de changement | Changement intégré |
| 11 | Phase 8 | Signalements + masquage |
| 12 | Phase 9 | Quota + doublons |
| 13 | Phase 10 | Réputation |
| 14 | Finalisation | Documentation + soutenance |

---

# 7. JOUR 1 — PHASE 0 + DÉBUT PHASE 1

## 7.1 Vérifier PHP

```bash
php -v
```

**Rôle :** vérifier que PHP est disponible.

## 7.2 Vérifier Composer

```bash
composer --version
```

**Rôle :** Composer gère les dépendances PHP.

## 7.3 Vérifier Node/npm

```bash
node --version
npm --version
```

---

## 7.4 Démarrer MySQL avec XAMPP

Ouvrir :

```text
XAMPP Control Panel
→ MySQL
→ Start
```

### Ne pas utiliser

```bash
net start MySQL80
```

Cette commande cherche un service Windows nommé `MySQL80`, alors que notre MySQL est lancé par XAMPP.

---

## 7.5 Vérifier le port

```bash
netstat -an | grep 3306
```

Une ligne contenant `3306` doit apparaître si MySQL écoute sur ce port.

---

## 7.6 Vérifier `mysql.exe`

Notre chemin est :

```text
C:\xampp\mysql\bin\mysql.exe
```

Depuis Git Bash :

```bash
/c/xampp/mysql/bin/mysql.exe --version
```

Si :

```bash
mysql --version
```

donne :

```text
bash: mysql: command not found
```

ajouter XAMPP au PATH :

```bash
printf '\nexport PATH="$PATH:/c/xampp/mysql/bin"\n' >> ~/.bashrc
source ~/.bashrc
mysql --version
```

Le PATH indique à Bash dans quels dossiers rechercher les exécutables.

---

## 7.7 Connexion MySQL

Sans mot de passe :

```bash
winpty mysql -u root
```

Avec mot de passe :

```bash
winpty mysql -u root -p
```

## 7.8 Créer la base

Dans MySQL :

```sql
CREATE DATABASE cohorte
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Puis :

```sql
SHOW DATABASES;
EXIT;
```

---

## 7.9 Configurer `.env`

```env
APP_NAME=Cohorte
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cohorte
DB_USERNAME=root
DB_PASSWORD=
```

Mettre le vrai mot de passe si root en possède un.

---

## 7.10 Tester Laravel

```bash
php artisan about
php artisan migrate
php artisan migrate:status
php artisan serve
```

Navigateur :

```text
http://127.0.0.1:8000
```

---

# 7.11 PHASE 1 — MODÈLE DE DONNÉES

Le guide demande notamment :

```text
Promotion
User : promotion_id, role, points
Publication
Reponse
Signalement
AppelIa
relations Eloquent
scopes
preventLazyLoading
```

## Ordre logique

```text
users Laravel existant
        ↓
promotions
        ↓
users.promotion_id / role / points
        ↓
publications
        ↓
reponses
        ↓
signalements
        ↓
appels_ia
```

L'ordre est important pour les clés étrangères.

---

## 7.12 Créer Promotion

```bash
php artisan make:model Promotion -mf
```

Cette commande crée :

```text
app/Models/Promotion.php
database/migrations/..._create_promotions_table.php
database/factories/PromotionFactory.php
```

`-m` = migration ; `-f` = factory.

La migration doit prévoir notamment :

```text
id
nom
code_invitation
annee
timestamps
```

---

## 7.13 Modifier users

```bash
php artisan make:migration add_cohorte_fields_to_users_table --table=users
```

Ajouter :

```text
promotion_id
role
points
```

et la clé étrangère vers :

```text
promotions.id
```

**Pourquoi une migration séparée ?** Parce que `users` est fourni par Laravel ; nous conservons ainsi l'historique entre le schéma Laravel et les ajouts spécifiques à Cohorte.

---

## 7.14 Créer Publication

```bash
php artisan make:model Publication -mf
```

Elle doit pouvoir être reliée à :

```text
user
promotion
type
titre
contenu
statut
timestamps
```

---

## 7.15 Créer Reponse

```bash
php artisan make:model Reponse -mf
```

Elle doit être reliée à :

```text
user
publication/question
```

et permettre la réponse retenue selon le cahier des charges.

---

## 7.16 Créer Signalement

```bash
php artisan make:model Signalement -m
```

Pas de factory : c'est une table de journalisation d'actions réelles.

---

## 7.17 Créer AppelIa

```bash
php artisan make:model AppelIa -m
```

Pas de factory : elle journalise les appels IA réels.

---

# 7.18 RELATIONS ELOQUENT

## User

Prévoir :

```php
$user->promotion
$user->publications
```

## Promotion

Prévoir :

```php
$promotion->membres
$promotion->publications
```

## Publication

Prévoir :

```php
$publication->auteur
$publication->promotion
$publication->reponses
$publication->signalements
```

## Reponse

Prévoir :

```php
$reponse->auteur
$reponse->publication
```

## Signalement

Prévoir :

```text
publication
user
```

## AppelIa

Prévoir les relations nécessaires au suivi des appels.

---

# 7.19 SCOPES

Le guide demande notamment :

```text
visibles
deLaPromotion
questions
```

Exemple :

```php
Publication::visibles()
    ->deLaPromotion($user->promotion_id)
    ->with('auteur')
    ->latest()
    ->paginate(15);
```

**Pourquoi ?** Les règles métier sont centralisées dans le modèle au lieu d'être répétées dans tous les contrôleurs.

---

# 7.20 ACTIVER preventLazyLoading

Dans :

```text
app/Providers/AppServiceProvider.php
```

Ajouter :

```php
use Illuminate\Database\Eloquent\Model;
```

Puis :

```php
Model::preventLazyLoading(! app()->isProduction());
```

### Pourquoi ?

Sans `with()` :

```php
Publication::visibles()->latest()->paginate(15);
```

puis :

```php
$publication->auteur->name
```

peut produire un N+1.

Correct :

```php
Publication::visibles()
    ->with('auteur')
    ->latest()
    ->paginate(15);
```

---

# 7.21 TESTER PHASE 1

```bash
php artisan migrate:fresh
php artisan migrate:status
```

Puis :

```bash
php artisan tinker
```

Tester :

```php
        $p = App\Models\Promotion::create([
        'nom' => 'Test',
        'code_invitation' => 'TEST01',
        'annee' => 2026
        ]);

$u = App\Models\User::factory()->create([
    'promotion_id' => $p->id
]);

$p->membres()->count();
$u->promotion->nom;
```

Résultats attendus :

```text
1
"Test"
```

Quitter Tinker :

```php
exit
```

Puis :

```bash
winpty mysql -u root -p cohorte -e "SHOW TABLES;"
```

---

# 7.22 COMMITS PHASE 1

Les messages attendus par le guide sont :

```bash
git commit -m "feat(bdd): creer la migration des promotions"
git commit -m "feat(bdd): ajouter promotion_id role et points aux utilisateurs"
git commit -m "feat(bdd): creer les migrations publications reponses et signalements"
git commit -m "feat(bdd): creer la migration des appels ia"
git commit -m "feat(models): declarer les relations eloquent entre les modeles"
git commit -m "feat(models): ajouter les scopes visibles delapromotion et questions"
git commit -m "chore: activer preventLazyLoading en developpement"
git commit -m "docs: journal de la phase 1"
```

---

# 8. JOUR 2 — FIN PHASE 1 + PHASE 2

## Objectif

Obtenir :

```text
factories
seeders
2 promotions distinctes
4 comptes conformes
publications
questions
réponses
migrate:fresh --seed fonctionnel
```

## Branche

```bash
git switch main
git pull origin main
git switch -c feat/02-seeders
```

## Factories

`Promotion`, `Publication` et `Reponse` doivent disposer de factories.

`Signalement` et `AppelIa` n'en nécessitent pas.

Pour `PublicationFactory`, la logique attendue comprend notamment :

```php
return [
    'promotion_id' => Promotion::factory(),
    'user_id' => User::factory(),
    'type' => 'post',
    'titre' => fake()->sentence(6),
    'contenu' => fake()->paragraphs(2, true),
    'statut' => 'publie',
    'created_at' => fake()->dateTimeBetween('-30 days'),
];
```

Prévoir les états :

```php
question()
enModeration()
```

## Seeders

```bash
php artisan make:seeder PromotionSeeder
php artisan make:seeder PublicationSeeder
```

Organisation :

```text
promotions
→ utilisateurs
→ publications
→ réponses
```

## Test central

```bash
php artisan migrate:fresh --seed
```

Puis vérifier avec Tinker les promotions, utilisateurs et contenus.

---

# 9. JOUR 3 — PHASE 3 FORTIFY

## Branche

```bash
git switch main
git pull origin main
git switch -c feat/03-fortify
```

## Installer

```bash
composer require laravel/fortify
php artisan fortify:install
```

## Vues

```text
resources/views/auth/
├── login.blade.php
├── register.blade.php
├── forgot-password.blade.php
└── reset-password.blade.php
```

Dans les formulaires POST :

```blade
@csrf
```

## Tester

```text
inscription
connexion
déconnexion
mauvais mot de passe
mot de passe oublié
réinitialisation
limitation des tentatives
```

---

# 10. JOUR 4 — FIN PHASE 3 + PHASE 4

## Objectif

Inscription avec code d'invitation.

Flux :

```text
formulaire
→ code invitation
→ recherche Promotion
→ valide ?
→ création User avec promotion_id
```

Le code doit être validé côté serveur.

### Tests

```text
code valide → OK
code invalide → erreur
code inexistant → erreur
promotion_id correct → OK
```

Créer ensuite le middleware d'appartenance à une promotion et protéger les zones concernées.

---

# 11. JOUR 5 — PHASE 5, FIL DE PROMOTION

## Branche

```bash
git switch main
git pull origin main
git switch -c feat/05-fil-promotion
```

## Contrôleur

```bash
php artisan make:controller Feed/PostController --resource --model=Publication
```

## Request

```bash
php artisan make:request StorePublicationRequest
```

## Policy

```bash
php artisan make:policy PublicationPolicy --model=Publication
```

## Routes

```bash
php artisan route:list --name=publications
```

## Vue

Créer :

```text
resources/views/feed/index.blade.php
```

## Règle fondamentale

```text
Promotion A → voit A
Promotion A → ne voit pas B
```

Le contrôle doit être côté serveur.

---

# 12. JOUR 6 — PHASE 5, SÉCURITÉ

## Test d'accès direct

```text
Utilisateur A
→ publication A : OK
→ URL publication B : 403/refus
```

Tester également les URLs tapées manuellement.

## N+1

Utiliser :

```php
->with('auteur')
```

---

# 13. JOUR 7 — PHASE 6, ENTRAIDE

## Questions

Créer :

```text
liste
création
détail
```

## Réponses

Permettre :

```text
consultation
ajout
affichage
```

## Réponse retenue

Seul l'auteur de la question peut sélectionner une réponse.

Tester :

```text
auteur → autorisé
autre utilisateur → refus
```

---

# 14. JOUR 8 — PHASE 7, OPENROUTER + CHANGEMENT

## `.env`

```env
OPENROUTER_API_KEY=
OPENROUTER_MODEL=
OPENROUTER_TIMEOUT=12
```

La clé ne doit jamais être écrite en dur.

## Client

Créer :

```text
app/Services/OpenRouterClient.php
```

Responsabilités :

```text
HTTP
authentification
timeout
erreurs
JSON
parsing
```

## Robustesse

Prévoir :

```text
timeout
retry
4xx/5xx
JSON invalide
réponse incomplète
clé absente
modèle indisponible
```

## Demande de changement

Documenter :

```text
Demande
Impact
Choix
Implémentation
Tests
```

L'intégration est prévue au jour 10.

---

# 15. JOUR 9 — PHASE 7, MODÉRATION

Flux :

```text
Publication
→ OpenRouterClient
→ analyse
→ statut
```

Prévoir notamment :

```text
acceptable
douteux
inacceptable
indisponible
```

Tester :

```text
IA disponible
IA indisponible
timeout
HTTP 4xx
HTTP 5xx
JSON invalide
champ manquant
```

Ne jamais supposer qu'une réponse IA est valide sans validation.

---

# 16. JOUR 10 — DEMANDE DE CHANGEMENT

Avant modification :

```bash
git status
git branch
git log --oneline --decorate -10
```

Implémenter la demande du jour 8.

Puis :

```bash
php artisan migrate:fresh --seed
```

Régression :

```text
auth
inscription
promotion
feed
questions
réponses
modération
sécurité
```

Mettre à jour :

```text
docs/JOURNAL.md
docs/DECISIONS.md
```

---

# 17. JOUR 11 — PHASE 8, SIGNALEMENTS

Un signalement associe notamment :

```text
user
publication
motif
statut
timestamps
```

Flux :

```text
publication
→ signalements
→ seuil
→ masquage
```

Tester :

```text
signalement
doublon
seuil non atteint
seuil atteint
masquage
```

Une contrainte d'unicité en base doit être utilisée lorsque la règle métier impose l'unicité.

---

# 18. JOUR 12 — PHASE 9, QUOTA ET DOUBLONS

## Quota

Créer :

```bash
php artisan make:middleware VerifieQuotaIa
```

Flux :

```text
demande IA
→ quota disponible ?
→ oui : appel
→ non : refus propre
```

## Doublons

```text
question
→ recherche similarité
→ similaire ?
   → avertissement
   → publier quand même
ou
   → publication normale
```

Tester :

```text
quota disponible
quota épuisé
question similaire
question différente
confirmation
annulation
```

---

# 19. JOUR 13 — PHASE 10, RÉPUTATION

Utiliser exactement le barème défini dans le guide.

Centraliser la logique des points.

Commande de référence :

```bash
php artisan make:command RecalculerReputation
```

Tester le droit concret lié au niveau de réputation, notamment l'épinglage lorsque les conditions du projet sont remplies.

---

# 20. JOUR 14 — FINALISATION ET SOUTENANCE

## Reconstruction

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
```

## Test final

```text
auth
invitation
promotion
feed
403
questions
réponses
réponse retenue
signalements
masquage
modération
panne IA
quota
doublons
réputation
```

## Git

```bash
git status
git log --oneline --graph --decorate --all
git branch -a
git tag
```

## `.env`

```bash
git ls-files .env
```

Ne doit rien retourner.

## README

Doit expliquer :

```text
présentation
prérequis
installation
XAMPP
MySQL
.env
migrations
seed
lancement
OpenRouter
comptes de démonstration
tests
architecture
```

## Journal

Pour chaque phase :

```markdown
## Phase X — Nom

### Ce que j'ai fait

### Pourquoi je l'ai fait ainsi

### Difficulté rencontrée

### Comment je l'ai résolue
```

---

# 21. COMMANDES DE DIAGNOSTIC

## Laravel

```bash
php artisan about
php artisan route:list
php artisan migrate:status
php artisan tinker
php artisan optimize:clear
composer dump-autoload
```

## MySQL

```bash
mysql --version
netstat -an | grep 3306
```

Puis :

```bash
winpty mysql -u root -p cohorte -e "SHOW TABLES;"
```

## Git

```bash
git status
git branch
git branch -a
git log --oneline --graph --all
git diff
git diff --staged
```

---

# 22. ERREURS FRÉQUENTES

## `mysql: command not found`

```bash
/c/xampp/mysql/bin/mysql.exe --version
```

Puis :

```bash
printf '\nexport PATH="$PATH:/c/xampp/mysql/bin"\n' >> ~/.bashrc
source ~/.bashrc
```

## `net start MySQL80` invalide

Démarrer MySQL dans XAMPP.

## `The name is required`

Tu as lancé :

```bash
php artisan make:migration
```

sans nom.

Il faut :

```bash
php artisan make:migration nom_de_la_migration
```

ou :

```bash
php artisan make:model Promotion -mf
```

## `Integrity constraint violation`

Vérifier :

```text
ordre des migrations
ordre des seeders
clés étrangères
factory
promotion_id
user_id
```

## `Attempted to lazy load`

Ajouter :

```php
->with('auteur')
```

## `419 Page Expired`

Ajouter :

```blade
@csrf
```

## `View [feed.index] not found`

Vérifier :

```text
resources/views/feed/index.blade.php
```

et :

```php
view('feed.index')
```

## `Undefined variable $publications`

Vérifier que le contrôleur transmet bien `$publications`.

## OpenRouter vide

Vérifier :

```text
OPENROUTER_API_KEY
OPENROUTER_MODEL
quota
modèle
HTTP
JSON
```

---

# 23. CHECKLIST FINALE

## Installation

- [ ] PHP
- [ ] Composer
- [ ] Node/npm
- [ ] Git
- [ ] XAMPP
- [ ] MySQL
- [ ] Laravel
- [ ] `.env`

## Git

- [ ] branches
- [ ] commits atomiques
- [ ] messages conformes
- [ ] pushes réguliers
- [ ] merges `--no-ff`
- [ ] tags
- [ ] revert documenté
- [ ] `.env` absent de l'historique

## Base

- [ ] promotions
- [ ] users modifiés
- [ ] publications
- [ ] réponses
- [ ] signalements
- [ ] appels IA
- [ ] clés étrangères
- [ ] index
- [ ] contrainte unique
- [ ] relations
- [ ] scopes
- [ ] preventLazyLoading

## Seed

- [ ] factories
- [ ] seeders
- [ ] 2 promotions
- [ ] 4 comptes conformes
- [ ] publications
- [ ] questions
- [ ] réponses
- [ ] `migrate:fresh --seed`

## Auth

- [ ] Fortify
- [ ] inscription
- [ ] connexion
- [ ] déconnexion
- [ ] reset password
- [ ] limitation
- [ ] code invitation

## Sécurité

- [ ] Policy
- [ ] FormRequest
- [ ] scopes
- [ ] cloisonnement
- [ ] accès direct URL
- [ ] 403
- [ ] validation serveur

## Fonctionnalités

- [ ] feed
- [ ] publications
- [ ] questions
- [ ] réponses
- [ ] réponse retenue
- [ ] signalements
- [ ] masquage
- [ ] quota
- [ ] doublons
- [ ] réputation
- [ ] droit associé

## IA

- [ ] client isolé
- [ ] clé dans `.env`
- [ ] modèle configurable
- [ ] timeout
- [ ] parsing défensif
- [ ] erreurs
- [ ] panne
- [ ] journalisation
- [ ] quota

## Documentation

- [ ] README
- [ ] JOURNAL.md
- [ ] DECISIONS.md
- [ ] IA.md
- [ ] cinq décisions argumentées
- [ ] journal détaillé
- [ ] utilisation IA documentée

---

# 24. RÈGLE DE FIN DE CHAQUE JOURNÉE

```bash
git status
```

Tester la reconstruction lorsque pertinent :

```bash
php artisan migrate:fresh --seed
```

Relire :

```bash
git diff
git diff --staged
```

Committer :

```bash
git commit -m "..."
```

Pousser :

```bash
git push
```

Puis mettre à jour :

```text
docs/JOURNAL.md
```

Toujours terminer une journée avec :

```text
Ce qui fonctionne
Ce qui reste
Le prochain objectif
```

---

# 25. OBJECTIF FINAL

Le dépôt doit permettre :

```text
git clone
→ composer install
→ configuration .env
→ MySQL/XAMPP
→ php artisan migrate:fresh --seed
→ php artisan serve
→ connexion
→ démonstration
```

Mais le projet doit aussi démontrer :

```text
Git
+
architecture
+
base de données
+
sécurité
+
authentification
+
fonctionnalités
+
IA
+
seeders
+
documentation
+
compréhension
```

> **Règle de travail :** commande comprise → fichier compris → code compris → test réussi → commit → documentation.

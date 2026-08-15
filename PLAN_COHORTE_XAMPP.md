# PLAN COMPLET DE RÉALISATION — PROJET COHORTE

> **Base :** document officiel « Projet Cohorte — Guide de l’évaluation »  
> **Adaptation :** Windows + Git Bash + XAMPP/MySQL  
> **Durée :** 14 jours  
> **Stack :** Laravel 12 + Blade + Laravel Fortify + MySQL/MariaDB + OpenRouter  
> **Objectif :** suivre la structure, les exigences et les livrables du guide tout en adaptant les commandes et l’environnement local à XAMPP.

---

## 0. Principes du plan

Ce plan conserve la structure du guide officiel :

- Phase 0 — Installation
- Phase 1 — Modèle de données
- Phase 2 — Factories et seeders
- Phase 3 — Authentification
- Phase 4 — Adhésion à une promotion
- Phase 5 — Fil de promotion
- Phase 6 — Entraide
- Phase 7 — Modération IA
- Phase 8 — Signalements
- Phase 9 — Quota IA et détection de doublons
- Phase 10 — Réputation

Le planning s’étale sur **14 jours** avec une demande de changement au jour 8 et son intégration au jour 10.

> **Règle d’adaptation importante :** les commandes spécifiques à Laragon ou à un service Windows `MySQL80` ne sont pas utilisées ici. MySQL est démarré depuis **XAMPP Control Panel**.

---

# 1. ENVIRONNEMENT DE TRAVAIL

## 1.1 Environnement retenu

```text
Système        : Windows
Terminal       : Git Bash / MINGW64
Serveur local  : XAMPP
Web            : Apache XAMPP
Base           : MySQL/MariaDB XAMPP
Framework      : Laravel 12
Frontend       : Blade
Auth           : Laravel Fortify
IA             : OpenRouter
Versioning     : Git + GitHub
```

## 1.2 Chemin MySQL XAMPP

Le client MySQL trouvé dans notre environnement est :

```text
C:\xampp\mysql\bin\mysql.exe
```

Depuis Git Bash :

```text
/c/xampp/mysql/bin/mysql.exe
```

## 1.3 Démarrage de MySQL

Ne pas utiliser :

```bash
net start MySQL80
```

Cette commande suppose un service Windows nommé `MySQL80`, qui n’est pas celui de notre installation XAMPP.

Utiliser :

```text
XAMPP Control Panel
    ↓
MySQL
    ↓
Start
```

## 1.4 Vérifier le port MySQL

Dans Git Bash :

```bash
netstat -an | grep 3306
```

Si MySQL écoute sur le port 3306, une ligne contenant `3306` doit apparaître.

---

# 2. CONFIGURATION MYSQL ADAPTÉE À XAMPP

## 2.1 Vérifier le client

```bash
mysql --version
```

Si `mysql` n’est pas reconnu, utiliser temporairement le chemin complet :

```bash
/c/xampp/mysql/bin/mysql.exe --version
```

Ou ajouter le chemin à `~/.bashrc` :

```bash
printf '\nexport PATH="$PATH:/c/xampp/mysql/bin"\n' >> ~/.bashrc
source ~/.bashrc
```

Puis :

```bash
mysql --version
```

## 2.2 Connexion root

Avec mot de passe :

```bash
winpty mysql -u root -p
```

Sans mot de passe :

```bash
winpty mysql -u root
```

> Dans Git Bash, `winpty` est utile pour les sessions interactives de MySQL qui demandent un mot de passe.

## 2.3 Créer la base

Dans MySQL :

```sql
CREATE DATABASE cohorte
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Vérifier :

```sql
SHOW DATABASES;
```

Quitter :

```sql
EXIT;
```

## 2.4 Configuration `.env`

Configuration de référence :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cohorte
DB_USERNAME=root
DB_PASSWORD=
```

Si le compte root XAMPP possède un mot de passe, renseigner ce mot de passe dans `DB_PASSWORD`.

> Le guide officiel utilise cette configuration MySQL de référence. Le plan ne crée donc pas artificiellement un compte `cohorte` tant que le document ne l’impose pas.

---

# 3. ORGANISATION GIT

## 3.1 Branche principale

```text
main
```

## 3.2 Branches par phase

```text
feat/00-installation
feat/01-modele-donnees
feat/02-seeders
feat/03-authentification-fortify
feat/04-adhesion-promotion
feat/05-fil-promotion
feat/06-entraide
feat/07-moderation-ia
feat/08-signalements
feat/09-quota-et-doublon
feat/10-reputation
```

## 3.3 Cycle d’une phase

```bash
git switch main
git pull
git switch -c feat/XX-nom-phase
```

Développement :

```bash
git status
git add <fichiers>
git commit -m "type(scope): description"
```

Fin de phase :

```bash
git push -u origin feat/XX-nom-phase
git switch main
git pull
git merge --no-ff feat/XX-nom-phase -m "merge: phase XX"
git tag phase-XX
git push origin main --tags
```

## 3.4 Commits atomiques

Utiliser notamment :

```text
feat
fix
refactor
docs
chore
test
wip
```

Exemples :

```bash
git commit -m "feat(db): creer les migrations"
git commit -m "feat(auth): installer fortify"
git commit -m "fix(feed): bloquer l acces hors promotion"
git commit -m "test(feed): verifier le cloisonnement"
git commit -m "docs: mettre a jour le journal"
```

Éviter :

```bash
git add .
git commit -m "tout le projet"
```

si cela mélange plusieurs responsabilités.

---

# 4. DOCUMENTATION DU PROJET

Créer dès le début :

```text
docs/
├── JOURNAL.md
├── DECISIONS.md
└── IA.md
```

## 4.1 JOURNAL.md

Pour chaque phase :

- travail réalisé ;
- commits importants ;
- tests effectués ;
- problèmes rencontrés ;
- corrections ;
- décisions.

## 4.2 DECISIONS.md

Documenter les décisions d’architecture et de conception :

- choix Laravel/Fortify ;
- stratégie de cloisonnement ;
- choix des scopes ;
- stratégie de modération ;
- comportement en panne de l’IA ;
- quota ;
- détection de doublons ;
- réputation ;
- choix de stockage/recalcul du score.

## 4.3 IA.md

Pour chaque utilisation importante d’une IA :

```text
Demande
→ résultat proposé
→ résultat retenu/rejeté
→ vérification humaine
→ raison du choix
```

---

# 5. JOUR 1 — PHASE 0 + PHASE 1

## Objectif

À la fin du jour 1 :

- projet Laravel installé ;
- environnement configuré ;
- dépôt Git initialisé ;
- MySQL XAMPP fonctionnel ;
- base `cohorte` créée ;
- migrations principales écrites.

---

## PHASE 0 — INSTALLATION

### Branche

```text
feat/00-installation
```

### 5.1 Vérifier les prérequis

```bash
php -v
composer --version
node --version
npm --version
mysql --version
```

Si MySQL n’est pas dans le PATH :

```bash
/c/xampp/mysql/bin/mysql.exe --version
```

### 5.2 Créer le projet

```bash
composer create-project laravel/laravel cohorte
cd cohorte
```

Vérifier :

```bash
php artisan --version
```

Générer la clé :
    
```bash
php artisan key:generate
```

### 5.3 Configurer `.env`

Configurer :

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

### 5.4 Tester la base

```bash
php artisan migrate
```

Puis :

```bash
php artisan migrate:status
```

### 5.5 Lancer Laravel

```bash
php artisan serve
```

Vérifier l’application dans le navigateur.

### 5.6 Initialiser Git

```bash
git init
git branch -M main
git add .
git commit -m "chore: initialiser le projet laravel"
```

Configurer le dépôt distant :

```bash
git remote add origin <URL_DU_DEPOT>
git push -u origin main
```

---

# 6. PHASE 1 — MODÈLE DE DONNÉES

## Branche

```text
feat/01-modele-donnees
```

## Objectif

Construire le schéma relationnel du projet et ses relations Eloquent.

## 6.1 Entités principales

Prévoir notamment :

```text
User
Promotion
Publication
Reponse
Signalement
```

Ajouter les tables/champs nécessaires aux fonctionnalités IA et aux règles métier du guide.

## 6.2 Migrations

Créer les migrations nécessaires avec :

```bash
php artisan make:migration ...
```

Vérifier :

- clés primaires ;
- clés étrangères ;
- index ;
- contraintes uniques ;
- types adaptés ;
- timestamps ;
- valeurs par défaut ;
- statuts.

## 6.3 Relations Eloquent

Configurer notamment :

```text
User → Promotion
Promotion → Users
Publication → User
Publication → Promotion
Publication → Reponses
Reponse → User
Reponse → Publication
Signalement → Publication
```

## 6.4 Test

```bash
php artisan migrate:fresh
php artisan migrate:status
```

Puis :

```bash
winpty mysql -u root -p cohorte -e "SHOW TABLES;"
```

## 6.5 Commits

```bash
git commit -m "feat(db): creer les migrations"
git commit -m "feat(db): definir les relations eloquent"
```

---

# 7. JOUR 2 — PHASE 1 + PHASE 2

## Objectif

À la fin du jour 2 :

- relations terminées ;
- factories opérationnelles ;
- seeders opérationnels ;
- données de démonstration ;
- `migrate:fresh --seed` fonctionnel.

---

# 8. PHASE 2 — FACTORIES ET SEEDERS

## Branche

```text
feat/02-seeders
```

## 8.1 Factories

Créer les factories nécessaires :

```text
UserFactory
PromotionFactory
PublicationFactory
ReponseFactory
SignalementFactory
```

Adapter selon le schéma final.

## 8.2 Seeders

Créer deux promotions.

Créer les comptes de démonstration demandés par le guide.

Le jeu de données doit permettre de tester :

- deux promotions différentes ;
- cloisonnement ;
- publications ;
- questions ;
- réponses ;
- réponse retenue ;
- signalements ;
- réputation.

## 8.3 Test principal

```bash
php artisan migrate:fresh --seed
```

Cette commande doit être considérée comme un test de référence.

## 8.4 Vérifier les données

```bash
winpty mysql -u root -p cohorte -e "SHOW TABLES;"
```

Puis consulter les tables nécessaires si besoin.

## 8.5 Commits

```bash
git commit -m "feat(seed): creer les factories"
git commit -m "feat(seed): ajouter les donnees de demonstration"
git commit -m "test(seed): verifier le seed complet"
```

---

# 9. JOUR 3 — PHASE 3

# AUTHENTIFICATION FORTIFY

## Branche

```text
feat/03-authentification-fortify
```

## Objectif

Mettre en place :

- inscription ;
- connexion ;
- déconnexion ;
- réinitialisation du mot de passe ;
- limitation des tentatives ;
- vues Blade personnalisées.

## 9.1 Installer Fortify

```bash
composer require laravel/fortify
php artisan fortify:install
```

## 9.2 Configurer Fortify

Configurer `config/fortify.php` et les actions nécessaires.

## 9.3 Vues

Créer/configurer les vues Blade :

```text
resources/views/auth/
├── login.blade.php
├── register.blade.php
├── forgot-password.blade.php
└── reset-password.blade.php
```

Adapter l’organisation au projet réel.

## 9.4 Tests

Tester :

```text
Inscription
Connexion
Déconnexion
Mot de passe oublié
Réinitialisation
Mauvais identifiants
Tentatives répétées
```

---

# 10. JOUR 4 — PHASE 3 + PHASE 4

# ADHÉSION À UNE PROMOTION

## Branche

```text
feat/04-adhesion-promotion
```

## 10.1 Code d’invitation

Le code doit :

- être obligatoire ;
- être validé ;
- identifier une promotion ;
- refuser un code invalide.

Intégrer la logique au processus d’inscription Fortify.

## 10.2 Middleware

Créer le middleware nécessaire pour vérifier qu’un utilisateur appartient à une promotion.

Logique :

```text
Utilisateur authentifié
        ↓
Promotion associée ?
        ↓
Oui → continuer
Non → comportement prévu
```

## 10.3 Tests

```text
Code valide → inscription OK
Code invalide → inscription refusée
Code inexistant → inscription refusée
```

---

# 11. JOUR 5 — PHASE 5

# FIL DE PROMOTION

## Branche

```text
feat/05-fil-promotion
```

## Objectif

Afficher uniquement les contenus accessibles à la promotion de l’utilisateur.

## 11.1 Fonctionnalités

- liste des publications ;
- création ;
- détail ;
- pagination ;
- filtres nécessaires.

## 11.2 Scopes

Prévoir notamment :

```text
deLaPromotion()
visibles()
questions()
posts()
```

## 11.3 Cloisonnement

Toutes les requêtes doivent être sécurisées côté serveur.

Ne pas se contenter de cacher les contenus dans Blade.

---

# 12. JOUR 6 — PHASE 5

# POLICIES + FORMREQUEST + SÉCURITÉ

## Objectif

Renforcer le cloisonnement.

## 12.1 Policy

Créer une Policy pour les publications et les actions protégées.

## 12.2 FormRequests

Valider systématiquement les entrées utilisateur.

## 12.3 Test d’accès direct

Utilisateur A :

```text
Publication de A → OK
Publication de B → 403/refus
```

Tester aussi les URLs saisies directement.

## 12.4 Vérifier les N+1

Utiliser `with()` lorsque nécessaire.

Exemple de logique :

```php
Publication::query()
    ->visibles()
    ->deLaPromotion($user->promotion_id)
    ->with('auteur')
    ->latest()
    ->paginate(15);
```

---

# 13. JOUR 7 — PHASE 6

# ENTRAIDE — QUESTIONS / RÉPONSES

## Branche

```text
feat/06-entraide
```

## 13.1 Questions

Créer :

- liste ;
- création ;
- détail.

## 13.2 Réponses

Permettre :

- consultation ;
- ajout ;
- affichage par question.

## 13.3 Réponse retenue

L’auteur de la question peut sélectionner une réponse.

Vérifier :

- autorisation ;
- relation ;
- unicité ;
- réputation future.

## 13.4 Tests

```text
Créer question
→ répondre
→ sélectionner réponse
→ vérifier l’état
```

---

# 14. JOUR 8 — PHASE 7

# OPENROUTER + DEMANDE DE CHANGEMENT

## Branche

```text
feat/07-moderation-ia
```

## 14.1 Configuration

Dans `.env` :

```env
OPENROUTER_API_KEY=
OPENROUTER_MODEL=
OPENROUTER_TIMEOUT=12
```

Ne jamais mettre la clé dans le code.

## 14.2 Service

Créer :

```text
app/Services/OpenRouterClient.php
```

Les contrôleurs ne doivent pas appeler directement OpenRouter.

## 14.3 Robustesse

Prévoir :

- timeout ;
- retry ;
- erreurs HTTP ;
- réponse JSON invalide ;
- réponse incomplète ;
- journalisation ;
- comportement de secours.

## 14.4 Demande de changement

Le jour 8, une demande de changement est reçue.

Documenter :

```text
demande
impact
choix
implémentation prévue
tests
```

L’intégration est prévue au jour 10.

---

# 15. JOUR 9 — PHASE 7

# MODÉRATION IA

## Objectif

Connecter OpenRouter au flux de publication/modération.

## 15.1 Statuts

Prévoir les statuts nécessaires, notamment :

```text
acceptable
douteux
inacceptable
indisponible
```

## 15.2 Flux

```text
Publication
    ↓
Service OpenRouter
    ↓
Analyse
    ↓
Résultat
    ↓
Statut de modération
```

## 15.3 Tests

Tester :

```text
IA disponible
IA indisponible
Timeout
Erreur HTTP
JSON invalide
Résultat incomplet
```

## 15.4 Aucun secret dans Git

Vérifier :

```bash
git status
git diff
```

et `.gitignore`.

---

# 16. JOUR 10 — INTÉGRATION DE LA DEMANDE DE CHANGEMENT

## Objectif

Intégrer la demande reçue au jour 8.

## 16.1 Développement

Faire les modifications sur la branche appropriée.

## 16.2 Régression

Tester :

```bash
php artisan migrate:fresh --seed
```

Puis :

```text
Authentification
Inscription
Promotion
Fil
Questions
Réponses
Modération
Sécurité
```

## 16.3 Documentation

Mettre à jour :

```text
docs/JOURNAL.md
docs/DECISIONS.md
```

---

# 17. JOUR 11 — PHASE 8

# SIGNALEMENTS

## Branche

```text
feat/08-signalements
```

## 17.1 Signalement

Un utilisateur doit pouvoir signaler une publication.

Stocker les informations nécessaires :

```text
auteur
publication
motif
statut
timestamps
```

## 17.2 Doublons

Empêcher les signalements identiques selon la règle métier.

Utiliser une contrainte appropriée en base.

## 17.3 Masquage

Prévoir le seuil de signalements.

Flux :

```text
Publication
    ↓
Signalements
    ↓
Seuil atteint
    ↓
Masquage
```

## 17.4 Tests

Tester :

- signalement autorisé ;
- doublon ;
- seuil ;
- masquage ;
- accès hors promotion.

---

# 18. JOUR 12 — PHASE 9

# QUOTA IA + DÉTECTION DE DOUBLON

## Branche

```text
feat/09-quota-et-doublon
```

## 18.1 Quota

Créer la logique de quota IA.

Suivre :

```text
quota maximum
appels effectués
appels restants
```

## 18.2 Middleware quota

Bloquer proprement les appels lorsque le quota est atteint.

## 18.3 Affichage

Afficher le quota restant à l’utilisateur lorsque demandé par le parcours.

## 18.4 Détection de doublon

Avant la publication d’une question :

```text
Question
    ↓
Recherche de questions similaires
    ↓
Similaire ?
    ├── Oui → afficher les similaires
    │          + proposer "Publier quand même"
    │
    └── Non → publier
```

Prévoir un mécanisme de confirmation permettant de publier après avertissement.

## 18.5 Tests

Tester :

```text
Quota disponible
Quota épuisé
Question similaire
Question non similaire
Confirmation "Publier quand même"
```

---

# 19. JOUR 13 — PHASE 10

# RÉPUTATION + FINITIONS

## Branche

```text
feat/10-reputation
```

## 19.1 Barème

Base de travail du guide :

```text
Réponse retenue : +10
Réponse écrite  : +3
Question posée  : +1
Publication refusée : -5
```

Le score ne doit pas devenir négatif si le guide impose un minimum de zéro.

## 19.2 Choix d’architecture

Documenter si la réputation est :

- stockée ;
- recalculée ;
- hybride.

Justifier le choix dans :

```text
docs/DECISIONS.md
```

## 19.3 Droit associé

Mettre en place le droit concret lié au niveau de réputation, notamment l’épinglage lorsque les conditions du projet sont satisfaites.

## 19.4 Finitions

Vérifier :

- titres ;
- messages de succès ;
- messages d’erreur ;
- états vides ;
- page 403 ;
- navigation ;
- pagination ;
- validation ;
- sécurité ;
- absence de N+1.

---

# 20. JOUR 14 — FINALISATION

## 20.1 Reconstruction complète

Commande obligatoire :

```bash
php artisan migrate:fresh --seed
```

Elle doit fonctionner sans erreur.

## 20.2 Tests finaux

Tester :

```text
Inscription
Connexion
Déconnexion
Reset password
Code d’invitation
Cloisonnement
Publications
Questions
Réponses
Réponse retenue
Signalements
Modération IA
Quota
Doublons
Réputation
Épinglage
```

---

# 21. VÉRIFICATION GIT FINALE

```bash
git status
git log --oneline --graph --all
git branch -a
git tag
```

Vérifier :

- branches par phase ;
- merges `--no-ff` ;
- commits atomiques ;
- Conventional Commits ;
- tags ;
- historique cohérent ;
- `.env` absent de Git.

---

# 22. REVERT OBLIGATOIRE

Démontrer un retour arrière avec :

```bash
git revert <commit>
```

Puis documenter :

```text
Pourquoi le commit a été annulé
Quel problème il provoquait
Comment la situation a été corrigée
```

Éviter de remplacer cette démonstration par un simple `reset --hard`.

---

# 23. README FINAL

Le README doit permettre à une personne extérieure de reconstruire le projet.

Structure recommandée :

```text
# Cohorte

## Présentation

## Prérequis

## Installation

## Configuration XAMPP

## Configuration MySQL

## Configuration .env

## Migration

## Seed

## Lancement

## Authentification

## OpenRouter

## Comptes de démonstration

## Tests

## Architecture

## Documentation
```

---

# 24. CONFIGURATION XAMPP — RÉCAPITULATIF

## Démarrage

```text
XAMPP Control Panel
→ Apache → Start
→ MySQL → Start
```

## Vérifier MySQL

```bash
netstat -an | grep 3306
```

## Vérifier client

```bash
mysql --version
```

## Connexion

```bash
winpty mysql -u root
```

ou :

```bash
winpty mysql -u root -p
```

## Base

```text
cohorte
```

## Hôte

```text
127.0.0.1
```

## Port

```text
3306
```

---

# 25. COMMANDES LARAVEL DE RÉFÉRENCE

```bash
php artisan --version
php artisan migrate
php artisan migrate:status
php artisan migrate:fresh
php artisan migrate:fresh --seed
php artisan db:seed
php artisan route:list
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan serve
```

---

# 26. COMMANDES GIT DE RÉFÉRENCE

```bash
git status
git branch
git branch -a
git switch main
git switch -c feat/nom-phase
git add <fichiers>
git commit -m "type(scope): description"
git push
git pull
git merge --no-ff
git tag
git log --oneline --graph --all
git revert <commit>
```

---

# 27. CHECKLIST FINALE

## Git

- [ ] Branche par phase
- [ ] Commits atomiques
- [ ] Conventional Commits
- [ ] Merges `--no-ff`
- [ ] Tags
- [ ] Revert documenté
- [ ] `.env` absent du dépôt

## Base de données

- [ ] Migrations
- [ ] Clés étrangères
- [ ] Index
- [ ] Contraintes uniques
- [ ] Relations Eloquent
- [ ] Factories
- [ ] Seeders
- [ ] Deux promotions
- [ ] Comptes de démonstration
- [ ] `migrate:fresh --seed` fonctionne

## Authentification

- [ ] Fortify
- [ ] Inscription
- [ ] Connexion
- [ ] Déconnexion
- [ ] Reset password
- [ ] Limitation des tentatives
- [ ] Code d’invitation

## Sécurité

- [ ] Policies
- [ ] FormRequests
- [ ] Scopes
- [ ] Cloisonnement par promotion
- [ ] Contrôle d’accès direct par URL
- [ ] Validation côté serveur
- [ ] Protection des actions sensibles

## Fonctionnalités

- [ ] Fil
- [ ] Publications
- [ ] Questions
- [ ] Réponses
- [ ] Réponse retenue
- [ ] Signalements
- [ ] Masquage
- [ ] Réputation
- [ ] Épinglage

## IA

- [ ] OpenRouterClient
- [ ] API key dans `.env`
- [ ] Modèle dans `.env`
- [ ] Timeout
- [ ] Retry
- [ ] Parsing défensif
- [ ] Gestion de panne
- [ ] Quota
- [ ] Détection de doublons

## Documentation

- [ ] README
- [ ] JOURNAL.md
- [ ] DECISIONS.md
- [ ] IA.md
- [ ] Décisions argumentées
- [ ] Utilisation de l’IA tracée

---

# 28. TABLEAU DU PLANNING

| Jour | Phase | Travail principal | Livraison |
|---:|---|---|---|
| 1 | 0 + 1 | Installation + modèle | Laravel + migrations |
| 2 | 1 + 2 | Relations + seeders | Seed complet |
| 3 | 3 | Fortify | Authentification |
| 4 | 3 + 4 | Invitation + promotion | Inscription cloisonnée |
| 5 | 5 | Fil | Fil de promotion |
| 6 | 5 | Sécurité | Policy + FormRequest + 403 |
| 7 | 6 | Entraide | Questions + réponses |
| 8 | 7 | OpenRouter | Client IA + changement reçu |
| 9 | 7 | Modération | Modération opérationnelle |
| 10 | Demande de changement | Intégration | Régression validée |
| 11 | 8 | Signalements | Masquage |
| 12 | 9 | Quota + doublon | Quota IA + détection |
| 13 | 10 | Réputation | Score + droit associé |
| 14 | Finalisation | Documentation + tests | Projet prêt pour soutenance |

---

# 29. ROUTINE DE TRAVAIL QUOTIDIENNE

Pour chaque journée :

```text
1. Lire l’objectif du jour
        ↓
2. Vérifier la branche Git
        ↓
3. Développer une petite fonctionnalité
        ↓
4. Tester immédiatement
        ↓
5. Corriger
        ↓
6. Commit atomique
        ↓
7. Mettre à jour JOURNAL.md
        ↓
8. Mettre à jour DECISIONS.md si nécessaire
        ↓
9. Mettre à jour IA.md si nécessaire
        ↓
10. Vérifier migrate:fresh --seed lorsque pertinent
        ↓
11. Finir la phase
        ↓
12. Merge --no-ff
        ↓
13. Tag
        ↓
14. Passer à la phase suivante
```

---

# 30. RÈGLES CRITIQUES

> **IMPORTANT — `.env`**
>
> Ne jamais commiter `.env`.

> **IMPORTANT — OpenRouter**
>
> Ne jamais écrire la clé API directement dans le code.

> **IMPORTANT — Git**
>
> Ne pas développer toutes les phases directement sur `main`.

> **IMPORTANT — Sécurité**
>
> Le cloisonnement entre promotions doit être assuré côté serveur.

> **IMPORTANT — Seed**
>
> `php artisan migrate:fresh --seed` doit fonctionner avant le rendu.

> **IMPORTANT — Documentation**
>
> Le journal doit être alimenté pendant le projet.

> **IMPORTANT — IA**
>
> Tout code généré ou fortement assisté par IA doit être compris, vérifié et documenté conformément aux règles du projet.

> **IMPORTANT — XAMPP**
>
> MySQL est démarré depuis XAMPP. Ne pas supposer l’existence du service Windows `MySQL80`.

---

# 31. OBJECTIF FINAL

À la fin des 14 jours, le projet doit être :

```text
Installable
    ↓
Migrable
    ↓
Seedable
    ↓
Authentifiable
    ↓
Cloisonné par promotion
    ↓
Fonctionnel
    ↓
Modéré par IA
    ↓
Sécurisé
    ↓
Testé
    ↓
Documenté
    ↓
Versionné proprement
    ↓
Prêt pour la soutenance
```

Le critère final n’est pas seulement que l’application « fonctionne ».

Le projet doit également démontrer :

- une architecture cohérente ;
- une bonne sécurité ;
- une bonne utilisation de Laravel ;
- une intégration IA propre ;
- une bonne traçabilité Git ;
- une documentation suffisante ;
- la capacité à expliquer les choix techniques.

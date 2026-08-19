# Cohorte

Réseau social privé de promotion : on n'y entre que sur invitation, on n'y voit
que le contenu de sa propre promotion, et un modèle de langage (OpenRouter)
modère les publications et détecte les questions en double.

Projet d'évaluation Wommate Technology — Laravel 12, Blade, Fortify, OpenRouter.

## Prérequis

- PHP 8.2 ou plus
- Composer
- XAMPP (Apache + MySQL/MariaDB) — MySQL démarré depuis le panneau de contrôle
- Node et npm
- Git

## Installation

```bash
git clone https://github.com/RassoulTech/Cohorte-Laravel.git cohorte
cd cohorte
composer install
cp .env.example .env
php artisan key:generate
```

Créer la base de données, MySQL étant démarré depuis XAMPP :

```sql
CREATE DATABASE cohorte CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Renseigner ensuite la section base de données du fichier `.env` :

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cohorte
DB_USERNAME=root
DB_PASSWORD=
```

Puis créer les tables et lancer le serveur :

```bash
php artisan migrate
php artisan serve
```

L'application est disponible sur http://127.0.0.1:8000.

## Configuration métier

Les règles chiffrées du projet sont centralisées dans `config/cohorte.php` et
pilotables depuis le `.env`, sans toucher au code :

| Variable | Rôle | Défaut |
|---|---|---:|
| `COHORTE_QUOTA_IA` | appels à l'IA par membre et par jour | 10 |
| `COHORTE_SEUIL_SIGNALEMENT` | signalements avant masquage automatique | 3 |
| `COHORTE_SEUIL_EPINGLAGE` | réputation ouvrant le droit d'épingler | 50 |
| `COHORTE_MODERATION_FAIL_OPEN` | publier quand même si l'IA ne répond pas | false |

## Comptes de démonstration

Reconstruire la base et la remplir :

```bash
php artisan migrate:fresh --seed
```

Le mot de passe est `password` pour les quatre comptes.

| Adresse | Rôle | Promotion | Sert à démontrer |
|---|---|---|---|
| `awa@cohorte.test` | apprenant | Groupe A (`DWA2026`) | le parcours normal d'un membre |
| `moussa@cohorte.test` | délégué | Groupe A (`DWA2026`) | la file de modération |
| `fatou@cohorte.test` | apprenant | Groupe B (`DWB2026`) | le cloisonnement entre promotions |
| `formateur@cohorte.test` | enseignant | *aucune* | la consultation sans publication |

Les deux codes d'invitation, pour tester l'inscription : `DWA2026` et `DWB2026`.

### Vérifier le cloisonnement

1. Se connecter avec `awa@cohorte.test` et relever l'identifiant d'une publication.
2. Se déconnecter, se connecter avec `fatou@cohorte.test`.
3. Saisir directement `/publications/{id}` dans le navigateur.
4. Résultat attendu : **erreur 403**.

### Le jeu de données généré

Pour chacune des deux promotions : 8 membres, 15 publications et 6 questions
avec de 0 à 3 réponses chacune, réparties sur les trente derniers jours.

## Clés OpenRouter

*À renseigner à partir de la phase 7. La clé se met uniquement dans le `.env`,
jamais dans le code ni dans le dépôt.*

## Documentation

- `docs/JOURNAL.md` — journal de bord, une entrée par phase
- `docs/DECISIONS.md` — les décisions de conception argumentées
- `docs/IA.md` — utilisation de l'IA générative, ce qui a été retenu et rejeté

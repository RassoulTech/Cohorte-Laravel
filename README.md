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

*Disponibles à partir de la phase 2, avec `php artisan migrate:fresh --seed`.*

## Clés OpenRouter

*À renseigner à partir de la phase 7. La clé se met uniquement dans le `.env`,
jamais dans le code ni dans le dépôt.*

## Documentation

- `docs/JOURNAL.md` — journal de bord, une entrée par phase
- `docs/DECISIONS.md` — les décisions de conception argumentées
- `docs/IA.md` — utilisation de l'IA générative, ce qui a été retenu et rejeté

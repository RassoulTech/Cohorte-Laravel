# Utilisation de l'IA générative

> Phase par phase : ce que j'ai demandé, ce que j'ai retenu, ce que j'ai rejeté
> et pourquoi.

---

## Phase 0 — Installation et mise en place du dépôt

### Ce que j'ai demandé

J'ai utilisé un assistant IA (Claude Code) pour relire le guide d'évaluation et
le plan de travail, vérifier l'état réel du dépôt, puis écrire la configuration
métier `config/cohorte.php`, le gabarit Blade et le composant d'alerte.

### Ce que j'ai retenu

- La centralisation des quatre valeurs métier dans `config/cohorte.php`, lues
  avec `config()` et jamais avec `env()` depuis un contrôleur, parce qu'un
  `php artisan config:cache` ferait renvoyer `null` à `env()`.
- Un composant `alerte` unique pour les messages flash et les erreurs de
  validation, appelé une seule fois depuis le gabarit plutôt que recopié dans
  chaque vue.

### Ce que j'ai rejeté, et pourquoi

- **Le gabarit du guide recopié tel quel.** Il contient `route('feed.index')`,
  `route('entraide.index')` et `route('profil.show')`, qui sont des routes des
  phases 5 et 6. Les écrire aujourd'hui ferait planter la première page qui
  utiliserait le gabarit, avec une erreur *Route not defined*. J'ai mis un lien
  `url('/')` et un commentaire indiquant où ajouter ces liens le moment venu.

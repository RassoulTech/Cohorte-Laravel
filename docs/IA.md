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

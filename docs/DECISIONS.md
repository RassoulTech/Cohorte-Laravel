# Décisions de conception

> Cinq décisions argumentées sont attendues au rendu final. Pour chacune :
> le choix retenu, l'alternative écartée, et la raison du choix.

---

## 1. Une seule table `publications` pour les posts et les questions — phase 1

**Choix retenu.** Les posts et les questions vivent dans la même table, séparés
par une colonne `type` (`post` | `question`) et isolés par le scope
`questions()`.

**Alternative écartée.** Deux tables distinctes `posts` et `questions`.

**Pourquoi.** Les deux objets partagent presque tout : un auteur, une promotion,
un contenu, un statut de modération, la possibilité d'être signalés et masqués.
Deux tables m'auraient obligé à dupliquer la logique de modération (phase 7) et
de signalement (phase 8), donc à écrire deux fois la même règle métier et à la
corriger deux fois. Le coût de la solution retenue est une colonne `type` à ne
jamais oublier dans les requêtes : c'est exactement le rôle des scopes.

---

## 2. `promotion_id` dupliqué sur `publications` — phase 1

**Choix retenu.** La table `publications` porte sa propre colonne
`promotion_id`, alors que la promotion pourrait être déduite de l'auteur par une
jointure sur `users`.

**Alternative écartée.** Ne stocker que `user_id` et retrouver la promotion via
`publication.auteur.promotion_id`.

**Pourquoi.** Deux raisons. D'abord la performance et la simplicité : la requête
de cloisonnement, qui est la requête la plus fréquente de l'application, devient
une simple condition sur une colonne indexée
(`index(['promotion_id', 'statut', 'created_at'])`), sans jointure. Ensuite la
justesse métier : une publication reste rattachée à la promotion dans laquelle
elle a été écrite, même si son auteur change de promotion plus tard. C'est une
dénormalisation assumée : la contrepartie est qu'il faut renseigner
`promotion_id` à la création d'une publication, et ne jamais le recalculer
depuis l'auteur.

---

## 3. Comportement en cas de panne d'OpenRouter : *fail-open* ou *fail-closed* — phase 7

*À rédiger en phase 7. La valeur est déjà pilotée par
`config('cohorte.moderation_fail_open')`, réglée à `false` par défaut.*

---

## 4. Score de réputation : stocké ou recalculé — phase 10

*À rédiger en phase 10. La colonne `users.points` existe depuis la phase 1, ce
qui oriente vers un score stocké et recalculable par commande, mais le choix
sera argumenté à ce moment-là.*

---

## 5. Ne pas soumettre la modération au quota d'IA — phase 9

*À rédiger en phase 9.*

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

**Contexte.** Chaque publication est soumise à un modèle de langage avant
enregistrement. Ce service extérieur peut être lent, en panne, ou renvoyer une
réponse inexploitable. Il faut décider du sort d'une publication dont la
modération n'a pas pu aboutir.

**Ce que la question n'est pas.** Elle ne porte pas sur un verdict erroné : le
parsing défensif de `ServiceModeration::interpreter()` écarte déjà toute réponse
douteuse. À ce stade, il ne reste aucun verdict du tout. La question est donc :
*en l'absence totale d'information, publie-t-on quand même ?*

**Alternative écartée : fail-open.** Publier malgré l'absence de contrôle. Ce
choix privilégie la continuité de service : l'application reste utilisable et
personne ne remarque la panne. Il est défendable sur une plateforme grand public
à fort volume, où une file d'attente deviendrait ingérable et où le coût d'un
faux positif est élevé.

**Choix retenu : fail-closed.** Une publication non contrôlée part en
`en_moderation` et attend la décision d'un délégué.

**Pourquoi ce choix ici, et pas ailleurs.**

L'enjeu est asymétrique. Dans un réseau interne à une école, une insulte ou un
harcèlement publiés atteignent des camarades identifiables et restent visibles.
Une publication retardée d'une heure ne cause aucun préjudice comparable.

Le volume rend la file soutenable. Une promotion compte une vingtaine de
membres. Même une panne d'une journée entière ne produirait pas les deux cents
publications qui rendraient la modération humaine impraticable — argument qui
serait décisif sur une plateforme de plusieurs milliers d'utilisateurs.

Le circuit de traitement existe déjà. Le rôle de délégué et sa file de
modération sont prévus par le cahier des charges. Envoyer une publication en
attente n'est pas une impasse : c'est un parcours normal de l'application.

L'utilisateur n'est jamais empêché de s'exprimer. Il publie, et un message lui
indique que sa publication sera validée par un délégué. Il n'y a ni erreur, ni
page bloquée, ni perte de son texte.

**Comment le choix est appliqué.** Il n'est pas écrit en dur. La constante
`cohorte.moderation_fail_open` est lue par `VerdictModeration::statutPublication()`
et pilotée depuis le `.env` :

```
COHORTE_MODERATION_FAIL_OPEN=false
```

Passer cette variable à `true` bascule l'application en fail-open sans modifier
une ligne de code. La décision reste donc révisable si le contexte change —
davantage de membres, ou une indisponibilité durable du fournisseur.

**Ce que nous avons vérifié.** Neuf situations simulées avec `Http::fake()` :
service en panne (503), réponse vide, contenu `null` malgré un HTTP 200, texte
sans JSON, JSON sans la clé attendue, verdict inventé. Toutes produisent le
verdict `Indisponible` et le statut `en_moderation`. Le cas du contenu `null`
n'est pas théorique : il a été observé en interrogeant réellement le catalogue
des modèles gratuits d'OpenRouter.

---

## 4. Score de réputation : stocké ou recalculé — phase 10

*À rédiger en phase 10. La colonne `users.points` existe depuis la phase 1, ce
qui oriente vers un score stocké et recalculable par commande, mais le choix
sera argumenté à ce moment-là.*

---

## 5. Ne pas soumettre la modération au quota d'IA — phase 9

*À rédiger en phase 9.*

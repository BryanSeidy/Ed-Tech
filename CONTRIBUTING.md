# Contribution Guide

1. Créez une branche à partir de develop
2. Utilisez feature/nom_de_la_branche
3. Suivez la convention de validation
4. Créer une demande de tirage (Pull Request)
5. Révision du code requise avant la fusion

*🚀Guide de Collaboration* :

 ```Workflow de l'Équipe```

Pour garder notre branche main stable et notre historique propre, nous appliquons désormais le flux de travail suivant. Merci de le suivre scrupuleusement.

> 1; Avant de coder (Chaque matin)

Assurez-vous de travailler sur la version la plus récente de l'application.

**git checkout dev**
**git pull origin dev**

> 2; Créer une branche de travail

On ne code jamais directement sur main. Créez une branche explicite pour votre tâche :

**git checkout -b feature/**`nom_de_ta_tache`

> 3; Soumettre votre travail (PR)

Une fois votre tâche terminée et testée localement :
Poussez votre branche sur GitHub :

**git push origin feature/**`nom_de_ta_tache`

Ouvrez une Pull Request (PR) sur l'interface GitHub (de votre branche vers main).
Décrivez vos changements : Expliquez brièvement quoi et pourquoi.
Assignez un Reviewer : Demandez au Lead ou à un collègue de relire votre code.

> 4; La Revue de Code

Si le reviewer demande des corrections (Request changes), faites-les directement sur votre branche et faites un nouveau push. La PR se mettra à jour toute seule.
Si le reviewer valide (Approve), le Lead effectuera un Squash and Merge. Votre branche sera alors supprimée.

> 5; Synchroniser après une fusion

Dès qu'une PR est fusionnée sur main, tout le monde doit mettre à jour sa branche locale pour éviter les conflits futurs :

**git checkout main**
**git pull origin main**
**git checkout** `votre-branche-en-cours`
**git merge main**

💡 *Les règles d'or*

* Zéro push direct sur main (bloqué par sécurité).
* Tests au vert obligatoires avant de demander une revue.
* Un seul sujet par PR : Si vous faites une feature et un fix, faites deux branches différentes.

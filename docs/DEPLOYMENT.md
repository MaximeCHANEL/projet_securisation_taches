# Procédure de déploiement

## 1. Présentation

Ce document décrit la procédure permettant d'installer et de démarrer Projet Securisation taches dans un environnement utilisant Docker.

L'utilisation de Docker permet de reproduire l'environnement de l'application sans installer directement PHP, MySQL ou MongoDB sur la machine hôte.

---

# 2. Prérequis

Le serveur ou poste utilisé doit disposer de :

* Docker ;
* Docker Compose ;
* Git.

Vérifier les installations :

```bash
docker --version
docker compose version
git --version
```

---

# 3. Récupération du projet

Cloner le dépôt :

```bash
git clone <URL_DU_DEPOT>
```

Se placer dans le dossier :

```bash
cd projet_securisation_taches
```

---

# 4. Configuration des variables d'environnement

Copier le fichier fourni :

```bash
cp .env.example .env
```

Le fichier `.env` contient les paramètres nécessaires à la connexion aux différents services.

Exemple :

```env
APP_ENV=development

DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=secure_tasks
DB_USERNAME=app_user
DB_PASSWORD=change_me

MONGO_HOST=mongodb
MONGO_PORT=27017
MONGO_DATABASE=secure_task_logs
```

Les valeurs réelles utilisées en production doivent être adaptées à l'environnement cible.

Le fichier `.env` ne doit pas être ajouté au dépôt Git.

---

# 5. Construction des conteneurs

Construire les images :

```bash
docker compose build
```

Puis démarrer les services :

```bash
docker compose up -d
```

Vérifier leur état :

```bash
docker compose ps
```

Les différents services doivent apparaître comme actifs.

---

# 6. Initialisation de la base de données

La base MySQL doit être initialisée avec le schéma prévu pour l'application.

Exemple :

```text
users
tasks
```

Les scripts SQL nécessaires peuvent être placés dans un dossier dédié :

```text
database/
└── init.sql
```

Si l'initialisation est automatisée par Docker Compose, elle est exécutée au démarrage du conteneur MySQL selon la configuration prévue.

---

# 7. Vérification de l'application

Après le démarrage des conteneurs, vérifier que l'API est accessible.

Tester par exemple :

```text
POST /register
POST /login
GET /tasks
```

Une première connexion permet de vérifier que :

* PHP fonctionne ;
* MySQL est accessible ;
* l'authentification fonctionne ;
* l'API répond correctement.

---

# 8. Vérification de MongoDB

Après une action utilisateur, vérifier qu'un événement a bien été enregistré dans MongoDB.

Par exemple :

```text
LOGIN
CREATE_TASK
UPDATE_TASK
DELETE_TASK
```

Cette vérification permet de confirmer que l'application communique correctement avec la base NoSQL.

---

# 9. Exécution des tests

Avant de considérer le déploiement comme valide, lancer les tests automatisés :

```bash
vendor/bin/phpunit
```

Tous les tests doivent être réussis avant la mise à disposition de l'application.

---

# 10. Vérification Docker

Afficher les conteneurs :

```bash
docker compose ps
```

Consulter les logs :

```bash
docker compose logs
```

Pour consulter les logs d'un service particulier :

```bash
docker compose logs php
docker compose logs mysql
docker compose logs mongodb
```

---

# 11. Mise à jour de l'application

Lorsqu'une nouvelle version du code est disponible :

```bash
git pull
```

Reconstruire les images si nécessaire :

```bash
docker compose build
```

Puis redémarrer les services :

```bash
docker compose up -d
```

Les tests doivent être exécutés après la mise à jour.

---

# 12. Arrêt de l'application

Pour arrêter les services :

```bash
docker compose down
```

Les volumes de données ne sont pas supprimés avec cette commande.

Pour supprimer également les volumes :

```bash
docker compose down -v
```

Cette dernière commande doit être utilisée avec précaution car elle peut supprimer les données persistantes des bases.

---

# 13. Sécurité du déploiement

Les éléments suivants doivent être vérifiés avant un déploiement réel :

* ne pas versionner le fichier `.env` ;
* utiliser des mots de passe différents pour l'environnement de production ;
* ne pas utiliser le compte `root` MySQL pour l'application ;
* limiter les ports exposés ;
* utiliser HTTPS pour les communications ;
* mettre à jour régulièrement les dépendances ;
* vérifier les logs de l'application ;
* effectuer des sauvegardes des données importantes.

---

# 14. Procédure de retour arrière

En cas de problème après une mise à jour, revenir à une version précédente du code :

```bash
git log
```

Identifier le commit souhaité puis :

```bash
git checkout <commit>
```

Reconstruire ensuite les conteneurs :

```bash
docker compose build
docker compose up -d
```

Les tests doivent être exécutés avant de remettre l'application à disposition.

---

# 15. Validation du déploiement

Le déploiement est considéré comme fonctionnel lorsque :

* les conteneurs sont démarrés ;
* l'API répond ;
* MySQL est accessible ;
* MongoDB est accessible ;
* l'inscription fonctionne ;
* la connexion fonctionne ;
* les opérations CRUD fonctionnent ;
* les contrôles d'accès fonctionnent ;
* les tests automatisés sont réussis ;
* aucune information sensible n'est exposée dans le dépôt.
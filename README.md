# Projet Sécurisation de tâches

## 1. Présentation

Projet Sécurisation de tâches est une petite API REST permettant à des utilisateurs de gérer leurs tâches personnelles de manière sécurisée.

Le projet a été réalisé dans le but de mettre en pratique plusieurs aspects du développement d'une application sécurisée : authentification, gestion des droits d'accès, accès à des bases de données SQL et NoSQL, tests automatisés, conteneurisation et intégration continue.

L'application permet notamment de :

* créer un compte utilisateur ;
* se connecter ;
* consulter ses tâches ;
* créer une tâche ;
* modifier une tâche ;
* supprimer une tâche ;
* consulter son historique d'activité.

Les données métier sont stockées dans MySQL tandis que MongoDB est utilisé pour conserver les événements liés à l'activité des utilisateurs.

---

## 2. Objectifs du projet

Le projet a plusieurs objectifs :

* développer une API REST fonctionnelle ;
* mettre en place une authentification sécurisée ;
* contrôler l'accès aux données selon l'utilisateur connecté ;
* utiliser une base de données SQL et une base NoSQL ;
* mettre en place des tests automatisés ;
* conteneuriser l'application avec Docker ;
* documenter son installation et son déploiement ;
* mettre en place une première démarche CI/CD avec GitHub Actions.

---

## 3. Architecture

L'application repose sur plusieurs composants :

```text
Client / Postman / Navigateur
            |
            v
          Nginx
            |
            v
        API PHP 8.3
        /          \
       v            v
    MySQL        MongoDB
      |              |
 Données métier   Logs / événements
```

### Rôle des différents composants

**Nginx**

Serveur web utilisé comme point d'entrée de l'application.

**PHP**

Le backend contient la logique de l'API, l'authentification, la validation des données et les accès aux bases de données.

**MySQL**

Base de données relationnelle utilisée pour les données structurées :

* utilisateurs ;
* tâches ;
* relations entre utilisateurs et tâches.

**MongoDB**

Base de données NoSQL utilisée pour conserver les événements et historiques d'activité :

* connexion ;
* création d'une tâche ;
* modification ;
* suppression.

**Docker**

Les différents services sont exécutés dans des conteneurs afin de disposer d'un environnement reproductible.

---

## 4. Technologies utilisées

| Technologie             | Utilisation                 |
| ----------------------- | --------------------------- |
| PHP 8.3                 | Backend et API REST         |
| PDO                     | Accès à MySQL               |
| MySQL                   | Base de données SQL         |
| MongoDB                 | Base de données NoSQL       |
| HTML / CSS / JavaScript | Interface simple éventuelle |
| PHPUnit                 | Tests automatisés           |
| Docker                  | Conteneurisation            |
| Docker Compose          | Orchestration des services  |
| Nginx                   | Serveur web                 |
| Git                     | Gestion des versions        |
| GitHub                  | Hébergement du code         |
| GitHub Actions          | Intégration continue        |

---

## 5. Fonctionnalités

### Gestion des utilisateurs

* inscription ;
* connexion ;
* authentification ;
* contrôle des accès.

### Gestion des tâches

* création ;
* consultation ;
* modification ;
* suppression.

### Historique d'activité

Les principales actions effectuées par les utilisateurs sont enregistrées dans MongoDB.

Exemples :

* `LOGIN`
* `CREATE_TASK`
* `UPDATE_TASK`
* `DELETE_TASK`

---

## 6. Sécurité

Plusieurs mesures sont mises en place dans l'application.

### Mots de passe

Les mots de passe ne sont pas enregistrés en clair. Ils sont hachés avant leur stockage en base de données.

PHP fournit notamment les fonctions :

```php
password_hash()
password_verify()
```

### Requêtes préparées

Les requêtes SQL utilisent PDO et des paramètres afin de limiter les risques d'injection SQL.

### Authentification

Les routes nécessitant une authentification vérifient que l'utilisateur est correctement identifié avant d'autoriser l'accès aux données.

### Contrôle des droits

Un utilisateur ne doit pouvoir consulter ou modifier que ses propres tâches.

### Variables sensibles

Les informations sensibles, telles que les identifiants de connexion aux bases de données, sont stockées dans des variables d'environnement.

Le fichier `.env` n'est pas versionné dans Git.

Un fichier `.env.example` est fourni afin d'indiquer les variables nécessaires à la configuration.

---

## 7. Installation

### Prérequis

Les éléments suivants sont nécessaires :

* Docker ;
* Docker Compose ;
* Git.

### Récupération du projet

```bash
git clone <URL_DU_DEPOT>
cd projet_securisation_taches
```

### Configuration

Copier le fichier d'exemple :

```bash
cp .env.example .env
```

Puis renseigner les valeurs nécessaires.

### Démarrage

L'application peut ensuite être démarrée avec :

```bash
docker compose up -d
```

Les conteneurs nécessaires sont alors démarrés.

---

## 8. Tests

Les tests automatisés sont réalisés avec PHPUnit.

Pour lancer les tests :

```bash
vendor/bin/phpunit
```

Les principaux scénarios testés concernent :

* inscription ;
* connexion ;
* authentification ;
* création d'une tâche ;
* consultation ;
* modification ;
* suppression ;
* contrôle des droits d'accès ;
* gestion des données invalides.

Les tests sont également exécutés automatiquement par GitHub Actions lors des modifications du projet.

---

## 9. API

La documentation détaillée des routes est disponible dans :

```text
docs/API.md
```

Les principales routes sont :

```text
POST   /register
POST   /login

GET    /tasks
POST   /tasks
PUT    /tasks/{id}
DELETE /tasks/{id}
```

---

## 10. Déploiement

La procédure détaillée de déploiement est disponible dans :

```text
docs/DEPLOYMENT.md
```

L'application est conçue pour être exécutée à l'aide de Docker Compose.

---

## 11. Intégration continue

GitHub Actions permet d'automatiser certaines vérifications du projet.

Lorsqu'une modification est envoyée sur le dépôt :

```text
Git push
    |
    v
GitHub Actions
    |
    v
Installation des dépendances
    |
    v
Exécution des tests PHPUnit
    |
    v
Résultat
```

Cette automatisation permet de détecter plus rapidement les régressions lors des modifications du code.

---

## 12. Organisation du projet

```text
projet_securisation_taches/
│
├── public/
│   └── index.php
│
├── src/
│   ├── Database.php
│   ├── MongoDatabase.php
│   ├── Auth.php
│   └── TaskController.php
│
├── tests/
│   ├── AuthTest.php
│   └── TaskTest.php
│
├── docs/
│   ├── API.md
│   └── DEPLOYMENT.md
│
├── .github/
│   └── workflows/
│       └── tests.yml
│
├── .env.example
├── .gitignore
├── composer.json
├── Dockerfile
├── docker-compose.yml
└── README.md
```

---

## 13. Arrêt de l'application

Pour arrêter les conteneurs :

```bash
docker compose down
```

Pour arrêter les conteneurs tout en supprimant les volumes associés :

```bash
docker compose down -v
```

> Attention : la suppression des volumes peut entraîner la suppression des données persistantes des bases de données.

---

## 14. Conclusion

Ce projet constitue une application volontairement simple permettant de mettre en œuvre plusieurs pratiques liées au développement d'une application sécurisée : gestion des utilisateurs, accès aux données SQL et NoSQL, tests automatisés, conteneurisation, documentation et intégration continue.
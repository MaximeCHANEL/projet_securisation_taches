# Documentation de l'API

## 1. Présentation

L'application expose une API REST permettant aux utilisateurs de gérer leurs tâches.

Les routes nécessitant une authentification doivent être appelées avec les informations d'authentification prévues par l'application.

---

# 2. Utilisateurs

## POST /register

Permet de créer un compte utilisateur.

### Requête

```json
{
    "email": "utilisateur@example.com",
    "password": "MotDePasse123!"
}
```

### Réponse

```json
{
    "message": "Utilisateur créé avec succès"
}
```

### Cas d'erreur

* `400` : données invalides ;
* `409` : utilisateur déjà existant ;
* `500` : erreur interne.

---

# 3. Authentification

## POST /login

Permet à un utilisateur de s'authentifier.

### Requête

```json
{
    "email": "utilisateur@example.com",
    "password": "MotDePasse123!"
}
```

### Réponse

```json
{
    "message": "Connexion réussie",
    "token": "..."
}
```

Le mécanisme d'authentification utilisé par l'application doit être conservé côté client et transmis lors des requêtes nécessitant une authentification.

### Cas d'erreur

* `400` : données manquantes ;
* `401` : identifiants incorrects ;
* `500` : erreur interne.

---

# 4. Tâches

## GET /tasks

Retourne les tâches appartenant à l'utilisateur connecté.

### Authentification

Authentification obligatoire.

### Réponse

```json
[
    {
        "id": 1,
        "title": "Préparer le rapport",
        "description": "Finaliser la partie technique",
        "status": "todo",
        "created_at": "2026-08-19 09:00:00"
    }
]
```

### Cas d'erreur

* `401` : utilisateur non authentifié ;
* `500` : erreur interne.

---

## POST /tasks

Crée une nouvelle tâche.

### Authentification

Authentification obligatoire.

### Requête

```json
{
    "title": "Préparer le rapport",
    "description": "Finaliser la partie technique",
    "status": "todo"
}
```

### Réponse

```json
{
    "message": "Tâche créée",
    "id": 1
}
```

### Cas d'erreur

* `400` : données invalides ;
* `401` : utilisateur non authentifié ;
* `500` : erreur interne.

---

## PUT /tasks/{id}

Modifie une tâche appartenant à l'utilisateur connecté.

### Authentification

Authentification obligatoire.

### Requête

```json
{
    "title": "Rapport B3 finalisé",
    "description": "Partie technique terminée",
    "status": "done"
}
```

### Réponse

```json
{
    "message": "Tâche modifiée"
}
```

### Cas d'erreur

* `400` : données invalides ;
* `401` : utilisateur non authentifié ;
* `403` : tâche appartenant à un autre utilisateur ;
* `404` : tâche inexistante.

---

## DELETE /tasks/{id}

Supprime une tâche appartenant à l'utilisateur connecté.

### Authentification

Authentification obligatoire.

### Réponse

```json
{
    "message": "Tâche supprimée"
}
```

### Cas d'erreur

* `401` : utilisateur non authentifié ;
* `403` : tâche appartenant à un autre utilisateur ;
* `404` : tâche inexistante.

---

# 5. Historique d'activité

Les actions importantes réalisées par les utilisateurs sont enregistrées dans MongoDB.

Exemple :

```json
{
    "user_id": 1,
    "action": "CREATE_TASK",
    "task_id": 12,
    "created_at": "2026-08-19T09:30:00"
}
```

Les actions enregistrées peuvent notamment être :

```text
LOGIN
CREATE_TASK
UPDATE_TASK
DELETE_TASK
```

---

# 6. Codes HTTP utilisés

| Code | Signification             |
| ---- | ------------------------- |
| 200  | Requête réussie           |
| 201  | Ressource créée           |
| 400  | Requête invalide          |
| 401  | Non authentifié           |
| 403  | Accès interdit            |
| 404  | Ressource inexistante     |
| 409  | Conflit                   |
| 500  | Erreur interne du serveur |

---

# 7. Tests avec Postman

Les différentes routes peuvent être testées avec Postman.

Les tests doivent notamment vérifier :

* le comportement nominal ;
* les données invalides ;
* l'absence d'authentification ;
* les droits d'accès ;
* les ressources inexistantes ;
* les erreurs serveur.
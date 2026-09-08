<?php

namespace App;

use PDO;
use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;

class TaskController
{
    private PDO $pdo;
    private Collection $historiqueCollection;

    public function __construct(PDO $pdo, Collection $historiqueCollection)
    {
        $this->pdo = $pdo;
        $this->historiqueCollection = $historiqueCollection;
    }

    public function getTasks(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT *
            FROM taches
            WHERE id_utilisateurs = :userId"
        );

        $stmt->execute([
            'userId' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTask(
        int $userId,
        string $titre,
        string $description,
        string $statut
    ): array
    {
        if (empty($titre)) {
            return [
                'error' => 'Title is required'
            ];
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO taches (
                id_utilisateurs,
                titre,
                description,
                statut
            )
            VALUES (
                :id_utilisateurs,
                :titre,
                :description,
                :statut
            )"
        );

        $stmt->execute([
            'id_utilisateurs' => $userId,
            'titre' => $titre,
            'description' => $description,
            'statut' => $statut
        ]);

        // Récupération de l'ID de la tâche créée
        $taskId = (int) $this->pdo->lastInsertId();

        // Enregistrement de l'action dans MongoDB
        $this->historiqueCollection->insertOne([
            'utilisateur_id' => (int) $userId,
            'tache_id' => $taskId,
            'action' => 'creation',
            'date_action' => new UTCDateTime()
        ]);

        return [
            'message' => 'Task created successfully'
        ];
    }

    public function updateTask(int $id, int $userId, array $data): array
    {
        // Vérifier que la tâche appartient bien à l'utilisateur
        $stmt = $this->pdo->prepare(
            "SELECT id_utilisateurs FROM taches WHERE id_taches = :id"
        );
        $stmt->execute(['id' => $id]);
        $task = $stmt->fetch();

        if (!$task) {
            return ['error' => 'Task not found'];
        }

        if ((int) $task['id_utilisateurs'] !== $userId) {
            return ['error' => 'Unauthorized'];
        }

        $titre = $data['titre'] ?? null;
        $description = $data['description'] ?? null;
        $statut = $data['statut'] ?? null;

        if ($titre === null || $description === null || $statut === null) {
            return ['error' => 'Missing required fields'];
        }

        $stmt = $this->pdo->prepare(
            "UPDATE taches
            SET titre = :titre, description = :description, statut = :statut
            WHERE id_taches = :id"
        );

        $stmt->execute([
            'id' => $id,
            'titre' => $titre,
            'description' => $description,
            'statut' => $statut
        ]);

        // Enregistrement de l'action dans MongoDB
        $this->historiqueCollection->insertOne([
            'utilisateur_id' => (int) $userId,
            'tache_id' => $id,
            'action' => 'modification',
            'date_action' => new UTCDateTime()
        ]);

        return [
            'message' => 'Task updated successfully'
        ];
    }

    public function deleteTask(
        int $userId,
        string $id
    ): array
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM taches
            WHERE id_taches = :id"
        );

        $stmt->execute([
            'id' => $id
        ]);

        // Enregistrement de l'action dans MongoDB
        $this->historiqueCollection->insertOne([
            'utilisateur_id' => $userId,
            'tache_id' => (int) $id,
            'action' => 'suppression',
            'date_action' => new UTCDateTime()
        ]);

        return [
            'message' => 'Task deleted successfully'
        ];
    }
}
?>
<?php

namespace App;

use PDO;

class TaskController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getTasks(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM taches"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTask(
        int $userId,
        string $titre,
        string $description
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
            'statut' => 'a_faire'
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
            return ['error' => 'Missing titre or description'];
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

        return [
            'message' => 'Task updated successfully'
        ];
    }

    public function deleteTask(string $id): array
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM taches
            WHERE id_taches = :id"
        );

        $stmt->execute([
            'id' => $id
        ]);

        return [
            'message' => 'Task deleted successfully'
        ];
    }
}
?>
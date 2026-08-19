<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\TaskController;
use PDO;

class TaskTest extends TestCase
{
    private PDO $pdo;
    private TaskController $taskController;

    protected function setUp(): void
    {
        $this->pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                getenv('DB_HOST'),
                getenv('DB_PORT'),
                getenv('DB_DATABASE')
            ),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD')
        );

        $this->pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        $this->taskController = new TaskController($this->pdo);
    }

    private function createUser(): int
    {
        $email = 'phpunit_' . uniqid() . '@test.fr';

        $stmt = $this->pdo->prepare(
            "INSERT INTO utilisateurs (mail, mot_de_passe)
             VALUES (:mail, :mot_de_passe)"
        );

        $stmt->execute([
            'mail' => $email,
            'mot_de_passe' => password_hash(
                'Test1234!',
                PASSWORD_DEFAULT
            )
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function createTask(int $userId): int
    {
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
            'titre' => 'Tâche PHPUnit',
            'description' => 'Description de la tâche PHPUnit',
            'statut' => 'a_faire'
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function testCreateTask(): void
    {
        $userId = $this->createUser();

        $result = $this->taskController->createTask(
            $userId,
            'Tâche PHPUnit',
            'Description de la tâche PHPUnit'
        );

        $this->assertArrayNotHasKey('error', $result);
        $this->assertEquals(
            'Task created successfully',
            $result['message']
        );
    }

    public function testGetTasks(): void
    {
        $userId = $this->createUser();
        $this->createTask($userId);

        $result = $this->taskController->getTasks();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayNotHasKey('error', $result);
    }

    public function testUpdateTask(): void
    {
        $userId = $this->createUser();
        $taskId = $this->createTask($userId);

        $result = $this->taskController->updateTask(
            $taskId,
            $userId,
            [
                'titre' => 'Tâche mise à jour',
                'description' => 'Description mise à jour',
                'statut' => 'en_cours'
            ]
        );

        $this->assertArrayNotHasKey('error', $result);
        $this->assertEquals(
            'Task updated successfully',
            $result['message']
        );
    }

    public function testDeleteTask(): void
    {
        $userId = $this->createUser();
        $taskId = $this->createTask($userId);

        $result = $this->taskController->deleteTask($taskId);

        $this->assertArrayNotHasKey('error', $result);
        $this->assertEquals(
            'Task deleted successfully',
            $result['message']
        );
    }

    public function testUserCannotModifyAnotherUsersTask(): void
    {
        $user1Id = $this->createUser();
        $user2Id = $this->createUser();

        $taskId = $this->createTask($user1Id);

        $result = $this->taskController->updateTask(
            $taskId,
            $user2Id,
            [
                'titre' => 'Tâche mise à jour',
                'description' => 'Description mise à jour',
                'statut' => 'en_cours'
            ]
        );

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals(
            'Unauthorized',
            $result['error']
        );
    }
}
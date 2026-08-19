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

    public function testCreateTask(): void
    {
        $result = $this->taskController->createTask(
            1,
            "Tâche PHPUnit",
            "Description de la tâche PHPUnit"
        );

        $this->assertArrayNotHasKey('error', $result);
        $this->assertEquals(
            'Task created successfully',
            $result['message']
        );
    }

    public function testGetTasks(): void
    {
        $result = $this->taskController->getTasks();

        $this->assertNotNull($result);

        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('error', $result);
    }

    public function testUpdateTask(): void
    {
        $result = $this->taskController->updateTask(
            8,
            1,
            [
                'titre' => 'Tâche mise à jour',
                'description' => 'Description mise à jour',
                'statut' => 'en_cours'
            ]
        );

        $this->assertArrayNotHasKey('error', $result);
    }

    public function testDeleteTask(): void
    {
        $result = $this->taskController->deleteTask(
            5
        );
        
        $this->assertArrayNotHasKey('error', $result);
    }

    public function testUserCannotModifyAnotherUsersTask(): void
    {
        $result = $this->taskController->updateTask(
            7,
            2,
            [
                'titre' => 'Tâche mise à jour',
                'description' => 'Description mise à jour',
                'statut' => 'en_cours'
            ]
        );
        
        $this->assertArrayHasKey('error', $result);
    }
}

?>
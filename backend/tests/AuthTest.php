<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Auth;
use PDO;

class AuthTest extends TestCase
{
    private PDO $pdo;
    private Auth $auth;

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

        $this->auth = new Auth($this->pdo);
    }

    public function testRegister(): void
    {

        $email = 'phpunit_' . uniqid() . '@test.fr';

        $result = $this->auth->register(
            $email,
            'Test1234!'
        );

        $this->assertArrayNotHasKey('error', $result);
    }

    public function testLoginPasswordValide(): void
    {
        $result = $this->auth->login(
            'phpunit_6a845bec6484c@test.fr',
            'Test1234!'
        );

        $this->assertArrayHasKey('token', $result);
        $this->assertNotEmpty($result['token']);
    }

    public function testLoginPasswordInvalide(): void
    {
        // On crée d'abord un utilisateur avec un mot de passe connu
        $email = 'phpunit_' . uniqid() . '@test.fr';
        $password = 'Test1234!';

        $registerResult = $this->auth->register($email, $password);
        $this->assertArrayNotHasKey('error', $registerResult);

        // On tente de se connecter avec le mauvais mot de passe
        $result = $this->auth->login($email, 'MauvaisMotDePasse!');

        $this->assertArrayNotHasKey('token', $result);
        $this->assertArrayHasKey('error', $result);
    }
}

?>
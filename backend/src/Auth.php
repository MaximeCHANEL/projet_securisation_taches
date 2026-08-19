<?php

namespace App;

use PDO;

class Auth
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function register(string $email, string $password): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'error' => 'Invalid email'
            ];
        }

        if (strlen($password) < 8) {
            return [
                'error' => 'Password must contain at least 8 characters'
            ];
        }

        $stmt = $this->pdo->prepare(
            "SELECT id_utilisateurs FROM utilisateurs WHERE mail = :mail"
        );

        $stmt->execute([
            'mail' => $email
        ]);

        if ($stmt->fetch()) {
            return [
                'error' => 'Email already exists'
            ];
        }

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $this->pdo->prepare(
            "INSERT INTO utilisateurs (mail, mot_de_passe)
            VALUES (:mail, :mot_de_passe)"
        );

        $stmt->execute([
            'mail' => $email,
            'mot_de_passe' => $hashedPassword
        ]);

        return [
            'message' => 'User created successfully'
        ];
    }

    public function login(string $email, string $password): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_utilisateurs, mot_de_passe
            FROM utilisateurs
            WHERE mail = :mail"
        );

        $stmt->execute([
            'mail' => $email
        ]);

        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['mot_de_passe'])) {
            return [
                'error' => 'Invalid credentials'
            ];
        }

        $token = bin2hex(random_bytes(32));

        $tokenHash = hash('sha256', $token);

        $stmt = $this->pdo->prepare(
            "INSERT INTO sessions (token_hash, id_utilisateurs)
            VALUES (:token_hash, :id_utilisateurs)"
        );

        $stmt->execute([
            'token_hash' => $tokenHash,
            'id_utilisateurs' => $user['id_utilisateurs']
        ]);

        return [
            'token' => $token
        ];
    }

    public function authenticate(): ?int
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return null;
        }

        $authorization = $headers['Authorization'];

        if (!str_starts_with($authorization, 'Bearer ')) {
            return null;
        }

        $token = substr($authorization, 7);

        $tokenHash = hash('sha256', $token);

        $stmt = $this->pdo->prepare(
            "SELECT id_utilisateurs
            FROM sessions
            WHERE token_hash = :token_hash"
        );

        $stmt->execute([
            'token_hash' => $tokenHash
        ]);

        $session = $stmt->fetch();

        if (!$session) {
            return null;
        }

        return (int) $session['id_utilisateurs'];
    }
}

?>
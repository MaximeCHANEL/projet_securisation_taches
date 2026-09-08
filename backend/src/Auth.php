<?php

namespace App;

use PDO;
use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;

class Auth
{
    private PDO $pdo;

    private Collection $mongoCollection;

    public function __construct(PDO $pdo, Collection $mongoCollection)
    {
        $this->pdo = $pdo;
        $this->mongoCollection = $mongoCollection;
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

        // Identifiants incorrects
        if (!$user || !password_verify($password, $user['mot_de_passe'])) {

            // Enregistrement de l'échec dans MongoDB
            $this->mongoCollection->insertOne([
                'email' => $email,
                'action' => 'connexion',
                'resultat' => 'echec',
                'date_connexion' => new UTCDateTime()
            ]);

            return [
                'error' => 'Invalid credentials'
            ];
        }

        // Génération du token
        $token = bin2hex(random_bytes(32));

        $tokenHash = hash('sha256', $token);

        // Expiration du token au bout d'une heure
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $stmt = $this->pdo->prepare(
            "INSERT INTO sessions (
                token_hash,
                id_utilisateurs,
                expires_at
            )
            VALUES (
                :token_hash,
                :id_utilisateurs,
                :expires_at
            )"
        );

        $stmt->execute([
            'token_hash' => $tokenHash,
            'id_utilisateurs' => $user['id_utilisateurs'],
            'expires_at' => $expiresAt
        ]);

        // Enregistrement de la connexion réussie dans MongoDB
        $this->mongoCollection->insertOne([
            'utilisateur_id' => (int) $user['id_utilisateurs'],
            'email' => $email,
            'action' => 'connexion',
            'resultat' => 'succes',
            'date_connexion' => new UTCDateTime()
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
            WHERE token_hash = :token_hash
            AND expires_at > NOW()"
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

    public function isAdmin(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT role
            FROM utilisateurs
            WHERE id_utilisateurs = :id"
        );

        $stmt->execute(['id' => $userId]);

        $user = $stmt->fetch();

        return $user && $user['role'] === 'admin';
    }

    public function logout(): bool
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return false;
        }

        $authorization = $headers['Authorization'];

        if (!str_starts_with($authorization, 'Bearer ')) {
            return false;
        }

        $token = substr($authorization, 7);

        $tokenHash = hash('sha256', $token);

        $stmt = $this->pdo->prepare(
            "DELETE FROM sessions
            WHERE token_hash = :token_hash"
        );

        $stmt->execute([
            'token_hash' => $tokenHash
        ]);

        return $stmt->rowCount() > 0;
    }
}

?>
<?php

$allowedOrigins = [
    'http://localhost:5500',
    'http://89.168.60.68:5500'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Auth;
use App\TaskController;

header('Content-Type: application/json');

// Fonction permettant de renvoyer une réponse JSON
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

// Récupération de la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Récupération de l'URL sans les paramètres

$url = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
);

// Connexion à la base de données
$database = new Database();
$pdo = $database->getConnection();

// Création des classes
$auth = new Auth($pdo);
$taskController = new TaskController($pdo);

/*
|--------------------------------------------------------------------------
| POST /register
|--------------------------------------------------------------------------
*/

if ($method === 'POST' && $url ==='/register') {
    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($data)) {
        jsonResponse([
            'error' => 'Invalid JSON'
        ], 400);
    }

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $result = $auth->register(
        $email,
        $password
    );

    if (isset($result['error'])) {
        jsonResponse($result, 400);
    }

    jsonResponse($result, 201);
}

/*
|--------------------------------------------------------------------------
| POST /login
|--------------------------------------------------------------------------
*/

if ($method === 'POST' && $url ==='/login') {
    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($data)) {
        jsonResponse([
            'error' => 'Invalid JSON'
        ], 400);
    }

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $result = $auth->login(
        $email,
        $password
    );

    if (isset($result['error'])) {
        jsonResponse($result, 400);
    }

    jsonResponse($result, 201);
}

/*
|--------------------------------------------------------------------------
| Vérification du token pour /tasks
|--------------------------------------------------------------------------
*/

if (
    str_starts_with($url, '/tasks')
) {
    $userId = $auth->authenticate();

    if ($userId === null) {
        jsonResponse([
            'error' => 'Unauthorized'
        ], 401);
    }
}

/*
|--------------------------------------------------------------------------
| GET /tasks
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && $url ==='/tasks') {

    $tasks = $taskController->getTasks($userId);

    jsonResponse([
        'data' => $tasks
    ]);
}

/*
|--------------------------------------------------------------------------
| POST /tasks
|--------------------------------------------------------------------------
*/

if ($method === 'POST' && $url ==='/tasks') {
    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($data)) {
        jsonResponse([
            'error' => 'Invalid JSON'
        ], 400);
    }

    $result = $taskController->createTask(
        $userId,
        $data['titre'],
        $data['description']
    );

    if (isset($result['error'])) {
        jsonResponse($result, 400);
    }

    jsonResponse($result, 201);
}

/*
|--------------------------------------------------------------------------
| PUT /tasks{id}
|--------------------------------------------------------------------------
*/

if (
    $method === 'PUT'
    && preg_match('#^/tasks/(\d+)$#', $url, $matches)
) {
    $taskId = (int) $matches[1];

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($data)) {
        jsonResponse([
            'error' => 'Invalid JSON'
        ], 400);
    }

    $result = $taskController->updateTask(
        $taskId,
        $userId,
        $data
    );

    if (isset($result['error'])) {
        jsonResponse($result, 400);
    }

    jsonResponse($result);
}

/*
|--------------------------------------------------------------------------
| DELETE /tasks{id}
|--------------------------------------------------------------------------
*/

if (
    $method === 'DELETE'
    && preg_match('#^/tasks/(\d+)$#', $url, $matches)
) {
    $taskId = (int) $matches[1];

    $result = $taskController->deleteTask(
        $taskId,
        $userId
    );

    if (isset($result['error'])) {
        jsonResponse($result, 400);
    }

    jsonResponse($result);
}

/*
|--------------------------------------------------------------------------
| AUTRE ROUTES (INEXISTANTES)
|--------------------------------------------------------------------------
*/

jsonResponse([
    'error' => 'Not Found'
], 404);


?>
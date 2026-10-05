<?php
/**
 * RESTful JSON API: Suchinta's Guardian Angel
 * 
 * Handles retrieval, addition, and deletion of advice records.
 */

// 1. JSON & CORS Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 2. Include database connection
$pdo = require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

// Determine requested action
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? null;

if (!$action) {
    if ($method === 'GET') {
        $action = 'get_all';
    } elseif ($method === 'POST') {
        $action = 'add';
    } elseif ($method === 'DELETE') {
        $action = 'delete';
    }
}

try {
    switch ($action) {
        // GET request (action=get_all)
        case 'get_all':
            $stmt = $pdo->query('SELECT id, content FROM advices ORDER BY id ASC');
            $advices = $stmt->fetchAll();

            $stmtPro = $pdo->query('SELECT id, content FROM pro_advices ORDER BY id ASC');
            $proAdvices = $stmtPro->fetchAll();

            echo json_encode([
                'normal' => $advices,
                'pro'    => $proAdvices
            ]);
            break;

        // GET request (action=get_pro)
        case 'get_pro':
            $stmtPro = $pdo->query('SELECT id, content FROM pro_advices ORDER BY id ASC');
            $proAdvices = $stmtPro->fetchAll();
            echo json_encode($proAdvices);
            break;

        // POST request (action=add)
        case 'add':
            $content = trim(strip_tags($input['content'] ?? $_POST['content'] ?? ''));

            if ($content === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Content cannot be empty.']);
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO advices (content) VALUES (:content)');
            $stmt->execute([':content' => $content]);

            echo json_encode([
                'success' => true,
                'id'      => (int)$pdo->lastInsertId(),
                'message' => 'Advice added successfully.'
            ]);
            break;

        // POST or DELETE request (action=delete)
        case 'delete':
            $id = filter_var($input['id'] ?? $_POST['id'] ?? $_GET['id'] ?? null, FILTER_VALIDATE_INT);

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Valid advice ID is required.']);
                exit;
            }

            $stmt = $pdo->prepare('DELETE FROM advices WHERE id = :id');
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Advice not found.']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Advice deleted successfully.'
            ]);
            break;

        // POST request (action=add_pro)
        case 'add_pro':
            $content = trim(strip_tags($input['content'] ?? $_POST['content'] ?? ''));

            if ($content === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Pro advice content cannot be empty.']);
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO pro_advices (content) VALUES (:content)');
            $stmt->execute([':content' => $content]);

            echo json_encode([
                'success' => true,
                'id'      => (int)$pdo->lastInsertId(),
                'message' => 'Pro advice added successfully.'
            ]);
            break;

        // POST or DELETE request (action=delete_pro)
        case 'delete_pro':
            $id = filter_var($input['id'] ?? $_POST['id'] ?? $_GET['id'] ?? null, FILTER_VALIDATE_INT);

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Valid pro advice ID is required.']);
                exit;
            }

            $stmt = $pdo->prepare('DELETE FROM pro_advices WHERE id = :id');
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Pro advice not found.']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Pro advice deleted successfully.'
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action or request method.']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

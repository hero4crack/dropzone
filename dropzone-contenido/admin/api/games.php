<?php
header('Content-Type: application/json');

// Si necesitas permitir peticiones desde otros orígenes (AJAX)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Corrige el path del require_once
require_once __DIR__ . '/../../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // CREAR O ACTUALIZAR JUEGO
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $image_url = trim($_POST['image_url'] ?? '');
    $background_image = trim($_POST['background_image'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $gameId = $_POST['gameId'] ?? null;

    // Validaciones
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'El nombre del juego es requerido']);
        exit;
    }

    if (empty($category_id)) {
        echo json_encode(['success' => false, 'message' => 'La categoría es requerida']);
        exit;
    }

    try {
        if ($action === 'create') {
            $query = "INSERT INTO games (name, description, category_id, image_url, background_image, featured, is_active, created_at, updated_at) 
                     VALUES (:name, :description, :category_id, :image_url, :background_image, :featured, :is_active, NOW(), NOW())";
            $stmt = $db->prepare($query);
        } else if ($action === 'update' && !empty($gameId)) {
            $query = "UPDATE games SET 
                     name = :name, 
                     description = :description, 
                     category_id = :category_id, 
                     image_url = :image_url, 
                     background_image = :background_image, 
                     featured = :featured, 
                     is_active = :is_active, 
                     updated_at = NOW() 
                     WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $gameId, PDO::PARAM_INT);
        } else {
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit;
        }

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':background_image', $background_image);
        $stmt->bindParam(':featured', $featured, PDO::PARAM_INT);
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $message = $action === 'create' ? 'Juego creado correctamente' : 'Juego actualizado correctamente';
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta']);
        }

    } catch(PDOException $e) {
        error_log("Error en games.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    }

} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    $gameId = $_GET['id'] ?? null;

    if ($action === 'get') {
        // OBTENER JUEGO POR ID
        if (empty($gameId)) {
            echo json_encode(['success' => false, 'message' => 'ID no especificado']);
            exit;
        }

        try {
            $stmt = $db->prepare("SELECT * FROM games WHERE id = :id");
            $stmt->bindParam(':id', $gameId, PDO::PARAM_INT);
            $stmt->execute();
            
            $game = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($game) {
                echo json_encode([
                    'success' => true,
                    'data' => $game
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Juego no encontrado']);
            }
        } catch(PDOException $e) {
            error_log("Error en games.php (get): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener el juego: ' . $e->getMessage()]);
        }

    } elseif ($action === 'delete') {
        // ELIMINAR JUEGO
        if (empty($gameId)) {
            echo json_encode(['success' => false, 'message' => 'ID no especificado']);
            exit;
        }

        try {
            // Primero eliminar productos relacionados
            $stmt = $db->prepare("DELETE FROM products WHERE game_id = :id");
            $stmt->bindParam(':id', $gameId, PDO::PARAM_INT);
            $stmt->execute();

            // Luego eliminar el juego
            $stmt = $db->prepare("DELETE FROM games WHERE id = :id");
            $stmt->bindParam(':id', $gameId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Juego y sus productos eliminados correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el juego']);
            }
        } catch(PDOException $e) {
            error_log("Error en games.php (delete): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // CREAR O ACTUALIZAR CATEGORÍA
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $categoryId = $_POST['categoryId'] ?? '';

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
        exit;
    }

    try {
        if ($action === 'create') {
            $query = "INSERT INTO categories (name, description, icon) VALUES (:name, :description, :icon)";
            $stmt = $db->prepare($query);
        } else if ($action === 'update') {
            $query = "UPDATE categories SET name = :name, description = :description, icon = :icon WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $categoryId);
        } else {
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit;
        }

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':icon', $icon);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Categoría guardada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
        }

    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    $categoryId = $_GET['id'] ?? '';

    if ($action === 'get') {
        // OBTENER CATEGORÍA POR ID
        if (empty($categoryId)) {
            echo json_encode(['success' => false, 'message' => 'ID no especificado']);
            exit;
        }

        try {
            $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
            $stmt->bindParam(':id', $categoryId);
            $stmt->execute();
            
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($category) {
                // Devolver la categoría directamente, sin envolver en 'success'
                echo json_encode($category);
            } else {
                echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            }
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }

    } elseif ($action === 'delete') {
        // ELIMINAR CATEGORÍA
        if (empty($categoryId)) {
            echo json_encode(['success' => false, 'message' => 'ID no especificado']);
            exit;
        }

        try {
            // Verificar si hay juegos usando esta categoría
            $stmt = $db->prepare("SELECT COUNT(*) FROM games WHERE category_id = :id");
            $stmt->bindParam(':id', $categoryId);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar: hay juegos usando esta categoría']);
            } else {
                $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
                $stmt->bindParam(':id', $categoryId);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Categoría eliminada correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar la categoría']);
                }
            }
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}
?>
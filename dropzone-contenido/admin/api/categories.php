<?php
header('Content-Type: application/json');

// Habilitar mostrar errores (solo para desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORREGIR RUTA - Basado en tu estructura de carpetas
$configFile = __DIR__ . '/../../../config/database.php';

// Verificar si el archivo de configuración existe
if (!file_exists($configFile)) {
    // Debug: mostrar información de la estructura de carpetas
    $debugInfo = [
        'current_dir' => __DIR__,
        'config_file_attempted' => $configFile,
        'files_in_config_dir' => file_exists(dirname($configFile)) ? scandir(dirname($configFile)) : 'Directorio no existe'
    ];
    
    echo json_encode([
        'success' => false, 
        'message' => 'Archivo de configuración no encontrado: ' . $configFile,
        'debug' => $debugInfo
    ]);
    exit;
}

require_once $configFile;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('No se pudo establecer conexión con la base de datos');
    }
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
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

    // Validaciones
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'El nombre de la categoría es requerido']);
        exit;
    }

    try {
        if ($action === 'create') {
            $query = "INSERT INTO categories (name, description, icon, created_at) VALUES (:name, :description, :icon, NOW())";
            $stmt = $db->prepare($query);
        } else if ($action === 'update' && !empty($categoryId)) {
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
            $message = $action === 'create' ? 'Categoría creada correctamente' : 'Categoría actualizada correctamente';
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
        }

    } catch(PDOException $e) {
        error_log("Error en categories.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
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
                // ESTRUCTURA CONSISTENTE: siempre devolver con 'success' y 'data'
                echo json_encode([
                    'success' => true,
                    'data' => $category
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            }
        } catch(PDOException $e) {
            error_log("Error en categories.php (get): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener la categoría: ' . $e->getMessage()]);
        }

    } elseif ($action === 'delete') {
        // ELIMINAR CATEGORÍA
        if (empty($categoryId)) {
            echo json_encode(['success' => false, 'message' => 'ID no especificado']);
            exit;
        }

        try {
            // Verificar si hay juegos usando esta categoría
            $stmt = $db->prepare("SELECT COUNT(*) as game_count FROM games WHERE category_id = :id");
            $stmt->bindParam(':id', $categoryId);
            $stmt->execute();
            $gameCount = $stmt->fetchColumn();
            
            if ($gameCount > 0) {
                echo json_encode(['success' => false, 'message' => "No se puede eliminar: hay $gameCount juego(s) usando esta categoría"]);
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
            error_log("Error en categories.php (delete): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
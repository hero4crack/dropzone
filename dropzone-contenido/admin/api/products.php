<?php
header('Content-Type: application/json');

// Habilitar mostrar errores (solo para desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORREGIR RUTAS - Diferentes formas de encontrar el archivo de configuración
$configFile = '';

// Opción 1: Ruta relativa desde admin/api/
$configFile = __DIR__ . '/../../config/database.php';

// Opción 2: Si la opción 1 no funciona, probar esta
if (!file_exists($configFile)) {
    $configFile = dirname(__DIR__, 2) . '/config/database.php';
}

// Opción 3: Ruta absoluta (ajusta según tu instalación)
if (!file_exists($configFile)) {
    $configFile = 'C:/xampp/htdocs/dropzone/config/database.php';
}

// Verificar si el archivo de configuración existe
if (!file_exists($configFile)) {
    // Listar archivos para debug
    $debugInfo = [
        'current_dir' => __DIR__,
        'config_file_attempted' => $configFile,
        'files_in_current_dir' => scandir(__DIR__),
        'files_in_parent_dir' => scandir(dirname(__DIR__))
    ];
    
    error_log("Debug info: " . print_r($debugInfo, true));
    
    echo json_encode([
        'success' => false, 
        'message' => 'Archivo de configuración no encontrado. Buscado en: ' . $configFile,
        'debug' => $debugInfo
    ]);
    exit;
}

// Incluir el archivo de configuración
require_once $configFile;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar si la conexión es válida
    if (!$db) {
        throw new Exception('No se pudo establecer conexión con la base de datos');
    }
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // CREAR O ACTUALIZAR PRODUCTO
    $action = $_POST['action'] ?? '';
    $game_id = $_POST['game_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $currency_amount = trim($_POST['currency_amount'] ?? '');
    $price = $_POST['price'] ?? '';
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $productId = $_POST['productId'] ?? '';

    // Validaciones
    if (empty($name) || empty($currency_amount) || empty($price) || empty($game_id)) {
        echo json_encode(['success' => false, 'message' => 'Nombre, cantidad, precio y juego son requeridos']);
        exit;
    }

    // Validar que el precio sea numérico
    if (!is_numeric($price) || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'El precio debe ser un número válido mayor a 0']);
        exit;
    }

    try {
        if ($action === 'create') {
            $query = "INSERT INTO products (game_id, name, description, currency_amount, price, currency, is_available, sort_order) 
                     VALUES (:game_id, :name, :description, :currency_amount, :price, :currency, :is_available, :sort_order)";
            $stmt = $db->prepare($query);
        } else if ($action === 'update' && !empty($productId)) {
            $query = "UPDATE products SET 
                     game_id = :game_id,
                     name = :name, 
                     description = :description, 
                     currency_amount = :currency_amount, 
                     price = :price,
                     currency = :currency,
                     is_available = :is_available,
                     sort_order = :sort_order
                     WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $productId);
        } else {
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit;
        }

        // Valores por defecto para campos adicionales
        $currency = 'USD';
        $sort_order = 0;

        $stmt->bindParam(':game_id', $game_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':currency_amount', $currency_amount);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':currency', $currency);
        $stmt->bindParam(':is_available', $is_available, PDO::PARAM_INT);
        $stmt->bindParam(':sort_order', $sort_order, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $message = $action === 'create' ? 'Producto creado correctamente' : 'Producto actualizado correctamente';
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta']);
        }

    } catch(PDOException $e) {
        error_log("Error en products.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    }

} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    $productId = $_GET['id'] ?? '';
    $gameId = $_GET['game_id'] ?? '';

    if ($action === 'get') {
        // OBTENER PRODUCTO POR ID
        if (empty($productId)) {
            echo json_encode(['success' => false, 'message' => 'ID no especificado']);
            exit;
        }

        try {
            $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->bindParam(':id', $productId);
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                echo json_encode([
                    'success' => true,
                    'data' => $product
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            }
        } catch(PDOException $e) {
            error_log("Error en products.php (get): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener el producto: ' . $e->getMessage()]);
        }

    } elseif ($action === 'get_game_products') {
        // OBTENER PRODUCTOS POR JUEGO
        if (empty($gameId)) {
            echo json_encode(['success' => false, 'message' => 'Game ID no especificado']);
            exit;
        }

        try {
            // Primero obtener información del juego
            $stmt = $db->prepare("SELECT id, name FROM games WHERE id = :game_id");
            $stmt->bindParam(':game_id', $gameId);
            $stmt->execute();
            $game = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$game) {
                echo json_encode(['success' => false, 'message' => 'Juego no encontrado']);
                exit;
            }

            // Luego obtener productos del juego
            $stmt = $db->prepare("SELECT * FROM products WHERE game_id = :game_id ORDER BY sort_order, price ASC");
            $stmt->bindParam(':game_id', $gameId);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'game' => $game,
                'products' => $products
            ]);

        } catch(PDOException $e) {
            error_log("Error en products.php (get_game_products): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener productos: ' . $e->getMessage()]);
        }

    } elseif ($action === 'delete') {
        // ELIMINAR PRODUCTO
        if (empty($productId)) {
            echo json_encode(['success' => false, 'message' => 'ID no especificado']);
            exit;
        }

        try {
            $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
            $stmt->bindParam(':id', $productId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto']);
            }
        } catch(PDOException $e) {
            error_log("Error en products.php (delete): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
    // CREAR O ACTUALIZAR PRODUCTO
    $action = $_POST['action'] ?? '';
    $game_id = $_POST['game_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $currency_amount = trim($_POST['currency_amount'] ?? '');
    $price = $_POST['price'] ?? null;
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $productId = $_POST['productId'] ?? null;

    // Validaciones
    if (empty($name) || empty($currency_amount) || $price === null || $game_id === null) {
        echo json_encode(['success' => false, 'message' => 'Nombre, cantidad, precio y juego son requeridos']);
        exit;
    }

    try {
        if ($action === 'create') {
            $query = "INSERT INTO products (game_id, name, description, currency_amount, price, is_available) 
                      VALUES (:game_id, :name, :description, :currency_amount, :price, :is_available)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':game_id', $game_id, PDO::PARAM_INT);
        } else if ($action === 'update' && !empty($productId)) {
            $query = "UPDATE products SET 
                        name = :name, 
                        description = :description, 
                        currency_amount = :currency_amount, 
                        price = :price, 
                        is_available = :is_available 
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        } else {
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit;
        }

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':currency_amount', $currency_amount);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':is_available', $is_available, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $message = $action === 'create' ? 'Producto creado correctamente' : 'Producto actualizado correctamente';
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
        }

    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    }

} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    $productId = $_GET['id'] ?? null;
    $gameId = $_GET['game_id'] ?? null;

    if ($action === 'get') {
        // OBTENER PRODUCTO POR ID
        if (empty($productId)) {
            echo json_encode(['success' => false, 'message' => 'ID no especificado']);
            exit;
        }

        try {
            $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                echo json_encode($product);
            } else {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            }
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al obtener el producto: ' . $e->getMessage()]);
        }

    } elseif ($action === 'get_game_products') {
        // OBTENER PRODUCTOS DE UN JUEGO
        if (empty($gameId)) {
            echo json_encode(['success' => false, 'message' => 'ID del juego no especificado']);
            exit;
        }

        try {
            // Obtener información del juego
            $stmt = $db->prepare("SELECT * FROM games WHERE id = :id");
            $stmt->bindParam(':id', $gameId, PDO::PARAM_INT);
            $stmt->execute();
            $game = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$game) {
                echo json_encode(['success' => false, 'message' => 'Juego no encontrado']);
                exit;
            }

            // Obtener productos del juego
            $stmt = $db->prepare("SELECT * FROM products WHERE game_id = :game_id ORDER BY price");
            $stmt->bindParam(':game_id', $gameId, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Siempre devolver success
            echo json_encode([
                'success' => true,
                'game' => $game,
                'products' => $products
            ]);
        } catch(PDOException $e) {
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
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto']);
            }
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
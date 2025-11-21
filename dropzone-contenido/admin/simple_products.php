<?php
require_once '../../includes/session.php';
require_once '../../config/database.php';

$sessionManager = new SessionManager();
$user = $sessionManager->getUserData();

if (!$user) {
    header('Location: ../dropzone-login/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener todos los juegos
$games = $db->query("SELECT id, name FROM games ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Si se envió el formulario
if ($_POST['action'] ?? '' === 'add_product') {
    $game_id = $_POST['game_id'];
    $name = $_POST['name'];
    $currency_amount = $_POST['currency_amount'];
    $price = $_POST['price'];
    
    try {
        $stmt = $db->prepare("INSERT INTO products (game_id, name, currency_amount, price, currency, is_available) 
                             VALUES (?, ?, ?, ?, 'USD', 1)");
        $stmt->execute([$game_id, $name, $currency_amount, $price]);
        $message = "✅ Producto agregado exitosamente!";
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}

// Obtener productos existentes
$products = [];
if ($_GET['game_id'] ?? '') {
    $game_id = $_GET['game_id'];
    $products = $db->prepare("SELECT * FROM products WHERE game_id = ? ORDER BY price");
    $products->execute([$game_id]);
    $products = $products->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestión Simple de Productos</title>
    <style>
        body { font-family: Arial; background: #1a1a1a; color: white; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin: 10px 0; }
        input, select { padding: 8px; width: 100%; margin: 5px 0; }
        button { background: #C8A032; color: black; padding: 10px 20px; border: none; cursor: pointer; }
        .product { background: #333; padding: 10px; margin: 5px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛍️ Gestión Simple de Productos</h1>
        
        <?php if (isset($message)) echo "<p>$message</p>"; ?>
        
        <h2>Seleccionar Juego</h2>
        <select id="gameSelect" onchange="window.location.href = '?game_id=' + this.value">
            <option value="">-- Selecciona un juego --</option>
            <?php foreach ($games as $game): ?>
                <option value="<?= $game['id'] ?>" <?= ($_GET['game_id'] ?? '') == $game['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($game['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($_GET['game_id'] ?? ''): ?>
            <?php 
            $current_game = $db->prepare("SELECT name FROM games WHERE id = ?");
            $current_game->execute([$_GET['game_id']]);
            $game_name = $current_game->fetchColumn();
            ?>
            
            <h2>Agregar Producto a: <?= htmlspecialchars($game_name) ?></h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_product">
                <input type="hidden" name="game_id" value="<?= $_GET['game_id'] ?>">
                
                <div class="form-group">
                    <label>Nombre del Producto:</label>
                    <input type="text" name="name" placeholder="Ej: 1000 V-Bucks" required>
                </div>
                
                <div class="form-group">
                    <label>Cantidad:</label>
                    <input type="text" name="currency_amount" placeholder="Ej: 1000" required>
                </div>
                
                <div class="form-group">
                    <label>Precio (Bs.):</label>
                    <input type="number" name="price" step="0.01" placeholder="Ej: 5.00" required>
                </div>
                
                <button type="submit">➕ Agregar Producto</button>
            </form>

            <h2>Productos Existentes</h2>
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product">
                        <strong><?= htmlspecialchars($product['name']) ?></strong><br>
                        Cantidad: <?= htmlspecialchars($product['currency_amount']) ?><br>
                        Precio: <?= number_format($product['price'], 2) ?> Bs.<br>
                        <small>ID: <?= $product['id'] ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay productos para este juego.</p>
            <?php endif; ?>
        <?php endif; ?>
        
        <br>
        <a href="index.php" style="color: #C8A032;">← Volver al Panel Principal</a>
    </div>
</body>
</html>
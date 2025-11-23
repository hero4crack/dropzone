<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$game_id = $_GET['game_id'] ?? 0;

// Obtener información del juego
$stmt = $db->prepare("
    SELECT g.*, c.name as category_name 
    FROM games g 
    LEFT JOIN categories c ON g.category_id = c.id 
    WHERE g.id = :game_id
");
$stmt->bindParam(':game_id', $game_id);
$stmt->execute();
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    die("Juego no encontrado");
}

// Obtener productos del juego
$stmt = $db->prepare("
    SELECT * FROM products 
    WHERE game_id = :game_id AND is_available = 1 
    ORDER BY price ASC
");
$stmt->bindParam(':game_id', $game_id);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Imágenes por defecto
$default_game_image = 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80';
$default_background = 'https://images.unsplash.com/photo-1542751110-97427bbecf20?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['name']); ?> - Precios | DROPZONE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --black: #000000;
            --dark-gray: #1A1A1A;
            --white: #FFFFFF;
            --gold: #C8A032;
            --medium-gray: #333333;
            --accent: #FF5E14;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--black);
            color: var(--white);
            font-family: 'Exo 2', sans-serif;
            min-height: 100vh;
        }
        
        /* Hero Section */
        .game-hero {
            position: relative;
            height: 50vh;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('<?php echo !empty($game['background_image']) ? htmlspecialchars($game['background_image']) : $default_background; ?>');
            background-size: cover;
            background-position: center;
            z-index: 1;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.7) 100%);
            z-index: 2;
        }
        
        .hero-content {
            position: relative;
            z-index: 3;
            text-align: center;
            max-width: 600px;
            padding: 0 2rem;
        }
        
        .game-avatar {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            background-image: url('<?php echo !empty($game['image_url']) ? htmlspecialchars($game['image_url']) : $default_game_image; ?>');
            background-size: cover;
            background-position: center;
            margin: 0 auto 1rem;
            border: 2px solid var(--gold);
            box-shadow: 0 4px 15px rgba(200, 160, 50, 0.3);
        }
        
        .game-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .game-category {
            font-size: 1.1rem;
            color: var(--gold);
            margin-bottom: 0.5rem;
        }
        
        /* Main Content */
        .main-content {
            position: relative;
            z-index: 10;
            background: var(--black);
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Products Grid - MEJOR ORGANIZADO */
        .products-section {
            margin: 2rem 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
            color: var(--white);
            font-family: 'Orbitron', sans-serif;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.2rem;
            justify-items: center;
        }
        
        /* Para 3 columnas en pantallas grandes */
        @media (min-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        /* Para 2 columnas en tablets */
        @media (max-width: 767px) and (min-width: 481px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        /* Para 1 columna en móviles */
        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
                max-width: 300px;
                margin: 0 auto;
            }
        }
        
        .product-item {
            background: var(--dark-gray);
            border: 2px solid var(--medium-gray);
            border-radius: 12px;
            padding: 1.8rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            width: 100%;
            max-width: 200px;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .product-item:hover {
            border-color: var(--gold);
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(200, 160, 50, 0.2);
        }
        
        .product-item.selected {
            border-color: var(--gold);
            background: linear-gradient(135deg, var(--dark-gray) 0%, #2a2a2a 100%);
            box-shadow: 0 8px 25px rgba(200, 160, 50, 0.3);
            transform: translateY(-3px);
        }
        
        .currency-amount {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--gold);
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 0.8rem;
            line-height: 1.2;
        }
        
        .product-price {
            font-size: 1.1rem;
            color: var(--white);
            opacity: 0.9;
            font-weight: 500;
        }
        
        .selected-indicator {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--gold);
            color: var(--black);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(200, 160, 50, 0.4);
        }
        
        /* Purchase Panel */
        .purchase-panel {
            background: var(--dark-gray);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid var(--medium-gray);
            margin-top: 2rem;
            display: none;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .purchase-panel.active {
            display: block;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .selected-product-info {
            flex: 1;
        }
        
        .selected-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--gold);
            font-family: 'Orbitron', sans-serif;
        }
        
        .selected-price {
            font-size: 1.2rem;
            color: var(--white);
            opacity: 0.9;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .quantity-btn {
            background: var(--medium-gray);
            color: var(--white);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 10px;
            font-size: 1.3rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-btn:hover {
            background: var(--gold);
            color: var(--black);
        }
        
        .quantity-display {
            font-size: 1.8rem;
            font-weight: bold;
            min-width: 70px;
            text-align: center;
            font-family: 'Orbitron', sans-serif;
            color: var(--gold);
        }
        
        .total-section {
            background: var(--black);
            border-radius: 12px;
            padding: 1.8rem;
            margin: 2rem 0;
            text-align: center;
            border: 2px solid var(--gold);
        }
        
        .total-label {
            font-size: 1.1rem;
            color: var(--white);
            opacity: 0.8;
            margin-bottom: 0.8rem;
        }
        
        .total-amount {
            font-size: 2.2rem;
            font-weight: bold;
            color: var(--gold);
            font-family: 'Orbitron', sans-serif;
        }
        
        .buy-button {
            display: block;
            width: 100%;
            background: var(--gold);
            color: var(--black);
            padding: 1.3rem;
            border: none;
            border-radius: 12px;
            font-size: 1.3rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Exo 2', sans-serif;
        }
        
        .buy-button:hover {
            background: var(--white);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(200, 160, 50, 0.4);
        }
        
        .buy-button:disabled {
            background: var(--medium-gray);
            color: var(--white);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* No Products */
        .no-products {
            text-align: center;
            padding: 3rem 2rem;
            background: var(--dark-gray);
            border-radius: 15px;
            border: 2px dashed var(--medium-gray);
            max-width: 500px;
            margin: 0 auto;
        }
        
        .no-products i {
            font-size: 2rem;
            color: var(--gold);
            margin-bottom: 1rem;
        }
        
        /* Navigation */
        .back-nav {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--medium-gray);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--gold);
            text-decoration: none;
            font-weight: bold;
            padding: 0.8rem 1.5rem;
            border: 2px solid var(--gold);
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            background: var(--gold);
            color: var(--black);
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="game-hero">
        <div class="hero-background"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="game-avatar"></div>
            <h1 class="game-title"><?php echo htmlspecialchars($game['name']); ?></h1>
            <div class="game-category"><?php echo htmlspecialchars($game['category_name']); ?></div>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php if (count($products) > 0): ?>
                <!-- Sección de Productos -->
                <section class="products-section">
                    <h2 class="section-title">🎯 Selecciona tu Paquete</h2>
                    
                    <!-- Grid de Productos Organizado -->
                    <div class="products-grid" id="productsGrid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-item" 
                                 data-amount="<?php echo htmlspecialchars($product['currency_amount']); ?>"
                                 data-price="<?php echo $product['price']; ?>"
                                 data-product-id="<?php echo $product['id']; ?>">
                                <div class="currency-amount"><?php echo htmlspecialchars($product['currency_amount']); ?></div>
                                <div class="product-price"><?php echo number_format($product['price'], 2); ?> Bs.</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                
                <!-- Panel de Compra (oculto inicialmente) -->
                <div class="purchase-panel" id="purchasePanel">
                    <div class="panel-header">
                        <div class="selected-product-info">
                            <div class="selected-amount" id="selectedAmount"></div>
                            <div class="selected-price" id="selectedPrice"></div>
                        </div>
                    </div>
                    
                    <div class="quantity-controls">
                        <button class="quantity-btn" id="decreaseBtn">-</button>
                        <div class="quantity-display" id="quantityDisplay">1</div>
                        <button class="quantity-btn" id="increaseBtn">+</button>
                    </div>
                    
                    <div class="total-section">
                        <div class="total-label">Total a Pagar:</div>
                        <div class="total-amount" id="totalAmount">0.00 Bs.</div>
                    </div>
                    
                    <button class="buy-button" id="buyButton">
                        <i class="fas fa-bolt"></i> Comprar Ahora
                    </button>
                </div>
            <?php else: ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h3>Próximamente...</h3>
                    <p>Estamos preparando los mejores paquetes para ti.</p>
                </div>
            <?php endif; ?>
            
            <!-- Navigation -->
            <div class="back-nav">
                <a href="index.php#products" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Volver a Todos los Juegos
                </a>
            </div>
        </div>
    </main>

    <script>
        let selectedProduct = null;
        let quantity = 1;
        let unitPrice = 0;

        // Seleccionar producto
        document.querySelectorAll('.product-item').forEach(item => {
            item.addEventListener('click', function() {
                // Remover selección anterior
                document.querySelectorAll('.product-item').forEach(i => {
                    i.classList.remove('selected');
                    i.querySelector('.selected-indicator')?.remove();
                });
                
                // Seleccionar nuevo
                this.classList.add('selected');
                
                // Agregar indicador
                const indicator = document.createElement('div');
                indicator.className = 'selected-indicator';
                indicator.innerHTML = '✓';
                this.appendChild(indicator);
                
                // Actualizar datos del producto seleccionado
                selectedProduct = {
                    amount: this.dataset.amount,
                    price: parseFloat(this.dataset.price),
                    productId: this.dataset.productId
                };
                
                unitPrice = selectedProduct.price;
                quantity = 1;
                
                // Actualizar UI
                updatePurchasePanel();
                calculateTotal();
                
                // Mostrar panel
                document.getElementById('purchasePanel').classList.add('active');
                
                // Scroll suave al panel
                document.getElementById('purchasePanel').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'center'
                });
            });
        });

        // Controles de cantidad
        document.getElementById('increaseBtn').addEventListener('click', function() {
            quantity++;
            updateQuantity();
            calculateTotal();
        });

        document.getElementById('decreaseBtn').addEventListener('click', function() {
            if (quantity > 1) {
                quantity--;
                updateQuantity();
                calculateTotal();
            }
        });

        // Actualizar cantidad display
        function updateQuantity() {
            document.getElementById('quantityDisplay').textContent = quantity;
        }

        // Actualizar panel de compra
        function updatePurchasePanel() {
            document.getElementById('selectedAmount').textContent = selectedProduct.amount;
            document.getElementById('selectedPrice').textContent = unitPrice.toFixed(2) + ' Bs. c/u';
            updateQuantity();
        }

        // Calcular total
        function calculateTotal() {
            const total = unitPrice * quantity;
            document.getElementById('totalAmount').textContent = total.toFixed(2) + ' Bs.';
        }

        // Botón de compra
        document.getElementById('buyButton').addEventListener('click', function() {
            if (!selectedProduct) return;
            
            const total = unitPrice * quantity;
            const message = `¡Perfecto! 🎮\n\nVas a comprar:\n• ${quantity} x ${selectedProduct.amount}\n• Total: ${total.toFixed(2)} Bs.\n\nPronto te contactaremos para completar tu pedido.`;
            
            alert(message);
            
            // Aquí puedes agregar la lógica real de compra
            // Por ejemplo: redirección a checkout, API call, etc.
        });

        // Inicializar
        updateQuantity();
    </script>
</body>
</html>
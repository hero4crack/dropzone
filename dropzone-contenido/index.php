<?php
require_once '../includes/session.php';
require_once '../config/database.php';

$sessionManager = new SessionManager();
$user = $sessionManager->getUserData();

$database = new Database();
$db = $database->getConnection();

// Obtener juegos destacados para el slider
$stmt = $db->query("
    SELECT g.*, c.name as category_name 
    FROM games g 
    LEFT JOIN categories c ON g.category_id = c.id 
    WHERE g.is_active = 1 AND g.featured = 1 
    ORDER BY g.updated_at DESC 
    LIMIT 3
");
$featuredGames = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías con juegos
$stmt = $db->query("
    SELECT c.*, COUNT(g.id) as game_count 
    FROM categories c 
    LEFT JOIN games g ON c.id = g.category_id AND g.is_active = 1 
    GROUP BY c.id 
    HAVING game_count > 0 
    ORDER BY c.name
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los juegos activos con sus productos
$stmt = $db->query("
    SELECT g.*, c.name as category_name, c.icon as category_icon 
    FROM games g 
    LEFT JOIN categories c ON g.category_id = c.id 
    WHERE g.is_active = 1 
    ORDER BY g.featured DESC, g.name
");
$allGames = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos para cada juego
$gamesWithProducts = [];
foreach ($allGames as $game) {
    $stmt = $db->prepare("
        SELECT * FROM products 
        WHERE game_id = :game_id AND is_available = 1 
        ORDER BY price 
        LIMIT 3
    ");
    $stmt->bindParam(':game_id', $game['id']);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $gamesWithProducts[] = [
        'game' => $game,
        'products' => $products
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DROPZONE - Recargas para Juegos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
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
            line-height: 1.6;
            overflow-x: hidden;
            font-family: 'Exo 2', sans-serif;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header */
        header {
            background-color: rgba(26, 26, 26, 0.95);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            border-bottom: 2px solid var(--gold);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Orbitron', sans-serif;
        }
        
        .logo span {
            color: var(--gold);
        }
        
        nav ul {
            display: flex;
            list-style: none;
            align-items: center;
        }
        
        nav li {
            margin-left: 1.5rem;
        }
        
        nav a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
            font-family: 'Exo 2', sans-serif;
        }
        
        nav a:hover {
            color: var(--gold);
        }
        
        nav a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gold);
            transition: width 0.3s;
        }
        
        nav a:hover::after {
            width: 100%;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--black);
        }
        
        .admin-link {
            background: var(--gold);
            color: var(--black);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .admin-link:hover {
            background: var(--white);
        }

        /* Hero Slider */
        .hero-slider {
            margin-top: 80px;
            position: relative;
            height: 600px;
            overflow: hidden;
        }
        
        .swiper {
            width: 100%;
            height: 100%;
        }
        
        .swiper-slide {
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        
        .slide-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            z-index: 1;
            transition: transform 8s ease;
        }
        
        .swiper-slide-active .slide-bg {
            transform: scale(1.1);
        }
        
        .slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.6) 50%, rgba(0,0,0,0.4) 100%);
            z-index: 2;
        }
        
        .slide-content {
            position: relative;
            z-index: 3;
            max-width: 600px;
            padding: 0 2rem;
            margin-left: 10%;
        }
        
        .slide-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 3px;
            font-family: 'Orbitron', sans-serif;
            line-height: 1.2;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.8);
        }
        
        .slide-content h1 span {
            color: var(--gold);
            display: block;
        }
        
        .slide-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 1px 1px 4px rgba(0,0,0,0.8);
        }
        
        .btn {
            display: inline-block;
            background-color: var(--gold);
            color: var(--black);
            padding: 1rem 2.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Exo 2', sans-serif;
            box-shadow: 0 4px 15px rgba(200, 160, 50, 0.3);
        }
        
        .btn:hover {
            background-color: var(--white);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(200, 160, 50, 0.5);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: var(--white);
            border: 2px solid var(--gold);
            margin-left: 1rem;
        }
        
        .btn-secondary:hover {
            background-color: var(--gold);
            color: var(--black);
        }
        
        .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
            background: var(--white);
            opacity: 0.5;
        }
        
        .swiper-pagination-bullet-active {
            background: var(--gold);
            opacity: 1;
        }
        
        /* Categories Section */
        .categories {
            padding: 5rem 0;
            background-color: var(--dark-gray);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
            position: relative;
            color: var(--white);
            font-family: 'Orbitron', sans-serif;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 3px;
            background: var(--gold);
            margin: 0.5rem auto;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .category-card {
            background-color: var(--black);
            border-radius: 12px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.3s;
            border: 1px solid var(--medium-gray);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gold);
            transform: scaleX(0);
            transition: transform 0.3s;
        }
        
        .category-card:hover::before {
            transform: scaleX(1);
        }
        
        .category-card:hover {
            transform: translateY(-10px);
            border-color: var(--gold);
            box-shadow: 0 12px 30px rgba(200, 160, 50, 0.2);
        }
        
        .category-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: var(--gold);
        }
        
        .category-card h3 {
            margin-bottom: 1rem;
            color: var(--white);
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
        }
        
        .category-count {
            color: var(--gold);
            font-weight: bold;
        }
        
        /* Products Section */
        .products {
            padding: 5rem 0;
            background-color: var(--black);
        }
        
        .product-category {
            margin-bottom: 5rem;
        }
        
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--medium-gray);
        }
        
        .category-header h2 {
            font-size: 2rem;
            color: var(--white);
            font-family: 'Orbitron', sans-serif;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .view-all {
            color: var(--gold);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            font-size: 1.1rem;
        }
        
        .view-all:hover {
            color: var(--white);
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }
        
        .product-card {
            background-color: var(--dark-gray);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            border: 1px solid var(--medium-gray);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            border-color: var(--gold);
            box-shadow: 0 10px 25px rgba(200, 160, 50, 0.2);
        }
        
        .product-img {
            height: 160px;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .product-img::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.7) 100%);
        }
        
        .product-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--white);
            font-family: 'Orbitron', sans-serif;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
            z-index: 2;
            position: relative;
            text-align: center;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-info h3 {
            margin-bottom: 0.5rem;
            color: var(--white);
            font-size: 1.2rem;
            font-family: 'Exo 2', sans-serif;
        }
        
        .price-list {
            margin: 1rem 0;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .price-item:last-child {
            border-bottom: none;
        }
        
        .currency-amount {
            color: var(--gold);
            font-weight: bold;
        }
        
        .price {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--gold);
        }
        
        .btn-small {
            display: block;
            background-color: var(--gold);
            color: var(--black);
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
            transition: all 0.3s;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
        
        .btn-small:hover {
            background-color: var(--white);
            transform: translateY(-2px);
        }
        
        /* Promo Banner */
        .promo-banner {
            padding: 5rem 0;
            background: linear-gradient(135deg, var(--dark-gray) 0%, var(--black) 100%);
            position: relative;
            overflow: hidden;
        }
        
        .promo-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="%231A1A1A"/><path d="M0 0L100 100M100 0L0 100" stroke="%23333333" stroke-width="1"/></svg>');
            opacity: 0.5;
        }
        
        .banner-content {
            background: linear-gradient(135deg, rgba(200, 160, 50, 0.1) 0%, rgba(26, 26, 26, 0.9) 100%);
            border-radius: 15px;
            padding: 4rem 3rem;
            text-align: center;
            border: 2px solid var(--gold);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        
        .banner-content::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(200, 160, 50, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .banner-content h2 {
            font-size: 2.8rem;
            margin-bottom: 1.5rem;
            color: var(--white);
            font-family: 'Orbitron', sans-serif;
            position: relative;
            z-index: 2;
        }
        
        .banner-content p {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 2;
        }
        
        .discount-code {
            display: inline-block;
            background: var(--gold);
            color: var(--black);
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1.4rem;
            margin: 1rem 0;
            position: relative;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        
        /* Footer */
        footer {
            background-color: var(--dark-gray);
            padding: 4rem 0 1.5rem;
            border-top: 1px solid var(--medium-gray);
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2.5rem;
            margin-bottom: 2.5rem;
        }
        
        .footer-column h3 {
            color: var(--gold);
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            position: relative;
            padding-bottom: 0.5rem;
            font-family: 'Orbitron', sans-serif;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 30px;
            height: 2px;
            background: var(--gold);
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column li {
            margin-bottom: 0.8rem;
        }
        
        .footer-column a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s;
            font-family: 'Exo 2', sans-serif;
        }
        
        .footer-column a:hover {
            color: var(--gold);
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--medium-gray);
            color: var(--white);
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: var(--gold);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid var(--medium-gray);
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            font-family: 'Exo 2', sans-serif;
        }



/* NUEVOS ESTILOS PARA TARJETAS DE JUEGOS COMPACTAS */
.games-grid-compact {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.game-card-compact {
    background: var(--dark-gray);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid var(--medium-gray);
    position: relative;
    cursor: pointer;
    text-decoration: none;
    display: block;
}

.game-card-compact:hover {
    transform: translateY(-5px);
    border-color: var(--gold);
    box-shadow: 0 8px 20px rgba(200, 160, 50, 0.2);
    text-decoration: none;
}

.game-image-container {
    position: relative;
    width: 100%;
    height: 150px;
    overflow: hidden;
}

.game-image {
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    transition: transform 0.3s ease;
    background-color: var(--black); /* Fondo de respaldo */
}

.game-card-compact:hover .game-image {
    transform: scale(1.05);
}

.hot-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(45deg, #FF5E14, #FF8C42);
    color: white;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 2px 8px rgba(255, 94, 20, 0.4);
    z-index: 2;
}

.game-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.8) 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.game-card-compact:hover .game-overlay {
    opacity: 1;
}

.game-name {
    color: var(--white);
    font-family: 'Orbitron', sans-serif;
    font-size: 0.9rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 0.5rem;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
}

.game-actions {
    display: flex;
    justify-content: center;
}

.btn-view-prices {
    background: var(--gold);
    color: var(--black);
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: bold;
    transition: all 0.3s;
}

.game-card-compact:hover .btn-view-prices {
    background: var(--white);
    transform: translateY(-2px);
}

/* Imagen por defecto cuando no carga */
.game-image::before {
    content: "🎮";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 2rem;
    color: var(--gold);
    opacity: 0.7;
    display: none;
}

.game-image:empty::before {
    display: block;
}



        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin-top: 1rem;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            nav li {
                margin: 0.5rem;
            }
            
            .slide-content {
                margin-left: 0;
                text-align: center;
            }
            
            .slide-content h1 {
                font-size: 2.2rem;
            }
            
            .btn-container {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                align-items: center;
            }
            
            .btn-secondary {
                margin-left: 0;
            }
            
            .category-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .banner-content {
                padding: 2.5rem 1.5rem;
            }
            
            .banner-content h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header id="mainHeader">
        <div class="container">
            <div class="header-content">
                <div class="logo">DROP<span>ZONE</span></div>
                <nav>
                    <ul>
                        <li><a href="#">Inicio</a></li>
                        <li><a href="#categories">Categorías</a></li>
                        <li><a href="#products">Juegos</a></li>
                        <li><a href="#">Soporte</a></li>
                        <?php if ($user): ?>
                            <li class="user-info">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                                <span><?php echo htmlspecialchars($user['username']); ?></span>
                                <?php 
                                // Verificar si es admin
                                $stmt = $db->prepare("SELECT role FROM admins WHERE user_id = :user_id");
                                $stmt->bindParam(':user_id', $user['id']);
                                $stmt->execute();
                                if ($stmt->rowCount() > 0): ?>
                                    <a href="admin/index.php" class="admin-link">Panel Admin</a>
                                <?php endif; ?>
                                <a href="../dropzone-login/logout.php" style="color: var(--gold); margin-left: 10px;">Cerrar Sesión</a>
                            </li>
                        <?php else: ?>
                            <li><a href="../dropzone-login/login.php">Iniciar Sesión</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Slider con juegos destacados -->
        <section class="hero-slider">
            <div class="swiper">
                <div class="swiper-wrapper">
                    <?php if (count($featuredGames) > 0): ?>
                        <?php foreach ($featuredGames as $index => $game): ?>
                            <div class="swiper-slide">
                                <div class="slide-bg" style="background-image: url('<?php echo $game['background_image'] ?: 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'; ?>')"></div>
                                <div class="slide-overlay"></div>
                                <div class="slide-content">
                                    <h1><?php echo htmlspecialchars($game['name']); ?> <span>Recargas</span></h1>
                                    <p><?php echo htmlspecialchars($game['description'] ?: 'Recarga tu cuenta y disfruta de todos los beneficios.'); ?></p>
                                    <div class="btn-container">
                                        <a href="#game-<?php echo $game['id']; ?>" class="btn">Comprar Ahora</a>
                                        <a href="#categories" class="btn btn-secondary">Ver Todos los Juegos</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Slides por defecto si no hay juegos destacados -->
                        <div class="swiper-slide">
                            <div class="slide-bg" style="background-image: url('https://images.unsplash.com/photo-1542751110-97427bbecf20?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80')"></div>
                            <div class="slide-overlay"></div>
                            <div class="slide-content">
                                <h1>Recargas <span>Instantáneas</span></h1>
                                <p>Consigue monedas, CP y V-Bucks para tus juegos favoritos. Entrega inmediata garantizada.</p>
                                <div class="btn-container">
                                    <a href="#categories" class="btn">Ver Juegos</a>
                                    <a href="#products" class="btn btn-secondary">Ver Ofertas</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </section>
        
        <!-- Categorías Principales -->
        <section class="categories" id="categories">
            <div class="container">
                <h2 class="section-title">Categorías Principales</h2>
                <div class="category-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <div class="category-icon">
                                <i class="<?php echo $category['icon'] ?: 'fas fa-gamepad'; ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p><?php echo htmlspecialchars($category['description'] ?: 'Descubre los mejores juegos'); ?></p>
                            <div class="category-count"><?php echo $category['game_count']; ?> juegos</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
      <!-- Juegos por Categoría - VERSIÓN CORREGIDA -->
<section class="products" id="products">
    <div class="container">
        <?php 
        $gamesByCategory = [];
        foreach ($gamesWithProducts as $item) {
            $categoryId = $item['game']['category_id'];
            if (!isset($gamesByCategory[$categoryId])) {
                $gamesByCategory[$categoryId] = [
                    'category' => $item['game'],
                    'games' => []
                ];
            }
            $gamesByCategory[$categoryId]['games'][] = $item;
        }
        ?>
        
        <?php foreach ($gamesByCategory as $categoryId => $categoryData): ?>
            <?php if (count($categoryData['games']) > 0): ?>
                <div class="product-category" id="category-<?php echo $categoryId; ?>">
                    <div class="category-header">
                        <h2>
                            <i class="<?php echo $categoryData['category']['category_icon'] ?: 'fas fa-gamepad'; ?>"></i>
                            <?php echo htmlspecialchars($categoryData['category']['category_name']); ?>
                        </h2>
                        <a href="#categories" class="view-all">Ver Todas las Categorías</a>
                    </div>
                    
                    <!-- GRID DE JUEGOS COMPACTO - SOLO IMAGEN Y NOMBRE -->
                    <div class="games-grid-compact">
                        <?php foreach ($categoryData['games'] as $item): ?>
                            <a href="game-prices.php?game_id=<?php echo $item['game']['id']; ?>" class="game-card-compact" id="game-<?php echo $item['game']['id']; ?>">
                                <div class="game-image-container">
                                    <div class="game-image" style="background-image: url('<?php echo !empty($item['game']['image_url']) ? htmlspecialchars($item['game']['image_url']) : 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'; ?>')">
                                        <?php if ($item['game']['featured']): ?>
                                            <div class="hot-badge">HOT</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="game-overlay">
                                        <div class="game-name"><?php echo htmlspecialchars($item['game']['name']); ?></div>
                                        <div class="game-actions">
                                            <span class="btn-view-prices">Ver Precios</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>
        
        <!-- Banner Promocional -->
        <section class="promo-banner">
            <div class="container">
                <div class="banner-content">
                    <h2>¡Oferta Especial de Lanzamiento!</h2>
                    <p>20% de descuento en tu primera compra</p>
                    <div class="discount-code">DROPZONE20</div>
                    <p>Válido por tiempo limitado. Aprovecha ahora.</p>
                    <a href="#products" class="btn">Aprovechar Oferta</a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>DROPZONE</h3>
                    <p>Tu plataforma confiable para recargas de juegos. Rápido, seguro y confiable.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-discord"></i></a>
                        <a href="#"><i class="fab fa-telegram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="#">Inicio</a></li>
                        <li><a href="#categories">Categorías</a></li>
                        <li><a href="#products">Juegos</a></li>
                        <li><a href="#">Soporte</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Soporte</h3>
                    <ul>
                        <li><a href="#">Centro de Ayuda</a></li>
                        <li><a href="#">Contacto</a></li>
                        <li><a href="#">Términos de Servicio</a></li>
                        <li><a href="#">Política de Privacidad</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Contacto</h3>
                    <ul>
                        <li><i class="fas fa-envelope"></i> soporte@dropzone.com</li>
                        <li><i class="fab fa-discord"></i> Discord</li>
                        <li><i class="fab fa-telegram"></i> Telegram</li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                &copy; 2024 DROPZONE. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        // Inicializar Swiper
        const swiper = new Swiper('.swiper', {
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            autoplay: {
                delay: 6000,
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
        });
        
        // Efecto de header al hacer scroll
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Smooth scroll para enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
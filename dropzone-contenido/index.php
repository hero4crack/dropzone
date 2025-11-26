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
<?php include '../includes/head.php'; ?>
<body>
    <header id="mainHeader">
        <div class="container">
            <div class="header-content">
                <div class="logo">DROP<span>ZONE</span></div>
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <nav id="mainNav">
                    <ul>
                        <li><a href="#">Inicio</a></li>
                        <li><a href="#categories">Categorías</a></li>
                        <li><a href="#products">Juegos</a></li>
                        <li><a href="#">Soporte</a></li>
                        <?php if ($user): ?>
                            <li class="user-info">
                                <?php 
                                // Construir la URL del avatar de Discord
                                $userAvatar = 'https://cdn.discordapp.com/embed/avatars/0.png'; // Por defecto
                                
                                if (!empty($user['avatar'])) {
                                    // Formato: https://cdn.discordapp.com/avatars/{user_id}/{avatar_hash}.png
                                    $userAvatar = "https://cdn.discordapp.com/avatars/{$user['id']}/{$user['avatar']}.png";
                                }
                                ?>
                                <img src="<?php echo $userAvatar; ?>" class="user-avatar" alt="Avatar" 
                                     onerror="this.src='https://cdn.discordapp.com/embed/avatars/0.png'">
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
        
        <!-- Juegos por Categoría - VERSIÓN COMPACTA CON SCROLL HORIZONTAL -->
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
                            </div>
                            
                            <!-- GRID DE JUEGOS COMPACTO CON SCROLL HORIZONTAL -->
                            <div class="games-scroll-container">
                                <div class="games-scroll-wrapper">
                                    <?php foreach ($categoryData['games'] as $item): ?>
                                        <a href="game-prices.php?game_id=<?php echo $item['game']['id']; ?>" class="game-card-compact" id="game-<?php echo $item['game']['id']; ?>">
                                            <div class="game-image-container">
                                                <div class="game-image" style="background-image: url('<?php echo !empty($item['game']['image_url']) ? htmlspecialchars($item['game']['image_url']) : 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'; ?>')">
                                                    <?php if ($item['game']['featured']): ?>
                                                        <div class="hot-badge">HOT</div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="game-name"><?php echo htmlspecialchars($item['game']['name']); ?></div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
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
        
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mainNav = document.getElementById('mainNav');
        
        mobileMenuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            const icon = this.querySelector('i');
            if (mainNav.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // Cerrar menú al hacer clic en un enlace
        document.querySelectorAll('#mainNav a').forEach(link => {
            link.addEventListener('click', function() {
                mainNav.classList.remove('active');
                mobileMenuToggle.querySelector('i').classList.remove('fa-times');
                mobileMenuToggle.querySelector('i').classList.add('fa-bars');
            });
        });
    </script>

    <style>
        /* Estilos adicionales para el scroll horizontal de juegos */
        .games-scroll-container {
            overflow-x: auto;
            padding: 15px 0;
            margin: 0 -10px;
            scrollbar-width: thin;
            scrollbar-color: var(--gold) var(--border-color);
        }

        .games-scroll-wrapper {
            display: flex;
            gap: 15px;
            padding: 0 10px;
            min-width: min-content;
        }

        .game-card-compact {
            flex: 0 0 auto;
            width: 160px;
            text-decoration: none;
            color: inherit;
            transition: transform 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
        }

        .game-card-compact:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.15);
        }

        .game-card-compact .game-image-container {
            position: relative;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .game-card-compact .game-image {
            width: 100%;
            height: 100px;
            background-size: cover;
            background-position: center;
            position: relative;
            flex-shrink: 0;
        }

        .game-card-compact .hot-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            z-index: 2;
        }

        .game-card-compact .game-name {
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-color);
            background: var(--card-bg);
            border-top: 1px solid var(--border-color);
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1.3;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Scrollbar personalizada */
        .games-scroll-container::-webkit-scrollbar {
            height: 6px;
        }

        .games-scroll-container::-webkit-scrollbar-track {
            background: var(--border-color);
            border-radius: 3px;
        }

        .games-scroll-container::-webkit-scrollbar-thumb {
            background: var(--gold);
            border-radius: 3px;
        }

        .games-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #d4af37;
        }

        /* Responsive mejorado para móviles */
        @media (max-width: 768px) {
            .games-scroll-container {
                padding: 10px 0;
                margin: 0 -5px;
            }

            .games-scroll-wrapper {
                gap: 12px;
                padding: 0 5px;
            }

            .game-card-compact {
                width: 140px;
            }
            
            .game-card-compact .game-image {
                height: 90px;
            }
            
            .game-card-compact .game-name {
                font-size: 13px;
                padding: 10px 6px;
                min-height: 45px;
                line-height: 1.2;
            }
        }

        @media (max-width: 480px) {
            .game-card-compact {
                width: 130px;
            }
            
            .game-card-compact .game-image {
                height: 85px;
            }

            .game-card-compact .game-name {
                font-size: 12px;
                padding: 8px 4px;
                min-height: 40px;
            }

            .games-scroll-wrapper {
                gap: 10px;
            }
        }

        @media (max-width: 360px) {
            .game-card-compact {
                width: 120px;
            }
            
            .game-card-compact .game-image {
                height: 80px;
            }

            .game-card-compact .game-name {
                font-size: 11px;
                padding: 8px 3px;
                min-height: 38px;
            }
        }

        /* Asegurar que el texto se muestre completo */
        .game-card-compact .game-name {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            text-overflow: ellipsis;
        }
    </style>
</body>
</html>
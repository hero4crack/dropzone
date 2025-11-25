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
</body>
</html>
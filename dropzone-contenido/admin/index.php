<?php
require_once '../../includes/session.php';

$sessionManager = new SessionManager();
$user = $sessionManager->getUserData();

// Verificar si es administrador
if (!$user) {
    header('Location: ../dropzone-login/login.php');
    exit();
}

require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Verificar permisos de admin
$query = "SELECT role FROM admins WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user['id']);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    die('No tienes permisos de administrador');
}

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si es super_admin
$isSuperAdmin = ($admin['role'] === 'super_admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - DROPZONE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --black: #000000;
            --dark-gray: #1A1A1A;
            --white: #FFFFFF;
            --gold: #C8A032;
            --medium-gray: #333333;
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
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: var(--dark-gray);
            padding: 2rem 1rem;
            border-right: 2px solid var(--gold);
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .logo span {
            color: var(--gold);
        }
        
        .nav-item {
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .nav-item:hover, .nav-item.active {
            background: var(--gold);
            color: var(--black);
        }
        
        .nav-item i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .btn {
            background: var(--gold);
            color: var(--black);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: var(--white);
        }
        
        .card {
            background: var(--dark-gray);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--medium-gray);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--white);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            background: var(--black);
            border: 1px solid var(--medium-gray);
            border-radius: 6px;
            color: var(--white);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .table th, .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .table th {
            background: var(--black);
            color: var(--gold);
        }
        
        .action-btn {
            background: none;
            border: none;
            color: var(--gold);
            cursor: pointer;
            margin: 0 0.25rem;
        }
        
        .action-btn:hover {
            color: var(--white);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }

        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .role-super_admin {
            background: #d4af37;
            color: #000;
        }
        
        .role-admin {
            background: #2d3748;
            color: #fff;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
        }

        .checkbox-group {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        /* ========== ESTILOS PARA MODALES ========== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal {
            background: var(--dark-gray);
            border: 2px solid var(--gold);
            border-radius: 12px;
            padding: 2rem;
            width: 90%;
            max-width: 450px;
            color: var(--white);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.7);
            transform: translateY(-30px) scale(0.9);
            transition: all 0.3s ease;
            position: relative;
        }

        .modal-overlay.active .modal {
            transform: translateY(0) scale(1);
        }

        .modal-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--medium-gray);
        }

        .modal-icon {
            font-size: 1.8rem;
            margin-right: 1rem;
            width: 30px;
            text-align: center;
        }

        .modal-success .modal-icon {
            color: #48bb78;
        }

        .modal-error .modal-icon {
            color: #e53e3e;
        }

        .modal-warning .modal-icon {
            color: #ed8936;
        }

        .modal-info .modal-icon {
            color: var(--gold);
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--white);
            font-family: 'Orbitron', sans-serif;
        }

        .modal-body {
            margin-bottom: 2rem;
            line-height: 1.6;
            font-size: 1rem;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .modal-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            font-family: 'Exo 2', sans-serif;
            font-size: 0.9rem;
            min-width: 80px;
        }

        .modal-btn-primary {
            background: var(--gold);
            color: var(--black);
        }

        .modal-btn-primary:hover {
            background: var(--white);
            transform: translateY(-2px);
        }

        .modal-btn-secondary {
            background: var(--medium-gray);
            color: var(--white);
        }

        .modal-btn-secondary:hover {
            background: #4a5568;
            transform: translateY(-2px);
        }

        .modal-btn-danger {
            background: #e53e3e;
            color: var(--white);
        }

        .modal-btn-danger:hover {
            background: #c53030;
            transform: translateY(-2px);
        }

        /* Modal de confirmación específico */
        .confirm-modal .modal-body {
            text-align: center;
            padding: 1rem 0;
            font-size: 1.1rem;
        }

        .confirm-modal .modal-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
            width: 100%;
        }

        .confirm-modal .modal-title {
            text-align: center;
            width: 100%;
        }

        /* Close button */
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: var(--medium-gray);
            color: var(--gold);
        }

        /* Loading states */
        .btn.loading {
            position: relative;
            color: transparent;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid transparent;
            border-top-color: var(--black);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">DROP<span>ZONE</span></div>
            
            <div class="nav-item active" data-tab="dashboard">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </div>
            <div class="nav-item" data-tab="games">
                <i class="fas fa-gamepad"></i>Gestión de Juegos
            </div>
            <div class="nav-item" data-tab="products">
                <i class="fas fa-coins"></i>Gestión de Precios
            </div>
            <div class="nav-item" data-tab="categories">
                <i class="fas fa-layer-group"></i>Categorías
            </div>
            
            <!-- Mostrar Gestión de Admins solo para super_admins -->
            <?php if ($isSuperAdmin): ?>
            <div class="nav-item" data-tab="admins">
                <i class="fas fa-users-cog"></i>Gestión de Admins
            </div>
            <?php endif; ?>
            
            <div class="nav-item" onclick="window.location.href='../index.php'">
                <i class="fas fa-eye"></i>Ver Tienda
            </div>
            <div class="nav-item" onclick="window.location.href='../../dropzone-login/logout.php'">
                <i class="fas fa-sign-out-alt"></i>Cerrar Sesión
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Panel Administrativo</h1>
                <div>
                    Bienvenido, <?php echo htmlspecialchars($user['username']); ?> 
                    <span class="role-badge <?php echo $admin['role'] === 'super_admin' ? 'role-super_admin' : 'role-admin'; ?>">
                        <?php echo htmlspecialchars($admin['role']); ?>
                    </span>
                </div>
            </div>
            
            <!-- Dashboard -->
            <div id="dashboard" class="tab-content active">
                <h2>Resumen General</h2>
                <div class="card">
                    <h3>Estadísticas Rápidas</h3>
                    <?php
                    $stats = [
                        'Total Juegos' => 'SELECT COUNT(*) FROM games',
                        'Productos Activos' => 'SELECT COUNT(*) FROM products WHERE is_available = 1',
                        'Categorías' => 'SELECT COUNT(*) FROM categories',
                        'Usuarios Registrados' => 'SELECT COUNT(*) FROM users',
                        'Administradores' => 'SELECT COUNT(*) FROM admins'
                    ];
                    
                    echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">';
                    foreach ($stats as $label => $query) {
                        $stmt = $db->query($query);
                        $count = $stmt->fetchColumn();
                        echo "
                        <div style='background: var(--black); padding: 1rem; border-radius: 6px; text-align: center; border: 1px solid var(--gold);'>
                            <div style='font-size: 2rem; color: var(--gold);'>$count</div>
                            <div>$label</div>
                        </div>";
                    }
                    echo '</div>';
                    ?>
                </div>
            </div>
            
            <!-- Gestión de Juegos -->
            <div id="games" class="tab-content">
                <h2>Gestión de Juegos</h2>
                <button class="btn" onclick="showGameForm()">
                    <i class="fas fa-plus"></i> Agregar Nuevo Juego
                </button>
                
                <!-- Formulario de Juego -->
                <div id="gameForm" class="card" style="display: none;">
                    <h3 id="gameFormTitle">Agregar Nuevo Juego</h3>
                    <form id="gameFormElement" enctype="multipart/form-data">
                        <input type="hidden" id="gameId" name="gameId" value="">
                        <div class="form-group">
                            <label>Nombre del Juego</label>
                            <input type="text" id="gameName" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea id="gameDescription" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Categoría</label>
                            <select id="gameCategory" name="category_id" class="form-control" required>
                                <option value="">Seleccionar categoría</option>
                                <?php
                                $stmt = $db->query("SELECT * FROM categories");
                                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$category['id']}'>{$category['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>URL de Imagen</label>
                            <input type="text" id="gameImage" name="image_url" class="form-control" placeholder="https://ejemplo.com/imagen.jpg">
                        </div>
                        <div class="form-group">
                            <label>Imagen de Fondo</label>
                            <input type="text" id="gameBackground" name="background_image" class="form-control" placeholder="https://ejemplo.com/fondo.jpg">
                        </div>
                        
                        <!-- CHECKBOXES CORREGIDOS -->
                        <div class="form-group">
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" id="gameFeatured" name="featured" value="1">
                                    Destacado
                                </label>
                                <label>
                                    <input type="checkbox" id="gameActive" name="is_active" value="1" checked>
                                    Activo
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn">Guardar Juego</button>
                        <button type="button" class="btn" onclick="hideGameForm()" style="background: var(--medium-gray);">Cancelar</button>
                    </form>
                </div>
                
                <!-- Lista de Juegos -->
                <div class="card">
                    <h3>Juegos Existentes</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Productos</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="gamesList">
                            <?php
                            $stmt = $db->query("
                                SELECT g.*, c.name as category_name, 
                                       (SELECT COUNT(*) FROM products p WHERE p.game_id = g.id) as product_count
                                FROM games g 
                                LEFT JOIN categories c ON g.category_id = c.id 
                                ORDER BY g.created_at DESC
                            ");
                            while ($game = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $status = $game['is_active'] ? '<span style="color: #48bb78;">Activo</span>' : '<span style="color: #e53e3e;">Inactivo</span>';
                                $featured = $game['featured'] ? '⭐' : '';
                                echo "
                                <tr>
                                    <td>{$featured} {$game['name']}</td>
                                    <td>{$game['category_name']}</td>
                                    <td>{$game['product_count']} productos</td>
                                    <td>$status</td>
                                    <td>
                                        <button class='action-btn' onclick='editGame({$game['id']})' title='Editar'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button class='action-btn' onclick='manageProducts({$game['id']})' title='Gestionar Productos'>
                                            <i class='fas fa-coins'></i>
                                        </button>
                                        <button class='action-btn' onclick='deleteGame({$game['id']})' title='Eliminar'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Gestión de Productos -->
            <div id="products" class="tab-content">
                <h2>Gestión de Productos y Precios</h2>
                
                <!-- Selector de Juegos -->
                <div class="card">
                    <h3>Seleccionar Juego</h3>
                    <div class="form-group">
                        <label>Elige un juego para gestionar sus productos:</label>
                        <select id="gameSelector" class="form-control" onchange="loadProductsForGame(this.value)">
                            <option value="">-- Selecciona un juego --</option>
                            <?php
                            $stmt = $db->query("SELECT id, name FROM games ORDER BY name");
                            while ($game = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$game['id']}'>{$game['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Gestión de Productos -->
                <div id="productsManagement" style="display: none;">
                    <!-- Formulario para agregar/editar producto -->
                    <div class="card">
                        <h3 id="productFormTitle">Agregar Nuevo Producto</h3>
                        <form id="productForm">
                            <input type="hidden" id="productId" name="productId" value="">
                            <input type="hidden" id="selectedGameId" name="game_id" value="">
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Nombre del Producto</label>
                                    <input type="text" id="productName" name="name" class="form-control" required 
                                           placeholder="Ej: CP, Cristales, Diamantes. etc.">
                                </div>
                                <div class="form-group">
                                    <label>Descripción</label>
                                    <input type="text" id="productDescription" name="description" class="form-control"
                                           placeholder="Ej: Paquete de monedas básico, Pase de Batalla, etc.">
                                </div>
                                <div class="form-group">
                                    <label>Cantidad (Cuanto recibira el cliente)</label>
                                    <input type="text" id="productCurrency" name="currency_amount" class="form-control" required
                                           placeholder="Ej: 1000, 5000, 10000">
                                </div>
                                <div class="form-group">
                                    <label>Precio (Bs.)</label>
                                    <input type="number" id="productPrice" name="price" class="form-control" step="0.01" required
                                           placeholder="Ej: 5.00, 10.00, 18.00">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="productAvailable" name="is_available" value="1" checked> Producto disponible
                                </label>
                            </div>
                            
                            <button type="submit" class="btn">
                                <i class="fas fa-save"></i> Guardar Producto
                            </button>
                            <button type="button" class="btn" onclick="resetProductForm()" style="background: var(--medium-gray);">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </form>
                    </div>

                    <!-- Lista de productos existentes -->
                    <div class="card">
                        <h3>Productos del Juego</h3>
                        <div id="productsList">
                            <p>No hay productos para este juego.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gestión de Categorías -->
            <div id="categories" class="tab-content">
                <h2>Gestión de Categorías</h2>
                <button class="btn" onclick="showCategoryForm()">
                    <i class="fas fa-plus"></i> Agregar Categoría
                </button>
                
                <div id="categoryForm" class="card" style="display: none;">
                    <h3 id="categoryFormTitle">Agregar Nueva Categoría</h3>
                    <form id="categoryFormElement">
                        <input type="hidden" id="categoryId" name="categoryId" value="">
                        <div class="form-group">
                            <label>Nombre de la Categoría</label>
                            <input type="text" id="categoryName" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea id="categoryDescription" name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Icono (FontAwesome)</label>
                            <input type="text" id="categoryIcon" name="icon" class="form-control" placeholder="fas fa-gamepad">
                        </div>
                        <button type="submit" class="btn">Guardar Categoría</button>
                        <button type="button" class="btn" onclick="hideCategoryForm()" style="background: var(--medium-gray);">Cancelar</button>
                    </form>
                </div>
                
                <div class="card">
                    <h3>Categorías Existentes</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Juegos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesList">
                            <?php
                            $stmt = $db->query("
                                SELECT c.*, COUNT(g.id) as game_count 
                                FROM categories c 
                                LEFT JOIN games g ON c.id = g.category_id 
                                GROUP BY c.id 
                                ORDER BY c.name
                            ");
                            while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "
                                <tr>
                                    <td><i class='{$category['icon']}'></i> {$category['name']}</td>
                                    <td>{$category['description']}</td>
                                    <td>{$category['game_count']} juegos</td>
                                    <td>
                                        <button class='action-btn' onclick='editCategory({$category['id']})'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button class='action-btn' onclick='deleteCategory({$category['id']})'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Gestión de Administradores -->
            <?php if ($isSuperAdmin): ?>
            <div id="admins" class="tab-content">
                <h2>Gestión de Administradores</h2>
                
                <div class="card">
                    <h3>Buscar Usuarios</h3>
                    <div class="form-group">
                        <input type="text" id="userSearch" class="form-control" 
                               placeholder="Buscar usuario por nombre o email..." 
                               onkeyup="searchUsers(this.value)">
                    </div>
                </div>
                
                <!-- Lista de Usuarios Encontrados -->
                <div class="card">
                    <h3>Usuarios del Sistema</h3>
                    <div id="usersList">
                        <p>Busca usuarios para gestionar sus permisos de administrador.</p>
                    </div>
                </div>
                
                <!-- Lista de Administradores -->
                <div class="card">
                    <h3>Administradores del Sistema</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Fecha de Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="adminsList">
                            <?php
                            $stmt = $db->query("
                                SELECT a.*, u.username, u.email, u.avatar, u.created_at as user_created
                                FROM admins a 
                                JOIN users u ON a.user_id = u.id 
                                ORDER BY a.role DESC, u.username
                            ");
                            while ($adminRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $roleClass = $adminRow['role'] === 'super_admin' ? 'role-super_admin' : 'role-admin';
                                $avatar = $adminRow['avatar'] ? $adminRow['avatar'] : 'https://cdn.discordapp.com/embed/avatars/0.png';
                                
                                echo "
                                <tr>
                                    <td>
                                        <img src='{$avatar}' class='user-avatar' alt='Avatar'>
                                        <div style='display: inline-block; vertical-align: middle;'>
                                            <strong>{$adminRow['username']}</strong><br>
                                            <small style='color: #888;'>{$adminRow['email']}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class='role-badge $roleClass'>{$adminRow['role']}</span>
                                    </td>
                                    <td>" . date('d/m/Y', strtotime($adminRow['user_created'])) . "</td>
                                    <td>
                                        <button class='action-btn' onclick='editAdminRole({$adminRow['id']})' title='Cambiar Rol'>
                                            <i class='fas fa-user-cog'></i>
                                        </button>";
                                
                                if ($adminRow['user_id'] != $user['id']) {
                                    echo "
                                        <button class='action-btn' onclick='removeAdmin({$adminRow['id']})' title='Quitar Permisos'>
                                            <i class='fas fa-user-times'></i>
                                        </button>";
                                } else {
                                    echo "
                                        <span style='color: #888; font-size: 0.8rem;'>Tú</span>";
                                }
                                echo "
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SCRIPT COMPLETAMENTE NUEVO -->
    <script>
    // ========== SISTEMA DE MODALES ==========
    class ModalSystem {
        constructor() {
            this.modals = new Map();
        }

        show(message, type = 'info', title = null) {
            return new Promise((resolve) => {
                const modalId = 'modal-' + Date.now();
                const modal = this.createModal(modalId, message, type, title, resolve);
                document.body.appendChild(modal);
                
                setTimeout(() => {
                    modal.classList.add('active');
                }, 10);
                
                this.modals.set(modalId, { element: modal, resolve });
            });
        }

        confirm(message, title = 'Confirmar acción') {
            return new Promise((resolve) => {
                const modalId = 'modal-' + Date.now();
                const modal = this.createConfirmModal(modalId, message, title, resolve);
                document.body.appendChild(modal);
                
                setTimeout(() => {
                    modal.classList.add('active');
                }, 10);
                
                this.modals.set(modalId, { element: modal, resolve });
            });
        }

        createModal(modalId, message, type, title, resolve) {
            const titles = {
                'success': 'Éxito',
                'error': 'Error', 
                'warning': 'Advertencia',
                'info': 'Información'
            };

            const icons = {
                'success': 'fas fa-check-circle',
                'error': 'fas fa-times-circle',
                'warning': 'fas fa-exclamation-triangle', 
                'info': 'fas fa-info-circle'
            };

            const modalOverlay = document.createElement('div');
            modalOverlay.className = 'modal-overlay';
            modalOverlay.innerHTML = `
                <div class="modal modal-${type}">
                    <button class="modal-close" onclick="modalSystem.close('${modalId}')">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="modal-header">
                        <i class="modal-icon ${icons[type]}"></i>
                        <div class="modal-title">${title || titles[type]}</div>
                    </div>
                    <div class="modal-body">
                        ${this.escapeHtml(message)}
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn modal-btn-primary" onclick="modalSystem.close('${modalId}')">
                            Aceptar
                        </button>
                    </div>
                </div>
            `;

            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    this.close(modalId);
                }
            });

            return modalOverlay;
        }

        createConfirmModal(modalId, message, title, resolve) {
            const modalOverlay = document.createElement('div');
            modalOverlay.className = 'modal-overlay';
            modalOverlay.innerHTML = `
                <div class="modal">
                    <button class="modal-close" onclick="modalSystem.close('${modalId}', false)">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="modal-header">
                        <i class="modal-icon fas fa-question-circle"></i>
                        <div class="modal-title">${title}</div>
                    </div>
                    <div class="modal-body">
                        ${this.escapeHtml(message)}
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn modal-btn-secondary" onclick="modalSystem.close('${modalId}', false)">
                            Cancelar
                        </button>
                        <button class="modal-btn modal-btn-danger" onclick="modalSystem.close('${modalId}', true)">
                            Confirmar
                        </button>
                    </div>
                </div>
            `;

            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    this.close(modalId, false);
                }
            });

            return modalOverlay;
        }

        close(modalId, result = true) {
            const modalData = this.modals.get(modalId);
            if (modalData) {
                const { element, resolve } = modalData;
                
                element.classList.remove('active');
                
                setTimeout(() => {
                    element.remove();
                    this.modals.delete(modalId);
                    if (resolve) resolve(result);
                }, 300);
            }
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Instancia global
    const modalSystem = new ModalSystem();

    // Funciones helper
    async function showSuccess(message) {
        await modalSystem.show(message, 'success');
    }

    async function showError(message) {
        await modalSystem.show(message, 'error');
    }

    async function showWarning(message) {
        await modalSystem.show(message, 'warning');
    }

    async function showInfo(message) {
        await modalSystem.show(message, 'info');
    }

    function setLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.classList.add('loading');
        } else {
            button.disabled = false;
            button.classList.remove('loading');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ========== NAVEGACIÓN ==========
    document.addEventListener('DOMContentLoaded', function() {
        // Navegación entre pestañas
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                if (this.dataset.tab) {
                    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                    
                    this.classList.add('active');
                    document.getElementById(this.dataset.tab).classList.add('active');
                }
            });
        });

        // Cerrar modales con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal-overlay.active');
                if (modals.length > 0) {
                    const lastModal = modals[modals.length - 1];
                    const modalId = Array.from(modalSystem.modals.entries())
                        .find(([id, data]) => data.element === lastModal)?.[0];
                    if (modalId) modalSystem.close(modalId);
                }
            }
        });

        console.log('✅ Sistema de modales inicializado');
    });

    // ========== GESTIÓN DE JUEGOS ==========
    function showGameForm() {
        document.getElementById('gameForm').style.display = 'block';
        document.getElementById('gameFormTitle').textContent = 'Agregar Nuevo Juego';
        document.getElementById('gameFormElement').reset();
        document.getElementById('gameId').value = '';
        document.getElementById('gameFeatured').checked = false;
        document.getElementById('gameActive').checked = true;
    }

    function hideGameForm() {
        document.getElementById('gameForm').style.display = 'none';
    }

    async function editGame(gameId) {
        try {
            const response = await fetch(`api/games.php?action=get&id=${gameId}`);
            const result = await response.json();
            
            if (!result.success) {
                await showError(result.message);
                return;
            }
            
            const game = result.data;
            document.getElementById('gameId').value = game.id;
            document.getElementById('gameName').value = game.name;
            document.getElementById('gameDescription').value = game.description || '';
            document.getElementById('gameCategory').value = game.category_id;
            document.getElementById('gameImage').value = game.image_url || '';
            document.getElementById('gameBackground').value = game.background_image || '';
            document.getElementById('gameFeatured').checked = game.featured == 1;
            document.getElementById('gameActive').checked = game.is_active == 1;
            
            document.getElementById('gameFormTitle').textContent = 'Editar Juego';
            document.getElementById('gameForm').style.display = 'block';
        } catch (error) {
            console.error('Error:', error);
            await showError('Error al cargar el juego');
        }
    }

    async function deleteGame(gameId) {
        const confirmed = await modalSystem.confirm(
            '¿Estás seguro de que quieres eliminar este juego? También se eliminarán todos sus productos.',
            'Eliminar Juego'
        );
        
        if (confirmed) {
            try {
                const response = await fetch(`api/games.php?action=delete&id=${gameId}`);
                const result = await response.json();
                
                if (result.success) {
                    await showSuccess(result.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    await showError(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                await showError('Error al eliminar el juego');
            }
        }
    }

    // Formulario de juegos
    document.getElementById('gameFormElement').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitButton = this.querySelector('button[type="submit"]');
        const gameId = document.getElementById('gameId').value;
        const formData = new FormData(this);
        formData.append('action', gameId ? 'update' : 'create');
        
        setLoading(submitButton, true);
        
        try {
            const response = await fetch('api/games.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                await showSuccess(result.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                await showError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            await showError('Error de conexión');
        } finally {
            setLoading(submitButton, false);
        }
    });

    // ========== GESTIÓN DE PRODUCTOS ==========
    function loadProductsForGame(gameId) {
        if (!gameId) {
            document.getElementById('productsManagement').style.display = 'none';
            return;
        }
        
        document.getElementById('productsManagement').style.display = 'block';
        document.getElementById('selectedGameId').value = gameId;
        resetProductForm();
        
        fetch(`api/products.php?action=get_game_products&game_id=${gameId}`)
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    document.getElementById('productsList').innerHTML = 
                        `<p style="color: #e53e3e;">Error: ${result.message}</p>`;
                    return;
                }
                updateProductsList(result.products, result.game);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('productsList').innerHTML = 
                    '<p style="color: #e53e3e;">Error de conexión</p>';
            });
    }

    function updateProductsList(products, game) {
        const container = document.getElementById('productsList');
        
        if (!products || products.length === 0) {
            container.innerHTML = '<p>No hay productos para este juego.</p>';
            return;
        }
        
        let html = `
            <p><strong>Juego:</strong> ${escapeHtml(game.name)}</p>
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        products.forEach(product => {
            const status = product.is_available ? 
                '<span style="color: #48bb78;">✅ Disponible</span>' : 
                '<span style="color: #e53e3e;">❌ No disponible</span>';
            
            html += `
                <tr>
                    <td>
                        <strong>${escapeHtml(product.name)}</strong><br>
                        <small style="color: #888;">${escapeHtml(product.description || 'Sin descripción')}</small>
                    </td>
                    <td><strong>${escapeHtml(product.currency_amount)}</strong></td>
                    <td><strong style="color: var(--gold);">${parseFloat(product.price).toFixed(2)} Bs.</strong></td>
                    <td>${status}</td>
                    <td>
                        <button class="action-btn" onclick="editExistingProduct(${product.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn" onclick="deleteExistingProduct(${product.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += `</tbody></table>`;
        container.innerHTML = html;
    }

    async function editExistingProduct(productId) {
        try {
            const response = await fetch(`api/products.php?action=get&id=${productId}`);
            const result = await response.json();
            
            if (!result.success) {
                await showError(result.message);
                return;
            }
            
            const product = result.data;
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('productCurrency').value = product.currency_amount;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productAvailable').checked = product.is_available == 1;
            
            document.getElementById('productFormTitle').textContent = 'Editar Producto';
        } catch (error) {
            console.error('Error:', error);
            await showError('Error al cargar el producto');
        }
    }

    async function deleteExistingProduct(productId) {
        const confirmed = await modalSystem.confirm(
            '¿Estás seguro de que quieres eliminar este producto?',
            'Eliminar Producto'
        );
        
        if (confirmed) {
            try {
                const response = await fetch(`api/products.php?action=delete&id=${productId}`);
                const result = await response.json();
                
                if (result.success) {
                    await showSuccess(result.message);
                    const gameId = document.getElementById('selectedGameId').value;
                    loadProductsForGame(gameId);
                } else {
                    await showError(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                await showError('Error al eliminar el producto');
            }
        }
    }

    function resetProductForm() {
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.getElementById('productAvailable').checked = true;
        document.getElementById('productFormTitle').textContent = 'Agregar Nuevo Producto';
    }

    document.getElementById('productForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitButton = this.querySelector('button[type="submit"]');
        const productId = document.getElementById('productId').value;
        const gameId = document.getElementById('selectedGameId').value;
        
        if (!gameId) {
            await showError('Primero selecciona un juego');
            return;
        }
        
        const formData = new FormData(this);
        formData.append('action', productId ? 'update' : 'create');
        
        setLoading(submitButton, true);
        
        try {
            const response = await fetch('api/products.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                await showSuccess(result.message);
                resetProductForm();
                loadProductsForGame(gameId);
            } else {
                await showError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            await showError('Error de conexión');
        } finally {
            setLoading(submitButton, false);
        }
    });

    // ========== GESTIÓN DE CATEGORÍAS ==========
    function showCategoryForm() {
        document.getElementById('categoryForm').style.display = 'block';
        document.getElementById('categoryFormTitle').textContent = 'Agregar Nueva Categoría';
        document.getElementById('categoryFormElement').reset();
        document.getElementById('categoryId').value = '';
    }

    function hideCategoryForm() {
        document.getElementById('categoryForm').style.display = 'none';
    }

    async function editCategory(categoryId) {
        try {
            const response = await fetch(`api/categories.php?action=get&id=${categoryId}`);
            const result = await response.json();
            
            if (!result.success) {
                await showError(result.message);
                return;
            }
            
            const category = result.data;
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description || '';
            document.getElementById('categoryIcon').value = category.icon || '';
            
            document.getElementById('categoryFormTitle').textContent = 'Editar Categoría';
            document.getElementById('categoryForm').style.display = 'block';
        } catch (error) {
            console.error('Error:', error);
            await showError('Error al cargar la categoría');
        }
    }

    async function deleteCategory(categoryId) {
        const confirmed = await modalSystem.confirm(
            '¿Estás seguro de que quieres eliminar esta categoría?',
            'Eliminar Categoría'
        );
        
        if (confirmed) {
            try {
                const response = await fetch(`api/categories.php?action=delete&id=${categoryId}`);
                const result = await response.json();
                
                if (result.success) {
                    await showSuccess(result.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    await showError(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                await showError('Error al eliminar la categoría');
            }
        }
    }

    document.getElementById('categoryFormElement').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitButton = this.querySelector('button[type="submit"]');
        const categoryId = document.getElementById('categoryId').value;
        const formData = new FormData(this);
        formData.append('action', categoryId ? 'update' : 'create');
        
        setLoading(submitButton, true);
        
        try {
            const response = await fetch('api/categories.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                await showSuccess(result.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                await showError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            await showError('Error de conexión');
        } finally {
            setLoading(submitButton, false);
        }
    });

    // ========== GESTIÓN MEJORADA DE ADMINS ==========
    let allUsers = [];

    async function searchUsers(searchTerm) {
        if (searchTerm.length < 2) {
            document.getElementById('usersList').innerHTML = '<p>Ingresa al menos 2 caracteres para buscar.</p>';
            return;
        }

        try {
            const response = await fetch(`api/admins.php?action=get_all_users&search=${encodeURIComponent(searchTerm)}`);
            const result = await response.json();
            
            if (!result.success) {
                document.getElementById('usersList').innerHTML = `<p style="color: #e53e3e;">Error: ${result.message}</p>`;
                return;
            }

            updateUsersList(result.users);
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('usersList').innerHTML = '<p style="color: #e53e3e;">Error de conexión</p>';
        }
    }

    function updateUsersList(users) {
    const container = document.getElementById('usersList');
    
    if (!users || users.length === 0) {
        container.innerHTML = '<p>No se encontraron usuarios.</p>';
        return;
    }
    
    let html = '<div style="display: grid; gap: 1rem;">';
    
    users.forEach(user => {
        // CAMBIO AQUÍ: user.current_role -> user.user_role
        const roleBadge = user.user_role === 'user' ? 
            '<span style="background: #4a5568; color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.8rem;">Cliente</span>' :
            `<span class="role-badge ${user.user_role === 'super_admin' ? 'role-super_admin' : 'role-admin'}">${user.user_role}</span>`;
        
        html += `
            <div style="display: flex; justify-content: between; align-items: center; padding: 1rem; background: var(--black); border-radius: 8px; border: 1px solid var(--medium-gray);">
                <div style="flex: 1;">
                    <img src="${user.avatar || 'https://cdn.discordapp.com/embed/avatars/0.png'}" class="user-avatar" alt="Avatar">
                    <strong>${escapeHtml(user.username)}</strong>
                    <br>
                    <small style="color: #888;">${escapeHtml(user.email)}</small>
                </div>
                <div style="margin: 0 1rem;">
                    ${roleBadge}
                </div>
                <div>
        `;
        
        // CAMBIO AQUÍ TAMBIÉN: user.current_role -> user.user_role
        if (user.user_role === 'user') {
            html += `
                <button class="btn" onclick="makeAdmin(${user.id}, 'admin')" style="margin-right: 0.5rem;">
                    <i class="fas fa-user-shield"></i> Hacer Admin
                </button>
                <button class="btn" onclick="makeAdmin(${user.id}, 'super_admin')">
                    <i class="fas fa-crown"></i> Hacer Super Admin
                </button>
            `;
        } else {
            html += `
                <button class="btn" onclick="editAdminRole(${user.admin_id})" style="margin-right: 0.5rem;">
                    <i class="fas fa-user-cog"></i> Cambiar Rol
                </button>
                ${user.user_role !== 'user' ? `
                <button class="btn" onclick="removeAdmin(${user.admin_id})" style="background: #e53e3e;">
                    <i class="fas fa-user-times"></i> Quitar Permisos
                </button>
                ` : ''}
            `;
        }
        
        html += `
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

    async function makeAdmin(userId, role) {
        const confirmed = await modalSystem.confirm(
            `¿Estás seguro de que quieres hacer a este usuario ${role === 'super_admin' ? 'Super Administrador' : 'Administrador'}?`,
            'Asignar Permisos de Administrador'
        );
        
        if (confirmed) {
            try {
                const formData = new FormData();
                formData.append('action', 'create');
                formData.append('user_id', userId);
                formData.append('role', role);
                
                const response = await fetch('api/admins.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    await showSuccess(result.message);
                    // Recargar la búsqueda actual
                    const searchTerm = document.getElementById('userSearch').value;
                    if (searchTerm) {
                        searchUsers(searchTerm);
                    }
                    // Recargar lista de admins
                    setTimeout(() => location.reload(), 1000);
                } else {
                    await showError(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                await showError('Error al asignar permisos');
            }
        }
    }

    async function editAdminRole(adminId) {
        try {
            const response = await fetch(`api/admins.php?action=get&id=${adminId}`);
            const result = await response.json();
            
            if (!result.success) {
                await showError(result.message);
                return;
            }
            
            const admin = result.data;
            const newRole = admin.role === 'super_admin' ? 'admin' : 'super_admin';
            const roleName = newRole === 'super_admin' ? 'Super Administrador' : 'Administrador';
            
            const confirmed = await modalSystem.confirm(
                `¿Estás seguro de que quieres cambiar el rol de este usuario a ${roleName}?`,
                'Cambiar Rol de Administrador'
            );
            
            if (confirmed) {
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('adminId', adminId);
                formData.append('role', newRole);
                
                const updateResponse = await fetch('api/admins.php', {
                    method: 'POST',
                    body: formData
                });
                const updateResult = await updateResponse.json();
                
                if (updateResult.success) {
                    await showSuccess(updateResult.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    await showError(updateResult.message);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            await showError('Error al cambiar el rol');
        }
    }

    async function removeAdmin(adminId) {
        const confirmed = await modalSystem.confirm(
            '¿Estás seguro de que quieres quitar los permisos de administrador a este usuario? Se convertirá en un cliente normal.',
            'Quitar Permisos de Administrador'
        );
        
        if (confirmed) {
            try {
                const response = await fetch(`api/admins.php?action=delete&id=${adminId}`);
                const result = await response.json();
                
                if (result.success) {
                    await showSuccess(result.message);
                    // Recargar la búsqueda actual
                    const searchTerm = document.getElementById('userSearch').value;
                    if (searchTerm) {
                        searchUsers(searchTerm);
                    }
                    // Recargar lista de admins
                    setTimeout(() => location.reload(), 1000);
                } else {
                    await showError(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                await showError('Error al quitar permisos');
            }
        }
    }

    // Función para gestionar productos desde juegos
    function manageProducts(gameId) {
        document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        document.querySelector('[data-tab="products"]').classList.add('active');
        document.getElementById('products').classList.add('active');
        
        document.getElementById('gameSelector').value = gameId;
        loadProductsForGame(gameId);
    }
    </script>
</body>
</html>
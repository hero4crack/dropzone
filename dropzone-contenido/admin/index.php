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
                    Bienvenido, <?php echo $user['username']; ?> 
                    <span class="role-badge <?php echo $admin['role'] === 'super_admin' ? 'role-super_admin' : 'role-admin'; ?>">
                        <?php echo $admin['role']; ?>
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
                
                <button class="btn" onclick="showAdminForm()">
                    <i class="fas fa-user-plus"></i> Agregar Administrador
                </button>
                
                <!-- Formulario de Administrador -->
                <div id="adminForm" class="card" style="display: none;">
                    <h3 id="adminFormTitle">Agregar Nuevo Administrador</h3>
                    <form id="adminFormElement">
                        <input type="hidden" id="adminId" name="adminId" value="">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label>Usuario</label>
                                <select id="adminUserId" name="user_id" class="form-control" required>
                                    <option value="">Seleccionar usuario</option>
                                    <?php
                                    $stmt = $db->query("
                                        SELECT u.id, u.username, u.avatar, u.email 
                                        FROM users u 
                                        LEFT JOIN admins a ON u.id = a.user_id 
                                        WHERE a.user_id IS NULL
                                        ORDER BY u.username
                                    ");
                                    while ($userRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $avatar = $userRow['avatar'] ? $userRow['avatar'] : 'https://cdn.discordapp.com/embed/avatars/0.png';
                                        echo "<option value='{$userRow['id']}' data-avatar='{$avatar}'>{$userRow['username']} ({$userRow['email']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Rol</label>
                                <select id="adminRole" name="role" class="form-control" required>
                                    <option value="admin">Administrador</option>
                                    <option value="super_admin">Super Administrador</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Guardar Administrador
                        </button>
                        <button type="button" class="btn" onclick="hideAdminForm()" style="background: var(--medium-gray);">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </form>
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
                                        <button class='action-btn' onclick='editAdmin({$adminRow['id']})' title='Editar'>
                                            <i class='fas fa-edit'></i>
                                        </button>";
                                
                                if ($adminRow['user_id'] != $user['id']) {
                                    echo "
                                        <button class='action-btn' onclick='deleteAdmin({$adminRow['id']})' title='Eliminar'>
                                            <i class='fas fa-trash'></i>
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

    <script src="admin.js"></script>
</body>
</html>
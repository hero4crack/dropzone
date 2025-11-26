<?php
require_once '../../../config/database.php';
require_once '../../../includes/session.php';

header('Content-Type: application/json');

$sessionManager = new SessionManager();
$user = $sessionManager->getUserData();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Verificar permisos de super admin
$query = "SELECT role FROM admins WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user['id']);
$stmt->execute();

$currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentAdmin || $currentAdmin['role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos de super administrador']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            $adminId = $_GET['id'] ?? $_POST['id'] ?? 0;
            $stmt = $db->prepare("
                SELECT a.*, u.username, u.email, u.avatar 
                FROM admins a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.id = :id
            ");
            $stmt->bindParam(':id', $adminId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $admin]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Administrador no encontrado']);
            }
            break;
            
        case 'create':
            $userId = $_POST['user_id'] ?? '';
            $role = $_POST['role'] ?? 'admin';
            
            if (empty($userId)) {
                echo json_encode(['success' => false, 'message' => 'Usuario no válido']);
                break;
            }
            
            // Verificar si el usuario ya es admin
            $stmt = $db->prepare("SELECT id, role FROM admins WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $existingAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
                // Actualizar el rol si ya existe
                $stmt = $db->prepare("UPDATE admins SET role = :role WHERE user_id = :user_id");
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':user_id', $userId);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Rol de administrador actualizado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar rol']);
                }
            } else {
                // Crear nuevo admin
                $stmt = $db->prepare("INSERT INTO admins (user_id, role, created_at) VALUES (:user_id, :role, NOW())");
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':role', $role);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Administrador creado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al crear administrador']);
                }
            }
            break;
            
        case 'update':
            $adminId = $_POST['adminId'] ?? 0;
            $role = $_POST['role'] ?? 'admin';
            
            // Verificar si es el último super_admin
            if ($role !== 'super_admin') {
                $stmt = $db->prepare("SELECT COUNT(*) as super_admin_count FROM admins WHERE role = 'super_admin' AND id != :id");
                $stmt->bindParam(':id', $adminId);
                $stmt->execute();
                $superAdminCount = $stmt->fetch(PDO::FETCH_ASSOC)['super_admin_count'];
                
                if ($superAdminCount == 0) {
                    echo json_encode(['success' => false, 'message' => 'Debe haber al menos un super administrador en el sistema']);
                    break;
                }
            }
            
            $stmt = $db->prepare("UPDATE admins SET role = :role WHERE id = :id");
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':id', $adminId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Administrador actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar administrador']);
            }
            break;
            
        case 'delete':
            $adminId = $_GET['id'] ?? $_POST['id'] ?? 0;
            
            // Obtener información del admin a eliminar
            $stmt = $db->prepare("SELECT user_id, role FROM admins WHERE id = :id");
            $stmt->bindParam(':id', $adminId);
            $stmt->execute();
            $adminToDelete = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$adminToDelete) {
                echo json_encode(['success' => false, 'message' => 'Administrador no encontrado']);
                break;
            }
            
            // No permitir eliminarse a sí mismo
            if ($adminToDelete['user_id'] == $user['id']) {
                echo json_encode(['success' => false, 'message' => 'No puedes eliminarte a ti mismo']);
                break;
            }
            
            // Verificar si es el último super_admin
            if ($adminToDelete['role'] === 'super_admin') {
                $stmt = $db->prepare("SELECT COUNT(*) as super_admin_count FROM admins WHERE role = 'super_admin' AND id != :id");
                $stmt->bindParam(':id', $adminId);
                $stmt->execute();
                $superAdminCount = $stmt->fetch(PDO::FETCH_ASSOC)['super_admin_count'];
                
                if ($superAdminCount == 0) {
                    echo json_encode(['success' => false, 'message' => 'No puedes eliminar al único super administrador']);
                    break;
                }
            }
            
            $stmt = $db->prepare("DELETE FROM admins WHERE id = :id");
            $stmt->bindParam(':id', $adminId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Administrador eliminado exitosamente. El usuario ahora es un cliente normal.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar administrador']);
            }
            break;
            
        case 'get_all_users':
            // Obtener todos los usuarios para el selector
            $search = $_GET['search'] ?? '';
            
            // QUERY CORREGIDA - usando backticks para el alias problemático
            $query = "
                SELECT u.id, u.username, u.email, u.avatar, 
                       CASE WHEN a.id IS NOT NULL THEN a.role ELSE 'user' END as user_role,
                       a.id as admin_id
                FROM users u 
                LEFT JOIN admins a ON u.id = a.user_id 
                WHERE u.username LIKE :search OR u.email LIKE :search
                ORDER BY u.username
                LIMIT 50
            ";
            
            $stmt = $db->prepare($query);
            $searchTerm = "%$search%";
            $stmt->bindParam(':search', $searchTerm);
            $stmt->execute();
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'users' => $users]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>
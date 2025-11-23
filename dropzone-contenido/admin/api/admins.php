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
            $stmt = $db->prepare("SELECT * FROM admins WHERE id = :id");
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
            $stmt = $db->prepare("SELECT id FROM admins WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'Este usuario ya es administrador']);
                break;
            }
            
            // Crear nuevo admin
            $stmt = $db->prepare("INSERT INTO admins (user_id, role, created_at) VALUES (:user_id, :role, NOW())");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':role', $role);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Administrador creado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear administrador']);
            }
            break;
            
        case 'update':
            $adminId = $_POST['adminId'] ?? 0;
            $role = $_POST['role'] ?? 'admin';
            
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
            
            // No permitir eliminarse a sí mismo
            $stmt = $db->prepare("SELECT user_id FROM admins WHERE id = :id");
            $stmt->bindParam(':id', $adminId);
            $stmt->execute();
            $adminToDelete = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($adminToDelete && $adminToDelete['user_id'] == $user['id']) {
                echo json_encode(['success' => false, 'message' => 'No puedes eliminarte a ti mismo']);
                break;
            }
            
            $stmt = $db->prepare("DELETE FROM admins WHERE id = :id");
            $stmt->bindParam(':id', $adminId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Administrador eliminado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar administrador']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>
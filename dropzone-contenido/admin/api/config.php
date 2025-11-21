<?php
// Configuración centralizada para APIs
session_start();

// Rutas absolutas
$base_dir = dirname(__DIR__, 2); // Sube 2 niveles desde admin/api/
$config_file = $base_dir . '../../config/database.php';
$session_file = $base_dir . '../../includes/session.php';

// Verificar que los archivos existen
if (!file_exists($config_file) || !file_exists($session_file)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Archivos de configuración no encontrados']);
    exit;
}

// Cargar archivos
require_once $config_file;
require_once $session_file;

// Verificar autenticación y permisos
$sessionManager = new SessionManager();
$user = $sessionManager->getUserData();

if (!$user) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Verificar permisos de admin
$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("SELECT role FROM admins WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user['id']);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No tienes permisos de administrador']);
    exit;
}

// Si todo está bien, continuar...
?>
<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $database = new Database();
    $db = $database->getConnection();
    $sessionManager = new SessionManager();
    
    // Buscar usuario por email
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar si es usuario de Discord (sin contraseña)
        if ($user['is_discord_user'] == 1 && empty($user['password_hash'])) {
            // Usuario de Discord sin contraseña - redirigir a establecer contraseña
            $_SESSION['setup_password_user'] = $user['id'];
            header('Location: ../setup-password.php');
            exit();
        }
        
        // Verificar contraseña para usuarios normales
        if (!empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            // Crear sesión
            $sessionManager->createSession($user['id']);
            
            // Redirigir al dashboard
            header('Location: ../../dropzone-contenido/index.php');
            exit();
        }
    }
    
    // Si hay error, redirigir al login con mensaje de error
    header('Location: ../login.php?error=invalid_credentials');
    exit();
}
?>
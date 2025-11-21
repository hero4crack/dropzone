<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validaciones básicas
    if ($password !== $confirmPassword) {
        header('Location: ../login.php?error=password_mismatch');
        exit();
    }
    
    if (strlen($password) < 6) {
        header('Location: ../login.php?error=password_short');
        exit();
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $sessionManager = new SessionManager();
    
    // Verificar si el email ya existe
    $query = "SELECT id FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        header('Location: ../login.php?error=email_exists');
        exit();
    }
    
    // Verificar si el username ya existe
    $query = "SELECT id FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        header('Location: ../login.php?error=username_exists');
        exit();
    }
    
    // Crear nuevo usuario
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (username, email, password_hash, is_discord_user) VALUES (:username, :email, :password_hash, 0)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password_hash', $passwordHash);
    
    if ($stmt->execute()) {
        $userId = $db->lastInsertId();
        
        // Crear sesión
        $sessionManager->createSession($userId);
        
        // Redirigir al dashboard
        header('Location: ../../dropzone-contenido/index.php');
        exit();
    } else {
        header('Location: ../login.php?error=registration_failed');
        exit();
    }
} else {
    header('Location: ../login.php');
    exit();
}
?>
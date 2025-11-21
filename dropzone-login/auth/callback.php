<?php
require_once '../../config/discord.php';
require_once '../../includes/session.php';

if (isset($_GET['code'])) {
    $discordAuth = new DiscordAuth();
    $sessionManager = new SessionManager();
    
    // Obtener access token
    $tokenData = $discordAuth->getAccessToken($_GET['code']);
    
    if (isset($tokenData['access_token'])) {
        // Obtener datos del usuario
        $userData = $discordAuth->getUserData($tokenData['access_token']);
        
        if (isset($userData['id'])) {
            // Guardar o actualizar usuario en la base de datos
            require_once '../../config/database.php';
            $database = new Database();
            $db = $database->getConnection();
            
            // Verificar si el usuario ya existe
            $query = "SELECT id, password_hash FROM users WHERE discord_id = :discord_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':discord_id', $userData['id']);
            $stmt->execute();
            
            $isUpdate = ($stmt->rowCount() > 0);
            
            if ($isUpdate) {
                // Actualizar usuario existente
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $userId = $row['id'];
                
                $query = "UPDATE users SET username = :username, email = :email, avatar = :avatar, access_token = :access_token, refresh_token = :refresh_token, is_discord_user = 1, updated_at = NOW() WHERE id = :id";
                $stmt = $db->prepare($query);
                
                // Bind parameters para UPDATE
                $stmt->bindParam(':username', $userData['username']);
                $stmt->bindParam(':email', $userData['email']);
                $stmt->bindParam(':avatar', $userData['avatar']);
                $stmt->bindParam(':access_token', $tokenData['access_token']);
                $stmt->bindParam(':refresh_token', $tokenData['refresh_token']);
                $stmt->bindParam(':id', $userId);
                
            } else {
                // Crear nuevo usuario de Discord
                $query = "INSERT INTO users (discord_id, username, email, avatar, access_token, refresh_token, is_discord_user) VALUES (:discord_id, :username, :email, :avatar, :access_token, :refresh_token, 1)";
                $stmt = $db->prepare($query);
                
                // Bind parameters para INSERT
                $stmt->bindParam(':discord_id', $userData['id']);
                $stmt->bindParam(':username', $userData['username']);
                $stmt->bindParam(':email', $userData['email']);
                $stmt->bindParam(':avatar', $userData['avatar']);
                $stmt->bindParam(':access_token', $tokenData['access_token']);
                $stmt->bindParam(':refresh_token', $tokenData['refresh_token']);
            }
            
            if ($stmt->execute()) {
                if (!$isUpdate) {
                    $userId = $db->lastInsertId();
                }
                
                // Crear sesión
                $sessionManager->createSession($userId);
                
                // Redirigir al dashboard
                header('Location: ../../dropzone-contenido/index.php');
                exit();
            } else {
                // Error en la ejecución de la consulta
                error_log("Error ejecutando consulta: " . implode(", ", $stmt->errorInfo()));
                header('Location: ../login.php?error=auth_failed');
                exit();
            }
        }
    }
    
    // Si hay error, redirigir al login con mensaje de error
    header('Location: ../login.php?error=auth_failed');
    exit();
} else {
    header('Location: ../login.php?error=no_code');
    exit();
}
?>
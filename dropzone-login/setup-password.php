<?php
session_start();
require_once __DIR__ . '../../config/database.php';

// Verificar que el usuario está en sesión para establecer contraseña
if (!isset($_SESSION['setup_password_user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['setup_password_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "UPDATE users SET password_hash = :password_hash, is_discord_user = 0 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':id', $user_id);
        
        if ($stmt->execute()) {
            // Limpiar sesión temporal y crear sesión real
            unset($_SESSION['setup_password_user']);
            
            require_once __DIR__ . '../../includes/session.php';
            $sessionManager = new SessionManager();
            $sessionManager->createSession($user_id);
            
            header('Location: ../dropzone-contenido/index.php');
            exit();
        } else {
            $error = "Error al establecer la contraseña";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Establecer Contraseña - DROPZONE</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .setup-container {
            width: 100%;
            max-width: 400px;
        }
        
        .setup-card {
            background-color: var(--dark-gray);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid var(--gold);
            text-align: center;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: 900;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 1rem;
        }
        
        .logo span {
            color: var(--gold);
        }
        
        h2 {
            margin-bottom: 1.5rem;
            color: var(--gold);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--white);
        }
        
        .form-control {
            width: 100%;
            padding: 0.9rem;
            background-color: var(--black);
            border: 1px solid var(--medium-gray);
            border-radius: 6px;
            color: var(--white);
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--gold);
        }
        
        .btn {
            width: 100%;
            background-color: var(--gold);
            color: var(--black);
            padding: 1rem;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn:hover {
            background-color: var(--white);
        }
        
        .error-message {
            background-color: rgba(255, 59, 59, 0.1);
            border: 1px solid rgba(255, 59, 59, 0.3);
            color: #ff3b3b;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .info-message {
            background-color: rgba(200, 160, 50, 0.1);
            border: 1px solid var(--gold);
            color: var(--gold);
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-card">
            <div class="logo">DROP<span>ZONE</span></div>
            <h2>Establecer Contraseña</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="info-message">
                <i class="fas fa-info-circle"></i>
                Tu cuenta fue creada con Discord. Por favor, establece una contraseña para poder iniciar sesión con email y contraseña.
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repite tu contraseña" required minlength="6">
                </div>
                
                <button type="submit" class="btn">Establecer Contraseña</button>
            </form>
        </div>
    </div>
</body>
</html>
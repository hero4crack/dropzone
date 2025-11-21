<?php
session_start();

class SessionManager {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function createSession($userId) {
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $query = "INSERT INTO sessions (user_id, session_token, expires_at) VALUES (:user_id, :session_token, :expires_at)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':session_token', $sessionToken);
        $stmt->bindParam(':expires_at', $expiresAt);
        
        if ($stmt->execute()) {
            setcookie('session_token', $sessionToken, time() + (30 * 24 * 60 * 60), '/');
            $_SESSION['user_id'] = $userId;
            return true;
        }
        return false;
    }
    
    public function validateSession() {
        if (isset($_COOKIE['session_token'])) {
            $sessionToken = $_COOKIE['session_token'];
            
            $query = "SELECT user_id FROM sessions WHERE session_token = :session_token AND expires_at > NOW()";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':session_token', $sessionToken);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $_SESSION['user_id'] = $row['user_id'];
                return $row['user_id'];
            }
        }
        return false;
    }
    
    public function destroySession() {
        if (isset($_COOKIE['session_token'])) {
            $sessionToken = $_COOKIE['session_token'];
            
            $query = "DELETE FROM sessions WHERE session_token = :session_token";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':session_token', $sessionToken);
            $stmt->execute();
            
            setcookie('session_token', '', time() - 3600, '/');
        }
        
        session_unset();
        session_destroy();
    }
    
    public function getUserData() {
        $userId = $this->validateSession();
        if (!$userId) return false;
        
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
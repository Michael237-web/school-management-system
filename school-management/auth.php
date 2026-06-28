<?php
// auth.php
require_once 'database.php';

class Auth {
    private $db;
    private $table;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->table = Database::table('users');
    }
    
    public function login($username, $password) {
        $conn = $this->db->getConnection();
        $username = $conn->real_escape_string($username);
        
        $sql = "SELECT * FROM {$this->table} WHERE (username = ? OR email = ?) AND is_active = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // Update last login
                $updateSql = "UPDATE {$this->table} SET updated_at = NOW() WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                
                return ['success' => true, 'user' => $user];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid username or password'];
    }
    
    public function logout() {
        // Clear session variables
        $_SESSION = array();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function isAdmin() {
        return $this->isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $conn = $this->db->getConnection();
        $userId = $_SESSION['user_id'];
        
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    public function register($data) {
        $conn = $this->db->getConnection();
        
        // Check if username or email exists
        $checkSql = "SELECT id FROM {$this->table} WHERE username = ? OR email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $data['username'], $data['email']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Hash password - use default 'password' if not provided
        $password = $data['password'] ?? 'password';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $sql = "INSERT INTO {$this->table} (username, email, password, full_name, phone, role) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", 
            $data['username'], 
            $data['email'], 
            $hashedPassword, 
            $data['full_name'], 
            $data['phone'] ?? '', 
            $data['role'] ?? 'student'
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'user_id' => $conn->insert_id];
        }
        
        return ['success' => false, 'message' => 'Registration failed: ' . $conn->error];
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public function requireRole($role) {
        $this->requireLogin();
        
        if ($_SESSION['role'] !== $role && $_SESSION['role'] !== 'admin') {
            header('Location: dashboard.php?error=unauthorized');
            exit();
        }
    }
    
    public function changePassword($userId, $oldPassword, $newPassword) {
        $conn = $this->db->getConnection();
        
        // Get current password
        $sql = "SELECT password FROM {$this->table} WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($oldPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                $updateSql = "UPDATE {$this->table} SET password = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("si", $hashedPassword, $userId);
                
                if ($updateStmt->execute()) {
                    return ['success' => true, 'message' => 'Password changed successfully'];
                }
            }
        }
        
        return ['success' => false, 'message' => 'Invalid old password'];
    }
}
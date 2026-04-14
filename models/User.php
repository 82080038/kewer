<?php
/**
 * User Model
 * 
 * Handles user-related database operations
 */

require_once __DIR__ . /../includes/database_class.php;

class User {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        return $this->db->selectOne("SELECT * FROM users WHERE id = ?", [$id]);
    }
    
    /**
     * Get user by username
     */
    public function getByUsername($username) {
        return $this->db->selectOne("SELECT * FROM users WHERE username = ?", [$username]);
    }
    
    /**
     * Get all users
     */
    public function getAll($cabangId = null) {
        $sql = "SELECT * FROM users";
        $params = [];
        
        if ($cabangId) {
            $sql .= " WHERE cabang_id = ?";
            $params[] = $cabangId;
        }
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        $sql = "INSERT INTO users (username, password, nama, email, role, cabang_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['nama'],
            $data['email'] ?? null,
            $data['role'],
            $data['cabang_id'] ?? null,
            $data['status'] ?? 'aktif'
        ];
        
        return $this->db->insert($sql, $params);
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        $sql = "UPDATE users SET nama = ?, email = ?, role = ?, cabang_id = ?, status = ? WHERE id = ?";
        $params = [
            $data['nama'],
            $data['email'] ?? null,
            $data['role'],
            $data['cabang_id'] ?? null,
            $data['status'] ?? 'aktif',
            $id
        ];
        
        if (!empty($data['password'])) {
            $sql = "UPDATE users SET password = ?, nama = ?, email = ?, role = ?, cabang_id = ?, status = ? WHERE id = ?";
            $params = [
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['nama'],
                $data['email'] ?? null,
                $data['role'],
                $data['cabang_id'] ?? null,
                $data['status'] ?? 'aktif',
                $id
            ];
        }
        
        return $this->db->update($sql, $params);
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        return $this->db->delete("DELETE FROM users WHERE id = ?", [$id]);
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword($username, $password) {
        $user = $this->getByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
?>

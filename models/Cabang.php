<?php
/**
 * Cabang Model
 * 
 * Handles branch (cabang) related database operations
 */

require_once __DIR__ . '/../includes/database_class.php';

class Cabang {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get cabang by ID
     */
    public function getById($id) {
        return $this->db->selectOne("
            SELECT c.*,
                   p.name as province_name, r.name as regency_name,
                   d.name as district_name, v.name as village_name
            FROM cabang c 
            LEFT JOIN provinces p ON c.province_id = p.id
            LEFT JOIN regencies r ON c.regency_id = r.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN villages v ON c.village_id = v.id
            WHERE c.id = ?", [$id]);
    }
    
    /**
     * Get cabang by kode
     */
    public function getByKode($kode) {
        return $this->db->selectOne("
            SELECT c.*,
                   p.name as province_name, r.name as regency_name,
                   d.name as district_name, v.name as village_name
            FROM cabang c 
            LEFT JOIN provinces p ON c.province_id = p.id
            LEFT JOIN regencies r ON c.regency_id = r.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN villages v ON c.village_id = v.id
            WHERE c.kode_cabang = ?", [$kode]);
    }
    
    /**
     * Get all cabang
     */
    public function getAll() {
        return $this->db->select("
            SELECT c.*,
                   p.name as province_name, r.name as regency_name,
                   d.name as district_name, v.name as village_name
            FROM cabang c 
            LEFT JOIN provinces p ON c.province_id = p.id
            LEFT JOIN regencies r ON c.regency_id = r.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN villages v ON c.village_id = v.id
            ORDER BY c.created_at DESC");
    }
    
    /**
     * Get active cabang
     */
    public function getActive() {
        return $this->db->select("
            SELECT c.*,
                   p.name as province_name, r.name as regency_name,
                   d.name as district_name, v.name as village_name
            FROM cabang c 
            LEFT JOIN provinces p ON c.province_id = p.id
            LEFT JOIN regencies r ON c.regency_id = r.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN villages v ON c.village_id = v.id
            WHERE c.status = 'aktif'
            ORDER BY c.nama_cabang ASC");
    }
    
    /**
     * Create new cabang
     */
    public function create($data) {
        $sql = "INSERT INTO cabang (kode_cabang, nama_cabang, alamat, province_id, regency_id, district_id, village_id, telp, email, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $data['kode_cabang'],
            $data['nama_cabang'],
            $data['alamat'] ?? null,
            $data['province_id'] ?? null,
            $data['regency_id'] ?? null,
            $data['district_id'] ?? null,
            $data['village_id'] ?? null,
            $data['telp'] ?? null,
            $data['email'] ?? null,
            $data['status'] ?? 'aktif'
        ];
        
        return $this->db->insert($sql, $params);
    }
    
    /**
     * Update cabang
     */
    public function update($id, $data) {
        $sql = "UPDATE cabang SET nama_cabang = ?, alamat = ?, province_id = ?, regency_id = ?, district_id = ?, village_id = ?, telp = ?, email = ?, status = ? WHERE id = ?";
        $params = [
            $data['nama_cabang'],
            $data['alamat'] ?? null,
            $data['province_id'] ?? null,
            $data['regency_id'] ?? null,
            $data['district_id'] ?? null,
            $data['village_id'] ?? null,
            $data['telp'] ?? null,
            $data['email'] ?? null,
            $data['status'] ?? 'aktif',
            $id
        ];
        
        return $this->db->update($sql, $params);
    }
    
    /**
     * Delete cabang
     */
    public function delete($id) {
        return $this->db->delete("DELETE FROM cabang WHERE id = ?", [$id]);
    }
    
    /**
     * Count active cabang
     */
    public function countActive() {
        return $this->db->count("cabang", "status = 'aktif'");
    }
}
?>

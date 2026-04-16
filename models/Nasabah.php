<?php
/**
 * Nasabah Model
 * 
 * Handles customer (nasabah) related database operations
 */

require_once __DIR__ . '/../includes/database_class.php';

class Nasabah {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get nasabah by ID
     */
    public function getById($id) {
        return $this->db->selectOne("
            SELECT n.*, c.nama_cabang,
                   p.name as province_name, r.name as regency_name,
                   d.name as district_name, v.name as village_name
            FROM nasabah n 
            LEFT JOIN cabang c ON n.cabang_id = c.id 
            LEFT JOIN provinces p ON n.province_id = p.id
            LEFT JOIN regencies r ON n.regency_id = r.id
            LEFT JOIN districts d ON n.district_id = d.id
            LEFT JOIN villages v ON n.village_id = v.id
            WHERE n.id = ?", [$id]);
    }
    
    /**
     * Get nasabah by kode
     */
    public function getByKode($kode) {
        return $this->db->selectOne("SELECT * FROM nasabah WHERE kode_nasabah = ?", [$kode]);
    }
    
    /**
     * Get nasabah by KTP
     */
    public function getByKTP($ktp) {
        return $this->db->selectOne("SELECT * FROM nasabah WHERE ktp = ?", [$ktp]);
    }
    
    /**
     * Get all nasabah with filters
     */
    public function getAll($cabangId = null, $status = null, $search = null) {
        $sql = "SELECT n.*, c.nama_cabang 
                FROM nasabah n 
                LEFT JOIN cabang c ON n.cabang_id = c.id 
                WHERE 1=1";
        $params = [];
        
        if ($cabangId) {
            $sql .= " AND n.cabang_id = ?";
            $params[] = $cabangId;
        }
        
        if ($status) {
            $sql .= " AND n.status = ?";
            $params[] = $status;
        }
        
        if ($search) {
            $sql .= " AND (n.nama LIKE ? OR n.kode_nasabah LIKE ? OR n.ktp LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY n.created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Create new nasabah
     */
    public function create($data) {
        $sql = "INSERT INTO nasabah (cabang_id, kode_nasabah, nama, alamat, province_id, regency_id, district_id, village_id, ktp, telp, email, jenis_usaha, lokasi_pasar, foto_ktp, foto_selfie, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $data['cabang_id'],
            $data['kode_nasabah'],
            $data['nama'],
            $data['alamat'] ?? null,
            $data['province_id'] ?? null,
            $data['regency_id'] ?? null,
            $data['district_id'] ?? null,
            $data['village_id'] ?? null,
            $data['ktp'],
            $data['telp'],
            $data['email'] ?? null,
            $data['jenis_usaha'] ?? null,
            $data['lokasi_pasar'] ?? null,
            $data['foto_ktp'] ?? null,
            $data['foto_selfie'] ?? null,
            $data['status'] ?? 'aktif'
        ];
        
        return $this->db->insert($sql, $params);
    }
    
    /**
     * Update nasabah
     */
    public function update($id, $data) {
        $sql = "UPDATE nasabah SET nama = ?, alamat = ?, province_id = ?, regency_id = ?, district_id = ?, village_id = ?, telp = ?, email = ?, jenis_usaha = ?, lokasi_pasar = ?, status = ? WHERE id = ?";
        $params = [
            $data['nama'],
            $data['alamat'] ?? null,
            $data['province_id'] ?? null,
            $data['regency_id'] ?? null,
            $data['district_id'] ?? null,
            $data['village_id'] ?? null,
            $data['telp'],
            $data['email'] ?? null,
            $data['jenis_usaha'] ?? null,
            $data['lokasi_pasar'] ?? null,
            $data['status'] ?? 'aktif',
            $id
        ];
        
        return $this->db->update($sql, $params);
    }
    
    /**
     * Delete nasabah
     */
    public function delete($id) {
        return $this->db->delete("DELETE FROM nasabah WHERE id = ?", [$id]);
    }
    
    /**
     * Count active nasabah by cabang
     */
    public function countActive($cabangId) {
        return $this->db->count("nasabah", "cabang_id = ? AND status = 'aktif'", [$cabangId]);
    }
}
?>

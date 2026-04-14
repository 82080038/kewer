<?php
/**
 * Expense Tracking Module
 * 
 * Handles comprehensive expense tracking including:
 * - Salary (gaji)
 * - Overtime (lembur)
 * - Bonus
 * - Operational costs (bensin, transport, listrik)
 * - Routine expenses (belanja rutin, makan, dll)
 * - Approval workflow
 */

require_once __DIR__ . '/database_class.php';

class Pengeluaran {
    private $db;
    private $cabangId;
    
    public function __construct($cabangId = null) {
        $this->db = db();
        $this->cabangId = $cabangId;
    }
    
    /**
     * Create new expense record
     */
    public function createExpense($data) {
        // Check if approval is required based on amount
        $thresholdSql = "SELECT setting_value FROM settings WHERE setting_key = 'require_approval_pengeluaran'";
        $threshold = $this->db->selectOne($thresholdSql);
        $approvalThreshold = $threshold ? (float)$threshold['setting_value'] : 500000;
        
        $status = ($data['jumlah'] >= $approvalThreshold) ? 'pending' : 'approved';
        
        $sql = "INSERT INTO pengeluaran 
                (cabang_id, kategori, sub_kategori, jumlah, tanggal, keterangan, bukti, petugas_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['cabang_id'] ?? $this->cabangId,
            $data['kategori'],
            $data['sub_kategori'] ?? null,
            $data['jumlah'],
            $data['tanggal'],
            $data['keterangan'] ?? null,
            $data['bukti'] ?? null,
            $data['petugas_id'] ?? null,
            $status
        ]);
    }
    
    /**
     * Update expense record
     */
    public function updateExpense($id, $data) {
        $sql = "UPDATE pengeluaran 
                SET kategori = ?, sub_kategori = ?, jumlah = ?, tanggal = ?, 
                    keterangan = ?, bukti = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [
            $data['kategori'],
            $data['sub_kategori'] ?? null,
            $data['jumlah'],
            $data['tanggal'],
            $data['keterangan'] ?? null,
            $data['bukti'] ?? null,
            $id
        ]);
    }
    
    /**
     * Approve expense
     */
    public function approveExpense($id, $approverId) {
        $sql = "UPDATE pengeluaran 
                SET status = 'approved', approved_by = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [$approverId, $id]);
    }
    
    /**
     * Reject expense
     */
    public function rejectExpense($id, $approverId) {
        $sql = "UPDATE pengeluaran 
                SET status = 'rejected', approved_by = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [$approverId, $id]);
    }
    
    /**
     * Get expense by ID
     */
    public function getById($id) {
        $sql = "SELECT p.*, c.nama_cabang, u1.nama as nama_petugas, u2.nama as nama_approver
                FROM pengeluaran p
                LEFT JOIN cabang c ON p.cabang_id = c.id
                LEFT JOIN users u1 ON p.petugas_id = u1.id
                LEFT JOIN users u2 ON p.approved_by = u2.id
                WHERE p.id = ?";
        
        return $this->db->selectOne($sql, [$id]);
    }
    
    /**
     * Get all expenses for branch
     */
    public function getBranchExpenses($filters = []) {
        $sql = "SELECT p.*, c.nama_cabang, u1.nama as nama_petugas, u2.nama as nama_approver
                FROM pengeluaran p
                LEFT JOIN cabang c ON p.cabang_id = c.id
                LEFT JOIN users u1 ON p.petugas_id = u1.id
                LEFT JOIN users u2 ON p.approved_by = u2.id
                WHERE p.cabang_id = ?";
        
        $params = [$this->cabangId];
        
        if (!empty($filters['kategori'])) {
            $sql .= " AND p.kategori = ?";
            $params[] = $filters['kategori'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['tanggal_mulai'])) {
            $sql .= " AND p.tanggal >= ?";
            $params[] = $filters['tanggal_mulai'];
        }
        
        if (!empty($filters['tanggal_selesai'])) {
            $sql .= " AND p.tanggal <= ?";
            $params[] = $filters['tanggal_selesai'];
        }
        
        $sql .= " ORDER BY p.tanggal DESC, p.created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get pending expenses (awaiting approval)
     */
    public function getPendingExpenses() {
        $sql = "SELECT p.*, c.nama_cabang, u1.nama as nama_petugas
                FROM pengeluaran p
                LEFT JOIN cabang c ON p.cabang_id = c.id
                LEFT JOIN users u1 ON p.petugas_id = u1.id
                WHERE p.cabang_id = ? AND p.status = 'pending'
                ORDER BY p.tanggal ASC, p.created_at ASC";
        
        return $this->db->select($sql, [$this->cabangId]);
    }
    
    /**
     * Get expense report by category
     */
    public function getReportByCategory($tanggalMulai, $tanggalSelesai) {
        $sql = "SELECT 
                    kategori,
                    COUNT(*) as jumlah_transaksi,
                    SUM(jumlah) as total_pengeluaran
                FROM pengeluaran
                WHERE cabang_id = ? 
                AND status = 'approved'
                AND tanggal BETWEEN ? AND ?
                GROUP BY kategori
                ORDER BY total_pengeluaran DESC";
        
        return $this->db->select($sql, [$this->cabangId, $tanggalMulai, $tanggalSelesai]);
    }
    
    /**
     * Get expense report by sub-category
     */
    public function getReportBySubCategory($kategori, $tanggalMulai, $tanggalSelesai) {
        $sql = "SELECT 
                    sub_kategori,
                    COUNT(*) as jumlah_transaksi,
                    SUM(jumlah) as total_pengeluaran
                FROM pengeluaran
                WHERE cabang_id = ? 
                AND kategori = ?
                AND status = 'approved'
                AND tanggal BETWEEN ? AND ?
                GROUP BY sub_kategori
                ORDER BY total_pengeluaran DESC";
        
        return $this->db->select($sql, [$this->cabangId, $kategori, $tanggalMulai, $tanggalSelesai]);
    }
    
    /**
     * Get total expenses for period
     */
    public function getTotalExpenses($tanggalMulai, $tanggalSelesai) {
        $sql = "SELECT 
                    SUM(jumlah) as total_pengeluaran,
                    COUNT(*) as jumlah_transaksi
                FROM pengeluaran
                WHERE cabang_id = ? 
                AND status = 'approved'
                AND tanggal BETWEEN ? AND ?";
        
        return $this->db->selectOne($sql, [$this->cabangId, $tanggalMulai, $tanggalSelesai]);
    }
    
    /**
     * Get salary expenses
     */
    public function getSalaryExpenses($bulan, $tahun) {
        $sql = "SELECT * FROM pengeluaran
                WHERE cabang_id = ? 
                AND kategori = 'gaji'
                AND status = 'approved'
                AND MONTH(tanggal) = ?
                AND YEAR(tanggal) = ?
                ORDER BY tanggal DESC";
        
        return $this->db->select($sql, [$this->cabangId, $bulan, $tahun]);
    }
    
    /**
     * Get overtime expenses
     */
    public function getOvertimeExpenses($bulan, $tahun) {
        $sql = "SELECT * FROM pengeluaran
                WHERE cabang_id = ? 
                AND kategori = 'lembur'
                AND status = 'approved'
                AND MONTH(tanggal) = ?
                AND YEAR(tanggal) = ?
                ORDER BY tanggal DESC";
        
        return $this->db->select($sql, [$this->cabangId, $bulan, $tahun]);
    }
    
    /**
     * Get operational expenses
     */
    public function getOperationalExpenses($tanggalMulai, $tanggalSelesai) {
        $sql = "SELECT * FROM pengeluaran
                WHERE cabang_id = ? 
                AND kategori = 'operasional'
                AND status = 'approved'
                AND tanggal BETWEEN ? AND ?
                ORDER BY tanggal DESC";
        
        return $this->db->select($sql, [$this->cabangId, $tanggalMulai, $tanggalSelesai]);
    }
    
    /**
     * Get routine expenses (belanja, makan, dll)
     */
    public function getRoutineExpenses($tanggalMulai, $tanggalSelesai) {
        $sql = "SELECT * FROM pengeluaran
                WHERE cabang_id = ? 
                AND kategori = 'belanja'
                AND status = 'approved'
                AND tanggal BETWEEN ? AND ?
                ORDER BY tanggal DESC";
        
        return $this->db->select($sql, [$this->cabangId, $tanggalMulai, $tanggalSelesai]);
    }
    
    /**
     * Delete expense (soft delete - just mark as rejected)
     */
    public function deleteExpense($id) {
        return $this->rejectExpense($id, null);
    }
}
?>

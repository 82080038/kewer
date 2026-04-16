<?php
namespace Kewer\Export;

class Exporter {
    private $data;
    private $filename;
    private $format;
    
    public function __construct(array $data, $filename = 'export', $format = 'csv') {
        $this->data = $data;
        $this->filename = $filename;
        $this->format = strtolower($format);
    }
    
    /**
     * Export data to CSV
     */
    public function toCSV() {
        if (empty($this->data)) {
            throw new \Exception('No data to export');
        }
        
        $filename = $this->filename . '.csv';
        $filepath = BASE_PATH . '/exports/' . $filename;
        
        // Create exports directory if it doesn't exist
        $exportDir = dirname($filepath);
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        // Add BOM for UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write headers
        $headers = array_keys($this->data[0]);
        fputcsv($file, $headers);
        
        // Write data
        foreach ($this->data as $row) {
            fputcsv($file, $row);
        }
        
        fclose($file);
        
        return $filepath;
    }
    
    /**
     * Export data to Excel (XLSX)
     */
    public function toExcel() {
        // For now, use CSV format (can be upgraded to PhpSpreadsheet later)
        return $this->toCSV();
    }
    
    /**
     * Export data to JSON
     */
    public function toJSON() {
        $filename = $this->filename . '.json';
        $filepath = BASE_PATH . '/exports/' . $filename;
        
        // Create exports directory if it doesn't exist
        $exportDir = dirname($filepath);
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        
        file_put_contents($filepath, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $filepath;
    }
    
    /**
     * Export data to PDF (basic implementation)
     */
    public function toPDF() {
        // This would require a library like TCPDF or DomPDF
        // For now, return a placeholder
        throw new \Exception('PDF export requires additional library (TCPDF or DomPDF)');
    }
    
    /**
     * Export based on format
     */
    public function export() {
        switch ($this->format) {
            case 'csv':
                return $this->toCSV();
            case 'excel':
            case 'xlsx':
                return $this->toExcel();
            case 'json':
                return $this->toJSON();
            case 'pdf':
                return $this->toPDF();
            default:
                throw new \Exception("Unsupported export format: {$this->format}");
        }
    }
    
    /**
     * Download file
     */
    public function download() {
        $filepath = $this->export();
        
        if (!file_exists($filepath)) {
            throw new \Exception('Export file not found');
        }
        
        $filename = basename($filepath);
        $filesize = filesize($filepath);
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $filesize);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        readfile($filepath);
        
        // Optionally delete file after download
        // unlink($filepath);
        
        exit;
    }
    
    /**
     * Set data
     */
    public function setData(array $data) {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Set filename
     */
    public function setFilename($filename) {
        $this->filename = $filename;
        return $this;
    }
    
    /**
     * Set format
     */
    public function setFormat($format) {
        $this->format = strtolower($format);
        return $this;
    }
}
?>

<?php
/**
 * OCR KTP Feature
 * 
 * Handles OCR (Optical Character Recognition) for KTP (Indonesian ID Card)
 * Extracts data from KTP images: NIK, Nama, TTL, Alamat
 * 
 * Requires Tesseract OCR library
 * Installation: composer require thiagoalessio/tesseract-ocr
 * 
 * Note: This is a placeholder implementation. For production use:
 * 1. Install Tesseract OCR on server
 * 2. Install PHP wrapper: composer require thiagoalessio/tesseract-ocr
 * 3. Configure Tesseract path
 * 4. Train Indonesian language data if needed
 */

class OcrKtp {
    private $tesseractPath;
    
    public function __construct($tesseractPath = null) {
        // Default Tesseract path for Linux
        $this->tesseractPath = $tesseractPath ?? '/usr/bin/tesseract';
    }
    
    /**
     * Extract text from KTP image
     * 
     * @param string $imagePath Path to KTP image file
     * @return array Extracted text and parsed data
     */
    public function extractFromImage($imagePath) {
        // Check if Tesseract is available
        if (!$this->isTesseractAvailable()) {
            return [
                'success' => false,
                'error' => 'Tesseract OCR tidak tersedia. Silakan install Tesseract OCR.'
            ];
        }
        
        // Check if image file exists
        if (!file_exists($imagePath)) {
            return [
                'success' => false,
                'error' => 'File gambar tidak ditemukan'
            ];
        }
        
        try {
            // Extract text using Tesseract
            $text = $this->runTesseract($imagePath);
            
            // Parse extracted text for KTP data
            $ktpData = $this->parseKtpText($text);
            
            return [
                'success' => true,
                'raw_text' => $text,
                'parsed_data' => $ktpData
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Gagal mengekstrak data: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if Tesseract OCR is available
     */
    private function isTesseractAvailable() {
        return file_exists($this->tesseractPath) && is_executable($this->tesseractPath);
    }
    
    /**
     * Run Tesseract OCR command
     */
    private function runTesseract($imagePath) {
        // Create temporary file for output
        $tempFile = tempnam(sys_get_temp_dir(), 'tesseract_');
        unlink($tempFile);
        $tempFile .= '.txt';
        
        // Run Tesseract command
        $command = escapeshellcmd($this->tesseractPath) . ' ' . escapeshellarg($imagePath) . ' ' . escapeshellarg(substr($tempFile, 0, -4)) . ' -l eng+ind';
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Tesseract command failed with code: $returnCode");
        }
        
        // Read extracted text
        if (!file_exists($tempFile)) {
            throw new Exception("Tesseract output file not created");
        }
        
        $text = file_get_contents($tempFile);
        
        // Clean up temporary file
        unlink($tempFile);
        
        return $text;
    }
    
    /**
     * Parse extracted text for KTP data
     * 
     * This is a simplified parser. For production use, you may need:
     * - More sophisticated regex patterns
     * - Machine learning model for better accuracy
     * - Manual verification step
     */
    private function parseKtpText($text) {
        $data = [
            'nik' => $this->extractNIK($text),
            'nama' => $this->extractNama($text),
            'tempat_lahir' => $this->extractTempatLahir($text),
            'tanggal_lahir' => $this->extractTanggalLahir($text),
            'alamat' => $this->extractAlamat($text),
            'confidence' => $this->calculateConfidence($text)
        ];
        
        return $data;
    }
    
    /**
     * Extract NIK (16 digit number)
     */
    private function extractNIK($text) {
        // Pattern: 16 consecutive digits
        if (preg_match('/\b\d{16}\b/', $text, $matches)) {
            return $matches[0];
        }
        return null;
    }
    
    /**
     * Extract Name
     */
    private function extractNama($text) {
        // Pattern: "Nama:" followed by text until next field
        if (preg_match('/Nama\s*[:]\s*([^\n]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        // Alternative: Look for "Nama" keyword
        if (preg_match('/Nama\s+([A-Z][a-zA-Z\s]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Extract Place of Birth
     */
    private function extractTempatLahir($text) {
        // Pattern: "Tempat/Tgl Lahir:" followed by place and date
        if (preg_match('/Tempat\s*[\/]\s*Tgl\s*Lahir\s*[:]\s*([A-Z][a-zA-Z\s]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        // Alternative: Look for city name pattern
        if (preg_match('/Lahir\s*[:]\s*([A-Z][a-zA-Z\s]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Extract Date of Birth
     */
    private function extractTanggalLahir($text) {
        // Pattern: Date in DD-MM-YYYY or DD/MM/YYYY format
        if (preg_match('/\b(\d{2}[-\/]\d{2}[-\/]\d{4})\b/', $text, $matches)) {
            return $matches[1];
        }
        
        // Alternative: Look for year pattern
        if (preg_match('/(\d{4})/', $text, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Extract Address
     */
    private function extractAlamat($text) {
        // Pattern: "Alamat:" followed by address text
        if (preg_match('/Alamat\s*[:]\s*([^\n]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        // Alternative: Look for street/pattern
        if (preg_match('/Jl\.?\s+([^\n]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Calculate confidence score based on extraction results
     */
    private function calculateConfidence($text) {
        $score = 0;
        $maxScore = 5;
        
        if ($this->extractNIK($text)) $score++;
        if ($this->extractNama($text)) $score++;
        if ($this->extractTempatLahir($text)) $score++;
        if ($this->extractTanggalLahir($text)) $score++;
        if ($this->extractAlamat($text)) $score++;
        
        return ($score / $maxScore) * 100;
    }
    
    /**
     * Validate KTP data
     */
    public function validateKtpData($data) {
        $errors = [];
        
        // Validate NIK (16 digits)
        if (!$data['nik'] || !preg_match('/^\d{16}$/', $data['nik'])) {
            $errors[] = 'NIK harus 16 digit angka';
        }
        
        // Validate Name (not empty)
        if (!$data['nama'] || strlen($data['nama']) < 3) {
            $errors[] = 'Nama tidak valid';
        }
        
        // Validate Date of Birth
        if ($data['tanggal_lahir']) {
            $date = DateTime::createFromFormat('d-m-Y', $data['tanggal_lahir']);
            if (!$date) {
                $errors[] = 'Format tanggal lahir tidak valid (harus DD-MM-YYYY)';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Manual verification form data
     * 
     * This method provides a fallback when OCR is not available
     * or when manual verification is preferred
     */
    public function manualVerification($formData) {
        $data = [
            'nik' => $formData['nik'] ?? null,
            'nama' => $formData['nama'] ?? null,
            'tempat_lahir' => $formData['tempat_lahir'] ?? null,
            'tanggal_lahir' => $formData['tanggal_lahir'] ?? null,
            'alamat' => $formData['alamat'] ?? null,
            'confidence' => 100, // Manual input = 100% confidence
            'source' => 'manual'
        ];
        
        $validation = $this->validateKtpData($data);
        
        return [
            'success' => $validation['valid'],
            'data' => $data,
            'errors' => $validation['errors']
        ];
    }
}
?>

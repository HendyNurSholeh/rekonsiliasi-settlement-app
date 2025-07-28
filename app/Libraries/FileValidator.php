<?php

namespace App\Libraries;

use DateTime;

class FileValidator
{
    private $errors = [];
    private $warnings = [];
    private $stats = [];

    // File type configurations
    private $fileConfigs = [
        'settle_edu' => [
            'extension' => 'txt',
            'delimiter' => ';',
            'required_columns' => ['TANGGAL', 'KODE PRODUK', 'NAMA_PRODUK', 'KODE_JURUSAN', 'KODE_BIAYA', 'NAMA_BIAYA', 'NOREK', 'AMOUNT', 'KODE_PRODUK_PRIVIDER'],
            'date_column' => 'TANGGAL',
            'amount_column' => 'AMOUNT',
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'encoding' => 'UTF-8'
        ],
        'settle_pajak' => [
            'extension' => 'txt',
            'delimiter' => '|',
            'required_columns' => ['TANGGAL', 'KODE PRODUK', 'NAMA_PRODUK', 'KODE_JURUSAN', 'KODE_BIAYA', 'NAMA_BIAYA', 'JENIS', 'NOREK', 'NAMA_REKENING', 'AMOUNT', 'NARATIVE'],
            'date_column' => 'TANGGAL',
            'amount_column' => 'AMOUNT',
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'encoding' => 'UTF-8'
        ],
        'mgate' => [
            'extension' => 'csv',
            'delimiter' => ';',
            'required_columns' => ['branch', 'stmt_booking_date', 'ft_bil_product', 'stmt_date_time', 'amount', 'fee'],
            'date_column' => 'stmt_booking_date',
            'amount_column' => 'amount',
            'max_file_size' => 50 * 1024 * 1024, // 50MB
            'encoding' => 'UTF-8'
        ],
        'agn_detail' => [
            'extension' => 'txt',
            'delimiter' => ';',
            'required_columns' => ['IDTRX', 'BLTH', 'TGL_WAKTU', 'IDPARTNER', 'PRODUK', 'MERCHANT', 'IDPEL', 'RP_AMOUNT', 'STATUS'],
            'date_column' => 'TGL_WAKTU', 
            'amount_column' => 'RP_AMOUNT',
            'max_file_size' => 20 * 1024 * 1024, // 20MB
            'encoding' => 'UTF-8'
        ]
    ];

    /**
     * Validasi file utama
     */
    public function validateFile($filePath, $fileType, $expectedDate)
    {
        $this->resetValidation();
        
        // 1. Validasi basic file
        if (!$this->validateBasicFile($filePath, $fileType)) {
            return $this->getValidationResult();
        }

        // 2. Validasi format dan struktur
        if (!$this->validateFileStructure($filePath, $fileType)) {
            return $this->getValidationResult();
        }

        // 3. Validasi data content
        if (!$this->validateFileContent($filePath, $fileType, $expectedDate)) {
            return $this->getValidationResult();
        }

        // 4. Generate statistics
        $this->generateFileStats($filePath, $fileType);

        return $this->getValidationResult();
    }

    /**
     * Validasi basic file (ukuran, format, dll)
     */
    private function validateBasicFile($filePath, $fileType)
    {
        $config = $this->fileConfigs[$fileType];
        
        // Check file exists
        if (!file_exists($filePath)) {
            $this->addError("File tidak ditemukan: {$filePath}");
            return false;
        }

        // Check file size
        $fileSize = filesize($filePath);
        if ($fileSize > $config['max_file_size']) {
            $maxSizeMB = $config['max_file_size'] / (1024 * 1024);
            $currentSizeMB = round($fileSize / (1024 * 1024), 2);
            $this->addError("Ukuran file terlalu besar: {$currentSizeMB}MB (maksimal {$maxSizeMB}MB)");
            return false;
        }

        // Check file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension !== $config['extension']) {
            $this->addError("Format file salah. Harapan: .{$config['extension']}, ditemukan: .{$extension}");
            return false;
        }

        // Check file is readable
        if (!is_readable($filePath)) {
            $this->addError("File tidak dapat dibaca");
            return false;
        }

        return true;
    }

    /**
     * Validasi struktur file (header, delimiter, encoding)
     */
    private function validateFileStructure($filePath, $fileType)
    {
        $config = $this->fileConfigs[$fileType];
        
        // Read first few lines
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->addError("Tidak dapat membuka file untuk validasi");
            return false;
        }

        // Check encoding (basic check)
        $firstLine = fgets($handle);
        if (!mb_check_encoding($firstLine, $config['encoding'])) {
            $this->addWarning("File mungkin tidak menggunakan encoding {$config['encoding']}");
        }

        // Reset to beginning
        rewind($handle);
        
        // Validate header
        $headerLine = fgets($handle);
        $headers = explode($config['delimiter'], trim($headerLine));
        
        // Clean headers - remove BOM, trim spaces, and empty headers (from trailing delimiters)
        $headers = array_map(function($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, $headers);
        
        // Remove empty headers (from trailing delimiters like "TERMINALID;")
        $headers = array_filter($headers, function($header) {
            return !empty($header);
        });
        
        // Re-index
        $headers = array_values($headers);
        
        // Check required columns
        $missingColumns = [];
        foreach ($config['required_columns'] as $requiredCol) {
            if (!in_array($requiredCol, $headers)) {
                $missingColumns[] = $requiredCol;
            }
        }

        if (!empty($missingColumns)) {
            $this->addError("Kolom yang diperlukan tidak ditemukan: " . implode(', ', $missingColumns));
            fclose($handle);
            return false;
        }

        // Check if file has data (at least 2 lines)
        $dataLine = fgets($handle);
        if (!$dataLine || trim($dataLine) === '') {
            $this->addError("File tidak memiliki data (hanya header)");
            fclose($handle);
            return false;
        }

        fclose($handle);
        return true;
    }

    /**
     * Validasi content data (tanggal, format amount, dll)
     */
    private function validateFileContent($filePath, $fileType, $expectedDate)
    {
        $config = $this->fileConfigs[$fileType];
        $handle = fopen($filePath, 'r');
        
        // Get and clean header
        $headerLine = trim(fgets($handle));
        $headers = explode($config['delimiter'], $headerLine);
        
        // Clean headers - remove BOM and empty headers (from trailing delimiters)
        $headers = array_map(function($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, $headers);
        
        // Remove empty headers (from trailing delimiters like "TERMINALID;")
        $headers = array_filter($headers, function($header) {
            return !empty($header);
        });
        
        // Re-index
        $headers = array_values($headers);
        
        $expectedColumnCount = count($headers);
        log_message('debug', 'Expected column count: ' . $expectedColumnCount . ', Headers: ' . implode(', ', $headers));
        
        $lineNumber = 2;
        $totalRows = 0;
        $invalidDates = 0;
        $invalidAmounts = 0;
        $columnMismatchErrors = 0;
        $totalAmount = 0;

        // Get column indexes
        $dateColIndex = array_search($config['date_column'], $headers);
        $amountColIndex = array_search($config['amount_column'], $headers);

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (empty($line)) continue;

            $data = explode($config['delimiter'], $line);
            $totalRows++;
            
            // STRICT COLUMN COUNT VALIDATION - reject if count doesn't match exactly
            if (count($data) !== $expectedColumnCount) {
                $columnMismatchErrors++;
                if ($columnMismatchErrors <= 5) { // Show max 5 examples
                    $this->addError("Baris {$lineNumber}: Jumlah kolom tidak sesuai. Ditemukan " . count($data) . " kolom, diharapkan {$expectedColumnCount} kolom.");
                }
            }

            // Validate date
            if ($dateColIndex !== false && isset($data[$dateColIndex])) {
                $dateValue = trim($data[$dateColIndex]);
                
                // Convert date format if needed (for mgate)
                if ($fileType === 'mgate') {
                    // Convert from YMMDD to Y-M-D format
                    if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $dateValue, $matches)) {
                        $dateValue = "20{$matches[1]}-{$matches[2]}-{$matches[3]}";
                    }
                }

                if (!$this->validateDateFormat($dateValue, $expectedDate)) {
                    $invalidDates++;
                    if ($invalidDates <= 5) { // Show max 5 examples
                        $this->addError("Baris {$lineNumber}: Tanggal tidak sesuai. Ditemukan: {$dateValue}, harapan: {$expectedDate}");
                    }
                }
            }

            // Validate amount
            if ($amountColIndex !== false && isset($data[$amountColIndex])) {
                $amountValue = trim($data[$amountColIndex]);
                if (!$this->validateAmountFormat($amountValue)) {
                    $invalidAmounts++;
                    if ($invalidAmounts <= 3) { // Show max 3 examples
                        $this->addError("Baris {$lineNumber}: Format amount tidak valid: {$amountValue}");
                    }
                } else {
                    $totalAmount += $this->parseAmount($amountValue);
                }
            }

            $lineNumber++;
        }

        fclose($handle);

        // Summary validation errors
        if ($columnMismatchErrors > 5) {
            $this->addError("Dan " . ($columnMismatchErrors - 5) . " baris dengan jumlah kolom salah lainnya");
        }
        
        if ($invalidDates > 5) {
            $this->addError("Dan " . ($invalidDates - 5) . " tanggal tidak valid lainnya");
        }

        if ($invalidAmounts > 3) {
            $this->addError("Dan " . ($invalidAmounts - 3) . " amount tidak valid lainnya");
        }

        // Store stats
        $this->stats = [
            'total_rows' => $totalRows,
            'expected_columns' => $expectedColumnCount,
            'column_mismatch_errors' => $columnMismatchErrors,
            'invalid_dates' => $invalidDates,
            'invalid_amounts' => $invalidAmounts,
            'total_amount' => $totalAmount,
            'valid_data_percentage' => ($columnMismatchErrors === 0 && $invalidDates === 0 && $invalidAmounts === 0) ? 100 : 0
        ];

        // Fail if ANY errors found (zero tolerance)
        if ($columnMismatchErrors > 0 || $invalidDates > 0 || $invalidAmounts > 0) {
            $errorSummary = [];
            if ($columnMismatchErrors > 0) {
                $errorSummary[] = "{$columnMismatchErrors} baris dengan format kolom salah";
            }
            if ($invalidDates > 0) {
                $errorSummary[] = "{$invalidDates} tanggal tidak valid";
            }
            if ($invalidAmounts > 0) {
                $errorSummary[] = "{$invalidAmounts} amount tidak valid";
            }
            $this->addError("File ditolak karena ditemukan: " . implode(", ", $errorSummary) . ". Semua data harus valid.");
            return false;
        }

        return true;
    }

    /**
     * Validasi format tanggal dan kesesuaian dengan expected date
     */
    private function validateDateFormat($dateValue, $expectedDate)
    {
        // Handle datetime format for AGN Detail (TGL_WAKTU)
        if (strpos($dateValue, ' ') !== false) {
            // Extract date part from datetime
            $datePart = substr($dateValue, 0, 10);
            return $datePart === $expectedDate;
        }
        
        // Try to parse date for other formats
        $date = DateTime::createFromFormat('Y-m-d', $dateValue);
        if (!$date || $date->format('Y-m-d') !== $dateValue) {
            return false;
        }

        // Check if matches expected date
        return $dateValue === $expectedDate;
    }

    /**
     * Validasi format amount
     */
    private function validateAmountFormat($amountValue)
    {
        // Remove common formatting
        $cleaned = str_replace([',', '.00'], '', $amountValue);
        return is_numeric($cleaned) && $cleaned >= 0;
    }

    /**
     * Parse amount to numeric value
     */
    private function parseAmount($amountValue)
    {
        $cleaned = str_replace([','], '', $amountValue);
        return floatval($cleaned);
    }

    /**
     * Generate file statistics
     */
    private function generateFileStats($filePath, $fileType)
    {
        $this->stats['file_size'] = round(filesize($filePath) / 1024, 2) . ' KB';
        $this->stats['file_type'] = $fileType;
        $this->stats['validation_time'] = date('Y-m-d H:i:s');
    }

    /**
     * Helper methods
     */
    private function resetValidation()
    {
        $this->errors = [];
        $this->warnings = [];
        $this->stats = [];
    }

    private function addError($message)
    {
        $this->errors[] = $message;
    }

    private function addWarning($message)
    {
        $this->warnings[] = $message;
    }

    private function getValidationResult()
    {
        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'stats' => $this->stats
        ];
    }

    /**
     * Get file configuration for specific type
     */
    public function getFileConfig($fileType)
    {
        return $this->fileConfigs[$fileType] ?? null;
    }
}

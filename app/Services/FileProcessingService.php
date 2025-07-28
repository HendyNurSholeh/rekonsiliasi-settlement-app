<?php

namespace App\Services;

use App\Libraries\FileValidator;
use App\Models\AgnDetailModel;
use App\Models\AgnSettleEduModel;
use App\Models\AgnSettlePajakModel;
use App\Models\AgnTrxMgateModel;

class FileProcessingService
{
    private $fileValidator;
    private $models = [];

    public function __construct()
    {
        $this->fileValidator = new FileValidator();
        
        // Initialize models
        $this->models = [
            'agn_detail' => new AgnDetailModel(),
            'settle_edu' => new AgnSettleEduModel(),
            'settle_pajak' => new AgnSettlePajakModel(),
            'mgate' => new AgnTrxMgateModel()
        ];
    }

    /**
     * Process uploaded file: validate then insert to database
     */
    public function processUploadedFile($filePath, $fileType, $tanggalRekon)
    {
        try {
            // Step 1: Validate file
            $validationResult = $this->fileValidator->validateFile($filePath, $fileType, $tanggalRekon);
            
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'message' => 'Validasi file gagal',
                    'errors' => $validationResult['errors'],
                    'warnings' => $validationResult['warnings'] ?? []
                ];
            }

            // Step 2: Parse and insert data if validation passed
            $insertResult = $this->insertFileDataToDatabase($filePath, $fileType, $tanggalRekon);
            
            if (!$insertResult['success']) {
                return $insertResult;
            }

            // Step 3: Return success with statistics
            return [
                'success' => true,
                'message' => 'File berhasil divalidasi dan data tersimpan',
                'stats' => $validationResult['stats'],
                'insert_stats' => $insertResult['stats'],
                'warnings' => $validationResult['warnings'] ?? []
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error processing file: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => 'Error sistem: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error_type' => get_class($e)
                ]
            ];
        }
    }

    /**
     * Insert file data to appropriate database table
     */
    private function insertFileDataToDatabase($filePath, $fileType, $tanggalRekon)
    {
        $config = $this->fileValidator->getFileConfig($fileType);
        $model = $this->models[$fileType];
        
        $handle = fopen($filePath, 'r');
        
        // Skip header
        $headerLine = fgets($handle);
        $headers = str_getcsv($headerLine, $config['delimiter']);
        
        // Clean headers - remove any BOM or extra whitespace
        $headers = array_map(function($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, $headers);
        
        // Log original headers for debugging
        log_message('debug', 'Original headers count: ' . count($headers));
        log_message('debug', 'Original headers: ' . print_r($headers, true));
        
        // Remove empty headers (caused by trailing semicolons)
        $headers = array_filter($headers, function($header) {
            return !empty($header);
        });
        
        // Re-index the array to ensure consecutive indices
        $headers = array_values($headers);
        
        log_message('debug', 'Cleaned headers count: ' . count($headers));
        log_message('debug', 'Cleaned headers: ' . print_r($headers, true));
        
        $insertedRows = 0;
        $errors = [];
        $batchData = [];
        $batchSize = 100; // Process in batches

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Clear existing data for this date (use appropriate field name based on file type)
            if ($fileType === 'agn_detail') {
                $model->where('v_TGL_FILE_REKON', $tanggalRekon)->delete();
            } else {
                $model->where('tanggal_rekon', $tanggalRekon)->delete();
            }

            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if (empty($line)) continue;

                $data = str_getcsv($line, $config['delimiter']);
                
                // Debug: Log problematic lines
                if (count($data) !== count($headers)) {
                    log_message('debug', "Column count mismatch - Headers: " . count($headers) . ", Data: " . count($data) . " for line: " . substr($line, 0, 100));
                }
                
                // Ensure data array has the same length as headers
                $data = array_pad($data, count($headers), '');
                
                // Clean data and ensure all values are strings - CRITICAL FIX
                $data = array_map(function($value) {
                    // Handle arrays specifically
                    if (is_array($value)) {
                        log_message('warning', "Array value found in CSV data: " . print_r($value, true));
                        return implode(',', array_map('strval', $value));
                    }
                    
                    // Handle null values
                    if ($value === null) {
                        return '';
                    }
                    
                    // Convert to string and trim
                    return trim((string) $value);
                }, $data);
                
                // Map data to database columns
                $mappedData = $this->mapFileDataToDbColumns($data, $headers, $fileType, $tanggalRekon);
                
                if ($mappedData) {
                    // Validate mapped data before adding to batch - ensure NO arrays
                    foreach ($mappedData as $key => $value) {
                        if (is_array($value)) {
                            log_message('error', "Array value detected for key {$key}: " . print_r($value, true));
                            // Convert array to string safely
                            $mappedData[$key] = is_array($value) ? implode(',', array_map('strval', $value)) : (string) $value;
                        } else {
                            // Force conversion to string for all non-numeric fields
                            if (!is_numeric($value) && !is_float($value)) {
                                $mappedData[$key] = (string) $value;
                            }
                        }
                    }
                    
                    // Double check - ensure absolutely no arrays remain
                    foreach ($mappedData as $key => $value) {
                        if (is_array($value)) {
                            log_message('error', "CRITICAL: Array still exists after conversion for key {$key}");
                            $mappedData[$key] = '';  // Set to empty string as fallback
                        }
                    }
                    
                    $batchData[] = $mappedData;
                    
                    // Insert in batches
                    if (count($batchData) >= $batchSize) {
                        try {
                            $model->insertBatch($batchData);
                            $insertedRows += count($batchData);
                            log_message('debug', "Inserted batch of " . count($batchData) . " records");
                        } catch (\Exception $e) {
                            log_message('error', 'Batch insert error: ' . $e->getMessage());
                            // Try inserting individually to identify problematic records
                            foreach ($batchData as $record) {
                                try {
                                    $model->insert($record);
                                    $insertedRows++;
                                } catch (\Exception $e2) {
                                    log_message('error', 'Individual insert error: ' . $e2->getMessage() . ' for record: ' . print_r($record, true));
                                }
                            }
                        }
                        $batchData = [];
                    }
                }
            }

            // Insert remaining data
            if (!empty($batchData)) {
                try {
                    $model->insertBatch($batchData);
                    $insertedRows += count($batchData);
                    log_message('debug', "Inserted final batch of " . count($batchData) . " records");
                } catch (\Exception $e) {
                    log_message('error', 'Final batch insert error: ' . $e->getMessage());
                    // Try inserting individually
                    foreach ($batchData as $record) {
                        try {
                            $model->insert($record);
                            $insertedRows++;
                        } catch (\Exception $e2) {
                            log_message('error', 'Individual insert error: ' . $e2->getMessage() . ' for record: ' . print_r($record, true));
                        }
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Database transaction failed');
            }

            fclose($handle);

            return [
                'success' => true,
                'stats' => [
                    'inserted_rows' => $insertedRows,
                    'table' => $model->getTable(),
                    'date' => $tanggalRekon
                ]
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            
            log_message('error', 'Database insertion error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', 'Error trace: ' . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => 'Error database: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error_type' => get_class($e)
                ]
            ];
        }
    }

    /**
     * Map file data columns to database columns
     */
    private function mapFileDataToDbColumns($data, $headers, $fileType, $tanggalRekon)
    {
        $mappedData = ['tanggal_rekon' => $tanggalRekon];

        switch ($fileType) {
            case 'settle_edu':
                return $this->mapEducationData($data, $headers, $tanggalRekon);
                
            case 'settle_pajak':
                return $this->mapPajakData($data, $headers, $tanggalRekon);
                
            case 'mgate':
                return $this->mapMgateData($data, $headers, $tanggalRekon);
                
            case 'agn_detail':
                return $this->mapAgnDetailData($data, $headers, $tanggalRekon);
                
            default:
                return null;
        }
    }

    /**
     * Map education file data
     */
    private function mapEducationData($data, $headers, $tanggalRekon)
    {
        $mapping = [
            'tanggal_rekon' => $tanggalRekon,
            'tanggal' => $this->getDataByHeader($data, $headers, 'TANGGAL'),
            'kode_produk' => $this->getDataByHeader($data, $headers, 'KODE PRODUK'),
            'nama_produk' => $this->getDataByHeader($data, $headers, 'NAMA_PRODUK'),
            'kode_jurusan' => $this->getDataByHeader($data, $headers, 'KODE_JURUSAN'),
            'kode_biaya' => $this->getDataByHeader($data, $headers, 'KODE_BIAYA'),
            'nama_biaya' => $this->getDataByHeader($data, $headers, 'NAMA_BIAYA'),
            'norek' => $this->getDataByHeader($data, $headers, 'NOREK'),
            'amount' => $this->parseAmount($this->getDataByHeader($data, $headers, 'AMOUNT')),
            'kode_produk_provider' => $this->getDataByHeader($data, $headers, 'KODE_PRODUK_PRIVIDER')
        ];

        return $mapping;
    }

    /**
     * Map pajak file data
     */
    private function mapPajakData($data, $headers, $tanggalRekon)
    {
        $mapping = [
            'tanggal_rekon' => $tanggalRekon,
            'tanggal' => $this->getDataByHeader($data, $headers, 'TANGGAL'),
            'kode_produk' => $this->getDataByHeader($data, $headers, 'KODE PRODUK'),
            'nama_produk' => $this->getDataByHeader($data, $headers, 'NAMA_PRODUK'),
            'kode_jurusan' => $this->getDataByHeader($data, $headers, 'KODE_JURUSAN'),
            'kode_biaya' => $this->getDataByHeader($data, $headers, 'KODE_BIAYA'),
            'nama_biaya' => $this->getDataByHeader($data, $headers, 'NAMA_BIAYA'),
            'jenis' => $this->getDataByHeader($data, $headers, 'JENIS'),
            'norek' => $this->getDataByHeader($data, $headers, 'NOREK'),
            'nama_rekening' => $this->getDataByHeader($data, $headers, 'NAMA_REKENING'),
            'amount' => $this->parseAmount($this->getDataByHeader($data, $headers, 'AMOUNT')),
            'narative' => $this->getDataByHeader($data, $headers, 'NARATIVE'),
            'kode_produk_provider' => $this->getDataByHeader($data, $headers, 'KODE_PRODUK_PROVIDER')
        ];

        return $mapping;
    }

    /**
     * Map mgate file data
     */
    private function mapMgateData($data, $headers, $tanggalRekon)
    {
        $stmtDate = $this->getDataByHeader($data, $headers, 'stmt_booking_date');
        
        // Convert YMMDD to Y-M-D
        if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $stmtDate, $matches)) {
            $stmtDate = "20{$matches[1]}-{$matches[2]}-{$matches[3]}";
        }

        $mapping = [
            'tanggal_rekon' => $tanggalRekon,
            'branch' => $this->getDataByHeader($data, $headers, 'branch'),
            'stmt_booking_date' => $stmtDate,
            'ft_bil_product' => $this->getDataByHeader($data, $headers, 'ft_bil_product'),
            'stmt_date_time' => $this->getDataByHeader($data, $headers, 'stmt_date_time'),
            'ft_bil_customer' => $this->getDataByHeader($data, $headers, 'ft_bil_customer'),
            'ft_term_id' => $this->getDataByHeader($data, $headers, 'ft_term_id'),
            'ft_debit_acct_no' => $this->getDataByHeader($data, $headers, 'ft_debit_acct_no'),
            'ft_trans_reff' => $this->getDataByHeader($data, $headers, 'ft_trans_reff'),
            'stmt_our_reference' => $this->getDataByHeader($data, $headers, 'stmt_our_reference'),
            'recipt_no' => $this->getDataByHeader($data, $headers, 'recipt_no'),
            'amount' => $this->parseAmount($this->getDataByHeader($data, $headers, 'amount')),
            'fee' => $this->parseAmount($this->getDataByHeader($data, $headers, 'fee'))
        ];

        return $mapping;
    }

    /**
     * Map agn detail file data
     */
    private function mapAgnDetailData($data, $headers, $tanggalRekon)
    {
        $blthTagihan = $this->getDataByHeader($data, $headers, 'BLTH_TAGIHAN');
        
        // Ensure all values are strings before mapping
        $mapping = [];
        $fieldMappings = [
            'IDTRX' => 'IDTRX',
            'BLTH' => 'BLTH',
            'TGL_WAKTU' => 'TGL_WAKTU',
            'IDPARTNER' => 'IDPARTNER',
            'PRODUK' => 'PRODUK',
            'MERCHANT' => 'MERCHANT',
            'IDPEL' => 'IDPEL',
            'RP_BILLER_POKOK' => 'RP_BILLER_POKOK',
            'RP_BILLER_DENDA' => 'RP_BILLER_DENDA',
            'RP_BILLER_LAIN' => 'RP_BILLER_LAIN',
            'RP_BILLER_POTONGAN' => 'RP_BILLER_POTONGAN',
            'RP_BILLER_TAG' => 'RP_BILLER_TAG',
            'RP_FEE_APP' => 'RP_FEE_APP',
            'RP_FEE_PARTNER' => 'RP_FEE_PARTNER',
            'RP_FEE_BILLER' => 'RP_FEE_BILLER',
            'RP_FEE_AGREGATOR' => 'RP_FEE_AGREGATOR',
            'RP_FEE_USER' => 'RP_FEE_USER',
            'RP_FEE_STRUK' => 'RP_FEE_STRUK',
            'RP_AMOUNT_STRUK' => 'RP_AMOUNT_STRUK',
            'RP_AMOUNT' => 'RP_AMOUNT',
            'LEMBAR' => 'LEMBAR',
            'AGN_REF' => 'AGN_REF',
            'CLIENT_REF' => 'CLIENT_REF',
            'CLIENT_STAN' => 'CLIENT_STAN',
            'CLIENT_IDTRX' => 'CLIENT_IDTRX',
            'BLTH_TAGIHAN' => 'BLTH_TAGIHAN',
            'STATUS' => 'STATUS',
            'KETERANGAN' => 'KETERANGAN',
            'SOURCE_DB' => 'SOURCE_DB',
            'OWNER' => 'OWNER',
            'OUTLET' => 'OUTLET',
            'KODE_USER' => 'KODE_USER',
            'USER' => 'USER',
            'SUB_IDPEL' => 'SUB_IDPEL',
            'IDPRODUK' => 'IDPRODUK',
            'REFF_BKS' => 'REFF_BKS',
            'TERMINALID' => 'TERMINALID'
        ];
        
        foreach ($fieldMappings as $dbField => $headerName) {
            $value = $this->getDataByHeader($data, $headers, $headerName);
            
            // Convert amounts to float for specific fields
            if (strpos($dbField, 'RP_') === 0) {
                $mapping[$dbField] = $this->parseAmount($value);
            } else {
                // Ensure all other values are strings
                $mapping[$dbField] = (string) $value;
            }
        }
        
        // Handle special case for BLTH_TAGIHAN with pipe separator
        $mapping['BLTH_TAGIHAN'] = str_replace('|', ',', (string) $blthTagihan);
        
        // Add date field
        $mapping['v_TGL_FILE_REKON'] = (string) $tanggalRekon;

        return $mapping;
    }

    /**
     * Parse amount value safely
     */
    private function parseAmount($amountValue)
    {
        // Handle null or empty values
        if ($amountValue === null || $amountValue === '') {
            return 0.0;
        }
        
        // Handle arrays
        if (is_array($amountValue)) {
            log_message('warning', "Array value passed to parseAmount: " . print_r($amountValue, true));
            $amountValue = implode(',', array_map('strval', $amountValue));
        }
        
        // Convert to string first
        $amountValue = (string) $amountValue;
        
        // If it's already a number, return it
        if (is_numeric($amountValue)) {
            return floatval($amountValue);
        }
        
        // Clean the string value
        $cleaned = str_replace([',', ' '], '', trim($amountValue));
        
        // If still not numeric after cleaning, return 0
        if (!is_numeric($cleaned)) {
            log_message('warning', "Non-numeric amount value: '{$amountValue}' cleaned to '{$cleaned}'");
            return 0.0;
        }
        
        return floatval($cleaned);
    }

    /**
     * Safely get data by header name
     */
    private function getDataByHeader($data, $headers, $headerName)
    {
        $index = array_search($headerName, $headers);
        if ($index === false) {
            log_message('debug', "Header '{$headerName}' not found in headers");
            return '';
        }
        
        if (!isset($data[$index])) {
            log_message('debug', "No data found at index {$index} for header '{$headerName}'");
            return '';
        }
        
        $value = $data[$index];
        
        // Ensure it's not an array
        if (is_array($value)) {
            log_message('warning', "Array value found for header '{$headerName}': " . print_r($value, true));
            // Convert array to string safely
            return implode(',', array_map('strval', $value));
        }
        
        // Ensure it's a string and handle null values
        if ($value === null) {
            return '';
        }
        
        // Convert to string safely
        return trim((string) $value);
    }

    /**
     * Get upload statistics for a date
     */
    public function getUploadStatistics($tanggalRekon)
    {
        $stats = [];

        foreach ($this->models as $type => $model) {
            if ($type === 'agn_detail') {
                // AGN Detail uses different field names
                $count = $model->where('v_TGL_FILE_REKON', $tanggalRekon)->countAllResults();
                $totalAmount = $model->selectSum('RP_AMOUNT')->where('v_TGL_FILE_REKON', $tanggalRekon)->first()['RP_AMOUNT'] ?? 0;
            } else {
                // Other models use standard field names
                $count = $model->where('tanggal_rekon', $tanggalRekon)->countAllResults();
                $totalAmount = $model->selectSum('amount')->where('tanggal_rekon', $tanggalRekon)->first()['amount'] ?? 0;
            }
            
            $stats[$type] = [
                'uploaded' => $count > 0,
                'count' => $count,
                'total_amount' => $totalAmount
            ];
        }

        return $stats;
    }
}

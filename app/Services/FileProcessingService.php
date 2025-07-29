<?php

namespace App\Services;

use App\Libraries\FileValidator;
use App\Models\TampAgnDetailModel;
use App\Models\TampAgnSettleEduModel;
use App\Models\TampAgnSettlePajakModel;
use App\Models\TampAgnTrxMgateModel;

class FileProcessingService
{
    private $fileValidator;
    private $models = [];

    public function __construct()
    {
        $this->fileValidator = new FileValidator();
        
        // Initialize models - using temporary tables
        $this->models = [
            'agn_detail' => new TampAgnDetailModel(),
            'settle_edu' => new TampAgnSettleEduModel(),
            'settle_pajak' => new TampAgnSettlePajakModel(),
            'mgate' => new TampAgnTrxMgateModel()
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
        
        log_message('info', 'Starting database insertion for file: ' . $filePath);
        log_message('info', 'File type: ' . $fileType . ', Date: ' . $tanggalRekon);
        
        // Read file content
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            throw new \Exception('Cannot read file: ' . $filePath);
        }
        
        // Split into lines
        $lines = explode("\n", $fileContent);
        if (empty($lines)) {
            throw new \Exception('File is empty');
        }
        
        log_message('info', 'Total lines in file: ' . count($lines));
        
        // Get header from first line
        $headerLine = trim($lines[0]);
        $headers = explode($config['delimiter'], $headerLine);
        
        // Clean headers - remove BOM and trim spaces
        $headers = array_map(function($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, $headers);
        
        // Only remove empty headers from the end (trailing delimiters)
        while (count($headers) > 0 && empty(end($headers))) {
            array_pop($headers);
        }
        
        // Re-index
        $headers = array_values($headers);
        
        log_message('info', 'Headers found: ' . implode(', ', $headers));
        log_message('info', 'Header count: ' . count($headers));
        
        $insertedRows = 0;
        $batchData = [];
        $batchSize = 100;

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Clear existing data for this date
            if ($fileType === 'agn_detail') {
                $deleted = $model->where('v_TGL_FILE_REKON', $tanggalRekon)->delete();
                log_message('info', 'Deleted existing records: ' . $deleted);
            } elseif ($fileType === 'settle_edu') {
                // Use Query Builder for table without primary key
                $deleted = $db->table('tamp_agn_settle_edu')->where('v_TGL_FILE_REKON', $tanggalRekon)->delete();
                log_message('info', 'Deleted existing records: ' . $deleted);
            } elseif (in_array($fileType, ['settle_pajak', 'mgate'])) {
                $deleted = $model->where('v_TGL_FILE_REKON', $tanggalRekon)->delete();
                log_message('info', 'Deleted existing records: ' . $deleted);
            } else {
                $deleted = $model->where('tanggal_rekon', $tanggalRekon)->delete();
                log_message('info', 'Deleted existing records: ' . $deleted);
            }

            // Process data lines (skip header)
            for ($i = 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (empty($line)) {
                    log_message('debug', 'Skipping empty line at index: ' . $i);
                    continue;
                }

                // Split line by delimiter
                $data = explode($config['delimiter'], $line);
                
                log_message('debug', 'Processing line ' . ($i + 1) . ' with ' . count($data) . ' columns, expected: ' . count($headers));
                
                // SPECIAL HANDLING FOR AGN_DETAIL: Allow 36, 37, or 38 columns
                if ($fileType === 'agn_detail') {
                    $columnCount = count($data);
                    $expectedCount = count($headers);
                    
                    // Allow 36, 37, or 38 columns for agn_detail
                    if ($columnCount < 36 || $columnCount > 38) {
                        throw new \Exception("Error pada baris " . ($i + 1) . ": Jumlah kolom tidak sesuai untuk file agn_detail. Ditemukan " . $columnCount . " kolom, diharapkan 36-38 kolom. Data: " . substr($line, 0, 100) . "...");
                    }
                    
                    // Normalize to 37 columns (standard format)
                    if ($columnCount === 36) {
                        // Add empty TERMINALID
                        $data[] = '';
                        log_message('info', 'Added empty TERMINALID for line ' . ($i + 1) . ' (36 columns)');
                    } elseif ($columnCount === 38) {
                        // Remove the last column (extra column beyond TERMINALID)
                        array_pop($data);
                        log_message('info', 'Removed extra column for line ' . ($i + 1) . ' (38 columns)');
                    }
                    // 37 columns is already correct, no changes needed
                } else {
                    // STRICT VALIDATION for other file types: Reject if column count doesn't match exactly
                    if (count($data) !== count($headers)) {
                        throw new \Exception("Error pada baris " . ($i + 1) . ": Jumlah kolom tidak sesuai. Ditemukan " . count($data) . " kolom, diharapkan " . count($headers) . " kolom. Data: " . substr($line, 0, 100) . "...");
                    }
                }
                
                // Convert all to strings
                $data = array_map(function($value) {
                    return trim((string) $value);
                }, $data);
                
                // Map data to database columns
                $mappedData = $this->mapFileDataToDbColumns($data, $headers, $fileType, $tanggalRekon);
                
                if ($mappedData) {
                    $batchData[] = $mappedData;
                    
                    // Insert in batches
                    if (count($batchData) >= $batchSize) {
                        // Use Query Builder directly for tables without primary key
                        if ($fileType === 'settle_edu') {
                            $db->table('tamp_agn_settle_edu')->insertBatch($batchData);
                        } elseif ($fileType === 'settle_pajak') {
                            $db->table('tamp_agn_settle_pajak')->insertBatch($batchData);
                        } elseif ($fileType === 'mgate') {
                            $db->table('tamp_agn_trx_mgate')->insertBatch($batchData);
                        } else {
                            $model->insertBatch($batchData);
                        }
                        $insertedRows += count($batchData);
                        log_message('info', 'Inserted batch of ' . count($batchData) . ' records. Total so far: ' . $insertedRows);
                        $batchData = [];
                    }
                } else {
                    log_message('warning', 'Failed to map data for line ' . ($i + 1));
                }
            }

            // Insert remaining data
            if (!empty($batchData)) {
                // Use Query Builder directly for tables without primary key
                if ($fileType === 'settle_edu') {
                    $db->table('tamp_agn_settle_edu')->insertBatch($batchData);
                } elseif ($fileType === 'settle_pajak') {
                    $db->table('tamp_agn_settle_pajak')->insertBatch($batchData);
                } elseif ($fileType === 'mgate') {
                    $db->table('tamp_agn_trx_mgate')->insertBatch($batchData);
                } else {
                    $model->insertBatch($batchData);
                }
                $insertedRows += count($batchData);
                log_message('info', 'Inserted final batch of ' . count($batchData) . ' records');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Database transaction failed');
            }

            log_message('info', 'Successfully inserted ' . $insertedRows . ' rows');

            return [
                'success' => true,
                'stats' => [
                    'inserted_rows' => $insertedRows,
                    'table' => $model->getTable(),
                    'date' => $tanggalRekon,
                    'total_lines_processed' => count($lines) - 1  // Exclude header
                ]
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            
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
            'TANGGAL' => $this->getDataByHeader($data, $headers, 'TANGGAL'),
            'KODE_PRODUK' => $this->getDataByHeader($data, $headers, 'KODE PRODUK'),
            'NAMA_PRODUK' => $this->getDataByHeader($data, $headers, 'NAMA_PRODUK'),
            'KODE_JURUSAN' => $this->getDataByHeader($data, $headers, 'KODE_JURUSAN'),
            'KODE_BIAYA' => $this->getDataByHeader($data, $headers, 'KODE_BIAYA'),
            'NAMA_BIAYA' => $this->getDataByHeader($data, $headers, 'NAMA_BIAYA'),
            'NOREK' => $this->getDataByHeader($data, $headers, 'NOREK'),
            'AMOUNT' => $this->parseAmount($this->getDataByHeader($data, $headers, 'AMOUNT')),
            'KODE_PRODUK_PRIVIDER' => $this->getDataByHeader($data, $headers, 'KODE_PRODUK_PRIVIDER'),
            'v_TGL_PROSES' => date('Y-m-d H:i:s'),
            'v_TGL_FILE_REKON' => $tanggalRekon
        ];

        return $mapping;
    }

    /**
     * Map pajak file data
     */
    private function mapPajakData($data, $headers, $tanggalRekon)
    {
        $mapping = [
            'TANGGAL' => $this->getDataByHeader($data, $headers, 'TANGGAL'),
            'KODE_PRODUK' => $this->getDataByHeader($data, $headers, 'KODE PRODUK'),
            'NAMA_PRODUK' => $this->getDataByHeader($data, $headers, 'NAMA_PRODUK'),
            'KODE_JURUSAN' => $this->getDataByHeader($data, $headers, 'KODE_JURUSAN'),
            'KODE_BIAYA' => $this->getDataByHeader($data, $headers, 'KODE_BIAYA'),
            'NAMA_BIAYA' => $this->getDataByHeader($data, $headers, 'NAMA_BIAYA'),
            'JENIS' => $this->getDataByHeader($data, $headers, 'JENIS'),
            'NOREK' => $this->getDataByHeader($data, $headers, 'NOREK'),
            'NAMA_REKENING' => $this->getDataByHeader($data, $headers, 'NAMA_REKENING'),
            'AMOUNT' => $this->parseAmount($this->getDataByHeader($data, $headers, 'AMOUNT')),
            'NARATIVE' => $this->getDataByHeader($data, $headers, 'NARATIVE'),
            'KODE_PRODUK_PROVIDER' => $this->getDataByHeader($data, $headers, 'KODE_PRODUK_PROVIDER'),
            'v_TGL_PROSES' => date('Y-m-d H:i:s'),
            'v_TGL_FILE_REKON' => $tanggalRekon
        ];

        return $mapping;
    }

    /**
     * Map mgate file data
     */
    private function mapMgateData($data, $headers, $tanggalRekon)
    {
        $stmtDate = $this->getDataByHeader($data, $headers, 'stmt_booking_date');
        
        // Convert date if needed - M-Gate uses YYYY-MM-DD format already
        // No conversion needed for this format
        
        $mapping = [
            'BRANCH' => $this->getDataByHeader($data, $headers, 'branch'),
            'STMT_BOOKING_DATE' => $stmtDate,
            'FT_BIL_PRODUCT' => $this->getDataByHeader($data, $headers, 'ft_bil_product'),
            'STMT_DATE_TIME' => $this->getDataByHeader($data, $headers, 'stmt_date_time'),
            'FT_BIL_CUSTOMER' => $this->getDataByHeader($data, $headers, 'ft_bil_customer'),
            'FT_TERM_ID' => $this->getDataByHeader($data, $headers, 'ft_term_id'),
            'FT_DEBIT_ACCT_NO' => $this->getDataByHeader($data, $headers, 'ft_debit_acct_no'),
            'FT_TRANS_REFF' => $this->getDataByHeader($data, $headers, 'ft_trans_reff'),
            'STMT_OUR_REFF' => $this->getDataByHeader($data, $headers, 'stmt_our_reference'),
            'RECIPT_NO' => $this->getDataByHeader($data, $headers, 'recipt_no'),
            'AMOUNT' => $this->parseAmount($this->getDataByHeader($data, $headers, 'amount')),
            'FEE' => $this->parseAmount($this->getDataByHeader($data, $headers, 'fee')),
            'v_TGL_PROSES' => date('Y-m-d H:i:s'),
            'v_TGL_FILE_REKON' => $tanggalRekon
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
                // AGN Detail uses v_TGL_FILE_REKON field
                $count = $model->where('v_TGL_FILE_REKON', $tanggalRekon)->countAllResults();
                $totalAmount = $model->selectSum('RP_AMOUNT')->where('v_TGL_FILE_REKON', $tanggalRekon)->first()['RP_AMOUNT'] ?? 0;
            } elseif (in_array($type, ['settle_edu', 'settle_pajak', 'mgate'])) {
                // All other models use v_TGL_FILE_REKON field and AMOUNT field
                $count = $model->where('v_TGL_FILE_REKON', $tanggalRekon)->countAllResults();
                $totalAmount = $model->selectSum('AMOUNT')->where('v_TGL_FILE_REKON', $tanggalRekon)->first()['AMOUNT'] ?? 0;
            } else {
                // Fallback for other models (should not happen with current setup)
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

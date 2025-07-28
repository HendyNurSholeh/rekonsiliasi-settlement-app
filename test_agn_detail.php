<?php

// Test script untuk debug agn_detail CSV upload (standalone)

// Test file path - sesuaikan dengan lokasi file CSV Anda
$testFilePath = 'd:\project\tsi\rekonsiliasi-settlement-app\202507281037_G_REKON_ANGON - 10. TRX_ANGON_CORE.csv';
$tanggalRekon = '2025-07-28';

echo "=== AGN DETAIL CSV TEST SCRIPT ===\n";
echo "File: $testFilePath\n";
echo "Tanggal Rekon: $tanggalRekon\n\n";

// Check if file exists
if (!file_exists($testFilePath)) {
    echo "ERROR: File tidak ditemukan: $testFilePath\n";
    echo "Silakan copy file CSV ke path tersebut atau ubah path di script ini.\n";
    exit;
}

echo "✓ File ditemukan\n";
echo "✓ Ukuran file: " . round(filesize($testFilePath) / 1024, 2) . " KB\n\n";

// Test 1: Read and validate file structure
echo "=== TEST 1: VALIDASI STRUKTUR FILE ===\n";

$fileContent = file_get_contents($testFilePath);
$lines = explode("\n", $fileContent);

echo "Jumlah baris: " . count($lines) . "\n";

// Get header
$headerLine = trim($lines[0]);
echo "Header raw: $headerLine\n";

$headers = explode(';', $headerLine);
echo "Jumlah kolom: " . count($headers) . "\n";

// Clean headers - remove quotes
$cleanHeaders = array_map(function($header) {
    $cleaned = trim($header);
    if (strlen($cleaned) >= 2 && substr($cleaned, 0, 1) === '"' && substr($cleaned, -1) === '"') {
        $cleaned = substr($cleaned, 1, -1);
    }
    return $cleaned;
}, $headers);

echo "Headers after cleaning:\n";
foreach ($cleanHeaders as $i => $header) {
    echo "  [$i] '$header'\n";
}

// Expected headers
$expectedHeaders = ['branch', 'stmt_booking_date', 'ft_bil_product', 'stmt_date_time', 'ft_bil_customer', 'ft_term_id', 'kosong', 'ft_debit_acct_no', 'ft_trans_reff', 'stmt_our_reference', 'recipt_no', 'amount', 'fee'];
echo "\nExpected headers:\n";
foreach ($expectedHeaders as $i => $header) {
    echo "  [$i] '$header'\n";
}

echo "\nHeader validation:\n";
$missingHeaders = [];
foreach ($expectedHeaders as $expected) {
    if (in_array($expected, $cleanHeaders)) {
        echo "  ✓ '$expected' found\n";
    } else {
        echo "  ✗ '$expected' MISSING\n";
        $missingHeaders[] = $expected;
    }
}

if (empty($missingHeaders)) {
    echo "✓ Semua header ditemukan!\n\n";
} else {
    echo "✗ Header yang hilang: " . implode(', ', $missingHeaders) . "\n\n";
}

// Test 2: Sample data parsing
echo "=== TEST 2: PARSING DATA SAMPLE ===\n";

if (count($lines) > 1) {
    $sampleLine = trim($lines[1]);
    echo "Sample line raw: $sampleLine\n";
    
    $sampleData = explode(';', $sampleLine);
    echo "Sample data columns: " . count($sampleData) . "\n";
    
    // Clean sample data - remove quotes
    $cleanSampleData = array_map(function($value) {
        $cleaned = trim($value);
        if (strlen($cleaned) >= 2 && substr($cleaned, 0, 1) === '"' && substr($cleaned, -1) === '"') {
            $cleaned = substr($cleaned, 1, -1);
        }
        return $cleaned;
    }, $sampleData);
    
    echo "Sample data after cleaning:\n";
    foreach ($cleanSampleData as $i => $value) {
        $headerName = isset($cleanHeaders[$i]) ? $cleanHeaders[$i] : "unknown_$i";
        echo "  [$headerName] = '$value'\n";
    }
    
    // Test mapping
    echo "\n=== TEST 3: MAPPING DATA ===\n";
    
    function getDataByHeader($data, $headers, $headerName) {
        $index = array_search($headerName, $headers);
        if ($index === false) {
            return '';
        }
        return isset($data[$index]) ? $data[$index] : '';
    }
    
    $mapping = [
        'BRANCH' => substr(getDataByHeader($cleanSampleData, $cleanHeaders, 'branch'), 0, 50),
        'STMT_BOOKING_DATE' => getDataByHeader($cleanSampleData, $cleanHeaders, 'stmt_booking_date'),
        'FT_BIL_PRODUCT' => substr(getDataByHeader($cleanSampleData, $cleanHeaders, 'ft_bil_product'), 0, 50),
        'STMT_DATE_TIME' => substr(getDataByHeader($cleanSampleData, $cleanHeaders, 'stmt_date_time'), 0, 50),
        'FT_BIL_CUSTOMER' => substr(getDataByHeader($cleanSampleData, $cleanHeaders, 'ft_bil_customer'), 0, 100),
        'FT_TERM_ID' => substr(getDataByHeader($cleanSampleData, $cleanHeaders, 'ft_term_id'), 0, 50),
        'FT_DEBIT_ACCT_NO' => substr(getDataByHeader($cleanSampleData, $cleanHeaders, 'ft_debit_acct_no'), 0, 50),
        'FT_TRANS_REFF' => substr(getDataByHeader($cleanSampleData, $cleanHeaders, 'ft_trans_reff'), 0, 100),
        'STMT_OUR_REFF' => substr(getDataByHeader($cleanSampleData, $cleanHeaders, 'stmt_our_reference'), 0, 50),
        'RECIPT_NO' => substr(getDataByHeader($cleanSampleData, $cleanHeaders, 'recipt_no'), 0, 50),
        'AMOUNT' => floatval(str_replace([',', ' '], '', getDataByHeader($cleanSampleData, $cleanHeaders, 'amount'))),
        'FEE' => floatval(str_replace([',', ' '], '', getDataByHeader($cleanSampleData, $cleanHeaders, 'fee'))),
        'v_TGL_PROSES' => date('Y-m-d H:i:s'),
        'v_TGL_FILE_REKON' => $tanggalRekon
    ];
    
    echo "Mapped data:\n";
    foreach ($mapping as $dbField => $value) {
        echo "  [$dbField] = ";
        if (is_string($value)) {
            echo "'$value' (length: " . strlen($value) . ")\n";
        } else {
            echo "$value\n";
        }
    }
    
    
    // Test 4: Simulasi database connection (akan pakai manual connection)
    echo "\n=== TEST 4: DATABASE TEST (Manual Connection) ===\n";
    
    // Manual database configuration - sesuaikan dengan config Anda
    $dbConfig = [
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',     
        'database' => 'bankkalsel_rekonsiliasi_settlement',
        'port' => 3306
    ];
    
    try {
        $pdo = new PDO("mysql:host={$dbConfig['hostname']};port={$dbConfig['port']};dbname={$dbConfig['database']}", 
                       $dbConfig['username'], $dbConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✓ Database connection OK\n";
        
        // Test if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 't_agn_detail'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table t_agn_detail exists\n";
            
            // Get table info
            $stmt = $pdo->query("DESCRIBE t_agn_detail");
            $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Table fields:\n";
            foreach ($fields as $field) {
                echo "  - {$field['Field']} ({$field['Type']})";
                if ($field['Null'] === 'NO') echo " NOT NULL";
                if ($field['Key'] === 'PRI') echo " PRIMARY KEY";
                echo "\n";
            }
            
            // Test single insert
            echo "\n=== TEST 5: SINGLE INSERT TEST ===\n";
            
            // First delete existing test data
            $stmt = $pdo->prepare("DELETE FROM t_agn_detail WHERE v_TGL_FILE_REKON = ?");
            $deleted = $stmt->execute([$tanggalRekon]);
            echo "Deleted existing test records\n";
            
            // Prepare insert statement
            $insertSQL = "INSERT INTO t_agn_detail (
                BRANCH, STMT_BOOKING_DATE, FT_BIL_PRODUCT, STMT_DATE_TIME, 
                FT_BIL_CUSTOMER, FT_TERM_ID, FT_DEBIT_ACCT_NO, FT_TRANS_REFF, 
                STMT_OUR_REFF, RECIPT_NO, AMOUNT, FEE, v_TGL_PROSES, v_TGL_FILE_REKON
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Try to insert one record
            try {
                $stmt = $pdo->prepare($insertSQL);
                $result = $stmt->execute([
                    $mapping['BRANCH'],
                    $mapping['STMT_BOOKING_DATE'], 
                    $mapping['FT_BIL_PRODUCT'],
                    $mapping['STMT_DATE_TIME'],
                    $mapping['FT_BIL_CUSTOMER'],
                    $mapping['FT_TERM_ID'],
                    $mapping['FT_DEBIT_ACCT_NO'],
                    $mapping['FT_TRANS_REFF'],
                    $mapping['STMT_OUR_REFF'],
                    $mapping['RECIPT_NO'],
                    $mapping['AMOUNT'],
                    $mapping['FEE'],
                    $mapping['v_TGL_PROSES'],
                    $mapping['v_TGL_FILE_REKON']
                ]);
                
                if ($result) {
                    echo "✓ Single insert successful!\n";
                    
                    // Count records
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM t_agn_detail WHERE v_TGL_FILE_REKON = ?");
                    $stmt->execute([$tanggalRekon]);
                    $count = $stmt->fetchColumn();
                    echo "✓ Records in database: $count\n";
                } else {
                    echo "✗ Single insert failed\n";
                }
            } catch (\Exception $e) {
                echo "✗ Insert exception: " . $e->getMessage() . "\n";
            }
            
        } else {
            echo "✗ Table t_agn_detail does not exist\n";
        }
        
    } catch (\Exception $e) {
        echo "✗ Database error: " . $e->getMessage() . "\n";
        echo "  Pastikan database config di script ini sesuai dengan config aplikasi Anda\n";
        echo "  Edit file test_agn_detail.php pada bagian \$dbConfig\n";
    }
    
} else {
    echo "✗ No data lines found in file\n";
}

echo "\n=== TEST SELESAI ===\n";
?>

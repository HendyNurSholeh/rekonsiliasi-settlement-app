<?php

// Test script untuk debug AGN Detail TXT upload (standalone)

// Test file path
$testFilePath = 'd:\project\tsi\rekonsiliasi-settlement-app\20250721_DETAIL_AGN.txt';
$tanggalRekon = '2025-07-21';

echo "=== AGN DETAIL TXT TEST SCRIPT ===\n";
echo "File: $testFilePath\n";
echo "Tanggal Rekon: $tanggalRekon\n\n";

// Check if file exists
if (!file_exists($testFilePath)) {
    echo "ERROR: File tidak ditemukan: $testFilePath\n";
    exit;
}

echo "✓ File ditemukan\n";
echo "✓ Ukuran file: " . round(filesize($testFilePath) / 1024, 2) . " KB\n\n";

// Test 1: Read and validate file structure
echo "=== TEST 1: VALIDASI STRUKTUR FILE TXT ===\n";

$fileContent = file_get_contents($testFilePath);
$lines = explode("\n", $fileContent);

echo "Jumlah baris: " . count($lines) . "\n";

// Get header
$headerLine = trim($lines[0]);
echo "Header raw: $headerLine\n";

$headers = explode(';', $headerLine);
echo "Jumlah kolom header: " . count($headers) . "\n";

echo "Headers:\n";
foreach ($headers as $i => $header) {
    echo "  [$i] '$header'\n";
}

// Sample data line
if (count($lines) > 1) {
    $sampleLine = trim($lines[1]);
    echo "\nSample line raw: $sampleLine\n";
    
    $sampleData = explode(';', $sampleLine);
    echo "Sample data columns: " . count($sampleData) . "\n";
    
    echo "Sample data:\n";
    foreach ($sampleData as $i => $value) {
        $headerName = isset($headers[$i]) ? $headers[$i] : "missing_header_$i";
        echo "  [$headerName] = '$value'\n";
    }
    
    // Test TERMINALID (last column)
    echo "\nTest kolom terakhir (TERMINALID):\n";
    $lastIndex = count($headers) - 1;
    echo "Expected TERMINALID at index: $lastIndex\n";
    echo "Actual data at last index: '" . (isset($sampleData[$lastIndex]) ? $sampleData[$lastIndex] : 'MISSING') . "'\n";
    
    // Test manual database connection
    echo "\n=== TEST 2: DATABASE CONNECTION TEST ===\n";
    
    $dbConfig = [
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',     
        'database' => 'db_sirela',
        'port' => 8111
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
            
            // Get table structure
            $stmt = $pdo->query("DESCRIBE t_agn_detail");
            $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Table fields: " . count($fields) . "\n";
            
            // Test data mapping
            echo "\n=== TEST 3: DATA MAPPING TEST ===\n";
            
            function getDataByHeaderTest($data, $headers, $headerName) {
                $index = array_search($headerName, $headers);
                if ($index === false) {
                    return '';
                }
                return isset($data[$index]) ? $data[$index] : '';
            }
            
            // Create mapping
            $mapping = [
                'IDTRX' => getDataByHeaderTest($sampleData, $headers, 'IDTRX'),
                'BLTH' => getDataByHeaderTest($sampleData, $headers, 'BLTH'),
                'TGL_WAKTU' => getDataByHeaderTest($sampleData, $headers, 'TGL_WAKTU'),
                'IDPARTNER' => getDataByHeaderTest($sampleData, $headers, 'IDPARTNER'),
                'PRODUK' => getDataByHeaderTest($sampleData, $headers, 'PRODUK'),
                'MERCHANT' => getDataByHeaderTest($sampleData, $headers, 'MERCHANT'),
                'IDPEL' => getDataByHeaderTest($sampleData, $headers, 'IDPEL'),
                'RP_BILLER_POKOK' => floatval(getDataByHeaderTest($sampleData, $headers, 'RP_BILLER_POKOK')),
                'RP_AMOUNT' => floatval(getDataByHeaderTest($sampleData, $headers, 'RP_AMOUNT')),
                'STATUS' => getDataByHeaderTest($sampleData, $headers, 'STATUS'),
                'TERMINALID' => getDataByHeaderTest($sampleData, $headers, 'TERMINALID'),
                'v_TGL_FILE_REKON' => $tanggalRekon
            ];
            
            echo "Mapped sample data:\n";
            foreach ($mapping as $field => $value) {
                echo "  [$field] = ";
                if (is_string($value)) {
                    echo "'$value' (length: " . strlen($value) . ")\n";
                } else {
                    echo "$value\n";
                }
            }
            
            // Test simple insert
            echo "\n=== TEST 4: SIMPLE INSERT TEST ===\n";
            
            // Delete test data first
            $stmt = $pdo->prepare("DELETE FROM t_agn_detail WHERE v_TGL_FILE_REKON = ?");
            $stmt->execute([$tanggalRekon]);
            echo "✓ Cleaned test data\n";
            
            // Simple insert with minimal fields
            $insertSQL = "INSERT INTO t_agn_detail (IDTRX, BLTH, TGL_WAKTU, IDPARTNER, PRODUK, RP_AMOUNT, STATUS, v_TGL_FILE_REKON) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            try {
                $stmt = $pdo->prepare($insertSQL);
                $result = $stmt->execute([
                    $mapping['IDTRX'],
                    $mapping['BLTH'],
                    $mapping['TGL_WAKTU'],
                    $mapping['IDPARTNER'],
                    $mapping['PRODUK'],
                    $mapping['RP_AMOUNT'],
                    $mapping['STATUS'],
                    $mapping['v_TGL_FILE_REKON']
                ]);
                
                if ($result) {
                    echo "✓ Simple insert successful!\n";
                    
                    // Count records
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM t_agn_detail WHERE v_TGL_FILE_REKON = ?");
                    $stmt->execute([$tanggalRekon]);
                    $count = $stmt->fetchColumn();
                    echo "✓ Records in database: $count\n";
                } else {
                    echo "✗ Simple insert failed\n";
                }
            } catch (\Exception $e) {
                echo "✗ Insert error: " . $e->getMessage() . "\n";
                echo "SQL State: " . $e->getCode() . "\n";
            }
            
        } else {
            echo "✗ Table t_agn_detail not found\n";
        }
        
    } catch (\Exception $e) {
        echo "✗ Database error: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "✗ No data lines found\n";
}

echo "\n=== TEST SELESAI ===\n";
?>

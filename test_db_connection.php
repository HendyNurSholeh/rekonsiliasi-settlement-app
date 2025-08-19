<?php
echo "=== DATABASE CONNECTION TEST ===\n";

// Test berbagai kombinasi konfigurasi
$configs = [
    [
        'host' => 'localhost',
        'port' => 8111,
        'user' => 'root',
        'pass' => '',
        'db' => 'db_sirela'
    ],
    [
        'host' => '127.0.0.1',
        'port' => 8111,
        'user' => 'root',
        'pass' => '',
        'db' => 'db_sirela'
    ],
    [
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
        'db' => 'db_sirela'
    ]
];

foreach ($configs as $i => $config) {
    echo "\n--- TEST " . ($i + 1) . " ---\n";
    echo "Host: {$config['host']}\n";
    echo "Port: {$config['port']}\n";
    echo "User: {$config['user']}\n";
    echo "Database: {$config['db']}\n";
    
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['db']}";
        echo "DSN: $dsn\n";
        
        $pdo = new PDO($dsn, $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✅ CONNECTION SUCCESS!\n";
        
        // Test query
        $stmt = $pdo->query("SELECT VERSION() as version");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "MySQL Version: " . $result['version'] . "\n";
        
        // Test database
        $stmt = $pdo->query("SELECT DATABASE() as current_db");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Current Database: " . $result['current_db'] . "\n";
        
        break; // Stop di konfigurasi yang berhasil
        
    } catch (PDOException $e) {
        echo "❌ CONNECTION FAILED: " . $e->getMessage() . "\n";
    }
}

echo "\n=== CHECKING SERVICES ===\n";
exec('tasklist | findstr mysql', $output);
if (!empty($output)) {
    echo "MySQL processes found:\n";
    foreach ($output as $line) {
        echo $line . "\n";
    }
} else {
    echo "No MySQL processes found in tasklist\n";
}
?>

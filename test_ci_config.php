<?php
// Load CodeIgniter untuk test konfigurasi
require_once __DIR__ . '/vendor/autoload.php';

// Simulate CodeIgniter environment
define('FCPATH', __DIR__ . '/public/');
define('APPPATH', __DIR__ . '/app/');
define('ROOTPATH', __DIR__ . '/');
define('WRITEPATH', __DIR__ . '/writable/');
define('SYSTEMPATH', __DIR__ . '/vendor/codeigniter4/framework/system/');

// Load environment variables
require_once SYSTEMPATH . 'Config/DotEnv.php';
(new CodeIgniter\Config\DotEnv(ROOTPATH))->load();

echo "=== ENVIRONMENT VARIABLES TEST ===\n";
echo "CI_ENVIRONMENT: " . getenv('CI_ENVIRONMENT') . "\n";
echo "database.default.hostname: " . getenv('database.default.hostname') . "\n";
echo "database.default.port: " . getenv('database.default.port') . "\n";
echo "database.default.database: " . getenv('database.default.database') . "\n";
echo "database.default.username: " . getenv('database.default.username') . "\n";

echo "\n=== CODEIGNITER CONFIG TEST ===\n";
try {
    // Load database config
    $config = new \Config\Database();
    echo "Default connection config:\n";
    print_r($config->default);
    
    echo "\n=== TESTING DIRECT CONNECTION ===\n";
    $db = \Config\Database::connect();
    echo "✅ CodeIgniter database connection successful!\n";
    
    // Test query
    $query = $db->query("SELECT VERSION() as version, DATABASE() as current_db");
    $result = $query->getRow();
    echo "MySQL Version: " . $result->version . "\n";
    echo "Current Database: " . $result->current_db . "\n";
    
} catch (Exception $e) {
    echo "❌ CodeIgniter connection failed: " . $e->getMessage() . "\n";
    echo "Error file: " . $e->getFile() . " line " . $e->getLine() . "\n";
}
?>

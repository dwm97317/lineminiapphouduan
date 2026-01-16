<?php
// Check balance log table structure
require_once __DIR__ . '/../source/application/database.php';

$config = [
    'type' => 'mysql',
    'hostname' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'hostport' => '3306',
    'charset' => 'utf8mb4',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['hostname']};port={$config['hostport']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Balance Log Table Structure ===\n\n";
    
    // Find balance log table
    $tables = $pdo->query("SHOW TABLES LIKE '%balance%'")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "Table: $table\n";
        $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "  {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']} {$col['Default']}\n";
        }
        echo "\n";
    }
    
    // Check for capital log table (might be used for balance logs)
    echo "=== Capital Log Table Structure ===\n\n";
    $tables = $pdo->query("SHOW TABLES LIKE '%capital%'")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "Table: $table\n";
        $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "  {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']} {$col['Default']}\n";
        }
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

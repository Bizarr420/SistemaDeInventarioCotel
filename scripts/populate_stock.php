<?php
$dbPath = __DIR__ . '/../database/database.sqlite';
if (!file_exists($dbPath)) {
    echo "ERROR: database file not found: $dbPath\n";
    exit(1);
}
try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "ERROR: could not open database: " . $e->getMessage() . "\n";
    exit(1);
}

// Get all products
$products = $pdo->query('SELECT id FROM products')->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($products) . " products\n";

// Get warehouse IDs for ALM-01 and ALM-02
$warehouses = $pdo->query('SELECT id, code FROM warehouses WHERE code IN ("ALM-01", "ALM-02")')->fetchAll(PDO::FETCH_KEY_PAIR);
if (count($warehouses) < 2) {
    echo "ERROR: warehouses ALM-01 and ALM-02 not found\n";
    exit(1);
}
echo "Warehouses: " . json_encode($warehouses) . "\n";

$alm01_id = null;
$alm02_id = null;
foreach ($warehouses as $id => $code) {
    if ($code === 'ALM-01') $alm01_id = $id;
    if ($code === 'ALM-02') $alm02_id = $id;
}

if (!$alm01_id || !$alm02_id) {
    echo "ERROR: could not find warehouse IDs\n";
    exit(1);
}

echo "ALM-01 ID: $alm01_id, ALM-02 ID: $alm02_id\n";

$now = date('Y-m-d H:i:s');
$inserted = 0;
$updated = 0;

// For each product, add 50 to ALM-01 and 50 to ALM-02
$insert = $pdo->prepare('INSERT INTO product_stocks (product_id, warehouse_id, current_stock, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');
$update = $pdo->prepare('UPDATE product_stocks SET current_stock = current_stock + ?, updated_at = ? WHERE product_id = ? AND warehouse_id = ?');
$check = $pdo->prepare('SELECT current_stock FROM product_stocks WHERE product_id = ? AND warehouse_id = ? LIMIT 1');

foreach ($products as $product) {
    $pid = $product['id'];
    
    // ALM-01: add 50
    $check->execute([$pid, $alm01_id]);
    $exists = $check->fetch();
    if ($exists) {
        $update->execute([50, $now, $pid, $alm01_id]);
        $updated++;
    } else {
        $insert->execute([$pid, $alm01_id, 50, $now, $now]);
        $inserted++;
    }
    
    // ALM-02: add 50
    $check->execute([$pid, $alm02_id]);
    $exists = $check->fetch();
    if ($exists) {
        $update->execute([50, $now, $pid, $alm02_id]);
        $updated++;
    } else {
        $insert->execute([$pid, $alm02_id, 50, $now, $now]);
        $inserted++;
    }
}

echo "DONE: inserted=$inserted, updated=$updated\n";
exit(0);

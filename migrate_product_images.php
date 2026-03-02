<?php
// migrate_product_images.php
// Run this script once to apply database_multi_image.sql migration safely.
// Usage (browser): http://localhost/ecommerce/migrate_product_images.php
// Usage (cli): php migrate_product_images.php

require_once __DIR__ . '/config/database.php';

// Safety: prevent running from remote servers if CLI not used
// (If you want remote access, remove this block)
if (php_sapi_name() !== 'cli') {
    // Allow run from localhost only
    $allowedHosts = ['127.0.0.1', '::1', 'localhost'];
    if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedHosts)) {
        http_response_code(403);
        echo "Forbidden: run this script from the server (localhost) only.";
        exit;
    }
}

$sqlFile = __DIR__ . '/database_multi_image.sql';
if (!file_exists($sqlFile)) {
    echo "Migration file not found: $sqlFile\n";
    exit;
}

// Check if product_images table already exists and has data
$tableCheck = $conn->query("SHOW TABLES LIKE 'product_images'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $countRes = $conn->query("SELECT COUNT(*) as cnt FROM product_images");
    $cnt = ($countRes) ? intval($countRes->fetch_assoc()['cnt']) : 0;
    if ($cnt > 0) {
        echo "Migration appears already applied. product_images contains $cnt rows.\n";
        echo "If you want to force re-run, call this script with ?force=1 (browser) or php migrate_product_images.php --force\n";
        exit;
    }
}

// Optionally allow force flag
$force = false;
if (php_sapi_name() === 'cli') {
    foreach ($argv as $arg) {
        if (in_array($arg, ['--force', '-f'])) $force = true;
    }
} else {
    if (isset($_GET['force']) && $_GET['force'] == '1') $force = true;
}

if ($tableCheck && $tableCheck->num_rows > 0 && !$force) {
    // Table exists but empty — proceed. If not empty, we exited earlier.
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "Failed to read migration file.\n";
    exit;
}

// Execute SQL using multi_query
if ($conn->multi_query($sql)) {
    do {
        if ($res = $conn->store_result()) {
            // free any result sets
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    if ($conn->errno) {
        echo "Migration finished with errors: (" . $conn->errno . ") " . $conn->error . "\n";
        exit;
    }

    echo "Migration applied successfully.\n";
    // Show summary
    $check = $conn->query("SHOW TABLES LIKE 'product_images'");
    if ($check && $check->num_rows > 0) {
        $cntRes = $conn->query("SELECT COUNT(*) as cnt FROM product_images");
        $cnt = ($cntRes) ? intval($cntRes->fetch_assoc()['cnt']) : 0;
        echo "product_images rows: $cnt\n";
    }
} else {
    echo "Failed to run migration: (" . $conn->errno . ") " . $conn->error . "\n";
}

// Close connection
$conn->close();

?>
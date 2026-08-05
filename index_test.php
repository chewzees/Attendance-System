<?php
/**
 * Test version of index.php to diagnose errors
 */

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Test</title></head><body>";
echo "<h1>PHP is working!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test database connection
echo "<h2>Testing Database Connection...</h2>";
try {
    require_once 'config/database.php';
    echo "<p style='color: green;'>✓ Config file loaded successfully!</p>";
    
    $db = getDB();
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✓ Database query successful! Users count: " . $result['count'] . "</p>";
    
    echo "<hr>";
    echo "<h3>All systems operational!</h3>";
    echo "<p><a href='index.php'>Go to Main Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

echo "</body></html>";
?>


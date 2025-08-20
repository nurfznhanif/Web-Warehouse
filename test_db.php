<?php
$host = 'localhost';
$dbname = 'vandhana';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "✅ Database connection successful!";
} catch(PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
?>
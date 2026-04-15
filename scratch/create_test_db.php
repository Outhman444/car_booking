<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=car_booking_test', 'root', 'root');
    echo "Tables in car_booking_test:\n";
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "- $table\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

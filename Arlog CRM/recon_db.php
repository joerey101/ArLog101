<?php
require 'db.php';
header('Content-Type: text/plain');

$tables = ['usuarios', 'candidatos', 'postulaciones', 'anuncios'];

foreach ($tables as $t) {
    echo "TABLE: $t\n";
    try {
        $stmt = $pdo->query("DESCRIBE $t");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo " - " . $c['Field'] . " (" . $c['Type'] . ")\n";
        }
    } catch (Exception $e) {
        echo " - Error/Missing: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
?>
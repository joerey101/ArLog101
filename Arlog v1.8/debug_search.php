<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

$term = "sec"; // Hardcoded test term
echo "<h1>Debug Search: '$term'</h1>";

try {
    // 1. Check DB Connection
    if ($pdo) {
        echo "<p style='color:green'>✅ DB Connection OK</p>";
    } else {
        echo "<p style='color:red'>❌ DB Connection FAILED</p>";
        exit;
    }

    // 2. Run Query
    $sql = "
        SELECT a.id, a.titulo, a.ubicacion, a.departamento, a.estado
        FROM anuncios a
        WHERE a.titulo LIKE ? OR a.departamento LIKE ?
    ";

    $search = "%$term%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$search, $search]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Raw Results (All Statuses):</h3>";
    echo "<pre>" . print_r($results, true) . "</pre>";

    // 3. Check Active Only
    $sqlActive = "
        SELECT a.id, a.titulo
        FROM anuncios a
        WHERE a.estado = 'activo' 
        AND (a.titulo LIKE ? OR a.departamento LIKE ?)
    ";
    $stmtActive = $pdo->prepare($sqlActive);
    $stmtActive->execute([$search, $search]);
    $resultsActive = $stmtActive->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Active Results (Should appear in API):</h3>";
    echo "<pre>" . print_r($resultsActive, true) . "</pre>";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
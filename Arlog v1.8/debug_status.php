<?php
require 'db.php';
echo "<h1>Debug Postulaciones Status</h1>";

try {
    $stmt = $pdo->query("SELECT id, candidato_id, anuncio_id, estado, fecha_postulacion FROM postulaciones ORDER BY id DESC LIMIT 10");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Candidato ID</th><th>Anuncio ID</th><th>Estado (DB)</th><th>Fecha</th></tr>";
    foreach ($rows as $row) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['candidato_id']}</td>";
        echo "<td>{$row['anuncio_id']}</td>";
        echo "<td><strong>{$row['estado']}</strong></td>";
        echo "<td>{$row['fecha_postulacion']}</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
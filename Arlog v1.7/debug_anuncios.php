<?php
require 'db.php';
$stmt = $pdo->query("SELECT id, titulo, estado, fecha_creacion FROM anuncios ORDER BY id DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h1>🔍 Inspector de Anuncios</h1>";
echo "<table border='1' style='border-collapse:collapse; width:100%; font-family:sans-serif;'>";
echo "<tr style='background:#eee;'><th>ID</th><th>Título</th><th>Estado</th><th>Fecha</th></tr>";

foreach($rows as $r) {
    $color = ($r['estado'] == 'activo') ? 'green' : 'red';
    echo "<tr>";
    echo "<td>{$r['id']}</td>";
    echo "<td>{$r['titulo']}</td>";
    echo "<td style='color:$color; font-weight:bold;'>{$r['estado']}</td>";
    echo "<td>{$r['fecha_creacion']}</td>";
    echo "</tr>";
}
echo "</table>";
?>
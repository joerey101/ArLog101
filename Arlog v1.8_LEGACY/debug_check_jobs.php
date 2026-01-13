<?php
require 'db.php';
header('Content-Type: text/plain');

echo "--- ESTADO DE LOS ANUNCIOS ---\n\n";

$sql = "SELECT id, titulo, usuario_id, estado, fecha_creacion FROM anuncios ORDER BY id DESC";
$stmt = $pdo->query($sql);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($jobs as $j) {
    echo "[ID: " . $j['id'] . "] " . $j['titulo'] . "\n";
    echo "    Usuario ID: " . $j['usuario_id'] . "\n";
    echo "    Estado:     " . $j['estado'] . "\n";
    echo "    Creado:     " . $j['fecha_creacion'] . "\n\n";
}

echo "TOTAL ENCONTRADOS: " . count($jobs) . "\n";
?>
<?php
require 'db.php';
$stmt = $pdo->query("DESCRIBE postulaciones");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "--- Estructura de Postulaciones ---\n";
foreach ($columns as $c) {
    echo $c['Field'] . " (" . $c['Type'] . ")\n";
}
?>
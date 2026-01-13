<?php
require 'db.php';
$stmt = $pdo->query("DESCRIBE perfiles_candidatos");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "--- Estructura de Perfiles Candidatos ---\n";
foreach ($columns as $c) {
    echo $c['Field'] . " (" . $c['Type'] . ")\n";
}
?>
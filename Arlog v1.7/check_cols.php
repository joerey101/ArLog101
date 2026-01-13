<?php
require 'db.php';
$stmt = $pdo->query("DESCRIBE perfiles_candidatos");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $columns);
?>
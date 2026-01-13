<?php
header('Content-Type: application/json');
require 'db.php';

$q = $_GET['q'] ?? '';

if (strlen($q) < 2) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

try {
    // Buscar coincidencias en título o tags
    // Limitamos a 5 resultados para el dropdown
    $sql = "
        SELECT a.id, a.titulo, a.ubicacion, a.departamento
        FROM anuncios a
        WHERE a.estado = 'activo' 
        AND (a.titulo LIKE ? OR a.departamento LIKE ?)
        LIMIT 5
    ";

    $stmt = $pdo->prepare($sql);
    $term = "%$q%";
    $stmt->execute([$term, $term]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $results]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
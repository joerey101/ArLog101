<?php
header('Content-Type: application/json');
require 'db.php';

try {
    // 1. Traer todos los anuncios activos
    $stmt = $pdo->query("
        SELECT id, titulo, descripcion, departamento, ubicacion, tipo_contrato 
        FROM anuncios 
        WHERE estado = 'activo' 
        ORDER BY fecha_creacion DESC
    ");
    $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Vincular etiquetas dinámicas
    foreach ($ofertas as &$job) {
        $job['etiquetas'] = [];

        try {
            // Updated JOIN to use the correct 'etiquetas' and 'anuncio_etiquetas' tables
            $stmt_tags = $pdo->prepare("
                SELECT e.nombre 
                FROM etiquetas e
                JOIN anuncio_etiquetas ae ON e.id = ae.etiqueta_id
                WHERE ae.anuncio_id = ?
            ");
            $stmt_tags->execute([$job['id']]);
            $tags = $stmt_tags->fetchAll(PDO::FETCH_COLUMN);
            if ($tags) {
                $job['etiquetas'] = $tags;
            }
        } catch (Exception $e_tags) {
            // Silently fail on tags to keep showing ads
            continue;
        }
    }

    echo json_encode(['success' => true, 'data' => $ofertas]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
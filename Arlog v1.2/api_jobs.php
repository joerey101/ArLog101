<?php
// api_jobs.php - Versión optimizada
header('Content-Type: application/json');
require 'db.php';

try {
    // 1. Traer todos los activos
    $stmt = $pdo->query("SELECT id, titulo, descripcion, departamento, ubicacion, tipo_contrato FROM anuncios WHERE estado = 'activo' ORDER BY id DESC");
    $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Vincular etiquetas de forma segura
    foreach ($ofertas as &$job) {
        $job['etiquetas'] = []; // Inicializamos siempre como array vacío
        
        try {
            $stmt_tags = $pdo->prepare("
                SELECT e.nombre 
                FROM etiquetas_maestras e
                JOIN anuncio_etiquetas ae ON e.id = ae.etiqueta_id
                WHERE ae.anuncio_id = ?
            ");
            $stmt_tags->execute([$job['id']]);
            $tags = $stmt_tags->fetchAll(PDO::FETCH_COLUMN);
            if ($tags) {
                $job['etiquetas'] = $tags;
            }
        } catch (Exception $e_tags) {
            // Si fallan las etiquetas, el anuncio se muestra igual
            continue; 
        }
    }

    echo json_encode(['success' => true, 'data' => $ofertas]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
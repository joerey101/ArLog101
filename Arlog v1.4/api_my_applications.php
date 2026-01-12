<?php
// api_my_applications.php - v1.4 (Sensor de Postulaciones Previas)
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    require 'db.php';

    // 1. Identificamos al candidato por su sesión
    $candidato_id = $_SESSION['candidato_id'] ?? null;
    $data = [];

    // 2. Si hay sesión, buscamos a qué IDs de anuncios ya se postuló
    if ($candidato_id) {
        $stmt = $pdo->prepare("SELECT anuncio_id FROM postulaciones WHERE candidato_id = ?");
        $stmt->execute([$candidato_id]);
        
        // Obtenemos solo los IDs para que el JS los procese rápido
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Devolvemos la lista (aunque esté vacía)
    echo json_encode([
        'success' => true, 
        'data' => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error en el sensor de aplicaciones: ' . $e->getMessage()
    ]);
}
<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

// 1. Seguridad: Solo empresa o admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol'] !== 'empresa' && $_SESSION['user_rol'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

// 2. Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);
$postulacion_id = $input['id'] ?? null;
$nuevo_estado = $input['estado'] ?? null;

if (!$postulacion_id || !$nuevo_estado) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Validación de estado Enum
$estados_validos = ['nuevo', 'visto', 'contactado', 'descartado'];
if (!in_array($nuevo_estado, $estados_validos)) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

try {
    // 3. Validar Propiedad: La postulación debe pertenecer a un anuncio creado por ESTA empresa
    // (Join trick: postulacion -> anuncio -> usuario_id)
    $stmtCheck = $pdo->prepare("
        SELECT p.id 
        FROM postulaciones p
        JOIN anuncios a ON p.anuncio_id = a.id
        WHERE p.id = ? AND a.usuario_id = ?
    ");
    $stmtCheck->execute([$postulacion_id, $_SESSION['user_id']]);

    // Si no encuentra fila, es que intentas tocar el candidato de otro.
    // Excepción: Admin puede todo.
    if (!$stmtCheck->fetch() && $_SESSION['user_rol'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso sobre esta postulación']);
        exit;
    }

    // 4. Actualizar Estado
    $stmtUpdate = $pdo->prepare("UPDATE postulaciones SET estado = ? WHERE id = ?");
    $stmtUpdate->execute([$nuevo_estado, $postulacion_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Estado actualizado',
        'estado' => $nuevo_estado
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
}
?>
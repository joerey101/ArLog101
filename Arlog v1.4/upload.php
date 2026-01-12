<?php
// upload.php - v1.4 Pro (Blindaje de Seguridad y Control de Duplicados)
session_start();
ob_start();

// 1. CONFIGURACIÓN DE SEGURIDAD
ini_set('display_errors', 0); 
error_reporting(E_ALL); 
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

try {
    require 'db.php'; 

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    // 2. VALIDACIÓN DE ARCHIVO (Límite 2MB)
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al recibir el archivo. Intente nuevamente.');
    }
    
    $file = $_FILES['file'];
    if ($file['size'] > 2097152) { // 2MB en bytes
        throw new Exception('El archivo es demasiado grande (Máximo 2MB). Reduzca el tamaño de su PDF.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        throw new Exception('Formato no válido. Solo se permiten archivos PDF.');
    }

    // 3. CAPTURA DE IDENTIDAD Y SESIÓN
    $candidato_id = $_SESSION['candidato_id'] ?? null;
    $nombreCandidato = $_SESSION['candidato_nombre'] ?? ($_POST['candidate_name'] ?? 'Anónimo');
    $emailCandidato  = $_SESSION['candidato_email'] ?? ($_POST['candidate_email'] ?? 'sin_email');
    $anuncioId = $_POST['job_id'] ?? null; 

    if (!$anuncioId) {
        throw new Exception("No se especificó un anuncio válido para la postulación.");
    }

    // 4. CONTROL DE DUPLICADOS (Blindaje v1.4)
    if ($candidato_id) {
        $check = $pdo->prepare("SELECT id FROM postulaciones WHERE candidato_id = ? AND anuncio_id = ?");
        $check->execute([$candidato_id, $anuncioId]);
        if ($check->fetch()) {
            throw new Exception('Ya te encuentras postulado a esta búsqueda. No es necesario enviar tu CV nuevamente.');
        }
    }

    // 5. GESTIÓN DE ALMACENAMIENTO
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    $nombreFinal = time() . '_' . substr($nombreLimpio, 0, 30) . '.pdf';
    $rutaDestino = $uploadDir . $nombreFinal;

    if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
        throw new Exception('Error interno al guardar el archivo en el servidor.');
    }

    // 6. INSERCIÓN EN BASE DE DATOS
    $sql = "INSERT INTO postulaciones (anuncio_id, candidato_id, nombre_candidato, email_candidato, ruta_archivo_pdf, estado, fecha_postulacion) 
            VALUES (?, ?, ?, ?, ?, 'nuevo', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$anuncioId, $candidato_id, $nombreCandidato, $emailCandidato, $rutaDestino]);

    $response = [
        'success' => true, 
        'message' => "¡Éxito! Postulación enviada correctamente.",
        'ref' => $pdo->lastInsertId()
    ];

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

if (ob_get_length()) ob_clean(); 
echo json_encode($response);
exit;
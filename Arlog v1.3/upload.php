<?php
// upload.php - v1.3 (Sincronizado con Identidad y Sesiones)
session_start();
ob_start();

// Configuración de errores
ini_set('display_errors', 0); 
error_reporting(E_ALL); 

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

try {
    require 'db.php'; 

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    if (!isset($_FILES['file'])) {
        throw new Exception('No se recibió ningún archivo.');
    }
    
    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error de subida PHP (Código: " . $file['error'] . ")");
    }

    // 1. CAPTURA DE IDENTIDAD (Mix v1.1 + v1.3)
    // Priorizamos la sesión si el usuario está logueado
    $candidato_id = $_SESSION['candidato_id'] ?? null;
    $nombreCandidato = $_SESSION['candidato_nombre'] ?? ($_POST['candidate_name'] ?? 'Anónimo');
    $emailCandidato  = $_SESSION['candidato_email'] ?? ($_POST['candidate_email'] ?? 'sin_email');
    
    // Captura de ID de anuncio desde el formulario
    $anuncioId = $_POST['job_id'] ?? null; 

    // 2. VINCULACIÓN INTELIGENTE (Tu lógica v1.1 de respaldo)
    if (!$anuncioId) {
        $tituloBusqueda = $_POST['title'] ?? '';
        $stmt = $pdo->prepare("SELECT id FROM anuncios WHERE titulo LIKE ? AND estado = 'activo' LIMIT 1");
        $stmt->execute(["%$tituloBusqueda%"]);
        $anuncioId = $stmt->fetchColumn();
    }

    if (!$anuncioId) {
        throw new Exception("No se especificó un anuncio válido para la postulación.");
    }

    // 3. VALIDACIÓN PDF
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        throw new Exception('Solo se permiten archivos PDF.');
    }

    // 4. GUARDAR ARCHIVO FÍSICO
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    $nombreFinal = time() . '_' . substr($nombreLimpio, 0, 50) . '.pdf';
    $rutaDestino = $uploadDir . $nombreFinal;

    if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
        throw new Exception('Error al guardar el archivo en disco.');
    }

   // 5. INSERTAR EN BASE DE DATOS (Sincronizado con tus columnas reales)
    $sql = "INSERT INTO postulaciones (anuncio_id, candidato_id, nombre_candidato, email_candidato, ruta_archivo_pdf, estado, fecha_postulacion) 
            VALUES (?, ?, ?, ?, ?, 'nuevo', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $anuncioId, 
        $candidato_id, 
        $nombreCandidato, 
        $emailCandidato, 
        $rutaDestino // Esta es la ruta del PDF
    ]);

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
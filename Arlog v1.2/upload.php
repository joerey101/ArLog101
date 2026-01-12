<?php
// upload.php - v1.1 (Con captura de identidad)

// 1. CONTROL DE SALIDA (BUFFER)
ob_start();

// Configuración de errores
ini_set('display_errors', 0); 
error_reporting(E_ALL); 

// Headers CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit;
}

$response = ['success' => false, 'message' => 'Error desconocido'];

try {
    // 2. CONEXIÓN
    if (!file_exists('db.php')) {
        throw new Exception('Falta el archivo db.php en el servidor.');
    }
    require 'db.php'; 

    // 3. VALIDACIONES BÁSICAS
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    if (!isset($_FILES['file'])) {
        throw new Exception('No se recibió ningún archivo.');
    }
    
    // Errores nativos de PHP
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error de subida PHP (Código: " . $_FILES['file']['error'] . ")");
    }

    $file = $_FILES['file'];

    // Validar PDF
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        throw new Exception('Solo se permiten archivos PDF.');
    }
    // Doble chequeo mime si es posible
    if (function_exists('mime_content_type')) {
        $mime = mime_content_type($file['tmp_name']);
        if ($mime !== 'application/pdf') {
             throw new Exception('El archivo no parece ser un PDF válido.');
        }
    }

    // 4. CAPTURA DE DATOS (NUEVO EN v1.1)
    // Recibimos los datos del formulario index.html actualizado
    $nombreCandidato = isset($_POST['candidate_name']) ? trim($_POST['candidate_name']) : 'Anónimo';
    $emailCandidato  = isset($_POST['candidate_email']) ? trim($_POST['candidate_email']) : 'sin_email';
    
    $titulo       = $_POST['title'] ?? 'Documento Sin Título';
    $departamento = $_POST['department'] ?? 'General';
    $descripcion  = $_POST['description'] ?? '';

    // Validacion extra de seguridad para campos vacíos
    if (empty($nombreCandidato)) $nombreCandidato = 'Usuario Desconocido';
    if (empty($emailCandidato))  $emailCandidato = 'no-reply@web.com';

    // 5. VINCULACIÓN CON ANUNCIO (Lógica inteligente)
    // Intentamos vincular con un anuncio que coincida con el departamento o título
    // Si no, buscamos "Buzón General"
    $anuncioId = null;
    
    // Intento 1: Buscar por coincidencia en título de anuncio (ej: "Chofer")
    $stmt = $pdo->prepare("SELECT id FROM anuncios WHERE titulo LIKE ? AND estado = 'activo' LIMIT 1");
    $stmt->execute(["%$titulo%"]);
    $anuncioId = $stmt->fetchColumn();

    // Intento 2: Si no, buscar el primer anuncio activo general
    if (!$anuncioId) {
        $stmt = $pdo->query("SELECT id FROM anuncios WHERE estado = 'activo' LIMIT 1");
        $anuncioId = $stmt->fetchColumn();
    }

    // Intento 3: Crear anuncio de respaldo si la BD está vacía
    if (!$anuncioId) {
        try {
            // Asumimos usuario ID 1 (admin)
            $pdo->exec("INSERT INTO anuncios (usuario_id, titulo, departamento, descripcion, estado) VALUES (1, 'Buzón General', 'RRHH', 'Auto-generado', 'activo')");
            $anuncioId = $pdo->lastInsertId();
        } catch (Exception $e) {
            // Si falla (ej: no existe usuario 1), seguimos sin anuncioId, pero lanzaremos error abajo si es crítico.
            // Para robustez, permitiremos guardar con anuncio_id NULL si la tabla lo permite, 
            // pero tu esquema original pide NOT NULL. Así que lanzamos error:
            throw new Exception("No hay ofertas activas donde guardar esta postulación.");
        }
    }

    // 6. GUARDAR ARCHIVO FÍSICO
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Nombre seguro: ID_tiempo_nombreLimpio.pdf
    $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    // Cortamos nombre si es muy largo
    $nombreLimpio = substr($nombreLimpio, 0, 50);
    $nombreFinal = time() . '_' . $nombreLimpio . '.' . $extension; // Corregir extension
    $nombreFinal = time() . '_' . $nombreLimpio . '.pdf';
    
    $rutaDestino = $uploadDir . $nombreFinal;

    if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
        throw new Exception('Error al guardar el archivo en disco.');
    }

    // 7. INSERTAR EN BASE DE DATOS (ACTUALIZADO v1.1)
    // Preparamos JSON con metadatos extra
    $datosExtra = json_encode([
        'titulo_documento' => $titulo,
        'departamento_interes' => $departamento,
        'descripcion_usuario' => $descripcion,
        'nombre_original' => $file['name']
    ], JSON_UNESCAPED_UNICODE);

    // SQL Dinámico
    $sql = "INSERT INTO postulaciones (anuncio_id, nombre_candidato, email_candidato, ruta_archivo_pdf, datos_extra, estado, fecha_postulacion) 
            VALUES (?, ?, ?, ?, ?, 'nuevo', NOW())";
    
    $stmt = $pdo->prepare($sql);
    // Aquí pasamos las nuevas variables $nombreCandidato y $emailCandidato
    $stmt->execute([$anuncioId, $nombreCandidato, $emailCandidato, $rutaDestino, $datosExtra]);

    $lastId = $pdo->lastInsertId();
    
    $response['success'] = true;
    $response['message'] = "Postulación recibida correctamente. (Ref: #$lastId)";

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// 8. SALIDA
if (ob_get_length()) ob_clean(); 
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit;
?>
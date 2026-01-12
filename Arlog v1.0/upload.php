<?php
// upload.php

// 1. CONTROL DE SALIDA (BUFFER)
// Esto captura cualquier error PHP visible y evita que rompa el JSON
ob_start();

// Configuración de errores para depuración interna, pero no salida
ini_set('display_errors', 0); 
error_reporting(E_ALL); 

// Headers CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Pre-flight para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit;
}

$response = ['success' => false, 'message' => 'Error desconocido'];

try {
    // 2. Incluir conexión (Protegido por try-catch global)
    if (!file_exists('db.php')) {
        throw new Exception('Falta el archivo db.php en el servidor.');
    }
    
    // Al incluir db.php, si falla, lanzará una Exception que atraparemos abajo
    require 'db.php'; 

    // 3. Validaciones de la petición
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Se requiere POST.');
    }

    // Verificar si hay errores de subida nativos de PHP
    if (!isset($_FILES['file'])) {
        throw new Exception('No se recibió ningún archivo (¿post_max_size muy bajo en php.ini?).');
    }
    
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $codigosError = [
            1 => 'El archivo excede upload_max_filesize en php.ini',
            2 => 'El archivo excede MAX_FILE_SIZE del formulario',
            3 => 'El archivo se subió parcialmente',
            4 => 'No se subió ningún archivo',
            6 => 'Falta carpeta temporal',
            7 => 'No se pudo escribir en disco',
            8 => 'Una extensión de PHP detuvo la subida'
        ];
        $errorMsg = $codigosError[$_FILES['file']['error']] ?? 'Error desconocido';
        throw new Exception("Error de subida PHP ($errorMsg).");
    }

    $file = $_FILES['file'];

    // Validar tipo MIME
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if ($mimeType !== 'application/pdf') {
            throw new Exception('Solo se permiten archivos PDF. Detectado: ' . $mimeType);
        }
    } else {
        // Fallback si el servidor no tiene fileinfo (raro, pero pasa)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            throw new Exception('Solo se permiten archivos PDF (verificación por extensión).');
        }
    }

    // 4. Datos del formulario
    $titulo = $_POST['title'] ?? 'Documento Sin Título';
    $departamento = $_POST['department'] ?? 'General';
    $descripcion = $_POST['description'] ?? '';

    // 5. Autocuración / Vinculación con Anuncio
    // Busca un anuncio "General" o usa el primero que encuentre
    $stmt = $pdo->prepare("SELECT id FROM anuncios WHERE titulo LIKE ? LIMIT 1");
    $stmt->execute(['%General%']); 
    $anuncioId = $stmt->fetchColumn();

    if (!$anuncioId) {
        $stmt = $pdo->query("SELECT id FROM anuncios LIMIT 1");
        $anuncioId = $stmt->fetchColumn();
    }

    // Si no hay ningún anuncio, creamos uno de emergencia
    if (!$anuncioId) {
        $sqlCrear = "INSERT INTO anuncios (titulo, departamento, descripcion, estado, fecha_creacion, usuario_id) 
                     VALUES ('Buzón General', 'Administración', 'Auto-generado por sistema', 'activo', NOW(), 1)";
        // Nota: Asumimos usuario_id 1. Si falla FK, capturamos error.
        try {
            $pdo->exec($sqlCrear);
            $anuncioId = $pdo->lastInsertId();
        } catch (Exception $e) {
            // Si falla crear anuncio (ej. no existe usuario 1), lanzamos error
            throw new Exception("No hay anuncios activos para vincular y no se pudo crear uno automático.");
        }
    }

    // 6. Mover Archivo
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('No se pudo crear carpeta uploads/. Verifique permisos.');
        }
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    // Nombre seguro: solo letras, numeros y guiones
    $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    $nombreFinal = $nombreLimpio . '_' . time() . '.' . $extension;
    $rutaDestino = $uploadDir . $nombreFinal;

    if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
        throw new Exception('Error al mover el archivo a la carpeta destino.');
    }

    // 7. Guardar en Base de Datos
    $datosExtra = json_encode([
        'titulo_documento' => $titulo,
        'departamento_interes' => $departamento,
        'descripcion_usuario' => $descripcion,
        'nombre_original' => $file['name']
    ], JSON_UNESCAPED_UNICODE);

    $sql = "INSERT INTO postulaciones (anuncio_id, nombre_candidato, email_candidato, ruta_archivo_pdf, datos_extra, estado, fecha_postulacion) 
            VALUES (?, 'Usuario Web', 'sin_email@web.com', ?, ?, 'nuevo', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$anuncioId, $rutaDestino, $datosExtra]);

    $response['success'] = true;
    $response['message'] = 'Archivo subido correctamente. Referencia: #' . $pdo->lastInsertId();

} catch (Exception $e) {
    // Captura errores de DB, de Archivo, o Lógicos
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// 8. LIMPIEZA FINAL Y SALIDA JSON
// Si hubo output previo (errores PHP, warnings), lo borramos
if (ob_get_length()) {
    ob_clean(); 
}
ob_end_clean(); // Cerrar buffer

// Forzar cabeceras JSON
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

echo json_encode($response);
exit;
?>
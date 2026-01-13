<?php
// upload.php - v1.6 (JSON Output Fixed)
session_start();
ob_start(); // Start capturing ANY output (warnings, includes, etc)

ini_set('display_errors', 0);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

$response = [];

try {
    require 'db.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    $candidato_id = $_SESSION['user_id'] ?? null;
    $anuncioId = $_POST['job_id'] ?? null;
    $nombreCandidato = $_POST['candidate_name'] ?? 'Usuario';
    $emailCandidato = $_POST['candidate_email'] ?? 'sin_email';

    // 1. Obtener datos del usuario si está logueado
    $userData = [];
    if ($candidato_id) {
        $stmtUser = $pdo->prepare("SELECT u.email, p.nombre, p.cv_url FROM usuarios u LEFT JOIN perfiles_candidatos p ON u.id = p.usuario_id WHERE u.id = ?");
        $stmtUser->execute([$candidato_id]);
        $userData = $stmtUser->fetch();

        if ($userData) {
            $emailCandidato = $userData['email'];
            $nombreCandidato = $userData['nombre'] ?? explode('@', $emailCandidato)[0];
        }
    }

    if (!$anuncioId)
        throw new Exception("Error: Falta ID del anuncio.");

    // 2. Validar duplicados
    if ($candidato_id) {
        $check = $pdo->prepare("SELECT id FROM postulaciones WHERE candidato_id = ? AND anuncio_id = ?");
        $check->execute([$candidato_id, $anuncioId]);
        if ($check->fetch())
            throw new Exception('Ya estás postulado a este aviso.');
    }

    // 3. LOGICA DE ARCHIVO
    $rutaFinal = "";

    // Caso A: Usar CV Guardado
    if (isset($_POST['use_saved']) && $_POST['use_saved'] === 'true' && $candidato_id) {
        if (empty($userData['cv_url']))
            throw new Exception("No tienes un CV guardado.");

        $savedPath = __DIR__ . '/' . $userData['cv_url'];

        if (!file_exists($savedPath)) {
            throw new Exception("El archivo guardado no existe físicamente en el servidor.");
        }

        // Preparar carpeta snapshot
        $uploadDir = 'uploads/snapshots/';
        $systemSnapshotDir = __DIR__ . '/' . $uploadDir;
        if (!is_dir($systemSnapshotDir))
            mkdir($systemSnapshotDir, 0755, true);

        $newName = "snap_" . time() . "_" . basename($userData['cv_url']);
        $rutaFinal = $uploadDir . $newName; // Relativa para BD
        $destPath = $systemSnapshotDir . $newName; // Absoluta para copy

        if (!copy($savedPath, $destPath)) {
            $e = error_get_last();
            throw new Exception("Error al copiar CV: " . $e['message']);
        }
    }
    // Caso B: Subida Normal
    else {
        // Validación Guest
        if (!$candidato_id) {
            // Permitir subida guest si se desea, por ahora bloqueamos si no hay archivo
            if (!isset($_FILES['file']))
                throw new Exception("Debes adjuntar un archivo.");
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            if ($candidato_id)
                throw new Exception('Debes adjuntar un CV PDF.'); // Mensaje para users logueados tratando de subir nuevo
            else
                throw new Exception('Error en la carga del archivo.');
        }

        $file = $_FILES['file'];
        if ($file['size'] > 5242880)
            throw new Exception('El archivo supera los 5MB.');

        $uploadDir = 'uploads/';
        $systemDir = __DIR__ . '/' . $uploadDir;
        if (!is_dir($systemDir))
            mkdir($systemDir, 0755, true);

        $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $rutaFinal = $uploadDir . time() . '_' . substr($nombreLimpio, 0, 20) . '.pdf';
        $destPath = $systemDir . basename($rutaFinal);

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new Exception('Error al guardar archivo en disco.');
        }

        // Guardar como default si se solicitó
        if ($candidato_id && isset($_POST['save_as_default']) && $_POST['save_as_default'] === 'true') {
            // Guardamos la ruta relativa
            $pdo->prepare("UPDATE perfiles_candidatos SET cv_url = ? WHERE usuario_id = ?")
                ->execute([$rutaFinal, $candidato_id]);
        }
    }

    // 4. INSERTAR POSTULACIÓN
    $uidToSave = $candidato_id ?? 0;

    // 4. INSERTAR POSTULACIÓN
    $uidToSave = $candidato_id ?? 0;

    // Corrección Final: Usamos 'ruta_archivo_pdf' que es la columna real en producción
    $stmt = $pdo->prepare("INSERT INTO postulaciones (anuncio_id, candidato_id, nombre_candidato, email_candidato, ruta_archivo_pdf, estado, fecha_postulacion) 
                          VALUES (?, ?, ?, ?, ?, 'nuevo', NOW())");

    $stmt->execute([$anuncioId, $uidToSave, $nombreCandidato, $emailCandidato, $rutaFinal]);

    $response = ['success' => true, 'message' => "¡Postulación enviada con éxito!"];

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// FINAL OUTPUT
ob_clean(); // Borramos cualquier echo previo (warnings, includes con espacios, etc)
echo json_encode($response); // Enviamos SOLO el JSON limpio
exit;
?>
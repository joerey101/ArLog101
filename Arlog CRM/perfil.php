<?php
session_start();
require 'db.php';

// Verificación de sesión unificada
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Procesar subida de CV desde el perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv_base'])) {
    try {
        $file = $_FILES['cv_base'];
        if ($file['error'] !== UPLOAD_ERR_OK)
            throw new Exception("Error al subir archivo");
        if ($file['size'] > 5 * 1024 * 1024)
            throw new Exception("El archivo supera los 5MB");
        if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf')
            throw new Exception("Solo PDF");

        $uploadDir = 'uploads/cvs_base/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true);

        $filename = "cv_base_" . $user_id . "_" . time() . ".pdf";
        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            // Actualizar BD (Logica Upsert para asegurar que se guarde)
            $nombre_temp = $_SESSION['user_name'] ?? 'Candidato';
            $sql = "INSERT INTO perfiles_candidatos (usuario_id, cv_url, nombre) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE cv_url = VALUES(cv_url)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $dest, $nombre_temp]);

            $message = "¡CV Actualizado con éxito!";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Obtener datos del usuario
$stmt = $pdo->prepare("
    SELECT u.email, p.nombre, p.cv_url 
    FROM usuarios u 
    LEFT JOIN perfiles_candidatos p ON u.id = p.usuario_id 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$nombre = $user['nombre'] ?? explode('@', $user['email'])[0];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Perfil | Arlog Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-50 font-['Inter']">

    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <a href="empleos.html"
                class="flex items-center gap-2 text-slate-500 hover:text-slate-800 font-bold transition">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <div class="flex items-center gap-4">
                <span class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($nombre); ?></span>
                <a href="auth.php?action=logout"
                    class="text-xs text-red-500 font-bold uppercase hover:underline">Salir</a>
            </div>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto mt-12 px-6">

        <div class="bg-white p-8 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100">
            <div class="text-center mb-8">
                <div
                    class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold">
                    <?php echo strtoupper(substr($nombre, 0, 1)); ?>
                </div>
                <h1 class="text-2xl font-bold text-slate-800"><?php echo htmlspecialchars($nombre); ?></h1>
                <p class="text-slate-500"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <?php if ($message): ?>
                <div
                    class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-center font-bold text-sm border border-emerald-100 animate-pulse">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-file-pdf text-red-500"></i> Mi CV Base
                </h3>

                <?php if (!empty($user['cv_url'])): ?>
                    <div
                        class="flex items-center justify-between bg-white p-4 rounded-xl border border-slate-200 mb-6 group hover:border-emerald-300 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase">Archivo Guardado</p>
                                <a href="<?php echo htmlspecialchars($user['cv_url']); ?>" target="_blank"
                                    class="text-sm font-bold text-emerald-600 hover:underline">Ver mi CV actual</a>
                            </div>
                        </div>
                        <span class="text-[10px] bg-slate-100 text-slate-400 px-2 py-1 rounded font-bold uppercase">Listo
                            para usar</span>
                    </div>
                <?php else: ?>
                    <div class="text-center py-6 border-2 border-dashed border-slate-300 rounded-xl mb-6">
                        <p class="text-slate-400 text-sm font-medium">Aún no tienes un CV guardado.</p>
                        <p class="text-[10px] text-slate-300">Cárgalo ahora para postularte con 1 click.</p>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="mt-4">
                    <label class="block w-full cursor-pointer">
                        <span class="sr-only">Elegir archivo</span>
                        <input type="file" name="cv_base" accept=".pdf" required class="block w-full text-xs text-slate-500 mb-4
                            file:mr-4 file:py-2.5 file:px-4
                            file:rounded-full file:border-0
                            file:text-xs file:font-bold
                            file:bg-slate-900 file:text-white
                            hover:file:bg-slate-800 cursor-pointer transition
                        " />
                    </label>
                    <button type="submit"
                        class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-emerald-600 transition shadow-lg shadow-slate-200 text-sm">
                        <?php echo !empty($user['cv_url']) ? 'Reemplazar CV' : 'Guardar CV'; ?>
                    </button>
                </form>
            </div>
        </div>

    </div>
</body>

</html>
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

// Procesar Formulario Completo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Datos Personales
        $nombre = $_POST['nombre'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $cuit = $_POST['cuit'] ?? '';
        $linkedin = $_POST['linkedin'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';

        $cv_path = null;

        // Procesar CV si se subió uno nuevo
        if (isset($_FILES['cv_base']) && $_FILES['cv_base']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['cv_base'];
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
                $cv_path = $dest;
            }
        }

        // Construir Query Dinámica (Upsert)
        // Asumimos que la tabla tiene: usuario_id, nombre, telefono, cuit, linkedin, ubicacion, cv_url
        // Si no se subió CV, no actualizamos esa columna

        // Primero obtener el CV actual si no se sube uno nuevo
        if (!$cv_path) {
            $stmtCV = $pdo->prepare("SELECT cv_url FROM perfiles_candidatos WHERE usuario_id = ?");
            $stmtCV->execute([$user_id]);
            $currentData = $stmtCV->fetch();
            $cv_path = $currentData['cv_url'] ?? '';
        }

        $sql = "INSERT INTO perfiles_candidatos (usuario_id, nombre, telefono, cuit, linkedin, ubicacion, cv_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                nombre = VALUES(nombre),
                telefono = VALUES(telefono),
                cuit = VALUES(cuit),
                linkedin = VALUES(linkedin),
                ubicacion = VALUES(ubicacion),
                cv_url = VALUES(cv_url)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $nombre, $telefono, $cuit, $linkedin, $ubicacion, $cv_path]);

        $message = "¡Perfil actualizado con éxito!";

    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Obtener datos del usuario
$stmt = $pdo->prepare("
    SELECT u.email, p.nombre, p.telefono, p.cuit, p.linkedin, p.ubicacion, p.cv_url 
    FROM usuarios u 
    LEFT JOIN perfiles_candidatos p ON u.id = p.usuario_id 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$nombre_display = $user['nombre'] ?: explode('@', $user['email'])[0];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Perfil | Arlog Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: 'Clash Display', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .hero-bg {
            background-color: #0f172a;
            background-image: radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 1) 0, transparent 50%),
                radial-gradient(at 50% 0%, hsla(225, 39%, 30%, 1) 0, transparent 50%),
                radial-gradient(at 100% 0%, hsla(339, 49%, 30%, 1) 0, transparent 50%);
        }
    </style>
</head>

<body class="hero-bg min-h-screen text-white">

    <nav
        class="p-6 flex justify-between items-center glass sticky top-0 z-50 bg-slate-900/80 backdrop-blur-md border-b border-white/10">
        <div class="max-w-3xl w-full mx-auto flex justify-between items-center">
            <a href="empleos.html"
                class="flex items-center gap-2 text-slate-400 hover:text-emerald-400 font-bold transition group">
                <i class="fas fa-search group-hover:scale-110 transition-transform"></i> Ver Trabajos
            </a>
            <div class="flex items-center gap-4">
                <a href="mis_postulaciones.php"
                    class="hidden md:flex items-center gap-2 text-xs text-emerald-400 font-bold uppercase hover:text-emerald-300 transition border border-emerald-500/30 px-3 py-1 rounded-lg hover:bg-emerald-500/10">
                    <i class="fas fa-briefcase"></i> Mis Postulaciones
                </a>
                <a href="auth.php?action=logout"
                    class="text-xs text-red-400 font-bold uppercase hover:text-red-300 transition border border-red-500/30 px-3 py-1 rounded-lg hover:bg-red-500/10">Salir</a>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto mt-12 px-6 pb-20">

        <div class="glass p-8 rounded-3xl shadow-2xl relative overflow-hidden border border-white/10">
            <!-- Background Decoration -->
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-emerald-500/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-blue-500/20 rounded-full blur-3xl"></div>

            <div class="text-center mb-10 relative z-10">
                <div
                    class="w-24 h-24 bg-gradient-to-br from-emerald-400 to-cyan-500 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-4xl font-bold shadow-lg shadow-emerald-500/30">
                    <?php echo strtoupper(substr($nombre_display, 0, 1)); ?>
                </div>
                <h1 class="text-3xl font-bold text-white mb-1"><?php echo htmlspecialchars($nombre_display); ?></h1>
                <p class="text-emerald-400 font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <?php if ($message): ?>
                <div
                    class="mb-8 p-4 bg-emerald-500/20 text-emerald-300 rounded-xl text-center font-bold text-sm border border-emerald-500/30 animate-pulse">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-8 relative z-10">

                <!-- Datos Personales -->
                <div class="bg-white/5 p-6 rounded-2xl border border-white/10">
                    <h3 class="font-bold text-white mb-6 flex items-center gap-2 text-lg border-b border-white/5 pb-4">
                        <i class="fas fa-user-edit text-cyan-400"></i> Datos Personales
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Nombre Completo</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>"
                                required
                                class="w-full px-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-white focus:outline-none focus:border-cyan-500/50 transition placeholder-slate-600">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Teléfono / WhatsApp</label>
                            <input type="text" name="telefono"
                                value="<?php echo htmlspecialchars($user['telefono'] ?? ''); ?>"
                                placeholder="+54 11 ..."
                                class="w-full px-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-white focus:outline-none focus:border-cyan-500/50 transition placeholder-slate-600">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">DNI / CUIT</label>
                            <input type="text" name="cuit" value="<?php echo htmlspecialchars($user['cuit'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-white focus:outline-none focus:border-cyan-500/50 transition placeholder-slate-600">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Ubicación
                                (Zona/Localidad)</label>
                            <input type="text" name="ubicacion"
                                value="<?php echo htmlspecialchars($user['ubicacion'] ?? ''); ?>"
                                placeholder="Ej: Zona Norte, Pilar"
                                class="w-full px-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-white focus:outline-none focus:border-cyan-500/50 transition placeholder-slate-600">
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Perfil de LinkedIn
                                (URL)</label>
                            <div class="relative">
                                <i class="fab fa-linkedin absolute left-4 top-3.5 text-slate-500"></i>
                                <input type="url" name="linkedin"
                                    value="<?php echo htmlspecialchars($user['linkedin'] ?? ''); ?>"
                                    placeholder="https://linkedin.com/in/..."
                                    class="w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-white focus:outline-none focus:border-cyan-500/50 transition placeholder-slate-600">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CV Section -->
                <div class="bg-white/5 p-6 rounded-2xl border border-white/10 relative z-10 backdrop-blur-md">
                    <h3 class="font-bold text-white mb-6 flex items-center gap-2 text-lg border-b border-white/5 pb-4">
                        <i class="fas fa-file-pdf text-red-500"></i> Mi CV Base
                    </h3>

                    <?php if (!empty($user['cv_url'])): ?>
                        <div
                            class="flex items-center justify-between bg-emerald-500/10 p-5 rounded-xl border border-emerald-500/20 mb-8 group hover:bg-emerald-500/20 transition">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1">Archivo
                                        Guardado</p>
                                    <a href="<?php echo htmlspecialchars($user['cv_url']); ?>" target="_blank"
                                        class="text-base font-bold text-white hover:text-emerald-300 hover:underline transition">Ver
                                        mi CV actual</a>
                                </div>
                            </div>
                            <span
                                class="text-[10px] bg-emerald-500 text-white px-3 py-1 rounded-full font-bold uppercase shadow-lg shadow-emerald-500/20">Listo</span>
                        </div>
                    <?php else: ?>
                        <div
                            class="text-center py-6 border-2 border-dashed border-white/20 rounded-xl mb-6 hover:border-white/40 transition bg-white/5">
                            <p class="text-slate-300 text-sm font-medium">Aún no tienes un CV guardado.</p>
                            <p class="text-[10px] text-slate-400 mt-1">Cárgalo para postularte con 1 click.</p>
                        </div>
                    <?php endif; ?>

                    <label class="block w-full cursor-pointer group">
                        <span
                            class="block text-xs font-bold text-slate-400 uppercase mb-2 group-hover:text-white transition">Seleccionar
                            Nuevo Archivo (PDF)</span>
                        <input type="file" name="cv_base" accept=".pdf" class="block w-full text-xs text-slate-400 mb-2
                            file:mr-4 file:py-3 file:px-6
                            file:rounded-xl file:border-0
                            file:text-xs file:font-bold
                            file:bg-slate-800 file:text-white
                            file:border file:border-white/10
                            hover:file:bg-slate-700 cursor-pointer transition
                            bg-white/5 rounded-xl border border-white/10
                        " />
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-4 bg-gradient-to-r from-emerald-500 to-cyan-600 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-emerald-500/20 transition transform hover:scale-[1.02] text-sm md:text-base mb-10">
                    Guardar Cambios
                </button>
            </form>
        </div>

    </div>
</body>

</html>
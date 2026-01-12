<?php
session_start();
require 'db.php';

// Verificar Sesión Empresa/Admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$anuncio_id = $_GET['id'] ?? null;

if (!$anuncio_id) {
    die("ID de anuncio no especificado.");
}

// Validar que el anuncio pertenezca a esta empresa (Seguridad)
// Nota: Usamos usuario_id según el esquema 'anuncios' recuperado en RECON
$stmtCheck = $pdo->prepare("SELECT titulo, departamento FROM anuncios WHERE id = ? AND usuario_id = ?");
$stmtCheck->execute([$anuncio_id, $_SESSION['user_id']]);
$anuncio = $stmtCheck->fetch();

if (!$anuncio && $_SESSION['user_rol'] !== 'admin') {
    die("Acceso denegado o anuncio inexistente.");
}

// Obtener Postulantes
// Usamos el esquema RECON: postulaciones (anuncio_id, nombre_candidato, email_candidato, ruta_archivo_pdf, fecha_postulacion, estado)
$stmt = $pdo->prepare("
    SELECT * FROM postulaciones 
    WHERE anuncio_id = ? 
    ORDER BY field(estado, 'nuevo', 'visto', 'contactado', 'descartado'), fecha_postulacion DESC
");
$stmt->execute([$anuncio_id]);
$candidatos = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulantes:
        <?php echo htmlspecialchars($anuncio['titulo']); ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen">

    <!-- Navbar Simple -->
    <nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-50 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="dashboard_empresa.php" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <h1 class="text-xl font-bold text-slate-800">
                Postulantes para <span class="text-emerald-600">
                    <?php echo htmlspecialchars($anuncio['titulo']); ?>
                </span>
            </h1>
        </div>
        <div>
            <span class="bg-slate-100 text-slate-500 text-xs font-bold px-3 py-1 rounded-full uppercase">
                <?php echo count($candidatos); ?> Candidatos
            </span>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto py-10 px-6">

        <?php if (empty($candidatos)): ?>
            <div class="text-center py-20 bg-white rounded-3xl shadow-sm border border-slate-100">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-user-astronaut text-3xl text-slate-300"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">Aún no hay postulantes</h3>
                <p class="text-slate-400">Tu anuncio está visible. ¡Ten paciencia!</p>
            </div>
        <?php else: ?>

            <div class="grid gap-4">
                <?php foreach ($candidatos as $c): ?>
                    <div
                        class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 hover:shadow-lg transition-all group">

                        <!-- Info Candidato -->
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-lg">
                                <?php echo strtoupper(substr($c['nombre_candidato'], 0, 1)); ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">
                                    <?php echo htmlspecialchars($c['nombre_candidato']); ?>
                                </h3>
                                <div class="flex gap-4 text-xs text-slate-500">
                                    <span><i class="fas fa-envelope mr-1"></i>
                                        <?php echo htmlspecialchars($c['email_candidato']); ?>
                                    </span>
                                    <span><i class="far fa-clock mr-1"></i>
                                        <?php echo date('d/m H:i', strtotime($c['fecha_postulacion'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Estado y Acciones -->
                        <div class="flex items-center gap-4 w-full md:w-auto mt-4 md:mt-0 justify-between md:justify-end">

                            <!-- Badges de Estado -->
                            <div class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                <?php
                                switch ($c['estado']) {
                                    case 'nuevo':
                                        echo 'bg-blue-100 text-blue-700';
                                        break;
                                    case 'visto':
                                        echo 'bg-yellow-100 text-yellow-700';
                                        break;
                                    case 'contactado':
                                        echo 'bg-emerald-100 text-emerald-700';
                                        break;
                                    case 'descartado':
                                        echo 'bg-red-100 text-red-700 opacity-50';
                                        break;
                                    default:
                                        echo 'bg-slate-100 text-slate-500';
                                }
                                ?>">
                                <?php echo $c['estado']; ?>
                            </div>

                            <!-- Botones -->
                            <div class="flex gap-2">
                                <!-- Ver CV -->
                                <?php
                                // Limpiamos la ruta para que sea relativa al root web si es necesario, 
                                // pero como 'uploads/...' está en el root, debería andar directo. 
                                // Si la ruta en BD es absoluta (/home/...), hay que limpiarla para el href web.
                                // RECON dice: varchar(255). Asumiremos relativa 'uploads/...'. 
                                // Si fuera absoluta, usamos basename.
                        
                                $webPath = $c['ruta_archivo_pdf'];
                                // Fix rápido si guardó absoluta:
                                if (strpos($webPath, '/home/') !== false) {
                                    $parts = explode('arlogjobs.joserey101.com/', $webPath);
                                    $webPath = end($parts);
                                }
                                ?>
                                <a href="<?php echo $webPath; ?>" target="_blank"
                                    class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-emerald-600 transition flex items-center gap-2">
                                    <i class="fas fa-file-pdf"></i> Ver CV
                                </a>

                                <!-- Acciones Rápidas (Placeholder v1.6) -->
                                <button
                                    class="w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 flex items-center justify-center transition"
                                    title="Descartar">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button
                                    class="w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:text-emerald-500 hover:border-emerald-200 flex items-center justify-center transition"
                                    title="Marcar Contactado">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>
</body>

</html>
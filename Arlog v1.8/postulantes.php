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

    <!-- Navbar Simple (Glass) -->
    <nav
        class="glass border-b border-white/10 px-6 py-4 sticky top-0 z-50 flex justify-between items-center bg-slate-900/80 backdrop-blur-md">
        <div class="flex items-center gap-6">
            <a href="dashboard_empresa.php"
                class="text-slate-400 hover:text-white transition flex items-center gap-2 group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Volver a Anuncios
            </a>
            <div class="h-6 w-px bg-white/10 hidden md:block"></div>
            <h1 class="text-xl font-bold text-white hidden md:block">
                Postulantes para <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">
                    <?php echo htmlspecialchars($anuncio['titulo']); ?>
                </span>
            </h1>
        </div>
        <div>
            <span
                class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 text-cyan-400 border border-cyan-500/20 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider shadow-lg shadow-cyan-500/10">
                <?php echo count($candidatos); ?> Candidatos
            </span>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto py-10 px-6 pb-20">

        <!-- Mobile Title -->
        <h1 class="text-2xl font-bold text-white mb-8 md:hidden text-center">
            <?php echo htmlspecialchars($anuncio['titulo']); ?>
        </h1>

        <?php if (empty($candidatos)): ?>
            <div class="text-center py-20 glass rounded-3xl border border-white/5">
                <div
                    class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 border border-white/10">
                    <i class="fas fa-user-astronaut text-4xl text-slate-500"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Aún no hay postulantes</h3>
                <p class="text-slate-400 text-sm">Tu anuncio está visible. ¡Ten paciencia!</p>
            </div>
        <?php else: ?>

            <div class="grid gap-4">
                <?php foreach ($candidatos as $c): ?>
                    <div id="row-<?php echo $c['id']; ?>"
                        class="glass p-6 rounded-2xl border border-white/5 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 hover:bg-white/5 transition-all group duration-300">

                        <!-- Info Candidato -->
                        <div class="flex items-center gap-5">
                            <div
                                class="w-14 h-14 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-emerald-500/20">
                                <?php echo strtoupper(substr($c['nombre_candidato'], 0, 1)); ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-lg group-hover:text-emerald-400 transition-colors">
                                    <?php echo htmlspecialchars($c['nombre_candidato']); ?>
                                </h3>
                                <div
                                    class="flex flex-col md:flex-row md:items-center gap-1 md:gap-4 text-xs text-slate-400 mt-1">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-envelope text-cyan-400"></i>
                                        <?php echo htmlspecialchars($c['email_candidato']); ?>
                                    </span>
                                    <span class="hidden md:inline text-slate-600">•</span>
                                    <span class="flex items-center gap-1.5"><i class="far fa-clock text-slate-500"></i>
                                        <?php echo date('d/m H:i', strtotime($c['fecha_postulacion'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Estado y Acciones -->
                        <div
                            class="flex flex-col md:flex-row items-start md:items-center gap-4 w-full md:w-auto justify-between md:justify-end mt-4 md:mt-0">

                            <!-- Badges de Estado -->
                            <div id="badge-<?php echo $c['id']; ?>" class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider border
                                <?php
                                switch ($c['estado']) {
                                    case 'nuevo':
                                        echo 'bg-blue-500/10 text-blue-400 border-blue-500/20';
                                        break;
                                    case 'visto':
                                        echo 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20';
                                        break;
                                    case 'contactado':
                                        echo 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                                        break;
                                    case 'descartado':
                                        echo 'bg-red-500/10 text-red-500 border-red-500/20 opacity-60';
                                        break;
                                    default:
                                        echo 'bg-slate-500/10 text-slate-400 border-white/5';
                                }
                                ?>">
                                <?php echo $c['estado']; ?>
                            </div>

                            <!-- Botones -->
                            <div class="flex gap-2">
                                <!-- Ver CV -->
                                <?php
                                $webPath = $c['ruta_archivo_pdf'];
                                // Fix rápido si guardó absoluta:
                                if (strpos($webPath, '/home/') !== false) {
                                    $parts = explode('arlogjobs.joserey101.com/', $webPath);
                                    $webPath = end($parts);
                                }
                                ?>
                                <a href="<?php echo $webPath; ?>" target="_blank"
                                    class="px-5 py-2.5 bg-white text-slate-900 text-xs font-bold rounded-xl hover:bg-cyan-400 hover:text-slate-900 transition flex items-center gap-2 shadow-lg shadow-white/5">
                                    <i class="fas fa-file-pdf"></i> <span class="hidden md:inline">Ver CV</span>
                                </a>

                                <!-- Acciones Rápidas -->
                                <button onclick="updateStatus(<?php echo $c['id']; ?>, 'contactado')"
                                    class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition"
                                    title="Marcar Contactado">
                                    <i class="fas fa-check"></i>
                                </button>

                                <button onclick="updateStatus(<?php echo $c['id']; ?>, 'descartado')"
                                    class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition"
                                    title="Descartar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>

    <script>
        // Auto-Mark as Seen Logic
        document.addEventListener('DOMContentLoaded', () => {
            const newBadges = document.querySelectorAll("div[id^='badge-']");
            newBadges.forEach(badge => {
                if (badge.textContent.trim().toLowerCase() === 'nuevo') {
                    const id = badge.id.split('-')[1];
                    // Call update silently
                    updateStatus(id, 'visto', true);
                }
            });
        });

        async function updateStatus(id, nuevoEstado, silent = false) {
            // Feedback Visual Inmediato (Optimista)
            const row = document.getElementById('row-' + id);
            const badge = document.getElementById('badge-' + id);

            // Animación de carga solo si es manual
            if (!silent) row.style.opacity = '0.5';

            try {
                const res = await fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, estado: nuevoEstado })
                });

                const data = await res.json();

                if (data.success) {
                    if (!silent) row.style.opacity = '1';

                    // Actualizar Badge Texto y Color
                    badge.textContent = nuevoEstado;
                    badge.className = "px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider border transition-all duration-300 transform scale-105";

                    // Reset classes
                    badge.classList.remove('bg-blue-500/10', 'text-blue-400', 'border-blue-500/20');

                    if (nuevoEstado === 'visto') {
                        badge.classList.add('bg-yellow-500/10', 'text-yellow-400', 'border-yellow-500/20');
                    } else if (nuevoEstado === 'contactado') {
                        badge.classList.add('bg-emerald-500/10', 'text-emerald-400', 'border-emerald-500/20');
                        row.classList.add('border-emerald-500/30', 'bg-emerald-500/5'); // Highlight row
                        row.classList.remove('opacity-60', 'grayscale');
                    } else if (nuevoEstado === 'descartado') {
                        badge.classList.add('bg-red-500/10', 'text-red-500', 'border-red-500/20', 'opacity-60');
                        row.classList.add('opacity-50', 'grayscale'); // Dim row
                        row.classList.remove('border-emerald-500/30', 'bg-emerald-500/5');
                    }

                } else {
                    if (!silent) alert('Error: ' + data.message);
                    row.style.opacity = '1';
                }

            } catch (e) {
                console.error(e);
                if (!silent) alert('Error de conexión');
                row.style.opacity = '1';
            }
        }
    </script>
</body>

</html>
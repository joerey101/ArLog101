<?php
session_start();
require 'db.php';

// Auth Check Unificado
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'candidato') {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Candidato';

// Obtener Postulaciones
// Verificamos columna 'candidato_id' vs 'usuario_id'. Asumimos 'candidato_id' basado en historial.
// Si falla, es probable que sea 'usuario_id'.
$sql = "SELECT p.fecha_postulacion, p.estado, a.titulo, a.departamento, a.ubicacion, a.id as anuncio_id 
        FROM postulaciones p
        JOIN anuncios a ON p.anuncio_id = a.id
        WHERE p.candidato_id = ?
        ORDER BY p.fecha_postulacion DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $postulaciones = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fallback por si la columna se llama usuario_id
    $sql = "SELECT p.fecha_postulacion, p.estado, a.titulo, a.departamento, a.ubicacion, a.id as anuncio_id 
            FROM postulaciones p
            JOIN anuncios a ON p.anuncio_id = a.id
            WHERE p.usuario_id = ?
            ORDER BY p.fecha_postulacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $postulaciones = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel | Arlog Jobs</title>
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

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="hero-bg flex min-h-screen text-white overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 glass hidden md:flex flex-col p-6 h-screen border-r border-white/5 relative z-20">
        <div class="mb-10 flex items-center gap-3">
            <img src="arlogjobs_logo.png" alt="Logo" class="h-8 filter brightness-0 invert opacity-80">
        </div>

        <nav class="space-y-2 flex-1">
            <a href="mis_postulaciones.php"
                class="flex items-center gap-3 bg-cyan-500/10 text-cyan-400 p-3 rounded-xl font-bold border border-cyan-500/20"><i
                    class="fas fa-clipboard-list"></i> Mis Postulaciones</a>
            <a href="perfil.php"
                class="flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/5 p-3 rounded-xl transition"><i
                    class="fas fa-user-circle"></i> Mi Perfil</a>
            <a href="index.php"
                class="flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/5 p-3 rounded-xl transition"><i
                    class="fas fa-globe"></i> Ver Sitio</a>
        </nav>

        <a href="auth.php?action=logout"
            class="flex items-center gap-3 text-red-400 hover:text-red-300 p-3 transition mt-auto hover:bg-red-500/10 rounded-xl"><i
                class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </aside>

    <main class="flex-1 p-6 md:p-8 overflow-y-auto h-screen relative scroll-smooth">

        <!-- Mobile Header -->
        <div class="md:hidden flex justify-between items-center mb-6">
            <img src="arlogjobs_logo.png" alt="Logo" class="h-8 filter brightness-0 invert">
            <button class="text-white"><i class="fas fa-bars text-2xl"></i></button>
        </div>

        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-white mb-1">Hola,
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500"><?php echo htmlspecialchars($user_name); ?></span>
                </h2>
                <p class="text-slate-400 text-sm">Aquí tienes el seguimiento de tus aplicaciones.</p>
            </div>
            <a href="empleos.html"
                class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-cyan-500/25 transition transform hover:scale-105 flex items-center gap-2">
                <i class="fas fa-search"></i> Buscar Empleos
            </a>
        </header>

        <!-- Stats Grid (Computed) -->
        <?php
        $totalApps = count($postulaciones);
        $totalVistos = 0;
        $totalDescartados = 0;
        foreach ($postulaciones as $p) {
            if ($p['estado'] == 'visto' || $p['estado'] == 'contactado')
                $totalVistos++;
            if ($p['estado'] == 'descartado')
                $totalDescartados++;
        }
        ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="glass p-6 rounded-2xl border border-white/5">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Total Postulaciones</p>
                <p class="text-4xl font-bold text-white"><?php echo $totalApps; ?></p>
            </div>
            <div class="glass p-6 rounded-2xl border border-white/5">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">En Proceso / Vistos</p>
                <p class="text-4xl font-bold text-cyan-400"><?php echo $totalVistos; ?></p>
            </div>
            <div class="glass p-6 rounded-2xl border border-white/5">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Descartados</p>
                <p class="text-4xl font-bold text-red-500/80"><?php echo $totalDescartados; ?></p>
            </div>
        </div>

        <!-- Applications List -->
        <div class="glass rounded-3xl border border-white/5 overflow-hidden">
            <div class="p-6 border-b border-white/5 flex justify-between items-center bg-white/5">
                <h3 class="font-bold text-lg text-white">Tu Historial</h3>
            </div>

            <?php if (empty($postulaciones)): ?>
                <div class="p-16 text-center text-slate-400">
                    <div
                        class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 border border-white/10">
                        <i class="fas fa-paper-plane text-3xl text-slate-500"></i>
                    </div>
                    <p class="font-medium text-white mb-2">No tienes postulaciones activas.</p>
                    <p class="text-xs text-slate-400">¡Postúlate a tu primera vacante hoy mismo!</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-white/5">
                    <?php foreach ($postulaciones as $p): ?>
                        <div
                            class="p-6 hover:bg-white/5 transition flex flex-col md:flex-row items-start md:items-center justify-between gap-4 group">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="font-bold text-white text-lg group-hover:text-cyan-400 transition-colors">
                                        <?php echo htmlspecialchars($p['titulo']); ?>
                                    </h4>

                                    <!-- Status Badge Logic -->
                                    <?php
                                    $statusClass = 'bg-slate-500/10 text-slate-400 border-slate-500/20';
                                    $text = $p['estado'];
                                    switch ($p['estado']) {
                                        case 'nuevo':
                                            $statusClass = 'bg-blue-500/10 text-blue-400 border-blue-500/20';
                                            $text = 'Enviada';
                                            break;
                                        case 'visto':
                                            $statusClass = 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20';
                                            $text = 'CV Visto';
                                            break;
                                        case 'contactado':
                                            $statusClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.2)]';
                                            $text = 'Contactado';
                                            break;
                                        case 'descartado':
                                            $statusClass = 'bg-red-500/10 text-red-500 border-red-500/20 opacity-60';
                                            $text = 'No seleccionado';
                                            break;
                                    }
                                    ?>
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border <?php echo $statusClass; ?>">
                                        <?php echo $text; ?>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">
                                    <i class="far fa-calendar mr-1 opacity-50"></i>
                                    <?php echo date('d/m/Y', strtotime($p['fecha_postulacion'])); ?>
                                    <span class="mx-2 opacity-30">|</span>
                                    <?php echo htmlspecialchars($p['departamento']); ?>
                                    <span class="mx-2 opacity-30">|</span>
                                    <i class="fas fa-map-marker-alt mr-1 opacity-50"></i>
                                    <?php echo htmlspecialchars($p['ubicacion']); ?>
                                </p>
                            </div>

                            <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
                                <a href="empleos.html"
                                    class="w-10 h-10 rounded-full bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-cyan-500 hover:border-cyan-500 flex items-center justify-center transition shadow-lg">
                                    <i class="fas fa-chevron-right text-sm"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>
<?php
session_start();
require 'db.php';

// Obtener Métricas Globales
$stats = [
    'anuncios' => 0,
    'candidatos' => 0,
    'empresas' => 0
];

try {
    $stats['anuncios'] = $pdo->query("SELECT COUNT(*) FROM anuncios WHERE estado = 'activo'")->fetchColumn();
    $stats['candidatos'] = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'candidato'")->fetchColumn();
    $stats['empresas'] = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'empresa'")->fetchColumn();
} catch (Exception $e) {
    // Fail silently visualmente
}

// Detectar sesión para botón inteligente
$isLogged = isset($_SESSION['user_id']);
$userRol = $_SESSION['user_rol'] ?? '';
$dashboardLink = ($userRol === 'empresa') ? 'dashboard_empresa.php' : 'empleos.html';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArLog Jobs | Conectando el Futuro Logístico</title>
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
        h3 {
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

<body class="hero-bg min-h-screen text-white flex flex-col">

    <!-- Navbar -->
    <nav class="p-6 flex justify-between items-center glass sticky top-0 z-50 bg-slate-900/80">
        <div class="flex items-center gap-2">
            <div
                class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-cyan-500 rounded-lg flex items-center justify-center font-bold text-slate-900">
                A</div>
            <span class="text-xl font-bold tracking-tight">ArLog <span class="text-emerald-400">Jobs</span></span>
        </div>
        <div>
            <?php if ($isLogged): ?>
                <a href="<?php echo $dashboardLink; ?>"
                    class="px-5 py-2 bg-white text-slate-900 rounded-full font-bold text-sm hover:bg-emerald-50 transition">
                    Ir a mi Dashboard <i class="fas fa-arrow-right ml-2"></i>
                </a>
            <?php else: ?>
                <span class="text-sm text-slate-400 font-medium tracking-wide uppercase">Plataforma de Talento
                    Logístico</span>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 container mx-auto px-4 py-16 flex flex-col items-center justify-center text-center">

        <!-- Hero Text -->
        <span
            class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full text-xs font-bold uppercase tracking-widest mb-6 inline-block">
            v1.7 Release
        </span>
        <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight max-w-4xl mx-auto">
            El Hub del Talento <br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">Logístico &
                Operativo</span>.
        </h1>
        <p class="text-slate-400 text-lg md:text-xl max-w-2xl mx-auto mb-12">
            Conectamos a las empresas líderes con los profesionales que mueven el mundo. Simple, rápido y efectivo.
        </p>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-8 mb-16 w-full max-w-3xl">
            <div class="p-4 glass rounded-2xl">
                <p class="text-3xl font-bold text-white">
                    <?php echo $stats['anuncios']; ?>
                </p>
                <p class="text-xs text-slate-400 uppercase font-bold tracking-wider">Oportunidades</p>
            </div>
            <div class="p-4 glass rounded-2xl">
                <p class="text-3xl font-bold text-emerald-400">
                    <?php echo $stats['candidatos']; ?>
                </p>
                <p class="text-xs text-slate-400 uppercase font-bold tracking-wider">Talentos</p>
            </div>
            <div class="p-4 glass rounded-2xl hidden md:block">
                <p class="text-3xl font-bold text-cyan-400">
                    <?php echo $stats['empresas']; ?>
                </p>
                <p class="text-xs text-slate-400 uppercase font-bold tracking-wider">Empresas</p>
            </div>
        </div>

        <!-- Role Bifurcation -->
        <div class="grid md:grid-cols-2 gap-6 w-full max-w-4xl">

            <!-- Cards Candidatos -->
            <div
                class="group relative bg-slate-800 hover:bg-slate-800/80 border border-slate-700 rounded-3xl p-8 text-left transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/50">
                <div
                    class="absolute top-6 right-6 w-10 h-10 bg-slate-700 rounded-full flex items-center justify-center text-slate-300 group-hover:bg-emerald-500 group-hover:text-white transition">
                    <i class="fas fa-user"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Soy Candidato</h3>
                <p class="text-slate-400 text-sm mb-8 pr-10">Busco mi próximo desafío en logística, transporte o
                    almacén.</p>

                <div class="flex flex-col gap-3">
                    <a href="empleos.html"
                        class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl text-center transition shadow-lg shadow-emerald-500/20">
                        Ver Ofertas
                    </a>
                    <div class="flex gap-3">
                        <a href="login_candidato.html"
                            class="flex-1 py-3 bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold rounded-xl text-center transition">Ingresar</a>
                        <a href="registro.html"
                            class="flex-1 py-3 bg-transparent border border-slate-600 hover:border-slate-400 text-slate-300 hover:text-white text-sm font-bold rounded-xl text-center transition">Registrarme</a>
                    </div>
                </div>
            </div>

            <!-- Cards Empresas -->
            <div
                class="group relative bg-slate-900 border border-slate-800 rounded-3xl p-8 text-left transition-all duration-300 hover:-translate-y-1 hover:border-cyan-500/50">
                <div
                    class="absolute top-6 right-6 w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-300 group-hover:bg-cyan-500 group-hover:text-white transition">
                    <i class="fas fa-building"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Soy Empresa</h3>
                <p class="text-slate-400 text-sm mb-8 pr-10">Busco talento calificado para potenciar mi operación.</p>

                <div class="flex flex-col gap-3">
                    <a href="login_empresa.html"
                        class="w-full py-3 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-xl text-center transition shadow-lg shadow-cyan-500/20">
                        Ingresar a Empresas
                    </a>
                    <a href="registro_empresa.html"
                        class="w-full py-3 bg-transparent border border-slate-700 hover:border-slate-500 text-slate-400 hover:text-white text-sm font-bold rounded-xl text-center transition">
                        Crear Cuenta Corporativa
                    </a>
                </div>
            </div>

        </div>

    </main>

    <footer class="p-8 text-center text-slate-600 text-sm md:flex md:justify-between md:container md:mx-auto">
        <p>&copy; 2026 ArLog Jobs. Todos los derechos reservados.</p>
        <div class="flex gap-4 justify-center mt-4 md:mt-0">
            <a href="#" class="hover:text-slate-400">Términos</a>
            <a href="#" class="hover:text-slate-400">Privacidad</a>
            <a href="#" class="hover:text-slate-400">Ayuda</a>
        </div>
    </footer>

</body>

</html>
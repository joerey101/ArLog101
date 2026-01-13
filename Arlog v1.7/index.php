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
    <nav
        class="p-6 flex justify-between items-center glass sticky top-0 z-50 bg-slate-900/80 backdrop-blur-md border-b border-white/10">
        <div class="flex items-center gap-3">
            <img src="arlogjobs_logo.png" alt="ArLog Jobs Logo"
                class="h-10 md:h-12 hover:scale-105 transition-transform duration-300">
        </div>
        <div>
            <?php if ($isLogged): ?>
                <a href="<?php echo $dashboardLink; ?>"
                    class="px-5 py-2 bg-white text-slate-900 rounded-full font-bold text-sm hover:bg-emerald-50 transition border border-transparent hover:border-emerald-200">
                    Ir a mi Dashboard <i class="fas fa-arrow-right ml-2"></i>
                </a>
            <?php else: ?>
                <div class="hidden md:flex gap-4">
                    <a href="login_empresa.html"
                        class="text-slate-300 hover:text-white text-sm font-medium transition py-2">Soy Empresa</a>
                    <a href="login_candidato.html"
                        class="px-5 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/50 rounded-full font-bold text-sm transition">
                        Ingresar
                    </a>
                </div>
                <div class="md:hidden">
                    <a href="login_candidato.html" class="text-emerald-400 font-bold"><i
                            class="fas fa-user-circle text-2xl"></i></a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main Content -->
    <main
        class="flex-1 container mx-auto px-4 py-12 md:py-20 flex flex-col items-center justify-start text-center min-h-[80vh]">

        <!-- Hero Text -->
        <span
            class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full text-xs font-bold uppercase tracking-widest mb-6 inline-block">
            v1.7 Visual Update
        </span>
        <h1 class="text-5xl md:text-7xl font-bold mb-8 leading-tight max-w-5xl mx-auto">
            El Hub del Talento <br>
            <span
                class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400 animate-gradient">Logístico
                &
                Operativo</span>.
        </h1>

        <!-- Search Bar Section -->
        <div class="w-full max-w-4xl mx-auto mb-12 relative z-10">
            <form action="empleos.html" method="GET"
                class="glass p-2 rounded-3xl flex flex-col md:flex-row items-center gap-2 border border-white/10 shadow-2xl shadow-emerald-500/10">
                <div class="flex-1 w-full relative group">
                    <i
                        class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-400 transition"></i>
                    <input type="text" name="q" placeholder="¿Qué estás buscando? (Ej: Clarkista)"
                        class="w-full bg-transparent text-white placeholder-slate-500 pl-12 pr-4 py-4 rounded-2xl focus:outline-none focus:bg-white/5 transition">
                </div>
                <div class="hidden md:block w-px h-10 bg-white/10"></div>
                <div class="flex-1 w-full relative group">
                    <i
                        class="fas fa-map-marker-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-cyan-400 transition"></i>
                    <input type="text" name="location" placeholder="Ubicación (Ej: Pilar)"
                        class="w-full bg-transparent text-white placeholder-slate-500 pl-12 pr-4 py-4 rounded-2xl focus:outline-none focus:bg-white/5 transition">
                </div>
                <button type="submit"
                    class="w-full md:w-auto px-8 py-4 bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 text-white font-bold rounded-2xl transition-all hover:scale-105 shadow-lg shadow-emerald-500/25">
                    Buscar
                </button>
            </form>
        </div>

        <!-- Categories Rectangles -->
        <div class="w-full max-w-5xl mx-auto mb-20">
            <p class="text-slate-400 text-sm font-medium uppercase tracking-wider mb-6">Categorías Populares</p>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Card 1 -->
                <a href="empleos.html?cat=transporte"
                    class="group glass p-4 rounded-xl border border-white/5 hover:border-emerald-500/30 hover:bg-white/5 transition flex items-center md:flex-col md:items-start gap-4">
                    <div
                        class="w-12 h-12 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition">
                        <i class="fas fa-truck text-xl"></i>
                    </div>
                    <div class="text-left">
                        <h3 class="text-white font-bold text-lg group-hover:text-emerald-300 transition">Transporte</h3>
                        <p class="text-slate-500 text-xs">Choferes, Reparto</p>
                    </div>
                    <div class="ml-auto md:hidden text-slate-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <!-- Card 2 -->
                <a href="empleos.html?cat=deposito"
                    class="group glass p-4 rounded-xl border border-white/5 hover:border-cyan-500/30 hover:bg-white/5 transition flex items-center md:flex-col md:items-start gap-4">
                    <div
                        class="w-12 h-12 rounded-lg bg-cyan-500/10 flex items-center justify-center text-cyan-400 group-hover:scale-110 transition">
                        <i class="fas fa-warehouse text-xl"></i>
                    </div>
                    <div class="text-left">
                        <h3 class="text-white font-bold text-lg group-hover:text-cyan-300 transition">Depósito</h3>
                        <p class="text-slate-500 text-xs">Carga, Picking, Autoelevador</p>
                    </div>
                    <div class="ml-auto md:hidden text-slate-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <!-- Card 3 -->
                <a href="empleos.html?cat=admin"
                    class="group glass p-4 rounded-xl border border-white/5 hover:border-purple-500/30 hover:bg-white/5 transition flex items-center md:flex-col md:items-start gap-4">
                    <div
                        class="w-12 h-12 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-400 group-hover:scale-110 transition">
                        <i class="fas fa-file-invoice text-xl"></i>
                    </div>
                    <div class="text-left">
                        <h3 class="text-white font-bold text-lg group-hover:text-purple-300 transition">Administración
                        </h3>
                        <p class="text-slate-500 text-xs">Analistas, Jefes</p>
                    </div>
                    <div class="ml-auto md:hidden text-slate-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <!-- Card 4 -->
                <a href="empleos.html?cat=tecnico"
                    class="group glass p-4 rounded-xl border border-white/5 hover:border-orange-500/30 hover:bg-white/5 transition flex items-center md:flex-col md:items-start gap-4">
                    <div
                        class="w-12 h-12 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-400 group-hover:scale-110 transition">
                        <i class="fas fa-cogs text-xl"></i>
                    </div>
                    <div class="text-left">
                        <h3 class="text-white font-bold text-lg group-hover:text-orange-300 transition">Técnicos</h3>
                        <p class="text-slate-500 text-xs">Mantenimiento, Seguridad</p>
                    </div>
                    <div class="ml-auto md:hidden text-slate-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Role Access (Bifurcation - Retained from v1.7 but compact) -->
        <div class="grid md:grid-cols-2 gap-4 w-full max-w-2xl mx-auto mb-12 opacity-80 hover:opacity-100 transition">
            <a href="login_candidato.html" class="glass p-4 rounded-xl flex items-center gap-4 hover:bg-white/5 transition group">
                <div class="w-10 h-10 bg-slate-700 rounded-full flex items-center justify-center text-slate-300 group-hover:bg-emerald-500 group-hover:text-white transition">
                     <i class="fas fa-user"></i>
                </div>
                <div class="text-left">
                     <h4 class="font-bold text-white">Soy Candidato</h4>
                     <p class="text-xs text-slate-400">Ingresar o Registrarse</p>
                </div>
            </a>
            <a href="login_empresa.html" class="glass p-4 rounded-xl flex items-center gap-4 hover:bg-white/5 transition group">
                <div class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-300 group-hover:bg-cyan-500 group-hover:text-white transition">
                     <i class="fas fa-building"></i>
                </div>
                <div class="text-left">
                     <h4 class="font-bold text-white">Soy Empresa</h4>
                     <p class="text-xs text-slate-400">Publicar empleos</p>
                </div>
            </a>
        </div>

        <!-- Stats Grid (Compact) -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 w-full max-w-3xl mt-auto border-t border-white/5 pt-12">
            <div class="p-4 text-center">
                <p class="text-3xl font-bold text-white mb-1">
                    <?php echo $stats['anuncios']; ?>
                </p>
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Oportunidades Activas</p>
            </div>
            <div class="p-4 text-center">
                <p class="text-3xl font-bold text-emerald-400 mb-1">
                    <?php echo $stats['candidatos']; ?>
                </p>
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Candidatos Registrados</p>
            </div>
            <div class="p-4 text-center hidden md:block">
                <p class="text-3xl font-bold text-cyan-400 mb-1">
                    <?php echo $stats['empresas']; ?>
                </p>
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Empresas Confían</p>
            </div>
        </div>

    </main>

    <footer
        class="p-8 text-center text-slate-600 text-sm md:flex md:justify-between md:container md:mx-auto border-t border-white/5">
        <p>&copy; 2026 ArLog Jobs. Todos los derechos reservados.</p>
        <div class="flex gap-4 justify-center mt-4 md:mt-0">
            <a href="#" class="hover:text-slate-400">Términos</a>
            <a href="#" class="hover:text-slate-400">Privacidad</a>
            <a href="#" class="hover:text-slate-400">Ayuda</a>
        </div>
    </footer>

</body>

</html>
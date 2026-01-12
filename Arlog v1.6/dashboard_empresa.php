<?php
session_start();
require 'db.php';

// Verificar Permisos
if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol'] !== 'empresa' && $_SESSION['user_rol'] !== 'admin')) {
    header("Location: login.html");
    exit;
}

// Obtener Etiquetas Reales
$etiquetas = $pdo->query("SELECT * FROM etiquetas ORDER BY tipo, nombre")->fetchAll(PDO::FETCH_GROUP);

// Obtener Anuncios del Usuario
$stmt = $pdo->prepare("
    SELECT a.*, 
           (SELECT COUNT(*) FROM postulaciones p WHERE p.anuncio_id = a.id) as cant_postulantes 
    FROM anuncios a 
    WHERE a.usuario_id = ? 
    ORDER BY a.fecha_creacion DESC
");
$stmt->execute([$_SESSION['user_id']]);
$misAnuncios = $stmt->fetchAll();

// Contadores
$totalActivos = 0;
$totalPostulantes = 0;

foreach ($misAnuncios as $a) {
    if ($a['estado'] == 'activo') {
        $totalActivos++;
    }
    // Sumar postulantes de cada aviso
    $totalPostulantes += $a['cant_postulantes'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Empresa | Arlog Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col p-6 sticky top-0 h-screen">
        <h1 class="text-xl font-bold mb-10 text-white">ArLog <span class="text-emerald-400">Empresas</span></h1>
        <nav class="space-y-4">
            <a href="dashboard_empresa.php"
                class="flex items-center gap-3 bg-slate-800 p-3 rounded-xl font-bold text-white"><i
                    class="fas fa-briefcase"></i> Mis Anuncios</a>
            <a href="#" class="flex items-center gap-3 text-slate-400 hover:text-white p-3 transition"><i
                    class="fas fa-users"></i> Postulantes</a>
            <a href="#" onclick="fetch('auth.php?action=logout').then(()=>window.location.href='login.html')"
                class="flex items-center gap-3 text-red-400 hover:text-red-300 p-3 transition mt-10"><i
                    class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto h-screen relative">

        <!-- Notificación de éxito -->
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div id="toast"
                class="absolute top-24 right-8 bg-emerald-100 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-xl shadow-lg flex items-center gap-3 animate-bounce z-[60]">
                <i class="fas fa-check-circle text-xl"></i>
                <div>
                    <p class="font-bold">¡Anuncio Publicado!</p>
                    <p class="text-xs">Ya está visible para los candidatos.</p>
                </div>
                <button onclick="document.getElementById('toast').remove()"
                    class="ml-4 text-emerald-600 hover:text-emerald-900"><i class="fas fa-times"></i></button>
            </div>
            <script>setTimeout(() => document.getElementById('toast').remove(), 5000);</script>
        <?php endif; ?>

        <header class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Hola,
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </h2>
                <p class="text-slate-500 text-sm">Gestiona tus búsquedas activas</p>
            </div>
            <button onclick="document.getElementById('modalNewJob').classList.remove('hidden')"
                class="px-6 py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition transform hover:scale-105">
                <i class="fas fa-plus mr-2"></i> Crear Anuncio
            </button>
        </header>

        <!-- Stats Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-400 text-xs font-bold uppercase">Anuncios Activos</p>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $totalActivos; ?></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-400 text-xs font-bold uppercase">Total Postulantes</p>
                <p class="text-3xl font-bold text-emerald-600 mt-2"><?php echo $totalPostulantes; ?></p>
            </div>
        </div>

        <!-- Lista de Anuncios -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Mis Búsquedas Recientes</h3>
            </div>

            <?php if (empty($misAnuncios)): ?>
                <div class="p-16 text-center text-slate-400">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-folder-open text-2xl text-slate-300"></i>
                    </div>
                    <p class="font-medium text-slate-600">No tienes anuncios publicados.</p>
                    <p class="text-xs mt-1">Crea tu primera búsqueda para empezar a recibir CVs.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-50">
                    <?php foreach ($misAnuncios as $anuncio): ?>
                        <div class="p-6 hover:bg-slate-50 transition flex items-center justify-between group">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <h4 class="font-bold text-slate-800 text-lg">
                                        <?php echo htmlspecialchars($anuncio['titulo']); ?>
                                    </h4>
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide
                                    <?php echo $anuncio['estado'] === 'activo' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'; ?>">
                                        <?php echo $anuncio['estado']; ?>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 font-medium uppercase">
                                    <i class="far fa-calendar mr-1"></i>
                                    <?php echo date('d/m/Y', strtotime($anuncio['fecha_creacion'])); ?>
                                    <span class="mx-2">•</span>
                                    <?php echo htmlspecialchars($anuncio['departamento']); ?>
                                </p>
                            </div>

                            <div class="flex items-center gap-6">
                                <a href="postulantes.php?id=<?php echo $anuncio['id']; ?>"
                                    class="group/stats text-center px-4 cursor-pointer hover:bg-slate-50 rounded-lg p-2 transition">
                                    <span
                                        class="block text-xl font-bold text-slate-800 group-hover/stats:text-emerald-600 transition-colors">
                                        <?php echo $anuncio['cant_postulantes']; ?>
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">Candidatos</span>
                                </a>
                                <a href="postulantes.php?id=<?php echo $anuncio['id']; ?>"
                                    class="w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-200 flex items-center justify-center transition shadow-sm">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Nuevo Anuncio (Mantener igual que antes) -->
    <div id="modalNewJob"
        class="fixed inset-0 bg-slate-900/50 hidden flex items-center justify-center p-4 backdrop-blur-sm z-50">
        <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
            <div
                class="p-6 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white/95 backdrop-blur z-10">
                <h3 class="font-bold text-lg text-slate-800">Nuevo Anuncio</h3>
                <button onclick="document.getElementById('modalNewJob').classList.add('hidden')"
                    class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 transition flex items-center justify-center"><i
                        class="fas fa-times"></i></button>
            </div>

            <form action="procesar_anuncio.php" method="POST" class="p-6 space-y-6">
                <!-- Título y Dept -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Título del Puesto</label>
                        <input type="text" name="titulo" required
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Area / Depto</label>
                        <select name="departamento"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none cursor-pointer">
                            <option>Logística</option>
                            <option>Almacén / Depósito</option>
                            <option>Transporte</option>
                            <option>Compras</option>
                            <option>RRHH</option>
                        </select>
                    </div>
                </div>

                <!-- Descripción -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Descripción de tareas</label>
                    <textarea name="descripcion" rows="5" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none transition resize-none"
                        placeholder="Describe las responsabilidades y beneficios..."></textarea>
                </div>

                <!-- Etiquetas Tags -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase">Requisitos (Skills)</label>
                        <span
                            class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded cursor-help"
                            title="Gestiona estas etiquetas desde el panel admin">Ayuda</span>
                    </div>

                    <div
                        class="flex flex-wrap gap-2 max-h-48 overflow-y-auto border border-slate-200 p-3 rounded-xl bg-slate-50 custom-scrollbar">
                        <?php if (empty($etiquetas)): ?>
                            <div class="w-full text-center py-4">
                                <p class="text-xs text-slate-400">No hay etiquetas disponibles.</p>
                                <p class="text-[10px] text-slate-300">Contacta al administrador para cargar el diccionario
                                    de skills.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($etiquetas as $tipo => $tagsGroup): ?>
                                <div
                                    class="w-full text-[10px] font-extrabold text-slate-400 uppercase mt-2 mb-1 pl-1 tracking-wider border-b border-slate-100 pb-1">
                                    <?php echo str_replace('_', ' ', $tipo); ?>
                                </div>
                                <?php foreach ($tagsGroup as $tag): ?>
                                    <label class="cursor-pointer group select-none">
                                        <input type="checkbox" name="etiquetas[]" value="<?php echo $tag['id']; ?>"
                                            class="peer hidden">
                                        <span
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-600 peer-checked:bg-slate-900 peer-checked:text-white peer-checked:border-slate-900 transition-all hover:border-emerald-400 shadow-sm">
                                            <?php echo htmlspecialchars($tag['nombre']); ?>
                                            <i
                                                class="fas fa-check text-[8px] opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer Form -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Ubicación</label>
                        <input type="text" name="ubicacion" required placeholder="Ej: Pilar, GBA"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Modalidad</label>
                        <select name="tipo_contrato"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none cursor-pointer">
                            <option>Full-Time Presencial</option>
                            <option>Part-Time</option>
                            <option>Híbrido</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex gap-4">
                    <button type="button" onclick="document.getElementById('modalNewJob').classList.add('hidden')"
                        class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-50 transition">Cancelar</button>
                    <button type="submit"
                        class="flex-1 py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-emerald-600 transition shadow-lg shadow-slate-200">Publicar
                        Búsqueda</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>
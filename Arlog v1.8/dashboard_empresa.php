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

// New logic for tagsDisponibles (from the provided new code)
$tagsQuery = $pdo->query("SELECT nombre FROM etiquetas ORDER BY nombre ASC");
$tagsDisponibles = $tagsQuery->fetchAll(PDO::FETCH_COLUMN);

// New logic for totalAnuncios and anunciosActivos (from the provided new code)
$totalAnuncios = count($misAnuncios);
$anunciosActivos = count(array_filter($misAnuncios, fn($a) => $a['estado'] === 'activo'));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Empresa | Arlog Jobs</title>
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
            <a href="dashboard_empresa.php"
                class="flex items-center gap-3 bg-cyan-500/10 text-cyan-400 p-3 rounded-xl font-bold border border-cyan-500/20"><i
                    class="fas fa-briefcase"></i> Mis Anuncios</a>
            <a href="#"
                class="flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/5 p-3 rounded-xl transition"><i
                    class="fas fa-users"></i> Postulantes <span
                    class="bg-white/10 text-xs py-0.5 px-2 rounded ml-auto"><?php echo $totalPostulantes; ?></span></a>
            <a href="index.php"
                class="flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/5 p-3 rounded-xl transition"><i
                    class="fas fa-globe"></i> Ver Sitio</a>
        </nav>

        <a href="#" onclick="fetch('auth.php?action=logout').then(()=>window.location.href='index.php')"
            class="flex items-center gap-3 text-red-400 hover:text-red-300 p-3 transition mt-auto hover:bg-red-500/10 rounded-xl"><i
                class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </aside>

    <main class="flex-1 p-6 md:p-8 overflow-y-auto h-screen relative scroll-smooth">

        <!-- Mobile Header -->
        <div class="md:hidden flex justify-between items-center mb-6">
            <img src="arlogjobs_logo.png" alt="Logo" class="h-8 filter brightness-0 invert">
            <button class="text-white"><i class="fas fa-bars text-2xl"></i></button>
        </div>

        <!-- Notificación de éxito -->
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div id="toast"
                class="absolute top-6 right-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 animate-bounce z-[60] backdrop-blur-md">
                <i class="fas fa-check-circle text-xl"></i>
                <div>
                    <p class="font-bold">¡Anuncio Publicado!</p>
                    <p class="text-xs text-emerald-400/80">Ya está visible para los candidatos.</p>
                </div>
                <button onclick="document.getElementById('toast').remove()"
                    class="ml-4 text-emerald-400 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <script>setTimeout(() => document.getElementById('toast').remove(), 5000);</script>
        <?php endif; ?>

        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-white mb-1">Hola,
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </h2>
                <p class="text-slate-400 text-sm">Gestiona tus búsquedas activas y evalúa talento.</p>
            </div>
            <button onclick="openNewJobModal()"
                class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-cyan-500/25 transition transform hover:scale-105 flex items-center gap-2">
                <i class="fas fa-plus"></i> Crear Anuncio
            </button>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="glass p-6 rounded-2xl border border-white/5">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Total Anuncios</p>
                <p class="text-4xl font-bold text-white"><?php echo $totalAnuncios; ?></p>
            </div>
            <div class="glass p-6 rounded-2xl border border-white/5">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Activos Ahora</p>
                <p class="text-4xl font-bold text-cyan-400"><?php echo $anunciosActivos; ?></p>
            </div>
            <div class="glass p-6 rounded-2xl border border-white/5">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Total Postulantes</p>
                <p class="text-4xl font-bold text-emerald-400"><?php echo $totalPostulantes; ?></p>
            </div>
        </div>

        <!-- Job List -->
        <div class="glass rounded-3xl border border-white/5 overflow-hidden">
            <div class="p-6 border-b border-white/5 flex justify-between items-center bg-white/5">
                <h3 class="font-bold text-lg text-white">Tus Búsquedas Recientes</h3>
            </div>

            <?php if (empty($misAnuncios)): ?>
                <div class="p-16 text-center text-slate-400">
                    <div
                        class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 border border-white/10">
                        <i class="fas fa-folder-open text-3xl text-slate-500"></i>
                    </div>
                    <p class="font-medium text-white mb-2">No tienes anuncios publicados.</p>
                    <p class="text-xs text-slate-400">Crea tu primera búsqueda para empezar a recibir CVs.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-white/5">
                    <?php foreach ($misAnuncios as $anuncio): ?>
                        <div
                            class="p-6 hover:bg-white/5 transition flex flex-col md:flex-row items-start md:items-center justify-between gap-4 group">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="font-bold text-white text-lg group-hover:text-cyan-400 transition-colors">
                                        <?php echo htmlspecialchars($anuncio['titulo']); ?>
                                    </h4>
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border
                                    <?php echo $anuncio['estado'] === 'activo' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border-slate-500/20'; ?>">
                                        <?php echo $anuncio['estado']; ?>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">
                                    <i class="far fa-calendar mr-1 opacity-50"></i>
                                    <?php echo date('d/m/Y', strtotime($anuncio['fecha_creacion'])); ?>
                                    <span class="mx-2 opacity-30">|</span>
                                    <?php echo htmlspecialchars($anuncio['departamento']); ?>
                                </p>
                            </div>

                            <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
                                <div class="flex items-center gap-2 mr-4 border-r border-white/10 pr-4">
                                    <!-- Botón Editar -->
                                    <button onclick='openEditModal({
                                            id: <?php echo $anuncio["id"]; ?>,
                                            titulo: <?php echo json_encode($anuncio["titulo"]); ?>,
                                            ubicacion: <?php echo json_encode($anuncio["ubicacion"]); ?>,
                                            departamento: <?php echo json_encode($anuncio["departamento"]); ?>,
                                            descripcion: <?php echo json_encode($anuncio["descripcion"]); ?>,
                                            tags: <?php echo json_encode(array_column($anuncio["tags"] ?? [], "nombre")); ?>
                                        })'
                                        class="w-8 h-8 rounded-lg bg-white/5 text-slate-400 hover:text-cyan-400 hover:bg-cyan-500/10 transition flex items-center justify-center"
                                        title="Editar Anuncio">
                                        <i class="fas fa-pencil-alt text-xs"></i>
                                    </button>

                                    <!-- Botón Eliminar -->
                                    <form action="eliminar_anuncio.php" method="POST"
                                        onsubmit="return confirm('¿Seguro que quieres eliminar este anuncio? Esta acción no se puede deshacer.');">
                                        <input type="hidden" name="id" value="<?php echo $anuncio['id']; ?>">
                                        <button type="submit"
                                            class="w-8 h-8 rounded-lg bg-white/5 text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition flex items-center justify-center"
                                            title="Eliminar Anuncio">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>

                                <a href="postulantes.php?id=<?php echo $anuncio['id']; ?>"
                                    class="group/stats text-center px-4 cursor-pointer hover:bg-white/5 rounded-xl p-2 transition border border-transparent hover:border-white/5">
                                    <span
                                        class="block text-2xl font-bold text-white group-hover/stats:text-emerald-400 transition-colors">
                                        <?php echo $anuncio['cant_postulantes']; ?>
                                    </span>
                                    <span
                                        class="text-[10px] text-slate-500 group-hover/stats:text-slate-300 font-bold uppercase transition-colors">Candidatos</span>
                                </a>
                                <a href="postulantes.php?id=<?php echo $anuncio['id']; ?>"
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

    <!-- Modal Create/Edit Job -->
    <div id="modalNewJob"
        class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[70] flex items-center justify-center p-4">
        <div
            class="glass bg-slate-900 rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-white/10 shadow-2xl relative">

            <div
                class="sticky top-0 bg-slate-900/95 backdrop-blur-xl p-6 border-b border-white/10 flex justify-between items-center z-10">
                <h3 id="modalTitle" class="text-xl font-bold text-white">Nuevo Anuncio</h3>
                <button onclick="document.getElementById('modalNewJob').classList.add('hidden')"
                    class="text-slate-400 hover:text-white transition"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form id="formAnuncio" action="procesar_anuncio.php" method="POST" class="p-6 space-y-6">
                <input type="hidden" name="anuncio_id" id="anuncio_id" value="">

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-cyan-400 uppercase tracking-wider ml-1">Título del
                        Puesto</label>
                    <input type="text" name="titulo" id="inputTitulo" required placeholder="Ej: Operario de Clark"
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:bg-white/10 focus:border-cyan-500/50 transition">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-cyan-400 uppercase tracking-wider ml-1">Ubicación</label>
                    <input type="text" name="ubicacion" id="inputUbicacion" placeholder="Ej: Zona Norte, Bs As"
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:bg-white/10 focus:border-cyan-500/50 transition">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-cyan-400 uppercase tracking-wider ml-1">Descripción del
                        trabajo</label>
                    <textarea name="descripcion" id="inputDescripcion" rows="6" required
                        placeholder="Describe las responsabilidades y requisitos..."
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:bg-white/10 focus:border-cyan-500/50 transition resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-cyan-400 uppercase tracking-wider ml-1">Departamento
                            / Área</label>
                        <select name="departamento" id="inputDepartamento" required
                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:bg-white/10 focus:border-cyan-500/50 transition appearance-none">
                            <option value="" class="bg-slate-900">Seleccionar...</option>
                            <option value="Transporte" class="bg-slate-900">Transporte</option>
                            <option value="Almacén/Depósito" class="bg-slate-900">Almacén/Depósito</option>
                            <option value="Compras" class="bg-slate-900">Compras</option>
                            <option value="Administración" class="bg-slate-900">Administración</option>
                            <option value="RRHH" class="bg-slate-900">RRHH</option>
                            <option value="Mantenimiento" class="bg-slate-900">Mantenimiento</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label
                            class="block text-xs font-bold text-cyan-400 uppercase tracking-wider ml-1 mb-2">Etiquetas
                            (Tags)</label>
                        <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto pr-2 custom-scrollbar">
                            <?php foreach ($tagsDisponibles as $tag): ?>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="etiquetas[]" value="<?php echo $tag; ?>"
                                        class="peer sr-only">
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-bold bg-white/5 text-slate-400 border border-white/10 peer-checked:bg-cyan-500 peer-checked:text-slate-900 peer-checked:border-cyan-400 transition select-none hover:bg-white/10">
                                        <?php echo $tag; ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-4 bg-gradient-to-r from-cyan-500 to-blue-600 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-cyan-500/25 transition transform hover:scale-[1.02]">
                    Publicar Anuncio
                </button>
            </form>
        </div>
    </div>

    <script>
        function openNewJobModal() {
            document.getElementById('formAnuncio').reset();
            document.getElementById('anuncio_id').value = '';
            document.getElementById('modalTitle').textContent = 'Nuevo Anuncio';

            // Uncheck all tags
            document.querySelectorAll('input[type="checkbox"]').forEach(el => el.checked = false);

            document.getElementById('modalNewJob').classList.remove('hidden');
        }

        function openEditModal(data) {
            document.getElementById('formAnuncio').reset();
            document.getElementById('anuncio_id').value = data.id;
            document.getElementById('modalTitle').textContent = 'Editar Anuncio';

            document.getElementById('inputTitulo').value = data.titulo || '';
            document.getElementById('inputUbicacion').value = data.ubicacion || '';
            document.getElementById('inputDescripcion').value = data.descripcion || '';
            document.getElementById('inputDepartamento').value = data.departamento || '';

            // Handle Tags
            const tagsRaw = data.tags || [];
            const allCheckboxes = document.querySelectorAll('input[name="etiquetas[]"]');
            allCheckboxes.forEach(cb => {
                cb.checked = tagsRaw.includes(cb.value);
            });

            document.getElementById('modalNewJob').classList.remove('hidden');
        }
    </script>
</body>

</html>
<?php
session_start();
require 'db.php';

// --- VERIFICACIÓN DE SEGURIDAD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php"); // Si no está logueado, mandar al login
    exit;
}

// --- LÓGICA: CREAR NUEVO ANUNCIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $titulo = $_POST['titulo'];
    $depto = $_POST['departamento'];
    $desc = $_POST['descripcion'];
    $usuario_id = $_SESSION['admin_id'];

    // Guardamos en la base de datos
    $sql = "INSERT INTO anuncios (usuario_id, titulo, departamento, descripcion) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $titulo, $depto, $desc]);
    
    // Recargar para evitar reenvío de formulario
    header("Location: anuncios.php");
    exit;
}

// --- LÓGICA: CAMBIAR ESTADO (PAUSAR/CERRAR) ---
if (isset($_GET['toggle_id'])) {
    $id = $_GET['toggle_id'];
    $current = $_GET['status'];
    $newStatus = ($current === 'activo') ? 'pausado' : 'activo';
    
    $stmt = $pdo->prepare("UPDATE anuncios SET estado = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
    header("Location: anuncios.php");
    exit;
}

// --- OBTENER ANUNCIOS Y CONTAR CANDIDATOS ---
// Esta consulta mágica cuenta cuántos postulantes tiene cada anuncio
$sql = "SELECT a.*, COUNT(p.id) as total_candidatos 
        FROM anuncios a 
        LEFT JOIN postulaciones p ON a.id = p.anuncio_id 
        GROUP BY a.id 
        ORDER BY a.fecha_creacion DESC";
$anuncios = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Anuncios - SecureDocs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- Navegación Admin -->
    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center sticky top-0 z-10">
        <div class="flex items-center gap-4">
            <div class="font-bold text-xl tracking-tight">SecureDocs <span class="text-emerald-600">Admin</span></div>
            <div class="h-6 w-px bg-slate-300 mx-2"></div>
            <a href="admin.php" class="text-sm font-medium text-slate-500 hover:text-slate-800">Candidatos</a>
            <a href="anuncios.php" class="text-sm font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full">Anuncios</a>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-slate-500 hidden sm:inline"><?php echo $_SESSION['admin_email']; ?></span>
            <a href="admin.php?logout=true" class="text-sm font-medium text-red-600 hover:text-red-800">Salir</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- COLUMNA IZQUIERDA: CREAR ANUNCIO -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 sticky top-24">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Publicar Nuevo Anuncio
                </h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="create">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Título del Puesto</label>
                        <input type="text" name="titulo" required placeholder="Ej: Chofer Semirremolque" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Departamento</label>
                        <select name="departamento" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                            <option value="Operaciones">Operaciones</option>
                            <option value="Logística">Logística</option>
                            <option value="Administración">Administración</option>
                            <option value="RRHH">Recursos Humanos</option>
                            <option value="Taller">Taller / Mantenimiento</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Descripción Breve</label>
                        <textarea name="descripcion" rows="4" required placeholder="Requisitos, zona de trabajo, horarios..." class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-2.5 rounded-lg hover:bg-slate-800 transition font-medium text-sm">
                        Publicar Oferta
                    </button>
                </form>
            </div>
        </div>

        <!-- COLUMNA DERECHA: LISTA DE ANUNCIOS -->
        <div class="lg:col-span-2">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Ofertas Activas</h2>
            
            <div class="space-y-4">
                <?php foreach ($anuncios as $job): ?>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex justify-between items-center hover:border-emerald-300 transition group">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="font-bold text-lg text-slate-800 group-hover:text-emerald-700 transition"><?php echo htmlspecialchars($job['titulo']); ?></h3>
                            <?php if($job['estado'] === 'activo'): ?>
                                <span class="bg-emerald-100 text-emerald-700 text-xs px-2 py-0.5 rounded-full font-bold">Activo</span>
                            <?php else: ?>
                                <span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full font-bold">Pausado</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-slate-500 mb-2"><?php echo htmlspecialchars($job['departamento']); ?> • Publicado el <?php echo date('d/m/Y', strtotime($job['fecha_creacion'])); ?></p>
                        <p class="text-sm text-slate-600 line-clamp-2"><?php echo htmlspecialchars($job['descripcion']); ?></p>
                    </div>

                    <div class="flex flex-col items-end gap-3 min-w-[140px]">
                        <div class="text-right">
                            <span class="block text-2xl font-bold text-slate-900"><?php echo $job['total_candidatos']; ?></span>
                            <span class="text-xs text-slate-500 uppercase font-bold">Candidatos</span>
                        </div>
                        
                        <div class="flex gap-2">
                            <!-- Botón Pausar/Activar -->
                            <a href="anuncios.php?toggle_id=<?php echo $job['id']; ?>&status=<?php echo $job['estado']; ?>" 
                               class="text-xs font-medium px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 transition 
                               <?php echo ($job['estado'] === 'activo') ? 'text-amber-600' : 'text-emerald-600'; ?>">
                               <?php echo ($job['estado'] === 'activo') ? 'Pausar' : 'Activar'; ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </main>
</body>
</html>
<?php
session_start();
require 'db.php';

// --- SEGURIDAD ---
if (!isset($_SESSION['admin_id'])) {
    // Auto-login temporal para desarrollo (Mismo que admin.php)
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_email'] = 'admin@arlog.com';
}

// --- LÓGICA: CREAR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $titulo = trim($_POST['titulo']);
    $depto = $_POST['departamento'];
    $desc = trim($_POST['descripcion']);
    
    if(!empty($titulo)) {
        $stmt = $pdo->prepare("INSERT INTO anuncios (usuario_id, titulo, departamento, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $titulo, $depto, $desc]);
    }
    header("Location: anuncios.php");
    exit;
}

// --- LÓGICA: CAMBIAR ESTADO ---
if (isset($_GET['toggle_id'])) {
    $id = $_GET['toggle_id'];
    $current = $_GET['status'];
    $newStatus = ($current === 'activo') ? 'pausado' : 'activo';
    
    $stmt = $pdo->prepare("UPDATE anuncios SET estado = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
    header("Location: anuncios.php");
    exit;
}

// --- DATOS ---
// Anuncios con conteo de postulantes
$sql = "SELECT a.*, COUNT(p.id) as total_candidatos 
        FROM anuncios a 
        LEFT JOIN postulaciones p ON a.id = p.anuncio_id 
        GROUP BY a.id 
        ORDER BY a.estado ASC, a.fecha_creacion DESC"; // Activos primero
$anuncios = $pdo->query($sql)->fetchAll();

// Contadores simples
$totalActivos = 0;
foreach($anuncios as $a) { if($a['estado'] === 'activo') $totalActivos++; }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ofertas - Arlog Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center sticky top-0 z-20 shadow-sm">
        <div class="flex items-center gap-4">
            <img src="arlogjobs_logo.png" alt="Arlog Jobs Admin" class="h-10 w-auto object-contain">
            
            <div class="h-6 w-px bg-slate-300 mx-2 hidden md:block"></div>
            <a href="admin.php" class="text-sm font-medium text-slate-500 hover:text-slate-800">Candidatos</a>
            <a href="anuncios.php" class="text-sm font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">Ofertas</a>
        </div>
        <div class="flex items-center gap-4">
            <a href="admin.php?logout=true" class="text-sm font-medium text-red-600 hover:text-red-800">Salir</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 sticky top-24">
                <div class="flex items-center gap-2 mb-6">
                    <div class="bg-emerald-100 p-2 rounded-lg text-emerald-600">
                        <i class="fas fa-plus"></i>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Nueva Búsqueda</h2>
                </div>

                <form method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="create">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Puesto Solicitado</label>
                        <input type="text" name="titulo" required placeholder="Ej: Analista Contable Senior" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Área / Depto</label>
                        <select name="departamento" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white cursor-pointer">
                            <option value="Operaciones">Operaciones</option>
                            <option value="Logística">Logística</option>
                            <option value="Administración">Administración</option>
                            <option value="RRHH">Recursos Humanos</option>
                            <option value="Comercial">Comercial</option>
                            <option value="Taller">Taller / Mantenimiento</option>
                            <option value="IT">Tecnología / Sistemas</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Descripción Breve</label>
                        <textarea name="descripcion" rows="4" required placeholder="Principales requisitos y zona de trabajo..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm transition"></textarea>
                        <p class="text-xs text-slate-400 mt-1 text-right">Visible para el candidato</p>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-3 rounded-lg hover:bg-slate-800 transition font-medium text-sm shadow-lg shadow-slate-200">
                        Publicar Oferta Ahora
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="flex justify-between items-end mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Ofertas Disponibles</h2>
                    <p class="text-slate-500 text-sm">Gestiona la visibilidad de tus búsquedas laborales.</p>
                </div>
                <div class="flex gap-4">
                    <div class="text-right px-4 py-2 bg-white rounded-lg border border-slate-200 shadow-sm">
                        <span class="block text-xl font-bold text-emerald-600 leading-none"><?php echo $totalActivos; ?></span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase">Activas</span>
                    </div>
                    <div class="text-right px-4 py-2 bg-white rounded-lg border border-slate-200 shadow-sm">
                        <span class="block text-xl font-bold text-slate-800 leading-none"><?php echo count($anuncios); ?></span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase">Total</span>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4">
                <?php if (empty($anuncios)): ?>
                    <div class="text-center py-12 bg-white rounded-xl border-2 border-dashed border-slate-200">
                        <p class="text-slate-400 font-medium">No hay ofertas creadas aún.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($anuncios as $job): 
                    $isActivo = ($job['estado'] === 'activo');
                ?>
                <div class="bg-white p-5 rounded-xl shadow-sm border <?php echo $isActivo ? 'border-l-4 border-l-emerald-500 border-slate-200' : 'border-l-4 border-l-slate-300 border-slate-200 bg-slate-50'; ?> transition hover:shadow-md relative group">
                    
                    <div class="flex justify-between items-start">
                        <div class="flex-1 pr-4">
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="font-bold text-lg text-slate-800"><?php echo htmlspecialchars($job['titulo']); ?></h3>
                                <?php if($isActivo): ?>
                                    <span class="bg-emerald-100 text-emerald-700 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wide border border-emerald-200">Online</span>
                                <?php else: ?>
                                    <span class="bg-slate-200 text-slate-600 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wide border border-slate-300">Pausada</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs font-bold text-slate-500 uppercase mb-2 tracking-wide">
                                <i class="far fa-building mr-1"></i> <?php echo htmlspecialchars($job['departamento']); ?> 
                                <span class="mx-2 text-slate-300">|</span> 
                                <i class="far fa-clock mr-1"></i> <?php echo date('d/m/Y', strtotime($job['fecha_creacion'])); ?>
                            </p>
                            <p class="text-sm text-slate-600 line-clamp-2"><?php echo htmlspecialchars($job['descripcion']); ?></p>
                        </div>

                        <div class="flex flex-col items-end gap-3 min-w-[120px]">
                            <div class="text-right">
                                <span class="block text-2xl font-bold text-slate-900"><?php echo $job['total_candidatos']; ?></span>
                                <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Candidatos</span>
                            </div>
                            
                            <div class="flex gap-2 mt-1">
                                <button onclick="alert('Enlace copiado (Próximamente funcional)')" class="text-slate-400 hover:text-emerald-600 p-2 rounded-lg hover:bg-emerald-50 transition" title="Copiar Link">
                                    <i class="fas fa-link"></i>
                                </button>
                                
                                <a href="anuncios.php?toggle_id=<?php echo $job['id']; ?>&status=<?php echo $job['estado']; ?>" 
                                   class="text-xs font-bold px-3 py-2 rounded-lg border transition shadow-sm flex items-center gap-2
                                   <?php echo $isActivo 
                                        ? 'border-slate-200 text-slate-600 hover:bg-slate-100' 
                                        : 'border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100'; ?>">
                                   <i class="fas <?php echo $isActivo ? 'fa-pause' : 'fa-play'; ?>"></i>
                                   <?php echo $isActivo ? 'Pausar' : 'Activar'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </main>
</body>
</html>
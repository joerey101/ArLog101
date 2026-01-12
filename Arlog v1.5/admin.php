<?php
session_start();
require_once 'db.php'; 

// --- 1. SEGURIDAD DE SESIÓN ---
// Si no hay sesión, simular login o redirigir. 
// Para este MVP, si no hay variable de sesión, creamos una dummy si entran directo
// (En producción real aquí iría un login form estricto)
if (!isset($_SESSION['admin_id'])) {
    // Auto-login temporal para desarrollo/MVP
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_email'] = 'admin@arlog.com';
}

// LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php"); // O a login.php
    exit;
}

// --- 2. LÓGICA DE ELIMINADO (NUEVO v1.1) ---
if (isset($_GET['delete_id'])) {
    $idBorrar = (int)$_GET['delete_id'];
    
    // Primero obtenemos la ruta del archivo para borrarlo del disco
    $stmt = $pdo->prepare("SELECT ruta_archivo_pdf FROM postulaciones WHERE id = ?");
    $stmt->execute([$idBorrar]);
    $archivo = $stmt->fetchColumn();

    if ($archivo) {
        // 1. Borrar archivo físico
        if (file_exists($archivo)) {
            unlink($archivo); 
        }
        // 2. Borrar registro DB
        $pdo->prepare("DELETE FROM postulaciones WHERE id = ?")->execute([$idBorrar]);
    }
    
    // Redirigir limpio
    header("Location: admin.php");
    exit;
}

// --- 3. CONSULTA DE CANDIDATOS ---
$sql = "SELECT p.*, a.titulo as titulo_anuncio 
        FROM postulaciones p 
        LEFT JOIN anuncios a ON p.anuncio_id = a.id 
        ORDER BY p.fecha_postulacion DESC";

try {
    $stmt = $pdo->query($sql);
    $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_db = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel RRHH - Arlog Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center sticky top-0 z-20 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="bg-emerald-600 text-white p-2 rounded-lg">
                <i class="fas fa-briefcase"></i>
            </div>
            <div>
                <h1 class="font-bold text-xl tracking-tight leading-tight">Arlog Jobs</h1>
                <p class="text-xs text-slate-500 font-medium">Panel de Control v1.1</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="anuncios.php" class="text-sm font-medium text-slate-500 hover:text-emerald-600 transition">Gestionar Anuncios</a>
            <div class="h-4 w-px bg-slate-300"></div>
            <a href="?logout=1" class="text-sm font-bold text-red-500 hover:text-red-700">Salir</a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-6">
        
        <header class="mb-8 flex justify-between items-end">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Postulaciones Recibidas</h2>
                <p class="text-slate-500 mt-1">Gestión de CVs y documentos entrantes.</p>
            </div>
            <div class="text-right">
                <span class="block text-3xl font-bold text-emerald-600"><?php echo count($candidatos); ?></span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Documentos</span>
            </div>
        </header>

        <?php if (isset($error_db)): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo $error_db; ?>
            </div>
        <?php endif; ?>

        <div class="grid gap-4">
            <?php if (empty($candidatos)): ?>
                <div class="text-center py-20 bg-white rounded-xl border border-dashed border-slate-300">
                    <div class="text-slate-300 text-5xl mb-4"><i class="far fa-folder-open"></i></div>
                    <p class="text-slate-500 font-medium">Aún no hay postulaciones.</p>
                </div>
            <?php else: ?>
                
                <?php foreach ($candidatos as $c): 
                    // Decodificar JSON de metadatos
                    $extra = json_decode($c['datos_extra'], true) ?? [];
                    
                    // Variables listas para usar (con fallbacks)
                    $nombre = !empty($c['nombre_candidato']) ? $c['nombre_candidato'] : 'Candidato Desconocido';
                    $email  = !empty($c['email_candidato']) ? $c['email_candidato'] : 'Sin email';
                    $tituloDoc = $extra['titulo_documento'] ?? 'Documento General';
                    $depto  = $extra['departamento_interes'] ?? 'General';
                    $archivoOriginal = $extra['nombre_original'] ?? basename($c['ruta_archivo_pdf']);
                    $fecha = date('d/m/Y H:i', strtotime($c['fecha_postulacion']));
                    
                    // Iniciales para el avatar
                    $iniciales = strtoupper(substr($nombre, 0, 2));
                ?>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition group relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>

                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        
                        <div class="flex items-center gap-4 min-w-[30%]">
                            <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-lg border border-slate-200 shrink-0">
                                <?php echo $iniciales; ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-900 leading-tight"><?php echo htmlspecialchars($nombre); ?></h3>
                                <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="text-sm text-emerald-600 hover:underline flex items-center gap-1">
                                    <i class="far fa-envelope"></i> <?php echo htmlspecialchars($email); ?>
                                </a>
                            </div>
                        </div>

                        <div class="flex-1 border-l border-slate-100 pl-6 hidden md:block">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded font-bold uppercase tracking-wide">
                                    <?php echo htmlspecialchars($depto); ?>
                                </span>
                                <span class="text-xs text-slate-400">• <?php echo $fecha; ?> hs</span>
                            </div>
                            <p class="font-medium text-slate-700"><?php echo htmlspecialchars($tituloDoc); ?></p>
                            <p class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                                <i class="fas fa-paperclip"></i> <?php echo htmlspecialchars($archivoOriginal); ?>
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="<?php echo htmlspecialchars($c['ruta_archivo_pdf']); ?>" target="_blank" 
                               class="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg text-sm font-medium hover:bg-slate-800 transition shadow-sm">
                                <i class="fas fa-download"></i> <span class="hidden sm:inline">PDF</span>
                            </a>
                            
                            <a href="admin.php?delete_id=<?php echo $c['id']; ?>" 
                               onclick="return confirm('¿Estás seguro de ELIMINAR este documento? Esta acción no se puede deshacer.');"
                               class="flex items-center justify-center w-9 h-9 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Eliminar postulación">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>

                    </div>
                    
                    <?php if(!empty($extra['descripcion_usuario'])): ?>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-sm text-slate-500 italic flex items-start gap-2">
                            <i class="fas fa-quote-left text-slate-300"></i>
                            <?php echo htmlspecialchars($extra['descripcion_usuario']); ?>
                        </div>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </main>

</body>
</html>
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

// Obtener Postulantes + Datos de Perfil Enriquecidos
// Priorizamos datos del perfil vivo (pc) sobre el snapshot de la postulación (p)
$stmt = $pdo->prepare("
    SELECT p.*, 
           pc.nombre as perfil_nombre, pc.apellido as perfil_apellido,
           pc.telefono, pc.zona, pc.localidad, pc.linkedin_url, pc.licencias, pc.cuit
    FROM postulaciones p 
    LEFT JOIN usuarios u ON p.email_candidato = u.email
    LEFT JOIN perfiles_candidatos pc ON u.id = pc.usuario_id
    WHERE p.anuncio_id = ? 
    ORDER BY field(p.estado, 'nuevo', 'visto', 'contactado', 'descartado'), p.fecha_postulacion DESC
");
$stmt->execute([$anuncio_id]);
$candidatos = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulantes: <?php echo htmlspecialchars($anuncio['titulo']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>

<body class="bg-indigo-50/50 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white border-b border-indigo-100 px-8 py-5 sticky top-0 z-50 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-6">
            <a href="dashboard_empresa.php" class="text-slate-400 hover:text-indigo-600 transition flex items-center gap-2 font-medium">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                    Postulantes para <span class="text-indigo-600"><?php echo htmlspecialchars($anuncio['titulo']); ?></span>
                </h1>
            </div>
        </div>
        <div>
            <span class="bg-indigo-50 text-indigo-600 border border-indigo-100 font-bold px-4 py-1.5 rounded-full text-sm">
                <?php echo count($candidatos); ?> Candidatos
            </span>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto py-12 px-6">

        <?php if (empty($candidatos)): ?>
            <div class="text-center py-24 bg-white rounded-3xl shadow-sm border border-slate-100 max-w-lg mx-auto">
                <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-search text-4xl text-indigo-200"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-700 mb-2">Esperando talentos</h3>
                <p class="text-slate-400">Aún no tienes postulantes para este aviso.</p>
            </div>
        <?php else: ?>

            <div class="space-y-6">
                <?php foreach ($candidatos as $c): 
                    // Lógica de Nombre: Usar Perfil si existe, sino Snapshot Postulación
                    $displayName = (!empty($c['perfil_nombre'])) 
                        ? $c['perfil_nombre'] . ' ' . $c['perfil_apellido'] 
                        : $c['nombre_candidato'];
                    
                    // Iniciales
                    $initials = strtoupper(substr($displayName, 0, 1));
                    
                    // WhatsApp Link Check
                    $hasPhone = !empty($c['telefono']);
                    $cleanPhone = $hasPhone ? preg_replace('/[^0-9]/', '', $c['telefono']) : '';
                    if($hasPhone && substr($cleanPhone, 0, 2) != '54') $cleanPhone = '549' . $cleanPhone;
                    $waLink = "https://wa.me/" . $cleanPhone;

                    // Estado Visual
                    $isDiscarded = $c['estado'] === 'descartado';
                    $opacityClass = $isDiscarded ? 'opacity-60 grayscale' : '';
                ?>
                    
                    <div id="row-<?php echo $c['id']; ?>" 
                         class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl transition-all duration-300 group <?php echo $opacityClass; ?>">
                        
                        <!-- Encabezado Tarjeta -->
                        <div class="p-6 md:p-8 flex flex-col md:flex-row gap-6 md:items-start border-b border-slate-50">
                            
                            <!-- Avatar -->
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl shadow-indigo-200 shadow-lg shrink-0">
                                <?php echo $initials; ?>
                            </div>

                            <!-- Info Principal -->
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h2 class="text-2xl font-bold text-slate-800 mb-1">
                                            <?php echo htmlspecialchars($displayName); ?>
                                        </h2>
                                        <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                                            <?php if(!empty($c['zona'])): ?>
                                                <span class="flex items-center gap-1.5 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100">
                                                    <i class="fas fa-map-marker-alt text-indigo-400"></i> 
                                                    <?php echo htmlspecialchars($c['localidad'] . ', ' . $c['zona']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="flex items-center gap-1.5 text-slate-400 py-1">
                                                 <i class="far fa-clock"></i> Postulado <?php echo date('d/m', strtotime($c['fecha_postulacion'])); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Badge Estado -->
                                    <div id="badge-<?php echo $c['id']; ?>" class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide
                                        <?php
                                        switch ($c['estado']) {
                                            case 'contactado': echo 'bg-emerald-100 text-emerald-700'; break;
                                            case 'descartado': echo 'bg-slate-100 text-slate-500'; break;
                                            case 'visto': echo 'bg-amber-100 text-amber-700'; break;
                                            default: echo 'bg-blue-50 text-blue-600';
                                        }
                                        ?>">
                                        <?php echo $c['estado']; ?>
                                    </div>
                                </div>

                                <!-- Datos de Contacto (Grid Grande) -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                                    
                                    <!-- Email -->
                                    <?php 
                                    $asuntoMail = "Consulta sobre tu postulación a " . $anuncio['titulo'] . " - ArLog Jobs";
                                    $mailtoLink = "mailto:" . $c['email_candidato'] . "?subject=" . rawurlencode($asuntoMail);
                                    ?>
                                    <a href="<?php echo $mailtoLink; ?>" 
                                       class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-100 transition group/item">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 group-hover/item:text-indigo-500 shadow-sm">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase">Email</p>
                                            <p class="text-sm font-semibold text-slate-700 truncate"><?php echo htmlspecialchars($c['email_candidato']); ?></p>
                                        </div>
                                    </a>

                                    <!-- Teléfono / WhatsApp -->
                                    <?php if($hasPhone): ?>
                                    <a href="<?php echo $waLink; ?>" target="_blank"
                                       class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50/50 hover:bg-emerald-50 border border-emerald-100/50 transition group/item">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-emerald-500 group-hover/item:text-emerald-600 shadow-sm">
                                            <i class="fab fa-whatsapp text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-emerald-400 uppercase">WhatsApp</p>
                                            <p class="text-sm font-semibold text-emerald-800"><?php echo htmlspecialchars($c['telefono']); ?></p>
                                        </div>
                                    </a>
                                    <?php else: ?>
                                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 opacity-50">
                                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-300">
                                                <i class="fas fa-phone-slash"></i>
                                            </div>
                                            <p class="text-sm font-medium text-slate-400">Sin teléfono</p>
                                        </div>
                                    <?php endif; ?>

                                    <!-- LinkedIn -->
                                    <?php if(!empty($c['linkedin_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($c['linkedin_url']); ?>" target="_blank"
                                       class="flex items-center gap-3 p-3 rounded-xl bg-blue-50/50 hover:bg-blue-50 border border-blue-100/50 transition group/item">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-blue-600 group-hover/item:text-blue-700 shadow-sm">
                                            <i class="fab fa-linkedin-in text-lg"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-[10px] font-bold text-blue-400 uppercase">LinkedIn</p>
                                            <p class="text-sm font-semibold text-blue-800 truncate">Ver Perfil</p>
                                        </div>
                                    </a>
                                    <?php endif; ?>

                                </div>

                                <!-- Licencias y Extras -->
                                <?php if(!empty($c['licencias'])): ?>
                                    <div class="mt-6 flex flex-wrap gap-2">
                                        <?php foreach(explode(',', $c['licencias']) as $lic): 
                                             $shortLic = explode(' - ', $lic)[0]; 
                                        ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                <i class="fas fa-id-card mr-2 opacity-50"></i> <?php echo $shortLic; ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>

                        <!-- Footer Acciones -->
                        <div class="bg-slate-50 px-6 py-4 flex items-center justify-between border-t border-slate-100">
                            
                            <!-- Botón CV -->
                            <?php 
                            $webPath = $c['ruta_archivo_pdf'];
                            if (strpos($webPath, '/home/') !== false) {
                                $parts = explode('arlogjobs.joserey101.com/', $webPath);
                                $webPath = end($parts);
                            }
                            ?>
                            <a href="<?php echo $webPath; ?>" target="_blank" class="inline-flex items-center gap-2 text-slate-600 font-bold text-sm hover:text-slate-900 transition">
                                <i class="fas fa-file-pdf text-red-500"></i>
                                <span>Ver Curriculum Vitae</span>
                            </a>

                            <!-- Acciones CRM -->
                            <div class="flex gap-3">
                                <button onclick="updateStatus(<?php echo $c['id']; ?>, 'descartado')" 
                                        class="px-4 py-2 rounded-lg text-sm font-bold text-slate-500 hover:bg-red-50 hover:text-red-600 transition border border-transparent hover:border-red-200">
                                    <i class="fas fa-times mr-2"></i> Descartar
                                </button>
                                <button onclick="updateStatus(<?php echo $c['id']; ?>, 'contactado')"
                                        class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-slate-900 hover:bg-emerald-600 transition shadow-lg shadow-slate-300 hover:shadow-emerald-200">
                                    <i class="fas fa-check mr-2"></i> Contactar
                                </button>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>

    <script>
        async function updateStatus(id, nuevoEstado) {
            // Feedback Visual Inmediato (Optimista)
            const row = document.getElementById('row-' + id);
            const badge = document.getElementById('badge-' + id);

            // Animación de carga
            row.style.opacity = '0.5';

            try {
                const res = await fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, estado: nuevoEstado })
                });

                const data = await res.json();

                if (data.success) {
                    row.style.opacity = '1';

                    // Actualizar Badge Texto y Color
                    badge.textContent = nuevoEstado;
                    badge.className = "px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-all duration-300 transform scale-105";

                    if (nuevoEstado === 'contactado') {
                        badge.classList.add('bg-emerald-100', 'text-emerald-700');
                        row.classList.add('border-emerald-200', 'bg-emerald-50/30'); // Highlight row
                        row.classList.remove('opacity-50', 'grayscale');
                    } else if (nuevoEstado === 'descartado') {
                        badge.classList.add('bg-red-100', 'text-red-700');
                        row.classList.add('opacity-50', 'grayscale'); // Dim row
                        row.classList.remove('border-emerald-200', 'bg-emerald-50/30');
                    }

                } else {
                    alert('Error: ' + data.message);
                    row.style.opacity = '1';
                }

            } catch (e) {
                console.error(e);
                alert('Error de conexión');
                row.style.opacity = '1';
            }
        }
    </script>
</body>

</html>
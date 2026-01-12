<?php
session_start();
require_once 'db.php'; // Usamos la conexión PDO arreglada

// 1. LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// 2. CONSULTAR CANDIDATOS (CON JOIN PARA VER EL TÍTULO DEL ANUNCIO)
// Traemos los datos de la tabla 'postulaciones' y el título desde 'anuncios'
$sql = "SELECT p.*, a.titulo as titulo_anuncio 
        FROM postulaciones p 
        LEFT JOIN anuncios a ON p.anuncio_id = a.id 
        ORDER BY p.fecha_postulacion DESC";

try {
    $stmt = $pdo->query($sql);
    $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_db = "Error al leer candidatos: " . $e->getMessage();
}

$status_icon = isset($error_db) ? "🔴 Error" : "🟢 En Línea";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - SecureDocs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">

    <nav class="bg-slate-900 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2">
                    <i class="fas fa-shield-alt text-emerald-400 text-xl"></i>
                    <span class="font-bold text-xl tracking-tight">Admin Panel</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-xs bg-slate-800 px-3 py-1 rounded-full border border-slate-700 text-emerald-400">
                        <?php echo $status_icon; ?>
                    </span>
                    <a href="?logout=1" class="text-sm hover:text-red-300 transition"><i class="fas fa-sign-out-alt"></i> Salir</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Postulaciones Recibidas
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Mostrando <?php echo count($candidatos); ?> candidatos registrados en el sistema.
                </p>
            </div>
        </div>

        <?php if (isset($error_db)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow" role="alert">
                <p class="font-bold">Error de Consulta</p>
                <p><?php echo $error_db; ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
            <?php if (empty($candidatos)): ?>
                <div class="text-center py-16 px-4">
                    <div class="mx-auto h-12 w-12 text-gray-300">
                        <i class="fas fa-folder-open text-4xl"></i>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay postulaciones aún</h3>
                    <p class="mt-1 text-sm text-gray-500">Sube un archivo desde el formulario para verlo aquí.</p>
                </div>
            <?php else: ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($candidatos as $c): 
                        // Decodificar datos extra (JSON) si existen
                        $extra = json_decode($c['datos_extra'], true);
                        $titulo_doc = $extra['titulo_documento'] ?? 'Documento Sin Título';
                        $depto = $extra['departamento_interes'] ?? 'General';
                        
                        // Limpiar ruta para el link (asegurar que no tenga ./ al inicio si no es necesario)
                        $ruta_pdf = $c['ruta_archivo_pdf'];
                    ?>
                    <li class="hover:bg-gray-50 transition duration-150 ease-in-out">
                        <div class="px-4 py-4 sm:px-6 flex items-center justify-between flex-wrap gap-4">
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-sm font-medium text-indigo-600 truncate">
                                        <?php echo htmlspecialchars($titulo_doc); ?>
                                    </p>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <?php echo htmlspecialchars($c['estado']); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:gap-6">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="fas fa-building flex-shrink-0 mr-1.5 text-gray-400"></i>
                                        <?php echo htmlspecialchars($depto); ?>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-500 mt-1 sm:mt-0">
                                        <i class="fas fa-calendar-alt flex-shrink-0 mr-1.5 text-gray-400"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($c['fecha_postulacion'])); ?>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-500 mt-1 sm:mt-0">
                                        <i class="fas fa-tag flex-shrink-0 mr-1.5 text-gray-400"></i>
                                        Anuncio: <?php echo htmlspecialchars($c['titulo_anuncio'] ?? 'General'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-shrink-0">
                                <a href="<?php echo htmlspecialchars($ruta_pdf); ?>" target="_blank" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-file-pdf mr-2"></i> Ver PDF
                                </a>
                            </div>

                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
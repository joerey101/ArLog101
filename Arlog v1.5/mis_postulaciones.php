<?php
session_start();
// Si no está logueado, lo mandamos al login
if (!isset($_SESSION['candidato_id'])) { 
    header('Location: login.html'); 
    exit; 
}
require 'db.php';

// Consulta: Unimos la tabla postulaciones con anuncios para traer el nombre del puesto
$sql = "SELECT p.fecha_postulacion, a.titulo, a.departamento, a.ubicacion 
        FROM postulaciones p
        JOIN anuncios a ON p.anuncio_id = a.id
        WHERE p.candidato_id = ?
        ORDER BY p.fecha_postulacion DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['candidato_id']]);
$mis_postulaciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Postulaciones | Arlog Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="bg-white border-b border-slate-200 p-4">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <a href="index.html" class="font-bold text-slate-800 italic">Arlog Jobs</a>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-600">Hola, <?php echo $_SESSION['candidato_nombre']; ?></span>
                <a href="index.html" class="text-xs bg-slate-100 px-3 py-2 rounded-lg font-bold">Ver más empleos</a>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto py-12 px-6">
        <h1 class="text-3xl font-bold text-slate-900 mb-8 tracking-tight">Mi Historial de Postulaciones</h1>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Puesto</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Área / Ubicación</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fecha</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($mis_postulaciones)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">
                                <i class="fas fa-folder-open text-4xl mb-4 block opacity-20"></i>
                                Aún no te has postulado a ninguna búsqueda.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach($mis_postulaciones as $p): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-5">
                            <p class="font-bold text-slate-800 text-base"><?php echo htmlspecialchars($p['titulo']); ?></p>
                        </td>
                        <td class="px-6 py-5">
                            <p class="text-sm text-slate-600"><?php echo htmlspecialchars($p['departamento']); ?></p>
                            <p class="text-[10px] text-slate-400 font-medium"><?php echo htmlspecialchars($p['ubicacion']); ?></p>
                        </td>
                        <td class="px-6 py-5 text-sm text-slate-500">
                            <?php echo date('d/m/Y', strtotime($p['fecha_postulacion'])); ?>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="bg-emerald-50 text-emerald-600 text-[10px] font-extrabold px-3 py-1.5 rounded-full uppercase border border-emerald-100">
                                Enviada
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
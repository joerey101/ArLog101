<?php
require 'db.php';
$stmt = $pdo->query("SELECT * FROM anuncios ORDER BY fecha_creacion DESC");
$anuncios = $stmt->fetchAll();
// Traer etiquetas para el formulario
$etiquetasMaster = $pdo->query("SELECT * FROM etiquetas_maestras")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Reclutador | Arlog Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-100 min-h-screen flex">

    <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col p-6 sticky top-0 h-screen">
        <img src="arlogjobs_logo.png" class="h-8 mb-10 brightness-0 invert">
        <nav class="space-y-4">
            <a href="anuncios.php" class="flex items-center gap-3 bg-emerald-600 p-3 rounded-xl font-bold"><i class="fas fa-plus-circle"></i> Nueva Búsqueda</a>
            <a href="postulaciones.php" class="flex items-center gap-3 text-slate-400 hover:text-white p-3 transition"><i class="fas fa-users"></i> Candidatos</a>
        </nav>
    </aside>

    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold text-slate-800 italic">Consola de Reclutamiento</h1>
            <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
                <span class="h-2 w-2 bg-emerald-500 rounded-full animate-pulse"></span> Sistema v1.2 Online
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                    <h2 class="text-xl font-bold text-slate-800 mb-6">Publicar nueva oferta</h2>
                    
                    <form action="procesar_anuncio.php" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Título del Puesto</label>
                                <input type="text" name="titulo" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Ej: Operario de Autoelevador">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Departamento / Área</label>
                                <select name="departamento" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer">
                                    <option>Logística</option>
                                    <option>Operaciones</option>
                                    <option>Administración</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Descripción de la Búsqueda</label>
                            <textarea name="descripcion" rows="4" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Detalla los requisitos..."></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Ubicación</label>
                                <input type="text" name="ubicacion" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Ej: Buenos Aires, Argentina">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Tipo de Contrato</label>
                                <select name="tipo_contrato" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl">
                                    <option>Full-time</option>
                                    <option>Part-time</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-3 tracking-widest">Etiquetas de Identificación Rápida</label>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach($etiquetasMaster as $tag): ?>
                                    <label class="cursor-pointer group">
                                        <input type="checkbox" name="etiquetas[]" value="<?php echo $tag['id']; ?>" class="hidden peer">
                                        <span class="px-4 py-2 rounded-full border border-slate-200 text-xs font-bold text-slate-500 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 transition-all inline-block hover:border-emerald-300">
                                            <?php echo htmlspecialchars($tag['nombre']); ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-lg hover:bg-emerald-600 transition-all shadow-xl flex items-center justify-center gap-3">
                            <i class="fas fa-paper-plane"></i> Publicar en ArLogJobs
                        </button>
                    </form>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-4 uppercase text-xs tracking-widest">Búsquedas Recientes</h3>
                    <div class="space-y-4">
                        <?php foreach($anuncios as $a): ?>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div class="truncate mr-2">
                                <p class="text-sm font-bold text-slate-800 truncate"><?php echo htmlspecialchars($a['titulo']); ?></p>
                                <p class="text-[10px] text-slate-400 uppercase font-bold italic"><?php echo htmlspecialchars($a['departamento']); ?></p>
                            </div>
                            <span class="h-2 w-2 bg-emerald-400 rounded-full"></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
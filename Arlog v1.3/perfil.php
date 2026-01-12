<?php
session_start();
if (!isset($_SESSION['candidato_id'])) { header('Location: login.html'); exit; }
require 'db.php';

$stmt = $pdo->prepare("SELECT * FROM candidatos WHERE id = ?");
$stmt->execute([$_SESSION['candidato_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil | Arlog Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">
    <nav class="bg-white p-4 border-b border-slate-200">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <a href="index.html" class="font-bold text-slate-800">← Volver a Ofertas</a>
            <span class="text-sm">Perfil de <b><?php echo $user['nombre']; ?></b></span>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto mt-10 p-8 bg-white rounded-2xl shadow-sm border border-slate-200">
        <h2 class="text-2xl font-bold mb-6 text-slate-800">Mi Información</h2>
        
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase">Nombre Completo</label>
                <p class="text-lg text-slate-800"><?php echo $user['nombre']; ?></p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase">Correo Electrónico</label>
                <p class="text-lg text-slate-800"><?php echo $user['email']; ?></p>
            </div>
            <hr class="my-6">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Mi CV actual</label>
                <?php if($user['cv_url']): ?>
                    <div class="flex items-center gap-3 p-3 bg-emerald-50 border border-emerald-100 rounded-lg text-emerald-700">
                        <i class="fas fa-file-pdf"></i>
                        <span class="text-sm font-medium">CV_Cargado.pdf</span>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-slate-400 italic">Aún no has cargado un CV base.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
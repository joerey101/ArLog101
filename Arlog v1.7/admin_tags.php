<?php
session_start();
require 'db.php';

// Seguridad: Solo Admin (simplificado, idealmente checkear flag en DB)
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin') {
    // Si no hay login, mostrar form básico de login admin o redirect
    // Para simplificar MVP, asumimos que se logueó como admin@arlog.com
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $pass = $_POST['password'];
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND rol = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_rol'] = 'admin';
            $_SESSION['user_name'] = 'Administrador';
        } else {
            $error = "Acceso denegado";
        }
    }
}

if (!isset($_SESSION['user_id'])) {
    ?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Admin Login | ArLog</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>

    <body class="bg-slate-900 flex items-center justify-center h-screen">
        <form method="POST" class="bg-white p-8 rounded-xl w-96">
            <h2 class="text-xl font-bold mb-4">Acceso Admin</h2>
            <?php if (isset($error))
                echo "<p class='text-red-500 mb-4'>$error</p>"; ?>
            <input type="email" name="email" placeholder="admin@arlog.com" class="w-full mb-3 p-2 border rounded">
            <input type="password" name="password" placeholder="Pass" class="w-full mb-4 p-2 border rounded">
            <button class="w-full bg-emerald-600 text-white p-2 rounded">Ingresar</button>
        </form>
    </body>

    </html>
    <?php
    exit;
}

// Lógica de ABM Etiquetas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_tag') {
        $nombre = trim($_POST['nombre']);
        $tipo = $_POST['tipo'];
        try {
            $stmt = $pdo->prepare("INSERT INTO etiquetas (nombre, tipo, estado) VALUES (?, ?, 'aprobada')");
            $stmt->execute([$nombre, $tipo]);
            $msg = "Etiqueta creada: $nombre";
        } catch (Exception $e) {
            $err = "Error al crear: " . $e->getMessage();
        }
    }

    if ($_POST['action'] === 'delete_tag') {
        $id = $_POST['id'];
        $pdo->prepare("DELETE FROM etiquetas WHERE id = ?")->execute([$id]);
    }
}

$etiquetas = $pdo->query("SELECT * FROM etiquetas ORDER BY tipo, nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Admin | Gobernanza de Etiquetas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-slate-50 min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-slate-800">Gobernanza de Etiquetas</h1>
            <a href="auth.php?action=logout" class="text-red-500 font-bold text-sm">Cerrar Sesión</a>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Formulario de Creación -->
            <div class="bg-white p-6 rounded-2xl shadow-sm h-fit">
                <h2 class="text-lg font-bold mb-4">Nueva Etiqueta</h2>
                <?php if (isset($msg))
                    echo "<p class='text-emerald-500 text-sm mb-2'>$msg</p>"; ?>
                <?php if (isset($err))
                    echo "<p class='text-red-500 text-sm mb-2'>$err</p>"; ?>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="add_tag">
                    <div>
                        <label class="block text-xs uppercase font-bold text-slate-400 mb-1">Nombre</label>
                        <input type="text" name="nombre" required placeholder="Ej: Autoelevador"
                            class="w-full p-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs uppercase font-bold text-slate-400 mb-1">Tipo</label>
                        <select name="tipo" class="w-full p-2 border rounded-lg bg-white">
                            <option value="hard_skill">Hard Skill (Técnica)</option>
                            <option value="soft_skill">Soft Skill (Blanda)</option>
                            <option value="certificacion">Certificación / Carnet</option>
                            <option value="documentacion">Documentación Legal</option>
                            <option value="idioma">Idioma</option>
                        </select>
                    </div>
                    <button
                        class="w-full bg-slate-900 text-white py-2 rounded-lg font-bold hover:bg-slate-800">Agregar</button>
                </form>

                <div class="mt-8 pt-6 border-t">
                    <h3 class="font-bold text-sm mb-2">Importar Packs Rápidos</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_tag">
                        <!-- Simplificado, en prod usaríamos un script de seed más complejo -->
                        <div class="space-y-2">
                            <button type="button" onclick="autoFill('Licencia LINTI', 'documentacion')"
                                class="w-full text-left text-xs p-2 bg-slate-100 rounded hover:bg-slate-200">Licencia
                                LINTI (Doc)</button>
                            <button type="button" onclick="autoFill('Manejo de SAP', 'hard_skill')"
                                class="w-full text-left text-xs p-2 bg-slate-100 rounded hover:bg-slate-200">SAP
                                (Hard)</button>
                            <button type="button" onclick="autoFill('Inglés Avanzado', 'idioma')"
                                class="w-full text-left text-xs p-2 bg-slate-100 rounded hover:bg-slate-200">Inglés
                                (Idioma)</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Listado -->
            <div class="md:col-span-2 space-y-4">
                <div class="bg-white p-6 rounded-2xl shadow-sm">
                    <h2 class="text-lg font-bold mb-4">Diccionario de Etiquetas (
                        <?php echo count($etiquetas); ?>)
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50">
                                <tr>
                                    <th class="px-4 py-2">Nombre</th>
                                    <th class="px-4 py-2">Tipo</th>
                                    <th class="px-4 py-2">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($etiquetas as $tag): ?>
                                    <tr class="border-b">
                                        <td class="px-4 py-3 font-medium">
                                            <?php echo htmlspecialchars($tag['nombre']); ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                            <?php
                                            if ($tag['tipo'] == 'hard_skill')
                                                echo 'bg-blue-100 text-blue-700';
                                            if ($tag['tipo'] == 'soft_skill')
                                                echo 'bg-purple-100 text-purple-700';
                                            if ($tag['tipo'] == 'documentacion')
                                                echo 'bg-orange-100 text-orange-700';
                                            ?>">
                                                <?php echo $tag['tipo']; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <form method="POST" onsubmit="return confirm('¿Borrar etiqueta?');">
                                                <input type="hidden" name="action" value="delete_tag">
                                                <input type="hidden" name="id" value="<?php echo $tag['id']; ?>">
                                                <button class="text-red-400 hover:text-red-600"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function autoFill(name, type) {
            document.querySelector('input[name="nombre"]').value = name;
            document.querySelector('select[name="tipo"]').value = type;
        }
    </script>
</body>

</html>
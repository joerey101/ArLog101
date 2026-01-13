<?php
session_start();
require 'db.php';

// Verificación de sesión unificada
if (!isset($_SESSION['user_id'])) {
    header('Location: login_candidato.html');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// --- PROCESAR FORMULARIO POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Recoger datos POST
        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $cuit = $_POST['cuit'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $linkedin = $_POST['linkedin'] ?? '';
        
        $zona = $_POST['zona'] ?? '';
        $localidad = $_POST['localidad'] ?? ''; 
        // Ubicacion "legada" concatenada para compatibilidad
        $ubicacion_full = $localidad . ', ' . $zona;

        // Licencias (Array -> String CSV)
        $licencias = isset($_POST['licencias']) ? implode(',', $_POST['licencias']) : '';

        // 2. Manejo de Archivo (CV)
        $cv_path = null;
        if (isset($_FILES['cv_base']) && $_FILES['cv_base']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['cv_base'];
            if ($file['size'] > 5 * 1024 * 1024) throw new Exception("El archivo supera los 5MB");
            if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') throw new Exception("Solo PDF");
            
            $uploadDir = 'uploads/cvs_base/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $filename = "cv_" . $user_id . "_" . time() . ".pdf";
            $cv_path = $uploadDir . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $cv_path)) throw new Exception("Error guardando archivo");
        }

        // 3. Upsert en Base de Datos
        // Primero verificamos si existe registro
        $stmtCheck = $pdo->prepare("SELECT usuario_id, cv_url FROM perfiles_candidatos WHERE usuario_id = ?");
        $stmtCheck->execute([$user_id]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            // Update
            $final_cv = $cv_path ? $cv_path : $existing['cv_url'];
            $sql = "UPDATE perfiles_candidatos SET 
                    nombre=?, apellido=?, cuit=?, telefono=?, linkedin_url=?, 
                    zona=?, localidad=?, ubicacion=?, licencias=?, cv_url=? 
                    WHERE usuario_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $apellido, $cuit, $telefono, $linkedin, $zona, $localidad, $ubicacion_full, $licencias, $final_cv, $user_id]);
        } else {
            // Insert
            $final_cv = $cv_path ? $cv_path : '';
            $sql = "INSERT INTO perfiles_candidatos 
                    (usuario_id, nombre, apellido, cuit, telefono, linkedin_url, zona, localidad, ubicacion, licencias, cv_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $nombre, $apellido, $cuit, $telefono, $linkedin, $zona, $localidad, $ubicacion_full, $licencias, $final_cv]);
        }
        
        // Actualizar Info Sesión si cambió el nombre
        if($nombre) $_SESSION['user_name'] = $nombre;

        $message = "¡Perfil actualizado correctamente!";

    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// --- LEER DATOS ACTUALES ---
$stmt = $pdo->prepare("SELECT * FROM perfiles_candidatos WHERE usuario_id = ?");
$stmt->execute([$user_id]);
$perfil = $stmt->fetch() ?: []; // Array vacío si no existe

// Datos de Usuario base (email)
$stmtU = $pdo->prepare("SELECT email FROM usuarios WHERE id = ?");
$stmtU->execute([$user_id]);
$userBase = $stmtU->fetch();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Perfil Logístico | Arlog Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>

<body class="bg-slate-50 min-h-screen pb-20">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <a href="empleos.html" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 font-bold transition">
                <i class="fas fa-arrow-left"></i> Volver a Empleos
            </a>
            <div class="flex items-center gap-4">
                <span class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($userBase['email']); ?></span>
                <a href="auth.php?action=logout" class="text-xs text-red-500 font-bold uppercase hover:underline">Salir</a>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto mt-10 px-6">
        
        <header class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-slate-900">Mi Perfil Profesional</h1>
            <p class="text-slate-500">Completa tus datos para que las empresas te encuentren.</p>
        </header>

        <?php if ($message): ?>
            <div id="toast" class="fixed top-24 right-10 bg-emerald-600 text-white px-6 py-4 rounded-xl shadow-2xl animate-bounce z-50 flex items-center gap-4">
                <i class="fas fa-check-circle text-2xl"></i>
                <div>
                    <p class="font-bold">¡Guardado!</p>
                    <p class="text-xs text-emerald-100"><?php echo $message; ?></p>
                </div>
            </div>
            <script>setTimeout(() => document.getElementById('toast').remove(), 4000);</script>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
            
            <!-- COLUMNA IZQUIERDA: CV y FOTO -->
            <div class="md:col-span-1 space-y-6">
                <!-- Tarjeta CV -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-400 to-emerald-600"></div>
                    
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-file-pdf text-red-500"></i> Mi CV Base
                    </h3>

                    <?php if (!empty($perfil['cv_url'])): ?>
                        <div class="mb-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
                             <div class="flex items-center gap-3">
                                <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Cargado</p>
                                    <a href="<?php echo htmlspecialchars($perfil['cv_url']); ?>" target="_blank"
                                        class="text-xs font-bold text-emerald-600 hover:underline">Ver archivo actual</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 mb-6">
                            <i class="fas fa-cloud-upload-alt text-4xl text-slate-300 mb-2"></i>
                            <p class="text-xs text-slate-400 font-medium">Sube tu CV para postular rápido</p>
                        </div>
                    <?php endif; ?>

                    <label class="block w-full">
                        <span class="sr-only">Seleccionar CV</span>
                        <input type="file" name="cv_base" accept=".pdf" 
                            class="block w-full text-xs text-slate-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-xs file:font-bold
                            file:bg-slate-900 file:text-white
                            hover:file:bg-slate-700 cursor-pointer mb-2"/>
                    </label>
                    <p class="text-[10px] text-slate-400 text-center">Solo formato PDF (Máx 5MB)</p>
                </div>

                <!-- Licencias (Ahora en la sidebar para visibilidad) -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-id-card text-slate-400"></i> Licencias / Carnets
                    </h3>
                    <div class="space-y-3">
                        <?php 
                        $misLicencias = isset($perfil['licencias']) ? explode(',', $perfil['licencias']) : [];
                        $opcionesLic = [
                            'B1 - Auto/Utilitario', 
                            'C - Camión sin acoplado', 
                            'E1 - Camión con acoplado', 
                            'LINTI (Cargas Generales)',
                            'LINTI (Cargas Peligrosas)',
                            'Manejo de Clark / Autoelevador'
                        ];
                        ?>
                        <?php foreach($opcionesLic as $lic): ?>
                           <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="licencias[]" value="<?php echo $lic; ?>"
                                    <?php echo in_array($lic, $misLicencias) ? 'checked' : ''; ?>
                                    class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 border-gray-300">
                                <span class="text-xs font-medium text-slate-600 group-hover:text-slate-900 transition"><?php echo $lic; ?></span>
                           </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: Datos Personales (Principal) -->
            <div class="md:col-span-2 space-y-6">
                
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                    <h3 class="font-bold text-lg text-slate-800 mb-6 pb-4 border-b border-slate-100">Datos Personales</h3>
                    
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nombre *</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($perfil['nombre'] ?? ''); ?>" required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition font-medium text-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Apellido *</label>
                            <input type="text" name="apellido" value="<?php echo htmlspecialchars($perfil['apellido'] ?? ''); ?>" required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition font-medium text-slate-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">CUIT / CUIL</label>
                            <input type="text" name="cuit" value="<?php echo htmlspecialchars($perfil['cuit'] ?? ''); ?>" placeholder="Sin guiones"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition font-medium text-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Teléfono / Celular *</label>
                            <input type="tel" name="telefono" value="<?php echo htmlspecialchars($perfil['telefono'] ?? ''); ?>" required placeholder="Ej: 11 1234 5678"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition font-medium text-slate-800">
                        </div>
                    </div>

                    <div class="mb-6">
                         <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Link Perfil LinkedIn</label>
                         <div class="relative">
                            <i class="fab fa-linkedin absolute left-4 top-3.5 text-blue-600 text-lg"></i>
                            <input type="url" name="linkedin" value="<?php echo htmlspecialchars($perfil['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/in/tu-usuario"
                                class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition text-slate-600 text-sm">
                         </div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                    <h3 class="font-bold text-lg text-slate-800 mb-6 pb-4 border-b border-slate-100">Ubicación (Zona de Residencia)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Zona / Región *</label>
                            <select id="zonaSelect" name="zona" onchange="updateLocalidades()" required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer">
                                <option value="">Selecciona una zona...</option>
                                <option value="CABA">Capital Federal (CABA)</option>
                                <option value="GBA Norte">GBA Zona Norte</option>
                                <option value="GBA Sur">GBA Zona Sur</option>
                                <option value="GBA Oeste">GBA Zona Oeste</option>
                                <option value="Interior BsAs">Interior Buenos Aires</option>
                                <option value="Otra">Otra Provincia</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Localidad / Barrio *</label>
                            <select id="localidadSelect" name="localidad" required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer disabled:bg-slate-100 disabled:text-slate-400">
                                <option value="">Primero elige zona...</option>
                            </select>
                            <!-- Input fallback si es "Otra" o no está en lista -->
                             <input type="text" id="localidadInput" name="localidad_manual" class="hidden mt-2 w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" placeholder="Escribe tu localidad...">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-bold text-lg hover:bg-emerald-600 transition shadow-xl shadow-slate-300 transform hover:-translate-y-1">
                    Guardar Cambios
                </button>

            </div>
        </form>
    </div>

    <!-- DATA: Diccionario de Localidades -->
    <script>
        // Data almacenada en JS para velocidad
        const geoData = {
            "CABA": ["Agronomía", "Almagro", "Balvanera", "Barracas", "Belgrano", "Boedo", "Caballito", "Chacarita", "Coghlan", "Colegiales", "Constitución", "Flores", "Floresta", "La Boca", "Liniers", "Mataderos", "Monserrat", "Monte Castro", "Nueva Pompeya", "Núñez", "Palermo", "Parque Avellaneda", "Parque Chacabuco", "Parque Chas", "Parque Patricios", "Paternal", "Puerto Madero", "Recoleta", "Retiro", "Saavedra", "San Cristóbal", "San Nicolás", "San Telmo", "Vélez Sársfield", "Versalles", "Villa Crespo", "Villa del Parque", "Villa Devoto", "Villa General Mitre", "Villa Lugano", "Villa Luro", "Villa Ortúzar", "Villa Pueyrredón", "Villa Real", "Villa Riachuelo", "Villa Santa Rita", "Villa Soldati", "Villa Urquiza"],
            "GBA Norte": ["Vicente López", "Olivos", "Florida", "San Isidro", "Martínez", "Boulogne", "San Fernando", "Victoria", "Tigre", "Don Torcuato", "General Pacheco", "Benavídez", "Nordelta", "Escobar", "Garín", "Maschwitz", "Pilar", "Del Viso"],
            "GBA Sur": ["Avellaneda", "Dock Sud", "Sarandí", "Domínico", "Wilde", "Lanús", "Gerli", "Remedios de Escalada", "Lomas de Zamora", "Banfield", "Temperley", "Quilmes", "Bernal", "Ezpeleta", "Solano", "Berazategui", "Florencio Varela", "Almirante Brown", "Adrogué", "Burzaco", "Ezeiza", "Canning", "Esteban Echeverría", "Monte Grande"],
            "GBA Oeste": ["La Matanza", "San Justo", "Ramos Mejía", "Lomas del Mirador", "Morón", "Castelar", "Haedo", "El Palomar", "Hurlingham", "Ituzaingó", "Merlo", "Moreno", "General Rodríguez", "Tres de Febrero", "Caseros", "Ciudadela", "San Martín", "Tres de Febrero"],
            "Interior BsAs": ["La Plata", "Mar del Plata", "Bahía Blanca", "Zárate", "Campana", "San Nicolás", "Pergamino", "Junín", "Olavarría", "Tandil"],
            "Otra": [] // Free text
        };

        const currentZona = "<?php echo $perfil['zona'] ?? ''; ?>";
        const currentLoc = "<?php echo $perfil['localidad'] ?? ''; ?>";

        function updateLocalidades() {
            const zona = document.getElementById('zonaSelect').value;
            const locSelect = document.getElementById('localidadSelect');
            const locInput = document.getElementById('localidadInput');

            locSelect.innerHTML = '<option value="">Selecciona...</option>';

            if (zona === 'Otra') {
                locSelect.classList.add('hidden');
                locSelect.removeAttribute('name');
                locInput.classList.remove('hidden');
                locInput.setAttribute('name', 'localidad');
                locInput.required = true;
                return;
            } else {
                locSelect.classList.remove('hidden');
                locSelect.setAttribute('name', 'localidad');
                locInput.classList.add('hidden');
                locInput.removeAttribute('name');
                locInput.required = false;
            }

            if (geoData[zona]) {
                locSelect.disabled = false;
                geoData[zona].sort().forEach(loc => {
                    const opt = document.createElement('option');
                    opt.value = loc;
                    opt.textContent = loc;
                    locSelect.appendChild(opt);
                });
            } else {
                locSelect.disabled = true;
            }
        }

        // Init values
        window.onload = () => {
            if(currentZona) {
                document.getElementById('zonaSelect').value = currentZona;
                updateLocalidades();
                
                // Si es input manual
                if(currentZona === 'Otra') {
                    document.getElementById('localidadInput').value = currentLoc;
                } else {
                    document.getElementById('localidadSelect').value = currentLoc;
                }
            }
        };
    </script>

</body>
</html>
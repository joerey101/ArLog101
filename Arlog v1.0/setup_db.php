<?php
// Incluimos la conexión (Asegúrate de que db.php use $pdo y esté en la misma carpeta)
require 'db.php';

echo "<h1>🛠️ Iniciando instalación de la Plataforma de Talento...</h1>";

try {
    // 1. TABLA DE USUARIOS (Admin y Asociados)
    // Guardará quién puede publicar anuncios
    $sqlUsers = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        rol ENUM('admin', 'asociado') DEFAULT 'asociado',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sqlUsers);
    echo "✅ Tabla 'usuarios' verificada.<br>";

    // 2. TABLA DE ANUNCIOS (Ofertas Laborales)
    // 'requisitos_json' nos permite agregar campos nuevos mañana sin programar
    $sqlJobs = "CREATE TABLE IF NOT EXISTS anuncios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        titulo VARCHAR(150) NOT NULL,
        descripcion TEXT,
        departamento VARCHAR(50) DEFAULT 'General',
        estado ENUM('activo', 'pausado', 'cerrado') DEFAULT 'activo',
        requisitos_json JSON DEFAULT NULL, 
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )";
    $pdo->exec($sqlJobs);
    echo "✅ Tabla 'anuncios' verificada (con soporte flexible).<br>";

    // 3. TABLA DE POSTULACIONES (Candidatos + Archivos)
    // Aquí vinculamos el PDF con el Anuncio específico
    $sqlApps = "CREATE TABLE IF NOT EXISTS postulaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        anuncio_id INT NOT NULL,
        nombre_candidato VARCHAR(150),
        email_candidato VARCHAR(100),
        ruta_archivo_pdf VARCHAR(255) NOT NULL,
        datos_extra JSON DEFAULT NULL,
        analisis_ia JSON DEFAULT NULL,
        estado ENUM('nuevo', 'visto', 'contactado', 'descartado') DEFAULT 'nuevo',
        fecha_postulacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE
    )";
    $pdo->exec($sqlApps);
    echo "✅ Tabla 'postulaciones' verificada.<br>";

    // --- CREAR ADMIN POR DEFECTO ---
    // Si no hay usuarios, creamos el Admin inicial
    $checkAdmin = $pdo->query("SELECT count(*) FROM usuarios")->fetchColumn();
    if ($checkAdmin == 0) {
        // Contraseña temporal: admin123 (Cámbiala después)
        $passHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (email, password_hash, rol) VALUES (?, ?, 'admin')");
        $stmt->execute(['admin@admin.com', $passHash]);
        echo "👤 Usuario Admin creado: <b>admin@admin.com</b> / Clave: <b>admin123</b><br>";
    } else {
        echo "ℹ️ Ya existen usuarios registrados.<br>";
    }

    // --- ANUNCIO DE PRUEBA ---
    // Creamos una "Búsqueda General" para que el sistema no esté vacío
    $checkJobs = $pdo->query("SELECT count(*) FROM anuncios")->fetchColumn();
    if ($checkJobs == 0) {
        $adminId = $pdo->query("SELECT id FROM usuarios LIMIT 1")->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO anuncios (usuario_id, titulo, descripcion, departamento) VALUES (?, ?, ?, ?)");
        $stmt->execute([$adminId, 'Candidatura Espontánea', 'Envíanos tu CV para futuras búsquedas.', 'RRHH']);
        echo "📢 Anuncio de prueba 'Candidatura Espontánea' creado.<br>";
    }

    echo "<h3>🚀 ¡Instalación Completada! El sistema está listo.</h3>";
    echo "<p style='color:red'>IMPORTANTE: Por seguridad, borra este archivo (setup_db.php) de tu servidor ahora.</p>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
    echo "<br><br><b>Pista:</b> Si el error dice 'call to a member function exec() on null', revisa tu archivo db.php.";
}
?>
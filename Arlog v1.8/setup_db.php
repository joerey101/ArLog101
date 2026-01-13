<?php
require 'db.php';

echo "<h1>🛠️ Actualizando Arquitectura de Datos ArLog...</h1>";

try {
    $pdo->query("SET FOREIGN_KEY_CHECKS = 0");

    // 1. USUARIOS (Base central de autenticación)
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        rol ENUM('admin', 'empresa', 'candidato') NOT NULL,
        estado ENUM('activo', 'futa_validacion', 'suspendido') DEFAULT 'activo',
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Tabla 'usuarios' lista.<br>";

    // 2. PERFILES EMPRESAS
    // Información específica para reclutadores
    $pdo->exec("CREATE TABLE IF NOT EXISTS perfiles_empresas (
        usuario_id INT PRIMARY KEY,
        razon_social VARCHAR(150),
        cuit VARCHAR(20),
        industria VARCHAR(100),
        logo_url VARCHAR(255),
        sitio_web VARCHAR(255),
        descripcion TEXT,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");
    echo "✅ Tabla 'perfiles_empresas' lista.<br>";

    // 3. PERFILES CANDIDATOS
    // Información del postulante
    $pdo->exec("CREATE TABLE IF NOT EXISTS perfiles_candidatos (
        usuario_id INT PRIMARY KEY,
        nombre VARCHAR(100),
        apellido VARCHAR(100),
        telefono VARCHAR(50),
        ubicacion VARCHAR(100),
        cv_url VARCHAR(255),
        linkedin_url VARCHAR(255),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");
    echo "✅ Tabla 'perfiles_candidatos' lista.<br>";

    // 4. ETIQUETAS (Skills, Certificaciones, Documentación)
    $pdo->exec("CREATE TABLE IF NOT EXISTS etiquetas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL UNIQUE,
        tipo ENUM('hard_skill', 'soft_skill', 'idioma', 'certificacion', 'documentacion') DEFAULT 'hard_skill',
        estado ENUM('aprobada', 'sugerida') DEFAULT 'sugerida'
    )");
    echo "✅ Tabla 'etiquetas' lista.<br>";

    // 5. ANUNCIOS (Jobs)
    // Actualizamos la tabla existente o la creamos
    $pdo->exec("CREATE TABLE IF NOT EXISTS anuncios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        titulo VARCHAR(150) NOT NULL,
        descripcion TEXT,
        departamento VARCHAR(100),
        ubicacion VARCHAR(100),
        tipo_contrato VARCHAR(50),
        modalidad ENUM('remoto', 'hibrido', 'presencial') DEFAULT 'presencial',
        rango_salarial VARCHAR(100),
        estado ENUM('activo', 'pausado', 'cerrado', 'borrador') DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");
    echo "✅ Tabla 'anuncios' lista.<br>";

    // 6. ANUNCIO_ETIQUETAS (Pivote)
    $pdo->exec("CREATE TABLE IF NOT EXISTS anuncio_etiquetas (
        anuncio_id INT NOT NULL,
        etiqueta_id INT NOT NULL,
        nivel_requerido ENUM('basico', 'intermedio', 'avanzado', 'experto') DEFAULT 'intermedio',
        es_excluyente BOOLEAN DEFAULT FALSE,
        PRIMARY KEY (anuncio_id, etiqueta_id),
        FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE,
        FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
    )");
    echo "✅ Tabla 'anuncio_etiquetas' lista.<br>";

    // 7. PERFIL_ETIQUETAS (Skills del Candidato)
    $pdo->exec("CREATE TABLE IF NOT EXISTS perfil_etiquetas (
        usuario_id INT NOT NULL,
        etiqueta_id INT NOT NULL,
        nivel ENUM('basico', 'intermedio', 'avanzado', 'experto') DEFAULT 'intermedio',
        validado BOOLEAN DEFAULT FALSE,
        PRIMARY KEY (usuario_id, etiqueta_id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
    )");
    echo "✅ Tabla 'perfil_etiquetas' lista.<br>";

    // 8. POSTULACIONES
    $pdo->exec("CREATE TABLE IF NOT EXISTS postulaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        anuncio_id INT NOT NULL,
        usuario_id INT NOT NULL,
        estado ENUM('nuevo', 'visto', 'entrevista', 'finalista', 'descartado', 'contratado') DEFAULT 'nuevo',
        fecha_postulacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        mensaje_candidato TEXT,
        cv_snapshot_url VARCHAR(255), -- Por si actualiza su CV después, guardamos el de ese momento
        match_score INT DEFAULT 0, -- Para el futuro algoritmo de IA
        FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");
    echo "✅ Tabla 'postulaciones' lista.<br>";

    $pdo->query("SET FOREIGN_KEY_CHECKS = 1");

    // Crear Admin por defecto si no existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@arlog.com']);
    if ($stmt->fetchColumn() == 0) {
        $pass = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO usuarios (email, password_hash, rol) VALUES (?, ?, 'admin')")
            ->execute(['admin@arlog.com', $pass]);
        echo "👤 Admin creado: admin@arlog.com / admin123<br>";
    }

    echo "<h3>🚀 Base de Datos Actualizada Correctamente.</h3>";

} catch (PDOException $e) {
    echo "❌ Error Crítico: " . $e->getMessage();
}
?>
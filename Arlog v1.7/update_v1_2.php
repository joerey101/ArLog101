<?php
require 'db.php';

echo "<h1>🛠️ Actualizando Base de Datos a v1.2...</h1>";

try {
    // 1. Crear tabla de candidatos
    $sqlCandidatos = "CREATE TABLE IF NOT EXISTS candidatos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        telefono VARCHAR(50),
        cv_url VARCHAR(255),
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sqlCandidatos);
    echo "✅ Tabla 'candidatos' verificada/creada.<br>";

    // 2. Agregar columna candidato_id a postulaciones (Sintaxis compatible)
    // Primero revisamos si la columna ya existe para evitar el error
    $checkColumn = $pdo->query("SHOW COLUMNS FROM postulaciones LIKE 'candidato_id'")->fetch();
    
    if (!$checkColumn) {
        $pdo->exec("ALTER TABLE postulaciones ADD candidato_id INT NULL AFTER anuncio_id");
        echo "✅ Columna 'candidato_id' añadida a la tabla postulaciones.<br>";
    } else {
        echo "ℹ️ La columna 'candidato_id' ya existía.<br>";
    }

    echo "<h3>🚀 Estructura v1.2 completada con éxito.</h3>";
    echo "<p>Ya puedes borrar este archivo del servidor.</p>";

} catch (Exception $e) {
    echo "<h3 style='color:red'>❌ Error durante la actualización:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>
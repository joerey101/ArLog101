<?php
require 'db.php';
try {
    // Crear tabla de etiquetas maestras
    $pdo->exec("CREATE TABLE IF NOT EXISTS etiquetas_maestras (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL UNIQUE
    )");

    // Crear tabla intermedia (Relación muchos a muchos)
    $pdo->exec("CREATE TABLE IF NOT EXISTS anuncio_etiquetas (
        anuncio_id INT,
        etiqueta_id INT,
        PRIMARY KEY (anuncio_id, etiqueta_id)
    )");

    // Insertar etiquetas base
    $tags = ['Urgente', 'Larga Distancia', 'Zonal', 'Sin Experiencia', 'Manejo de Clark', 'Turno Noche'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO etiquetas_maestras (nombre) VALUES (?)");
    foreach ($tags as $t) { $stmt->execute([$t]); }

    echo "✅ Sistema de etiquetas inicializado correctamente.";
} catch (Exception $e) { echo "❌ Error: " . $e->getMessage(); }
?>
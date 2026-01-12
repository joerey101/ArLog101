<?php
require 'db.php';
try {
    // 1. Crear tabla de etiquetas maestras
    $pdo->exec("CREATE TABLE IF NOT EXISTS etiquetas_maestras (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL UNIQUE,
        categoria VARCHAR(50) -- Ej: 'Habilidad', 'Urgencia', 'Zona'
    )");

    // 2. Crear tabla intermedia para vincular anuncios con etiquetas
    $pdo->exec("CREATE TABLE IF NOT EXISTS anuncio_etiquetas (
        anuncio_id INT,
        etiqueta_id INT,
        PRIMARY KEY (anuncio_id, etiqueta_id)
    )");

    // 3. Insertar algunas "Etiquetas Madre" base
    $tags = ['Urgente', 'Larga Distancia', 'Zonal', 'Con Retiro', 'Sin Experiencia', 'Manejo de Clark'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO etiquetas_maestras (nombre, categoria) VALUES (?, 'General')");
    foreach ($tags as $t) { $stmt->execute([$t]); }

    echo "✅ Árbol de etiquetas inicializado.";
} catch (Exception $e) { echo "❌ Error: " . $e->getMessage(); }
?>
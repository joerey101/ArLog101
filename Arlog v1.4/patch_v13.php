<?php
require 'db.php';
try {
    // Agregamos la columna para vincular la postulación con el ID del usuario
    $pdo->exec("ALTER TABLE postulaciones ADD COLUMN candidato_id INT AFTER anuncio_id");
    echo "<h1>✅ Parche v1.3 Aplicado</h1>";
    echo "<p>La columna 'candidato_id' ha sido agregada con éxito a la tabla de postulaciones.</p>";
    echo "<a href='index.html'>Volver al inicio</a>";
} catch (Exception $e) {
    // Si ya existe, nos avisará
    echo "<h1>⚠️ Aviso</h1>";
    echo "<p>El parche ya estaba aplicado o hubo un detalle: " . $e->getMessage() . "</p>";
}
?>
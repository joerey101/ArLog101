<?php
require 'db.php';
echo "<h1>🛠️ Fix de Base de Datos</h1>";

try {
    // Intentamos ampliar el ENUM para aceptar los nuevos roles
    $sql = "ALTER TABLE usuarios MODIFY COLUMN rol ENUM('admin', 'empresa', 'candidato', 'asociado') DEFAULT 'candidato'";
    $pdo->exec($sql);
    echo "<p style='color:green'>✅ Columna ROL modificada correctamente.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Error al modificar tabla: " . $e->getMessage() . "</p>";
}
?>
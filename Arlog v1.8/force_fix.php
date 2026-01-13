<?php
require 'db.php';
echo "<h1>🛠️ Force Fix DB</h1>";

try {
    // CAMBIO RADICAL: Dejar de usar ENUM y usar VARCHAR para evitar problemas de compatibilidad
    $sql = "ALTER TABLE usuarios MODIFY COLUMN rol VARCHAR(50) NOT NULL DEFAULT 'candidato'";
    $pdo->exec($sql);
    echo "<p style='color:green'>✅ Columna ROL cambiada a VARCHAR(50). Restricciones eliminadas.</p>";

    // De paso, intentamos arreglar al usuario fly
    $stmt = $pdo->prepare("UPDATE usuarios SET rol = 'empresa' WHERE email = ?");
    $stmt->execute(['fly@fly.com']);
    echo "<p style='color:blue'>ℹ️ Intenté forzar rol 'empresa' a fly@fly.com</p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Error extremo: " . $e->getMessage() . "</p>";
}
?>
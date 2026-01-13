<?php
require 'db.php';

// Script para asegurar que las columnas del perfil existan
$columns = [
    'telefono' => 'VARCHAR(50)',
    'cuit' => 'VARCHAR(20)',
    'linkedin' => 'VARCHAR(255)',
    'ubicacion' => 'VARCHAR(100)'
];

$table = 'perfiles_candidatos';

echo "<html><body style='font-family:sans-serif; background:#0f172a; color:white; padding:40px;'>";
echo "<h2 style='color:#34d399'>Mantenimiento de Base de Datos</h2>";

foreach ($columns as $col => $type) {
    try {
        // Intentar agregar la columna
        $pdo->exec("ALTER TABLE $table ADD COLUMN $col $type NULL");
        echo "<p>✅ Columna <b>$col</b> agregada exitosamente.</p>";
    } catch (PDOException $e) {
        // Verificar si es error de "ya existe" (SQLSTATE[42S21])
        if (strpos($e->getMessage(), '42S21') !== false || strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p style='color:#94a3b8'>ℹ️ La columna <b>$col</b> ya existe (OK).</p>";
        } else {
            echo "<p style='color:#f87171'>⚠️ Error con $col: " . $e->getMessage() . "</p>";
        }
    }
}
echo "<hr style='border-color:#334155'>";
echo "<p>Base de datos actualizada.</p>";
echo "<a href='perfil.php' style='display:inline-block; margin-top:10px; padding:10px 20px; background:#10b981; color:white; text-decoration:none; border-radius:5px; font-weight:bold;'>Ir a Mi Perfil</a>";
echo "</body></html>";
?>
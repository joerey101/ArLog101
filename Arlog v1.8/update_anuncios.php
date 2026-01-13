<?php
require 'db.php';

echo "<h1>🛠️ Actualizando campos de Anuncios (Modo Compatible)</h1>";

try {
    // 1. Obtener las columnas actuales de la tabla 'anuncios'
    $query = $pdo->query("SHOW COLUMNS FROM anuncios");
    $columns = $query->fetchAll(PDO::FETCH_COLUMN);

    // 2. Función para agregar columna si no existe
    function agregarColumna($pdo, $columns, $nombreColumna, $definicion, $despuesDe) {
        if (!in_array($nombreColumna, $columns)) {
            $sql = "ALTER TABLE anuncios ADD $nombreColumna $definicion AFTER $despuesDe";
            $pdo->exec($sql);
            echo "✅ Columna <b>$nombreColumna</b> añadida correctamente.<br>";
        } else {
            echo "ℹ️ La columna <b>$nombreColumna</b> ya existe. No se realizaron cambios.<br>";
        }
    }

    // 3. Intentar agregar los nuevos campos
    agregarColumna($pdo, $columns, 'descripcion', 'TEXT', 'titulo');
    agregarColumna($pdo, $columns, 'ubicacion', 'VARCHAR(100)', 'departamento');
    agregarColumna($pdo, $columns, 'tipo_contrato', 'VARCHAR(50)', 'ubicacion');

    echo "<h3>🚀 Estructura de Anuncios v1.2 completada con éxito.</h3>";
    echo "<p><a href='index.html'>Ir al Portal</a> | <a href='anuncios.php'>Ir al Admin</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color:red'>❌ Error durante la actualización:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>
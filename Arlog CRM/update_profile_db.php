<?php
require 'db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $updates = [
        "ADD COLUMN cuit VARCHAR(20) AFTER apellido",
        "ADD COLUMN zona VARCHAR(50) AFTER ubicacion",
        "ADD COLUMN localidad VARCHAR(100) AFTER zona",
        "ADD COLUMN licencias VARCHAR(255) AFTER linkedin_url"
    ];

    foreach ($updates as $sql) {
        try {
            $pdo->exec("ALTER TABLE perfiles_candidatos $sql");
            echo "Columna agregada: $sql <br>";
        } catch (PDOException $e) {
            // Ignoramos error si la columna ya existe (para re-run safe)
            if (strpos($e->getMessage(), "Duplicate column") !== false) {
                echo "Columna ya existía: " . $e->getMessage() . "<br>";
            } else {
                echo "Nota: " . $e->getMessage() . "<br>";
            }
        }
    }
    echo "<h3>Migración de Perfil completada.</h3>";

} catch (PDOException $e) {
    echo "Error General: " . $e->getMessage();
}
?>
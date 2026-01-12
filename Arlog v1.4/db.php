<?php
/* ARCHIVO DE CONEXIÓN A LA BASE DE DATOS
   --------------------------------------
   SOLUCIÓN DREAMHOST:
   1. $servername NO puede ser "localhost". Debe ser tu host real, ej: mysql.midominio.com
   2. Pon tu contraseña real.
*/

// IMPORTANTE: Cambia esto por lo que dice tu panel de DreamHost bajo "MySQL Databases" -> "Hostname"
// Probablemente sea: mysql.arlogjobs.joserey101.com
$servername = "mysql.arlogjobs.joserey101.com"; 

$username   = "user_arlog";  // El usuario que creaste en el panel
$password   = "Miami128!Roxette"; // La contraseña de ese usuario
$dbname     = "base_arlog";           // El nombre de tu base de datos

try {
    // Conexión usando el Hostname real
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configuración de errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Zona horaria Argentina
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $pdo->exec("SET time_zone = '-03:00';");

} catch (PDOException $e) {
    // Si falla, detener todo y mostrar el error claro
    die("❌ Error de Conexión: " . $e->getMessage());
}

// Compatibilidad
$conn = $pdo; 
?>
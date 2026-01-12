<?php
require 'db.php';

echo "<h1>Debug de Anuncios</h1>";

// 1. Ver usuarios
echo "<h2>Usuarios</h2>";
$users = $pdo->query("SELECT id, email, rol, fecha_registro FROM usuarios ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($users, true) . "</pre>";

// 2. Ver Anuncios
echo "<h2>Últimos 5 Anuncios</h2>";
$jobs = $pdo->query("SELECT id, usuario_id, titulo, estado, fecha_creacion FROM anuncios ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($jobs, true) . "</pre>";
?>
<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    if (empty($nombre) || empty($email) || empty($pass)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos']); exit;
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO candidatos (nombre, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $email, $hash]);
        $_SESSION['candidato_id'] = $pdo->lastInsertId();
        $_SESSION['candidato_nombre'] = $nombre;
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'El email ya existe']);
    }
}

if ($action === 'login') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $stmt = $pdo->prepare("SELECT id, nombre, password_hash FROM candidatos WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password_hash'])) {
        $_SESSION['candidato_id'] = $user['id'];
        $_SESSION['candidato_nombre'] = $user['nombre'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos incorrectos']);
    }
}

if ($action === 'check') {
    echo json_encode(['logged' => isset($_SESSION['candidato_id']), 'nombre' => $_SESSION['candidato_nombre'] ?? '']);
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
}
<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// REGISTRO UNIFICADO
if ($action === 'register') {
    // Recibimos datos comunes
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'candidato'; // 'candidato' o 'empresa'

    // Datos específicos
    $nombre = trim($_POST['nombre'] ?? '');
    // Para empresa podrían venir más datos, simplificamos MVP

    if (empty($email) || empty($password) || empty($nombre)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        exit;
    }

    if (!in_array($rol, ['candidato', 'empresa'])) {
        echo json_encode(['success' => false, 'message' => 'Rol inválido.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Crear Usuario
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (email, password_hash, rol) VALUES (?, ?, ?)");
        $stmt->execute([$email, $hash, $rol]);
        $user_id = $pdo->lastInsertId();

        // 2. Crear Perfil
        if ($rol === 'candidato') {
            $stmt = $pdo->prepare("INSERT INTO perfiles_candidatos (usuario_id, nombre) VALUES (?, ?)");
            $stmt->execute([$user_id, $nombre]);
        } else {
            // Empresa: usamos 'nombre' como Razón Social temporalmente
            $stmt = $pdo->prepare("INSERT INTO perfiles_empresas (usuario_id, razon_social) VALUES (?, ?)");
            $stmt->execute([$user_id, $nombre]);
        }

        $pdo->commit();

        // Auto-login
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_rol'] = $rol;
        $_SESSION['user_name'] = $nombre;

        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'El email ya está registrado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
        }
    }
}

// LOGIN
if ($action === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, password_hash, rol, email FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_rol'] = $user['rol'];

        // Obtener nombre según rol
        $nombre = "Usuario";
        if ($user['rol'] === 'candidato') {
            $stmtProfile = $pdo->prepare("SELECT nombre FROM perfiles_candidatos WHERE usuario_id = ?");
            $stmtProfile->execute([$user['id']]);
            $profile = $stmtProfile->fetch();
            $nombre = $profile['nombre'] ?? 'Candidato';
        } elseif ($user['rol'] === 'empresa') {
            $stmtProfile = $pdo->prepare("SELECT razon_social FROM perfiles_empresas WHERE usuario_id = ?");
            $stmtProfile->execute([$user['id']]);
            $profile = $stmtProfile->fetch();
            $nombre = $profile['razon_social'] ?? 'Empresa';
        }

        if ($nombre === "Usuario") {
            // Fallback: usar parte del email si no tiene perfil
            $nombre = explode('@', $user['email'])[0];
        }
        $_SESSION['user_name'] = $nombre;

        echo json_encode(['success' => true, 'rol' => $user['rol']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Credenciales inválidas.']);
    }
}

// CHECK SESSION
if ($action === 'check') {
    $has_cv = false;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT cv_url FROM perfiles_candidatos WHERE usuario_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cv = $stmt->fetchColumn();
        if (!empty($cv)) {
            $has_cv = true;
        }
    }

    echo json_encode([
        'logged' => isset($_SESSION['user_id']),
        'id' => $_SESSION['user_id'] ?? null,
        'nombre' => $_SESSION['user_name'] ?? '',
        'rol' => $_SESSION['user_rol'] ?? '',
        'has_cv' => $has_cv
    ]);
}

// LOGOUT
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
}
?>
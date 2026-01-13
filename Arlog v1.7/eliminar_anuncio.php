<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'empresa') {
    die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $anuncio_id = $_POST['id'];
    $usuario_id = $_SESSION['user_id'];

    try {
        // Verificar propiedad
        $stmt = $pdo->prepare("SELECT id FROM anuncios WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$anuncio_id, $usuario_id]);

        if ($stmt->fetch()) {
            // Eliminar (Asumiendo ON DELETE CASCADE para tags y postulaciones, sino habría que borrar manual)
            // Borrado manual de dependencias por seguridad si no está configurado el CASCADE
            $pdo->beginTransaction();

            $pdo->prepare("DELETE FROM anuncio_etiquetas WHERE anuncio_id = ?")->execute([$anuncio_id]);
            $pdo->prepare("DELETE FROM postulaciones WHERE anuncio_id = ?")->execute([$anuncio_id]);
            $pdo->prepare("DELETE FROM anuncios WHERE id = ?")->execute([$anuncio_id]);

            $pdo->commit();
            header("Location: dashboard_empresa.php?msg=deleted");
            exit;
        } else {
            die("Error: Anuncio no encontrado o no tienes permiso.");
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
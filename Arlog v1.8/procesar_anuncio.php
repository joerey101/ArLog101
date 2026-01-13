<?php
session_start();
require 'db.php';

// Verificación de seguridad básica
if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol'] !== 'empresa' && $_SESSION['user_rol'] !== 'admin')) {
    die("Acceso denegado: No tienes permisos para publicar.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['user_id'];

    // Datos del formulario
    $anuncio_id = $_POST['anuncio_id'] ?? null; // ID para Edición, si existe
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $departamento = $_POST['departamento'];
    $ubicacion = $_POST['ubicacion'];
    $tipo_contrato = $_POST['tipo_contrato'] ?? 'full_time'; // Default
    $etiquetas_seleccionadas = $_POST['etiquetas'] ?? [];

    try {
        $pdo->beginTransaction();

        if ($anuncio_id) {
            // === MODO EDICIÓN ===

            // 1. Verificar propiedad
            $stmtCheck = $pdo->prepare("SELECT id FROM anuncios WHERE id = ? AND usuario_id = ?");
            $stmtCheck->execute([$anuncio_id, $usuario_id]);
            if (!$stmtCheck->fetch()) {
                throw new Exception("No tienes permiso para editar este anuncio.");
            }

            // 2. Actualizar Anuncio
            $sql = "UPDATE anuncios SET titulo = ?, descripcion = ?, departamento = ?, ubicacion = ?, tipo_contrato = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $descripcion, $departamento, $ubicacion, $tipo_contrato, $anuncio_id]);

            // 3. Limpiar etiquetas viejas
            $pdo->prepare("DELETE FROM anuncio_etiquetas WHERE anuncio_id = ?")->execute([$anuncio_id]);

        } else {
            // === MODO CREACIÓN ===

            $sql = "INSERT INTO anuncios (usuario_id, titulo, descripcion, departamento, ubicacion, tipo_contrato, estado) 
                    VALUES (?, ?, ?, ?, ?, ?, 'activo')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $titulo, $descripcion, $departamento, $ubicacion, $tipo_contrato]);

            $anuncio_id = $pdo->lastInsertId();
        }

        // === PROCESAR ETIQUETAS (Común para ambos) ===
        if (!empty($etiquetas_seleccionadas)) {
            // IMPORTANTE: anuncio_etiquetas ahora tiene campos extra (nivel, excluyente), usaremos defaults por ahora
            $sql_tags = "INSERT INTO anuncio_etiquetas (anuncio_id, etiqueta_id, nivel_requerido, es_excluyente) VALUES (?, ?, 'intermedio', 0)";
            $stmt_tags = $pdo->prepare($sql_tags);
            foreach ($etiquetas_seleccionadas as $tag_id) {
                // Aseguramos que sea entero
                $tag_id = filter_var($tag_id, FILTER_SANITIZE_NUMBER_INT);
                if ($tag_id) {
                    $stmt_tags->execute([$anuncio_id, $tag_id]);
                }
            }
        }

        $pdo->commit();

        // Redirigir con feedback
        $msg = $anuncio_id ? "updated" : "created";
        header("Location: dashboard_empresa.php?status=$msg");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        die("Error al procesar el anuncio: " . $e->getMessage());
    }
}
?>
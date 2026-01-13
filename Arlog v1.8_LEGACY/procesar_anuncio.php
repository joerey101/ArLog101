<?php
session_start();
require 'db.php';

// Verificación de seguridad básica
if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol'] !== 'empresa' && $_SESSION['user_rol'] !== 'admin')) {
    die("Acceso denegado: No tienes permisos para publicar.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Identificación del Reclutador (Usamos la sesión real)
    $usuario_id = $_SESSION['user_id'];

    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $departamento = $_POST['departamento'];
    $ubicacion = $_POST['ubicacion'];
    $tipo_contrato = $_POST['tipo_contrato'];

    // Capturamos el array de etiquetas seleccionadas
    $etiquetas_seleccionadas = $_POST['etiquetas'] ?? [];

    try {
        $pdo->beginTransaction();

        // 2. Insertar el Anuncio Principal
        $sql = "INSERT INTO anuncios (usuario_id, titulo, descripcion, departamento, ubicacion, tipo_contrato, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'activo')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $titulo, $descripcion, $departamento, $ubicacion, $tipo_contrato]);

        $anuncio_id = $pdo->lastInsertId();

        // 3. Vincular con las Etiquetas
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

        // 4. Redirigir al Nuevo Dashboard
        header("Location: dashboard_empresa.php?status=success");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al procesar el anuncio: " . $e->getMessage());
    }
}
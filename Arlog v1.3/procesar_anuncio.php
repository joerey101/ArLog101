<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Identificación del Reclutador
    $usuario_id = $_SESSION['admin_id'] ?? 1; 

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

        // 3. Vincular con las Etiquetas Madre
        if (!empty($etiquetas_seleccionadas)) {
            $sql_tags = "INSERT INTO anuncio_etiquetas (anuncio_id, etiqueta_id) VALUES (?, ?)";
            $stmt_tags = $pdo->prepare($sql_tags);
            foreach ($etiquetas_seleccionadas as $tag_id) {
                $stmt_tags->execute([$anuncio_id, $tag_id]);
            }
        }

        $pdo->commit();
        // Redirigir con éxito
        header("Location: anuncios.php?status=success");
        
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al procesar el anuncio: " . $e->getMessage());
    }
}
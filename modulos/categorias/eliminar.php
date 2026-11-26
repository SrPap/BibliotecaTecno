<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

solo_post();
validar_csrf();

$id = (int) ($_POST['id'] ?? 0);

$stmt = $conexion->prepare("SELECT nombre FROM categoria WHERE id_categoria = ?");
$stmt->execute([$id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    flash('danger', 'La categoría no existe.');
    redireccionar('index.php');
}

// No se puede eliminar si está en uso
$stmt = $conexion->prepare("SELECT COUNT(*) FROM libro WHERE id_categoria = ?");
$stmt->execute([$id]);
$libros = (int) $stmt->fetchColumn();

$stmt = $conexion->prepare("SELECT COUNT(*) FROM recurso_digital WHERE id_categoria = ?");
$stmt->execute([$id]);
$recursos = (int) $stmt->fetchColumn();

if ($libros > 0 || $recursos > 0) {

    $partes = [];
    if ($libros > 0) {
        $partes[] = $libros . ($libros === 1 ? ' libro' : ' libros');
    }
    if ($recursos > 0) {
        $partes[] = $recursos . ($recursos === 1 ? ' recurso digital' : ' recursos digitales');
    }

    flash(
        'warning',
        'No se puede eliminar "' . $categoria['nombre'] . '" porque está asociada a '
        . implode(' y ', $partes) . '. Puedes desactivarla desde Editar.'
    );
    redireccionar('index.php');
}

try {
    $stmt = $conexion->prepare("DELETE FROM categoria WHERE id_categoria = ?");
    $stmt->execute([$id]);
    flash('success', 'Categoría eliminada correctamente.');
} catch (PDOException $e) {
    error_log('Error al eliminar categoría: ' . $e->getMessage());
    flash('danger', 'No se pudo eliminar la categoría.');
}

redireccionar('index.php');

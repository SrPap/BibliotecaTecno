<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

solo_post();
validar_csrf();

$id = (int) ($_POST['id'] ?? 0);

$stmt = $conexion->prepare("SELECT CONCAT(nombres, ' ', apellidos) AS nombre FROM autor WHERE id_autor = ?");
$stmt->execute([$id]);
$registro = $stmt->fetch();

if (!$registro) {
    flash('danger', 'El registro no existe.');
    redireccionar('index.php');
}

// No se puede eliminar si está en uso
$stmt = $conexion->prepare("SELECT COUNT(*) FROM libro_autor WHERE id_autor = ?");
$stmt->execute([$id]);
$enUso = (int) $stmt->fetchColumn();

if ($enUso > 0) {
    flash(
        'warning',
        'No se puede eliminar "' . $registro['nombre'] . '" porque está asociado a '
        . $enUso . ($enUso === 1 ? ' libro.' : ' libros.')
    );
    redireccionar('index.php');
}

try {
    $stmt = $conexion->prepare("DELETE FROM autor WHERE id_autor = ?");
    $stmt->execute([$id]);
    flash('success', 'Autor eliminado correctamente.');
} catch (PDOException $e) {
    error_log('Error al eliminar autor: ' . $e->getMessage());
    flash('danger', 'No se pudo eliminar el registro.');
}

redireccionar('index.php');

<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

solo_post();
validar_csrf();

$id = (int) ($_POST['id'] ?? 0);

$stmt = $conexion->prepare("SELECT titulo FROM libro WHERE id_libro = ?");
$stmt->execute([$id]);
$libro = $stmt->fetch();

if (!$libro) {
    flash('danger', 'El libro no existe.');
    redireccionar('index.php');
}

// No se elimina un libro que ya tiene ejemplares o pedidos registrados
$stmt = $conexion->prepare("SELECT COUNT(*) FROM ejemplar WHERE id_libro = ?");
$stmt->execute([$id]);
$ejemplares = (int) $stmt->fetchColumn();

$stmt = $conexion->prepare("SELECT COUNT(*) FROM pedido WHERE id_libro = ?");
$stmt->execute([$id]);
$pedidos = (int) $stmt->fetchColumn();

if ($ejemplares > 0 || $pedidos > 0) {

    $motivos = [];
    if ($ejemplares > 0) {
        $motivos[] = $ejemplares . ($ejemplares === 1 ? ' ejemplar' : ' ejemplares');
    }
    if ($pedidos > 0) {
        $motivos[] = $pedidos . ($pedidos === 1 ? ' pedido' : ' pedidos');
    }

    flash(
        'warning',
        'No se puede eliminar "' . $libro['titulo'] . '" porque tiene ' . implode(' y ', $motivos)
        . ' registrados. Elimina primero los ejemplares sin préstamos.'
    );
    redireccionar('index.php');
}

try {
    // libro_autor se elimina en cascada
    $stmt = $conexion->prepare("DELETE FROM libro WHERE id_libro = ?");
    $stmt->execute([$id]);
    flash('success', 'Libro eliminado correctamente.');
} catch (PDOException $e) {
    error_log('Error al eliminar libro: ' . $e->getMessage());
    flash('danger', 'No se pudo eliminar el libro.');
}

redireccionar('index.php');

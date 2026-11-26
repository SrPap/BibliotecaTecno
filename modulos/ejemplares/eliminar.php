<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

solo_post();
validar_csrf();

$id = (int) ($_POST['id'] ?? 0);

$stmt = $conexion->prepare("SELECT codigo_ejemplar, estado FROM ejemplar WHERE id_ejemplar = ?");
$stmt->execute([$id]);
$ejemplar = $stmt->fetch();

if (!$ejemplar) {
    flash('danger', 'El ejemplar no existe.');
    redireccionar('index.php');
}

// No se elimina un ejemplar con historial de préstamos ni uno en circulación
$stmt = $conexion->prepare("SELECT COUNT(*) FROM prestamo WHERE id_ejemplar = ?");
$stmt->execute([$id]);
$prestamos = (int) $stmt->fetchColumn();

if (in_array($ejemplar['estado'], ['SOLICITADO', 'PRESTADO'], true) || $prestamos > 0) {
    flash(
        'warning',
        'No se puede eliminar el ejemplar "' . $ejemplar['codigo_ejemplar']
        . '" porque está en circulación o tiene préstamos registrados. '
        . 'Puedes marcarlo como "Perdido" o "Mantenimiento" desde Editar.'
    );
    redireccionar('index.php');
}

try {
    $stmt = $conexion->prepare("DELETE FROM ejemplar WHERE id_ejemplar = ?");
    $stmt->execute([$id]);
    flash('success', 'Ejemplar eliminado correctamente.');
} catch (PDOException $e) {
    error_log('Error al eliminar ejemplar: ' . $e->getMessage());
    flash('danger', 'No se pudo eliminar el ejemplar.');
}

redireccionar('index.php');

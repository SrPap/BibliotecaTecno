<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

$modo = 'editar';
$idEditar = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $conexion->prepare(
    "SELECT codigo_ejemplar, id_libro, estado, ubicacion FROM ejemplar WHERE id_ejemplar = ?"
);
$stmt->execute([$idEditar]);
$datos = $stmt->fetch();

if (!$datos) {
    flash('danger', 'El ejemplar que intentas editar no existe.');
    redireccionar('index.php');
}

require __DIR__ . '/_formulario.php';

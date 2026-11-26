<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

$modo = 'editar';
$idEditar = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $conexion->prepare("SELECT nombres, apellidos FROM autor WHERE id_autor = ?");
$stmt->execute([$idEditar]);
$datos = $stmt->fetch();

if (!$datos) {
    flash('danger', 'El registro que intentas editar no existe.');
    redireccionar('index.php');
}

require __DIR__ . '/_formulario.php';

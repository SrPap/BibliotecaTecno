<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

$modo = 'editar';
$idEditar = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $conexion->prepare(
    "SELECT codigo_libro, titulo, isbn, anio_publicacion, id_categoria, id_editorial
     FROM libro
     WHERE id_libro = ?"
);
$stmt->execute([$idEditar]);
$datos = $stmt->fetch();

if (!$datos) {
    flash('danger', 'El libro que intentas editar no existe.');
    redireccionar('index.php');
}

$stmt = $conexion->prepare("SELECT id_autor FROM libro_autor WHERE id_libro = ?");
$stmt->execute([$idEditar]);
$autoresSeleccionados = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

require __DIR__ . '/_formulario.php';

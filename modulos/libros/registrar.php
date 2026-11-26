<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

$modo = 'registrar';
$idEditar = null;
$datos = [
    'codigo_libro' => '',
    'titulo' => '',
    'isbn' => '',
    'anio_publicacion' => '',
    'id_categoria' => '',
    'id_editorial' => ''
];
$autoresSeleccionados = [];

require __DIR__ . '/_formulario.php';

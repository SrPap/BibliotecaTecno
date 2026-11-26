<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

$modo = 'registrar';
$idEditar = null;
$datos = ['nombre' => '', 'descripcion' => '', 'activo' => 1];

require __DIR__ . '/_formulario.php';

<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

$modo = 'registrar';
$idEditar = null;
$datos = ['nombres' => '', 'apellidos' => ''];

require __DIR__ . '/_formulario.php';

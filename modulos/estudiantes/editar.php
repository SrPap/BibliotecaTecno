<?php
require_once __DIR__ . '/../../includes/verificar_sesion.php';
require_once __DIR__ . '/../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id_estudiante'])) {
    $sql = "UPDATE estudiante
            SET codigo_estudiante = :codigo, nombres = :nombres, apellidos = :apellidos,
                dni = :dni, correo = :correo, carrera = :carrera, activo = :activo
            WHERE id_estudiante = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':codigo'    => trim($_POST['codigo_estudiante'] ?? ''),
        ':nombres'   => trim($_POST['nombres'] ?? ''),
        ':apellidos' => trim($_POST['apellidos'] ?? ''),
        ':dni'       => trim($_POST['dni'] ?? ''),
        ':correo'    => trim($_POST['correo'] ?? ''),
        ':carrera'   => trim($_POST['carrera'] ?? ''),
        ':activo'    => (int)($_POST['activo'] ?? 1),
        ':id'        => (int)$_POST['id_estudiante']
    ]);
}
header('Location: index.php');
exit;

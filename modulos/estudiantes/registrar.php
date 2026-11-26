<?php
require_once __DIR__ . '/../../includes/verificar_sesion.php';
require_once __DIR__ . '/../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO estudiante (codigo_estudiante, nombres, apellidos, dni, correo, carrera, activo)
            VALUES (:codigo, :nombres, :apellidos, :dni, :correo, :carrera, :activo)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':codigo'    => trim($_POST['codigo_estudiante'] ?? ''),
        ':nombres'   => trim($_POST['nombres'] ?? ''),
        ':apellidos' => trim($_POST['apellidos'] ?? ''),
        ':dni'       => trim($_POST['dni'] ?? ''),
        ':correo'    => trim($_POST['correo'] ?? ''),
        ':carrera'   => trim($_POST['carrera'] ?? ''),
        ':activo'    => (int)($_POST['activo'] ?? 1)
    ]);
}
header('Location: index.php');
exit;

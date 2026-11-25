<?php
require_once __DIR__ . '/../../includes/verificar_sesion.php';
require_once __DIR__ . '/../../config/conexion.php';

if (!empty($_GET['id'])) {
    $stmt = $conexion->prepare("DELETE FROM estudiante WHERE id_estudiante = :id");
    $stmt->execute([':id' => (int)$_GET['id']]);
}
header('Location: index.php');
exit;

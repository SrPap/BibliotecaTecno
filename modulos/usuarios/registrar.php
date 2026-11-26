<?php
require_once __DIR__ . '/../../includes/verificar_sesion.php';
require_once __DIR__ . '/../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
$password       = $_POST['password'] ?? '';
$id_rol         = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);
$id_estudiante  = !empty($_POST['id_estudiante']) ? filter_var($_POST['id_estudiante'], FILTER_VALIDATE_INT) : null;
$activo         = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;
$activo         = ($activo === 0) ? 0 : 1;

if ($nombre_usuario === '' || $password === '' || !$id_rol) {
    header('Location: index.php?error=' . urlencode('Completa usuario, contraseña y rol.'));
    exit;
}

try {
    // Verificar que no exista el nombre de usuario
    $stmtCheck = $conexion->prepare("SELECT id_usuario FROM usuario WHERE nombre_usuario = :usuario LIMIT 1");
    $stmtCheck->execute([':usuario' => $nombre_usuario]);
    if ($stmtCheck->fetch()) {
        header('Location: index.php?error=' . urlencode('Ese nombre de usuario ya existe.'));
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuario (nombre_usuario, password_hash, id_rol, id_estudiante, activo)
            VALUES (:nombre_usuario, :password_hash, :id_rol, :id_estudiante, :activo)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':nombre_usuario' => $nombre_usuario,
        ':password_hash'  => $password_hash,
        ':id_rol'         => $id_rol,
        ':id_estudiante'  => $id_estudiante,
        ':activo'         => $activo,
    ]);

    header('Location: index.php?ok=' . urlencode('Usuario registrado correctamente.'));
    exit;
} catch (PDOException $e) {
    error_log("Error al registrar usuario: " . $e->getMessage());
    header('Location: index.php?error=' . urlencode('No se pudo registrar el usuario.'));
    exit;
}

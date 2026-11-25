<?php
require_once __DIR__ . '/../../includes/verificar_sesion.php';
require_once __DIR__ . '/../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id_usuario     = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
$nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
$password       = $_POST['password'] ?? '';
$id_rol         = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);
$id_estudiante  = !empty($_POST['id_estudiante']) ? filter_var($_POST['id_estudiante'], FILTER_VALIDATE_INT) : null;
$activo         = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;
$activo         = ($activo === 0) ? 0 : 1;

if (!$id_usuario || $nombre_usuario === '' || !$id_rol) {
    header('Location: index.php?error=' . urlencode('Datos incompletos para actualizar el usuario.'));
    exit;
}

try {
    // Evitar duplicar nombre_usuario con otro ID
    $stmtCheck = $conexion->prepare("SELECT id_usuario FROM usuario WHERE nombre_usuario = :usuario AND id_usuario <> :id LIMIT 1");
    $stmtCheck->execute([
        ':usuario' => $nombre_usuario,
        ':id'      => $id_usuario,
    ]);
    if ($stmtCheck->fetch()) {
        header('Location: index.php?error=' . urlencode('Otro usuario ya usa ese nombre de usuario.'));
        exit;
    }

    if ($password !== '') {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE usuario
                SET nombre_usuario = :nombre_usuario,
                    password_hash = :password_hash,
                    id_rol = :id_rol,
                    id_estudiante = :id_estudiante,
                    activo = :activo
                WHERE id_usuario = :id_usuario";
        $params = [
            ':nombre_usuario' => $nombre_usuario,
            ':password_hash'  => $password_hash,
            ':id_rol'         => $id_rol,
            ':id_estudiante'  => $id_estudiante,
            ':activo'         => $activo,
            ':id_usuario'     => $id_usuario,
        ];
    } else {
        $sql = "UPDATE usuario
                SET nombre_usuario = :nombre_usuario,
                    id_rol = :id_rol,
                    id_estudiante = :id_estudiante,
                    activo = :activo
                WHERE id_usuario = :id_usuario";
        $params = [
            ':nombre_usuario' => $nombre_usuario,
            ':id_rol'         => $id_rol,
            ':id_estudiante'  => $id_estudiante,
            ':activo'         => $activo,
            ':id_usuario'     => $id_usuario,
        ];
    }

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);

    header('Location: index.php?ok=' . urlencode('Usuario actualizado correctamente.'));
    exit;
} catch (PDOException $e) {
    error_log("Error al editar usuario: " . $e->getMessage());
    header('Location: index.php?error=' . urlencode('No se pudo actualizar el usuario.'));
    exit;
}

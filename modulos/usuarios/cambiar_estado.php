<?php
require_once __DIR__ . '/../../includes/verificar_sesion.php';
require_once __DIR__ . '/../../config/conexion.php';

$id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT)
    ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_usuario) {
    header('Location: index.php?error=' . urlencode('ID de usuario no válido.'));
    exit;
}

try {
    $stmt = $conexion->prepare("SELECT id_usuario, nombre_usuario, activo FROM usuario WHERE id_usuario = :id LIMIT 1");
    $stmt->execute([':id' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: index.php?error=' . urlencode('El usuario no existe.'));
        exit;
    }

    // Evitar desactivar la propia sesión activa
    $sesionUsuario = $_SESSION['usuario'] ?? $_SESSION['nombre_usuario'] ?? null;
    if ((int)$usuario['activo'] === 1 && $sesionUsuario && $usuario['nombre_usuario'] === $sesionUsuario) {
        header('Location: index.php?error=' . urlencode('No puedes desactivar tu propio usuario en sesión.'));
        exit;
    }

    $nuevoEstado = ((int)$usuario['activo'] === 1) ? 0 : 1;

    $upd = $conexion->prepare("UPDATE usuario SET activo = :activo WHERE id_usuario = :id");
    $upd->execute([
        ':activo' => $nuevoEstado,
        ':id'     => $id_usuario,
    ]);

    $mensaje = ($nuevoEstado === 1) ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.';
    header('Location: index.php?ok=' . urlencode($mensaje));
    exit;
} catch (PDOException $e) {
    error_log("Error al cambiar estado de usuario: " . $e->getMessage());
    header('Location: index.php?error=' . urlencode('No se pudo cambiar el estado del usuario.'));
    exit;
}
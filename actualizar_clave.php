
<?php

require_once __DIR__ . '/config/conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($usuario === '' || $contrasena === '') {

        $mensaje = 'Completa todos los campos.';

    } elseif (strlen($contrasena) < 8) {

        $mensaje = 'La contraseña debe tener al menos 8 caracteres.';

    } else {

        // Generar hash seguro
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        // Actualizar únicamente la contraseña del usuario indicado
        $sql = "UPDATE usuario
                SET password_hash = :hash
                WHERE nombre_usuario = :usuario";

        $consulta = $conexion->prepare($sql);

        $consulta->execute([
            ':hash' => $hash,
            ':usuario' => $usuario
        ]);

        if ($consulta->rowCount() > 0) {

            $mensaje = 'Contraseña actualizada correctamente.';

        } else {

            $mensaje = 'No se encontró el usuario o no hubo cambios.';

        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actualizar contraseña</title>
</head>

<body>

    <h2>Actualizar contraseña de prueba</h2>

    <?php if ($mensaje !== ''): ?>
        <p><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>Nombre de usuario:</label>
        <input type="text" name="usuario" required>

        <br><br>

        <label>Nueva contraseña:</label>
        <input type="password" name="contrasena" minlength="8" required>

        <br><br>

        <button type="submit">Actualizar contraseña</button>

    </form>

</body>
</html>

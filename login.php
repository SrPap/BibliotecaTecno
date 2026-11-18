
<?php

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

iniciar_sesion();

// Si ya inició sesión, ir al panel principal
if (sesion_activa()) {
    redireccionar('principal.php');
}

$error = '';
$nombreUsuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombreUsuario = trim($_POST['nombre_usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($nombreUsuario === '' || $contrasena === '') {

        $error = 'Ingresa tu usuario y contraseña.';

    } else {

        try {

            // Buscar usuario y su rol
            $sql = "SELECT
                        u.id_usuario,
                        u.nombre_usuario,
                        u.password_hash,
                        u.activo,
                        r.nombre AS rol
                    FROM usuario u
                    INNER JOIN rol r
                        ON u.id_rol = r.id_rol
                    WHERE u.nombre_usuario = :usuario
                    LIMIT 1";

            $consulta = $conexion->prepare($sql);

            $consulta->execute([
                ':usuario' => $nombreUsuario
            ]);

            $usuario = $consulta->fetch();

            // Comprobar que el usuario existe y está activo
            if (
                $usuario &&
                (int) $usuario['activo'] === 1 &&
                password_verify($contrasena, $usuario['password_hash'])
            ) {

                // Renovar el identificador de sesión
                session_regenerate_id(true);

                // Guardar información de la sesión
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
                $_SESSION['rol'] = $usuario['rol'];

                // Redireccionar al panel principal
                redireccionar('principal.php');

            } else {

                $error = 'Usuario o contraseña incorrectos, o cuenta inactiva.';

            }

        } catch (PDOException $e) {

            error_log('Error en el login: ' . $e->getMessage());

            $error = 'Ocurrió un problema al intentar iniciar sesión.';

        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión | BibliotecaTecno</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Iconos Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <main class="login-container">

        <section class="login-card">

            <div class="login-header">

                <div class="login-icon">
                    <i class="bi bi-book-half"></i>
                </div>

                <h1>BibliotecaTecno</h1>

                <p>Sistema de Gestión de Biblioteca y Préstamo de Libros</p>

            </div>

            <div class="login-body">

                <h2>Bienvenido</h2>

                <p class="login-description">
                    Ingresa tus credenciales para acceder al sistema.
                </p>

                <?php if ($error !== ''): ?>

                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= limpiar($error) ?>
                    </div>

                <?php endif; ?>

                <form method="POST" action="login.php">

                    <div class="mb-3">

                        <label for="nombre_usuario" class="form-label">
                            Nombre de usuario
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>

                            <input
                                type="text"
                                class="form-control"
                                id="nombre_usuario"
                                name="nombre_usuario"
                                placeholder="Ingresa tu usuario"
                                value="<?= limpiar($nombreUsuario) ?>"
                                maxlength="60"
                                autocomplete="username"
                                required>

                        </div>

                    </div>

                    <div class="mb-4">

                        <label for="contrasena" class="form-label">
                            Contraseña
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>

                            <input
                                type="password"
                                class="form-control"
                                id="contrasena"
                                name="contrasena"
                                placeholder="Ingresa tu contraseña"
                                autocomplete="current-password"
                                required>

                            <button
                                class="btn btn-outline-secondary"
                                type="button"
                                id="mostrarContrasena"
                                aria-label="Mostrar contraseña">

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>

                    </div>

                    <button type="submit" class="btn btn-login w-100">

                        <i class="bi bi-box-arrow-in-right"></i>
                        Iniciar sesión

                    </button>

                </form>

            </div>

            <div class="login-footer">

                <p>
                    Instituto de Educación Superior Público
                    <br>
                    “Cap. FAP José Abelardo Quiñones” - Tumbes
                </p>

                <small>BibliotecaTecno © 2026</small>

            </div>

        </section>

    </main>

    <script>

        const botonMostrar = document.getElementById('mostrarContrasena');
        const campoContrasena = document.getElementById('contrasena');

        botonMostrar.addEventListener('click', function () {

            const mostrar = campoContrasena.type === 'password';

            campoContrasena.type = mostrar ? 'text' : 'password';

            botonMostrar.innerHTML = mostrar
                ? '<i class="bi bi-eye-slash"></i>'
                : '<i class="bi bi-eye"></i>';

            botonMostrar.setAttribute(
                'aria-label',
                mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña'
            );

        });

    </script>

</body>
</html>

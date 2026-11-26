
<?php
$rol = obtener_rol();
$nombreUsuario = obtener_usuario();
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BibliotecaTecno | <?= limpiar($tituloPagina ?? 'Panel principal') ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="/BibliotecaTecno/assets/css/dashboard.css">

</head>

<body>

    <header class="topbar">

        <a
            href="#"
            class="brand"
            id="brandToggle"
            role="button"
            aria-label="Mostrar u ocultar menú"
            aria-controls="sidebar"
            aria-expanded="true">
            <i class="bi bi-book-half"></i>
            <span>BibliotecaTecno</span>
        </a>

        <div class="topbar-user">

            <div class="user-avatar">
                <i class="bi bi-person-fill"></i>
            </div>

            <div class="user-info">
                <strong><?= limpiar($nombreUsuario) ?></strong>
                <small><?= limpiar($rol) ?></small>
            </div>

            <a
                href="/BibliotecaTecno/logout.php"
                class="logout-link"
                title="Cerrar sesión">

                <i class="bi bi-box-arrow-right"></i>

            </a>

        </div>

    </header>

    <div class="dashboard-layout">

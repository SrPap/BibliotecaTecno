<?php
// includes/layout_modulos.php
function iniciarLayoutModulo(string $tituloPagina, string $moduloActivo = '') {
    $usuario = $_SESSION['nombre_usuario'] ?? ($_SESSION['usuario'] ?? 'ricardo_demo');
    $rol     = $_SESSION['rol'] ?? 'ADMINISTRADOR';
    $base    = '/BibliotecaTecno';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?> | BibliotecaTecno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --bt-topbar: #0b4a7d;
            --bt-sidebar: #13344c;
            --bt-sidebar-active: #0d6efd;
            --bt-bg: #f3f6f9;
            --bt-title: #0b4a7d;
            --bt-table-head: #e8eff6;
        }
        body {
            background-color: var(--bt-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            color: #2b3440;
        }
        /* Barra superior */
        .bt-topbar {
            background-color: var(--bt-topbar);
            color: #fff;
            height: 60px;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1030;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        }
        .bt-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        /* Sidebar lateral */
        .bt-sidebar {
            background-color: var(--bt-sidebar);
            width: 240px;
            position: fixed;
            top: 60px;
            bottom: 0;
            left: 0;
            overflow-y: auto;
            padding: 1rem 0;
            color: #c7d5e2;
        }
        .bt-section-title {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #7e9bb3;
            padding: 1.15rem 1.35rem 0.45rem;
        }
        .bt-nav-link {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.82rem 1.35rem;
            margin-bottom: 0.12rem;
            color: #dce7f1;
            text-decoration: none;
            font-size: 0.92rem;
            transition: background 0.15s, color 0.15s;
        }
        .bt-nav-link:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .bt-nav-link.active {
            background-color: var(--bt-sidebar-active);
            color: #fff;
            font-weight: 600;
        }
        .bt-logout {
            color: #ff7b7b !important;
            border-top: 1px solid rgba(255,255,255,0.08);
            margin-top: 1rem;
            padding-top: 0.85rem;
        }
        /* Contenedor principal */
        .bt-main {
            margin-left: 240px;
            margin-top: 60px;
            padding: 1.75rem 2rem;
            min-height: calc(100vh - 60px);
        }
        .bt-page-title {
            color: var(--bt-title);
            font-weight: 700;
            font-size: 1.55rem;
            margin-bottom: 0.2rem;
        }
        .bt-page-subtitle {
            color: #6c7a89;
            font-size: 0.9rem;
            margin-bottom: 1.4rem;
        }
        /* Tarjetas modernas estilo panel */
        .bt-card {
            background: #fff;
            border: 1px solid #e3e9f0;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(15, 35, 55, 0.04);
            padding: 1.35rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        .bt-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.1rem;
        }
        .bt-card-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #1f2d3d;
            margin: 0;
        }
        /* Tabla estilo BibliotecaTecno */
        .bt-table thead th {
            background-color: var(--bt-table-head);
            color: #2b3e50;
            font-weight: 600;
            font-size: 0.84rem;
            border-bottom: 1px solid #d5e0ec;
            padding: 0.75rem 0.9rem;
        }
        .bt-table tbody td {
            padding: 0.78rem 0.9rem;
            font-size: 0.9rem;
            border-color: #edf2f7;
            vertical-align: middle;
        }
        /* Tarjetas KPI con borde izquierdo de color */
        .bt-kpi {
            background: #fff;
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
            box-shadow: 0 3px 10px rgba(15, 35, 55, 0.04);
            border: 1px solid #e3e9f0;
            border-left-width: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .bt-kpi-blue { border-left-color: #1976d2; }
        .bt-kpi-green { border-left-color: #2e9e66; }
        .bt-kpi-orange { border-left-color: #f39c12; }
        .bt-kpi-red { border-left-color: #dc3545; }
        @media print {
            .bt-topbar, .bt-sidebar, .no-print { display: none !important; }
            .bt-main { margin: 0; padding: 0; }
        }
        @media (max-width: 768px) {
            .bt-sidebar { display: none; }
            .bt-main { margin-left: 0; padding: 1.2rem; }
        }
    </style>
</head>
<body>

<header class="bt-topbar">
    <a href="<?= $base ?>/principal.php" class="bt-brand">
        <i class="bi bi-book-half fs-4"></i>
        <span>BibliotecaTecno</span>
    </a>
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2 text-end">
            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                <i class="bi bi-person-fill fs-5"></i>
            </div>
            <div class="lh-1">
                <div class="fw-semibold" style="font-size:0.86rem;"><?= htmlspecialchars($usuario) ?></div>
                <small style="font-size:0.68rem;opacity:0.8;"><?= htmlspecialchars($rol) ?></small>
            </div>
        </div>
        <a href="<?= $base ?>/logout.php" class="text-white opacity-75" title="Cerrar sesión">
            <i class="bi bi-box-arrow-right fs-5"></i>
        </a>
    </div>
</header>

<aside class="bt-sidebar">
    <div class="bt-section-title">Menú Principal</div>
    <a href="<?= $base ?>/principal.php" class="bt-nav-link <?= $moduloActivo === 'inicio' ? 'active' : '' ?>">
        <i class="bi bi-house-door"></i> Inicio
    </a>

    <div class="bt-section-title">Gestión de Biblioteca</div>
    <a href="<?= $base ?>/modulos/libros/index.php" class="bt-nav-link <?= $moduloActivo === 'libros' ? 'active' : '' ?>">
        <i class="bi bi-book"></i> Libros
    </a>
    <a href="<?= $base ?>/modulos/ejemplares/index.php" class="bt-nav-link <?= $moduloActivo === 'ejemplares' ? 'active' : '' ?>">
        <i class="bi bi-journal-bookmark"></i> Ejemplares
    </a>
    <a href="<?= $base ?>/modulos/autores/index.php" class="bt-nav-link <?= $moduloActivo === 'autores' ? 'active' : '' ?>">
        <i class="bi bi-people"></i> Autores
    </a>
    <a href="<?= $base ?>/modulos/categorias/index.php" class="bt-nav-link <?= $moduloActivo === 'categorias' ? 'active' : '' ?>">
        <i class="bi bi-grid"></i> Categorías
    </a>
    <a href="<?= $base ?>/modulos/editoriales/index.php" class="bt-nav-link <?= $moduloActivo === 'editoriales' ? 'active' : '' ?>">
        <i class="bi bi-building"></i> Editoriales
    </a>

    <div class="bt-section-title">Usuarios</div>
    <a href="<?= $base ?>/modulos/estudiantes/index.php" class="bt-nav-link <?= $moduloActivo === 'estudiantes' ? 'active' : '' ?>">
        <i class="bi bi-mortarboard"></i> Estudiantes
    </a>
    <a href="<?= $base ?>/modulos/usuarios/index.php" class="bt-nav-link <?= $moduloActivo === 'usuarios' ? 'active' : '' ?>">
        <i class="bi bi-person-badge"></i> Usuarios
    </a>
    <a href="<?= $base ?>/modulos/roles/index.php" class="bt-nav-link <?= $moduloActivo === 'roles' ? 'active' : '' ?>">
        <i class="bi bi-shield-check"></i> Roles
    </a>

    <div class="bt-section-title">Préstamos</div>
    <a href="<?= $base ?>/modulos/solicitudes/index.php" class="bt-nav-link <?= $moduloActivo === 'solicitudes' ? 'active' : '' ?>">
        <i class="bi bi-clipboard-check"></i> Solicitudes
    </a>
    <a href="<?= $base ?>/modulos/prestamos/index.php" class="bt-nav-link <?= $moduloActivo === 'prestamos' ? 'active' : '' ?>">
        <i class="bi bi-arrow-left-right"></i> Préstamos
    </a>
    <a href="<?= $base ?>/modulos/reportes/index.php" class="bt-nav-link <?= $moduloActivo === 'reportes' ? 'active' : '' ?>">
        <i class="bi bi-bar-chart-line"></i> Reportes
    </a>

    <a href="<?= $base ?>/logout.php" class="bt-nav-link bt-logout">
        <i class="bi bi-box-arrow-left"></i> Cerrar sesión
    </a>
</aside>

<main class="bt-main">
<?php
}

function cerrarLayoutModulo() {
?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}

<?php

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/verificar_sesion.php';

$rol = obtener_rol();
$nombreUsuario = obtener_usuario();
$idUsuario = (int) $_SESSION['id_usuario'];

$esAdmin = ($rol === 'ADMINISTRADOR');

$estadisticas = [];
$ultimosRegistros = [];
$solicitudesPendientes = [];

try {

    if ($esAdmin) {

        // Estadísticas generales del administrador
        $consultas = [
            'libros' => "SELECT COUNT(*) FROM libro",
            'ejemplares' => "SELECT COUNT(*) FROM ejemplar",
            'estudiantes' => "SELECT COUNT(*) FROM estudiante WHERE activo = 1",
            'prestamos' => "SELECT COUNT(*) FROM prestamo WHERE estado = 'ACTIVO'",
            'solicitudes' => "SELECT COUNT(*) FROM pedido WHERE estado = 'PENDIENTE'"
        ];

        foreach ($consultas as $clave => $sql) {
            $estadisticas[$clave] = (int) $conexion->query($sql)->fetchColumn();
        }

        // Últimos préstamos
        $sql = "SELECT
                    p.id_prestamo,
                    l.titulo,
                    e.nombres,
                    e.apellidos,
                    p.fecha_prestamo,
                    p.fecha_vencimiento,
                    p.estado
                FROM prestamo p
                INNER JOIN pedido s
                    ON p.id_pedido = s.id_pedido
                INNER JOIN libro l
                    ON s.id_libro = l.id_libro
                INNER JOIN estudiante e
                    ON s.id_estudiante = e.id_estudiante
                ORDER BY p.id_prestamo DESC
                LIMIT 5";

        $ultimosRegistros = $conexion->query($sql)->fetchAll();

        // Solicitudes pendientes
        $sql = "SELECT
                    s.id_pedido,
                    l.titulo,
                    e.nombres,
                    e.apellidos,
                    s.fecha_solicitud
                FROM pedido s
                INNER JOIN libro l
                    ON s.id_libro = l.id_libro
                INNER JOIN estudiante e
                    ON s.id_estudiante = e.id_estudiante
                WHERE s.estado = 'PENDIENTE'
                ORDER BY s.fecha_solicitud DESC
                LIMIT 5";

        $solicitudesPendientes = $conexion->query($sql)->fetchAll();

    } else {

        // Identificar al estudiante asociado a la cuenta
        $sql = "SELECT id_estudiante
                FROM usuario
                WHERE id_usuario = :id";

        $consulta = $conexion->prepare($sql);
        $consulta->execute([':id' => $idUsuario]);

        $idEstudiante = $consulta->fetchColumn();

        if ($idEstudiante) {

            $idEstudiante = (int) $idEstudiante;

            // Estadísticas personales
            $sql = "SELECT COUNT(*)
                    FROM pedido
                    WHERE id_estudiante = :id";

            $consulta = $conexion->prepare($sql);
            $consulta->execute([':id' => $idEstudiante]);
            $estadisticas['solicitudes'] = (int) $consulta->fetchColumn();

            $sql = "SELECT COUNT(*)
                    FROM prestamo p
                    INNER JOIN pedido s
                        ON p.id_pedido = s.id_pedido
                    WHERE s.id_estudiante = :id
                    AND p.estado = 'ACTIVO'";

            $consulta = $conexion->prepare($sql);
            $consulta->execute([':id' => $idEstudiante]);
            $estadisticas['prestamos'] = (int) $consulta->fetchColumn();

            $sql = "SELECT COUNT(*)
                    FROM pedido
                    WHERE id_estudiante = :id
                    AND estado = 'PENDIENTE'";

            $consulta = $conexion->prepare($sql);
            $consulta->execute([':id' => $idEstudiante]);
            $estadisticas['pendientes'] = (int) $consulta->fetchColumn();

            // Últimas solicitudes del estudiante
            $sql = "SELECT
                        s.id_pedido,
                        l.titulo,
                        s.fecha_solicitud,
                        s.estado
                    FROM pedido s
                    INNER JOIN libro l
                        ON s.id_libro = l.id_libro
                    WHERE s.id_estudiante = :id
                    ORDER BY s.fecha_solicitud DESC
                    LIMIT 5";

            $consulta = $conexion->prepare($sql);
            $consulta->execute([':id' => $idEstudiante]);
            $ultimosRegistros = $consulta->fetchAll();

        } else {

            $estadisticas = [
                'solicitudes' => 0,
                'prestamos' => 0,
                'pendientes' => 0
            ];
        }
    }

} catch (PDOException $e) {

    error_log('Error al cargar el panel: ' . $e->getMessage());

    $errorPanel = 'No se pudo cargar toda la información del panel.';

}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

?>

<main class="dashboard-content">

    <div class="welcome-section">

        <div>
            <h1>¡Bienvenido, <?= limpiar($nombreUsuario) ?>!</h1>

            <p>
                <?= $esAdmin
                    ? 'Desde aquí puedes administrar la biblioteca.'
                    : 'Consulta tus libros, solicitudes y préstamos.' ?>
            </p>
        </div>

        <div class="welcome-date">
            <i class="bi bi-calendar3"></i>
            <?= date('d/m/Y') ?>
        </div>

    </div>

    <?php if (isset($errorPanel)): ?>
        <div class="alert alert-warning">
            <?= limpiar($errorPanel) ?>
        </div>
    <?php endif; ?>

    <?php if ($esAdmin): ?>

        <h4 class="section-title">Resumen general</h4>

        <div class="stats-grid">

            <div class="stat-card blue">
                <div>
                    <p>Total de libros</p>
                    <h2><?= $estadisticas['libros'] ?? 0 ?></h2>
                </div>
                <i class="bi bi-book"></i>
            </div>

            <div class="stat-card green">
                <div>
                    <p>Ejemplares</p>
                    <h2><?= $estadisticas['ejemplares'] ?? 0 ?></h2>
                </div>
                <i class="bi bi-journal-bookmark"></i>
            </div>

            <div class="stat-card orange">
                <div>
                    <p>Estudiantes activos</p>
                    <h2><?= $estadisticas['estudiantes'] ?? 0 ?></h2>
                </div>
                <i class="bi bi-people"></i>
            </div>

            <div class="stat-card purple">
                <div>
                    <p>Préstamos activos</p>
                    <h2><?= $estadisticas['prestamos'] ?? 0 ?></h2>
                </div>
                <i class="bi bi-arrow-left-right"></i>
            </div>

            <div class="stat-card red">
                <div>
                    <p>Solicitudes pendientes</p>
                    <h2><?= $estadisticas['solicitudes'] ?? 0 ?></h2>
                </div>
                <i class="bi bi-clock-history"></i>
            </div>

        </div>

        <section class="dashboard-panel">

            <div class="panel-heading">
                <h4>Últimos préstamos</h4>
                <a href="modulos/prestamos/index.php">Ver todos</a>
            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Libro</th>
                            <th>Estudiante</th>
                            <th>Fecha de préstamo</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($ultimosRegistros) > 0): ?>

                            <?php foreach ($ultimosRegistros as $registro): ?>

                                <tr>
                                    <td><?= limpiar($registro['titulo']) ?></td>

                                    <td>
                                        <?= limpiar($registro['nombres'] . ' ' . $registro['apellidos']) ?>
                                    </td>

                                    <td><?= limpiar($registro['fecha_prestamo']) ?></td>

                                    <td><?= limpiar($registro['fecha_vencimiento']) ?></td>

                                    <td>
                                        <span class="status-badge">
                                            <?= limpiar($registro['estado']) ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No hay préstamos registrados.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <section class="dashboard-panel">

            <div class="panel-heading">
                <h4>Solicitudes pendientes</h4>
                <a href="modulos/solicitudes/index.php">Ver todas</a>
            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Libro</th>
                            <th>Estudiante</th>
                            <th>Fecha de solicitud</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($solicitudesPendientes) > 0): ?>

                            <?php foreach ($solicitudesPendientes as $solicitud): ?>

                                <tr>
                                    <td><?= limpiar($solicitud['titulo']) ?></td>

                                    <td>
                                        <?= limpiar($solicitud['nombres'] . ' ' . $solicitud['apellidos']) ?>
                                    </td>

                                    <td><?= limpiar($solicitud['fecha_solicitud']) ?></td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    No hay solicitudes pendientes.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    <?php else: ?>

        <h4 class="section-title">Mi biblioteca</h4>

        <div class="stats-grid student-stats">

            <div class="stat-card blue">
                <div>
                    <p>Mis solicitudes</p>
                    <h2><?= $estadisticas['solicitudes'] ?? 0 ?></h2>
                </div>
                <i class="bi bi-file-earmark-text"></i>
            </div>

            <div class="stat-card green">
                <div>
                    <p>Préstamos activos</p>
                    <h2><?= $estadisticas['prestamos'] ?? 0 ?></h2>
                </div>
                <i class="bi bi-book-half"></i>
            </div>

            <div class="stat-card orange">
                <div>
                    <p>Solicitudes pendientes</p>
                    <h2><?= $estadisticas['pendientes'] ?? 0 ?></h2>
                </div>
                <i class="bi bi-clock"></i>
            </div>

        </div>

        <section class="dashboard-panel">

            <div class="panel-heading">
                <h4>Mis últimas solicitudes</h4>
                <a href="modulos/mis_libros/solicitudes.php">Ver todas</a>
            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Libro</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($ultimosRegistros) > 0): ?>

                            <?php foreach ($ultimosRegistros as $registro): ?>

                                <tr>
                                    <td><?= limpiar($registro['titulo']) ?></td>
                                    <td><?= limpiar($registro['fecha_solicitud']) ?></td>
                                    <td>
                                        <span class="status-badge">
                                            <?= limpiar($registro['estado']) ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    Todavía no tienes solicitudes.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

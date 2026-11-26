<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../includes/layout_modulos.php';

$db = $conexion ?? ($pdo ?? ($conn ?? null));
$buscar = trim($_GET['buscar'] ?? '');

$sql = "SELECT pr.id_prestamo, pr.fecha_prestamo, pr.fecha_vencimiento, pr.fecha_devolucion, pr.estado,
               ej.id_ejemplar, ej.codigo_ejemplar,
               l.titulo,
               e.nombres, e.apellidos, e.codigo_estudiante
        FROM prestamo pr
        INNER JOIN ejemplar ej ON pr.id_ejemplar = ej.id_ejemplar
        INNER JOIN libro l ON ej.id_libro = l.id_libro
        LEFT JOIN pedido p ON pr.id_pedido = p.id_pedido
        LEFT JOIN estudiante e ON p.id_estudiante = e.id_estudiante
        WHERE 1=1";

$params = [];
if ($buscar !== '') {
    $sql .= " AND (l.titulo LIKE :q OR ej.codigo_ejemplar LIKE :q OR e.apellidos LIKE :q OR e.nombres LIKE :q)";
    $params[':q'] = "%{$buscar}%";
}
$sql .= " ORDER BY pr.id_prestamo DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

iniciarLayoutModulo('Gestión de Préstamos', 'prestamos');
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h1 class="bt-page-title">Gestión de Préstamos</h1>
        <p class="bt-page-subtitle mb-0">Controla los ejemplares entregados, fechas de vencimiento y devoluciones.</p>
    </div>
    <span class="badge bg-white text-secondary border px-3 py-2 rounded-pill shadow-sm">
        <i class="bi bi-arrow-left-right text-primary me-1"></i> <?= count($prestamos) ?> préstamos
    </span>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-3">
        <i class="bi bi-check-circle-fill me-1"></i> Préstamo actualizado correctamente.
    </div>
<?php endif; ?>

<div class="bt-card">
    <div class="bt-card-header">
        <h2 class="bt-card-title">Listado de préstamos</h2>
        <div class="d-flex flex-wrap gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="buscar" value="<?= htmlspecialchars($buscar) ?>" class="form-control form-control-sm" placeholder="Buscar libro, código o estudiante..." style="width: 240px;">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </form>
            <a href="registrar.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Nuevo préstamo
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table bt-table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Libro / Ejemplar</th>
                    <th>Estudiante</th>
                    <th>Fecha préstamo</th>
                    <th>Vencimiento</th>
                    <th>Devolución</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($prestamos)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">No hay préstamos registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($prestamos as $pr): ?>
                    <?php
                    $vencido = ($pr['estado'] === 'ACTIVO' && $pr['fecha_vencimiento'] < date('Y-m-d'));
                    if ($pr['estado'] === 'DEVUELTO') {
                        $badge = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                        $textoEstado = 'DEVUELTO';
                    } elseif ($vencido) {
                        $badge = 'bg-danger-subtle text-danger border border-danger-subtle';
                        $textoEstado = 'VENCIDO';
                    } else {
                        $badge = 'bg-primary-subtle text-primary border border-primary-subtle';
                        $textoEstado = 'ACTIVO';
                    }
                    ?>
                    <tr>
                        <td class="fw-semibold text-muted">#<?= (int)$pr['id_prestamo'] ?></td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($pr['titulo']) ?></div>
                            <small class="text-muted">Cód. Ejemplar: <?= htmlspecialchars($pr['codigo_ejemplar']) ?></small>
                        </td>
                        <td>
                            <?php if (!empty($pr['apellidos'])): ?>
                                <div><?= htmlspecialchars($pr['apellidos'] . ', ' . $pr['nombres']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($pr['codigo_estudiante']) ?></small>
                            <?php else: ?>
                                <span class="text-muted">Registro directo</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($pr['fecha_prestamo']) ?></td>
                        <td><?= htmlspecialchars($pr['fecha_vencimiento']) ?></td>
                        <td><?= htmlspecialchars($pr['fecha_devolucion'] ?? '-') ?></td>
                        <td><span class="badge rounded-pill <?= $badge ?> px-3 py-1"><?= $textoEstado ?></span></td>
                        <td class="text-end">
                            <?php if ($pr['estado'] !== 'DEVUELTO'): ?>
                                <a href="devolver.php?id=<?= (int)$pr['id_prestamo'] ?>" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-box-arrow-in-left"></i> Devolver
                                </a>
                            <?php else: ?>
                                <span class="text-muted small"><i class="bi bi-check2-all"></i> Entregado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php cerrarLayoutModulo(); ?>
<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../includes/layout_modulos.php';

$db = $conexion ?? ($pdo ?? ($conn ?? null));

$desde  = $_GET['desde'] ?? date('Y-m-01');
$hasta  = $_GET['hasta'] ?? date('Y-m-d');
$estado = $_GET['estado'] ?? '';

$sql = "SELECT pr.id_prestamo,
               COALESCE(CONCAT(e.apellidos, ', ', e.nombres), 'Registro directo') AS estudiante,
               COALESCE(e.codigo_estudiante, '-') AS codigo_estudiante,
               l.titulo AS libro,
               ej.codigo_ejemplar,
               pr.fecha_prestamo,
               pr.fecha_vencimiento,
               COALESCE(pr.fecha_devolucion, '-') AS fecha_devolucion,
               CASE
                   WHEN pr.estado = 'DEVUELTO' THEN 'DEVUELTO'
                   WHEN pr.fecha_vencimiento < CURDATE() THEN 'VENCIDO'
                   ELSE 'ACTIVO'
               END AS estado_calculado
        FROM prestamo pr
        INNER JOIN ejemplar ej ON pr.id_ejemplar = ej.id_ejemplar
        INNER JOIN libro l ON ej.id_libro = l.id_libro
        LEFT JOIN pedido p ON pr.id_pedido = p.id_pedido
        LEFT JOIN estudiante e ON p.id_estudiante = e.id_estudiante
        WHERE pr.fecha_prestamo BETWEEN :desde AND :hasta";

$params = [':desde' => $desde, ':hasta' => $hasta];

if ($estado === 'DEVUELTO') {
    $sql .= " AND pr.estado = 'DEVUELTO'";
} elseif ($estado === 'VENCIDO') {
    $sql .= " AND pr.estado != 'DEVUELTO' AND pr.fecha_vencimiento < CURDATE()";
} elseif ($estado === 'ACTIVO') {
    $sql .= " AND pr.estado = 'ACTIVO' AND pr.fecha_vencimiento >= CURDATE()";
}

$sql .= " ORDER BY pr.fecha_prestamo DESC, pr.id_prestamo DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="reporte_prestamos_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, ['ID', 'Codigo Estudiante', 'Estudiante', 'Libro', 'Ejemplar', 'Fecha Prestamo', 'Vencimiento', 'Devolucion', 'Estado']);
    foreach ($filas as $f) {
        fputcsv($out, [
            $f['id_prestamo'],
            $f['codigo_estudiante'],
            $f['estudiante'],
            $f['libro'],
            $f['codigo_ejemplar'],
            $f['fecha_prestamo'],
            $f['fecha_vencimiento'],
            $f['fecha_devolucion'],
            $f['estado_calculado']
        ]);
    }
    fclose($out);
    exit;
}

$totalActivos   = count(array_filter($filas, fn($r) => $r['estado_calculado'] === 'ACTIVO'));
$totalVencidos  = count(array_filter($filas, fn($r) => $r['estado_calculado'] === 'VENCIDO'));
$totalDevueltos = count(array_filter($filas, fn($r) => $r['estado_calculado'] === 'DEVUELTO'));

iniciarLayoutModulo('Reportes de Biblioteca', 'reportes');
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3 no-print">
    <div>
        <h1 class="bt-page-title">Reportes de Préstamos</h1>
        <p class="bt-page-subtitle mb-0">Filtra movimientos por fecha y exporta los resultados en CSV o impresión.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="?desde=<?= urlencode($desde) ?>&hasta=<?= urlencode($hasta) ?>&estado=<?= urlencode($estado) ?>&export=csv" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="bt-kpi bt-kpi-blue">
            <div>
                <small class="text-muted d-block">Total registros</small>
                <span class="fs-4 fw-bold"><?= count($filas) ?></span>
            </div>
            <i class="bi bi-journal-text fs-3 text-primary opacity-75"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bt-kpi bt-kpi-orange">
            <div>
                <small class="text-muted d-block">Préstamos activos</small>
                <span class="fs-4 fw-bold"><?= $totalActivos ?></span>
            </div>
            <i class="bi bi-arrow-left-right fs-3 text-warning opacity-75"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bt-kpi bt-kpi-green">
            <div>
                <small class="text-muted d-block">Devueltos</small>
                <span class="fs-4 fw-bold"><?= $totalDevueltos ?></span>
            </div>
            <i class="bi bi-check2-circle fs-3 text-success opacity-75"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bt-kpi bt-kpi-red">
            <div>
                <small class="text-muted d-block">Vencidos</small>
                <span class="fs-4 fw-bold"><?= $totalVencidos ?></span>
            </div>
            <i class="bi bi-clock-history fs-3 text-danger opacity-75"></i>
        </div>
    </div>
</div>

<div class="bt-card no-print">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Desde</label>
            <input type="date" name="desde" class="form-control form-control-sm" value="<?= htmlspecialchars($desde) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Hasta</label>
            <input type="date" name="hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($hasta) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Estado</label>
            <select name="estado" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="ACTIVO" <?= $estado === 'ACTIVO' ? 'selected' : '' ?>>Activos</option>
                <option value="VENCIDO" <?= $estado === 'VENCIDO' ? 'selected' : '' ?>>Vencidos</option>
                <option value="DEVUELTO" <?= $estado === 'DEVUELTO' ? 'selected' : '' ?>>Devueltos</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="bi bi-funnel"></i> Generar reporte
            </button>
        </div>
    </form>
</div>

<div class="bt-card">
    <div class="bt-card-header">
        <h2 class="bt-card-title">Detalle de movimientos (<?= htmlspecialchars($desde) ?> al <?= htmlspecialchars($hasta) ?>)</h2>
    </div>
    <div class="table-responsive">
        <table class="table bt-table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Estudiante</th>
                    <th>Libro</th>
                    <th>Ejemplar</th>
                    <th>Préstamo</th>
                    <th>Vencimiento</th>
                    <th>Devolución</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($filas)): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">No hay resultados para el rango seleccionado.</td></tr>
            <?php else: ?>
                <?php foreach ($filas as $f): ?>
                    <?php
                    $badge = match ($f['estado_calculado']) {
                        'DEVUELTO' => 'bg-secondary-subtle text-secondary',
                        'VENCIDO'  => 'bg-danger-subtle text-danger',
                        default    => 'bg-primary-subtle text-primary',
                    };
                    ?>
                    <tr>
                        <td class="fw-semibold text-muted">#<?= (int)$f['id_prestamo'] ?></td>
                        <td><?= htmlspecialchars($f['estudiante']) ?></td>
                        <td><?= htmlspecialchars($f['libro']) ?></td>
                        <td><?= htmlspecialchars($f['codigo_ejemplar']) ?></td>
                        <td><?= htmlspecialchars($f['fecha_prestamo']) ?></td>
                        <td><?= htmlspecialchars($f['fecha_vencimiento']) ?></td>
                        <td><?= htmlspecialchars($f['fecha_devolucion']) ?></td>
                        <td><span class="badge rounded-pill <?= $badge ?> px-3 py-1"><?= htmlspecialchars($f['estado_calculado']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php cerrarLayoutModulo(); ?>
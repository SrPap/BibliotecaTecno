<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../includes/layout_modulos.php';

$db = $conexion ?? ($pdo ?? ($conn ?? null));
$filtroEstado = $_GET['estado'] ?? '';
$buscar = trim($_GET['buscar'] ?? '');

$sql = "SELECT p.id_pedido, p.fecha_solicitud, p.estado, p.observacion,
               e.codigo_estudiante, e.nombres, e.apellidos,
               l.codigo_libro, l.titulo
        FROM pedido p
        INNER JOIN estudiante e ON p.id_estudiante = e.id_estudiante
        INNER JOIN libro l ON p.id_libro = l.id_libro
        WHERE 1=1";

$params = [];
if ($filtroEstado !== '') {
    $sql .= " AND p.estado = :estado";
    $params[':estado'] = $filtroEstado;
}
if ($buscar !== '') {
    $sql .= " AND (e.nombres LIKE :q OR e.apellidos LIKE :q OR e.codigo_estudiante LIKE :q OR l.titulo LIKE :q)";
    $params[':q'] = "%{$buscar}%";
}
$sql .= " ORDER BY p.fecha_solicitud DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

iniciarLayoutModulo('Gestión de Solicitudes', 'solicitudes');
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h1 class="bt-page-title">Gestión de Solicitudes</h1>
        <p class="bt-page-subtitle mb-0">Consulta, aprueba o rechaza las solicitudes de préstamo de los estudiantes.</p>
    </div>
    <span class="badge bg-white text-secondary border px-3 py-2 rounded-pill shadow-sm">
        <i class="bi bi-clipboard-check text-primary me-1"></i> <?= count($solicitudes) ?> solicitudes
    </span>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-3">
        <i class="bi bi-check-circle-fill me-1"></i> Operación realizada correctamente.
    </div>
<?php endif; ?>

<div class="bt-card">
    <div class="bt-card-header">
        <h2 class="bt-card-title">Listado de solicitudes</h2>
        <div class="d-flex flex-wrap gap-2">
            <form method="GET" class="d-flex flex-wrap gap-2">
                <input type="text" name="buscar" value="<?= htmlspecialchars($buscar) ?>" class="form-control form-control-sm" placeholder="Buscar estudiante o libro..." style="width: 210px;">
                <select name="estado" class="form-select form-select-sm" style="width: 155px;" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="PENDIENTE" <?= $filtroEstado === 'PENDIENTE' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="APROBADA" <?= $filtroEstado === 'APROBADA' ? 'selected' : '' ?>>Aprobada</option>
                    <option value="RECHAZADA" <?= $filtroEstado === 'RECHAZADA' ? 'selected' : '' ?>>Rechazada</option>
                </select>
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </form>
            <a href="registrar.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Nueva solicitud
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table bt-table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Estudiante</th>
                    <th>Libro solicitado</th>
                    <th>Fecha de solicitud</th>
                    <th>Estado</th>
                    <th>Observación</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($solicitudes)): ?>
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">No hay solicitudes registradas.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($solicitudes as $s): ?>
                    <?php
                    switch ($s['estado']) {
                        case 'APROBADA':
                            $badge = 'bg-success-subtle text-success border border-success-subtle';
                            break;
                        case 'RECHAZADA':
                            $badge = 'bg-danger-subtle text-danger border border-danger-subtle';
                            break;
                        default:
                            $badge = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                            break;
                    }
                    ?>
                    <tr>
                        <td class="fw-semibold text-muted">#<?= (int)$s['id_pedido'] ?></td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($s['apellidos'] . ', ' . $s['nombres']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($s['codigo_estudiante']) ?></small>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($s['titulo']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($s['codigo_libro']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($s['fecha_solicitud']) ?></td>
                        <td><span class="badge rounded-pill <?= $badge ?> px-3 py-1"><?= htmlspecialchars($s['estado']) ?></span></td>
                        <td class="text-muted"><?= htmlspecialchars($s['observacion'] ?? '-') ?></td>
                        <td class="text-end">
                            <a href="gestionar.php?id=<?= (int)$s['id_pedido'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-sliders"></i> Gestionar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php cerrarLayoutModulo(); ?>
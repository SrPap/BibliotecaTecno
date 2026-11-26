<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../includes/layout_modulos.php';

$db = $conexion ?? ($pdo ?? ($conn ?? null));
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $db->prepare("SELECT p.*, e.nombres, e.apellidos, e.codigo_estudiante, l.titulo, l.codigo_libro
                      FROM pedido p
                      INNER JOIN estudiante e ON p.id_estudiante = e.id_estudiante
                      INNER JOIN libro l ON p.id_libro = l.id_libro
                      WHERE p.id_pedido = :id");
$stmt->execute([':id' => $id]);
$solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$solicitud) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoEstado = in_array($_POST['estado'] ?? '', ['APROBADA', 'RECHAZADA', 'PENDIENTE'], true) ? $_POST['estado'] : 'PENDIENTE';
    $observacion = trim($_POST['observacion'] ?? '');

    $upd = $db->prepare("UPDATE pedido SET estado = :estado, observacion = :obs WHERE id_pedido = :id");
    $upd->execute([
        ':estado' => $nuevoEstado,
        ':obs'    => $observacion !== '' ? $observacion : null,
        ':id'     => $id
    ]);

    if ($nuevoEstado === 'APROBADA' && isset($_POST['ir_prestamo'])) {
        header("Location: ../prestamos/registrar.php?id_pedido={$id}&id_libro={$solicitud['id_libro']}");
        exit;
    }

    header('Location: index.php?msg=gestionada');
    exit;
}

iniciarLayoutModulo('Gestionar Solicitud', 'solicitudes');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="bt-page-title">Gestionar Solicitud #<?= (int)$solicitud['id_pedido'] ?></h1>
        <p class="bt-page-subtitle mb-0">Evalúa el pedido y autoriza el préstamo del ejemplar.</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="bt-card" style="max-width: 720px;">
    <div class="row g-3 mb-4 p-3 rounded-3" style="background:#f8fafc;border:1px solid #e6edf5;">
        <div class="col-md-6">
            <small class="text-muted d-block">Estudiante</small>
            <strong><?= htmlspecialchars($solicitud['apellidos'] . ', ' . $solicitud['nombres']) ?></strong>
            <span class="text-muted small">(<?= htmlspecialchars($solicitud['codigo_estudiante']) ?>)</span>
        </div>
        <div class="col-md-6">
            <small class="text-muted d-block">Libro solicitado</small>
            <strong><?= htmlspecialchars($solicitud['titulo']) ?></strong>
            <span class="text-muted small">(<?= htmlspecialchars($solicitud['codigo_libro']) ?>)</span>
        </div>
        <div class="col-12">
            <small class="text-muted d-block">Fecha de solicitud</small>
            <span><?= htmlspecialchars($solicitud['fecha_solicitud']) ?></span>
        </div>
    </div>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Estado de la solicitud</label>
            <select name="estado" class="form-select">
                <option value="PENDIENTE" <?= $solicitud['estado'] === 'PENDIENTE' ? 'selected' : '' ?>>PENDIENTE</option>
                <option value="APROBADA" <?= $solicitud['estado'] === 'APROBADA' ? 'selected' : '' ?>>APROBADA</option>
                <option value="RECHAZADA" <?= $solicitud['estado'] === 'RECHAZADA' ? 'selected' : '' ?>>RECHAZADA</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Observación</label>
            <textarea name="observacion" class="form-control" rows="2"><?= htmlspecialchars($solicitud['observacion'] ?? '') ?></textarea>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2"></i> Guardar estado
            </button>
            <button type="submit" name="ir_prestamo" value="1" class="btn btn-success">
                <i class="bi bi-arrow-left-right"></i> Aprobar y registrar préstamo
            </button>
        </div>
    </form>
</div>

<?php cerrarLayoutModulo(); ?>
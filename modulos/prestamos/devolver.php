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

$stmt = $db->prepare("SELECT pr.*, ej.codigo_ejemplar, l.titulo
                      FROM prestamo pr
                      INNER JOIN ejemplar ej ON pr.id_ejemplar = ej.id_ejemplar
                      INNER JOIN libro l ON ej.id_libro = l.id_libro
                      WHERE pr.id_prestamo = :id");
$stmt->execute([':id' => $id]);
$prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prestamo) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_devolucion = $_POST['fecha_devolucion'] ?? date('Y-m-d');
    $estado_ejemplar  = ($_POST['estado_ejemplar'] === 'DANADO') ? 'DANADO' : 'DISPONIBLE';

    try {
        $db->beginTransaction();

        $updPr = $db->prepare("UPDATE prestamo
                               SET fecha_devolucion = :fecha_devolucion, estado = 'DEVUELTO'
                               WHERE id_prestamo = :id");
        $updPr->execute([
            ':fecha_devolucion' => $fecha_devolucion,
            ':id'               => $id
        ]);

        $updEj = $db->prepare("UPDATE ejemplar SET estado = :estado WHERE id_ejemplar = :id_ejemplar");
        $updEj->execute([
            ':estado'      => $estado_ejemplar,
            ':id_ejemplar' => $prestamo['id_ejemplar']
        ]);

        $db->commit();
        header('Location: index.php?msg=devuelto');
        exit;
    } catch (Throwable $e) {
        $db->rollBack();
        $error = 'Error al registrar devolución: ' . $e->getMessage();
    }
}

iniciarLayoutModulo('Devolución de Préstamo', 'prestamos');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="bt-page-title">Devolución de Préstamo #<?= (int)$prestamo['id_prestamo'] ?></h1>
        <p class="bt-page-subtitle mb-0">Registra el retorno del ejemplar a la biblioteca.</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="bt-card" style="max-width: 720px;">
    <div class="row g-3 mb-4 p-3 rounded-3" style="background:#f8fafc;border:1px solid #e6edf5;">
        <div class="col-md-6">
            <small class="text-muted d-block">Libro</small>
            <strong><?= htmlspecialchars($prestamo['titulo']) ?></strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Ejemplar</small>
            <strong><?= htmlspecialchars($prestamo['codigo_ejemplar']) ?></strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Vencimiento</small>
            <strong><?= htmlspecialchars($prestamo['fecha_vencimiento']) ?></strong>
        </div>
    </div>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Fecha de devolución</label>
            <input type="date" name="fecha_devolucion" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Estado físico del ejemplar</label>
            <select name="estado_ejemplar" class="form-select">
                <option value="DISPONIBLE">DISPONIBLE (en buen estado)</option>
                <option value="DANADO">DAÑADO (requiere revisión)</option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle"></i> Confirmar devolución
            </button>
            <a href="index.php" class="btn btn-light border">Cancelar</a>
        </div>
    </form>
</div>

<?php cerrarLayoutModulo(); ?>
<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../includes/layout_modulos.php';

$db = $conexion ?? ($pdo ?? ($conn ?? null));
$id_pedido_pre = (int)($_GET['id_pedido'] ?? 0);
$id_libro_pre  = (int)($_GET['id_libro'] ?? 0);

$sqlEjemplares = "SELECT ej.id_ejemplar, ej.codigo_ejemplar, ej.ubicacion, l.id_libro, l.titulo
                  FROM ejemplar ej
                  INNER JOIN libro l ON ej.id_libro = l.id_libro
                  WHERE ej.estado IN ('DISPONIBLE', 'ACTIVO')";
if ($id_libro_pre > 0) {
    $sqlEjemplares .= " AND l.id_libro = :id_libro";
}
$sqlEjemplares .= " ORDER BY l.titulo ASC, ej.codigo_ejemplar ASC";

$stmtEj = $db->prepare($sqlEjemplares);
if ($id_libro_pre > 0) {
    $stmtEj->execute([':id_libro' => $id_libro_pre]);
} else {
    $stmtEj->execute();
}
$ejemplares = $stmtEj->fetchAll(PDO::FETCH_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ejemplar       = (int)($_POST['id_ejemplar'] ?? 0);
    $id_pedido         = !empty($_POST['id_pedido']) ? (int)$_POST['id_pedido'] : null;
    $fecha_prestamo    = $_POST['fecha_prestamo'] ?? date('Y-m-d');
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+7 days'));

    if ($id_ejemplar <= 0) {
        $error = 'Debes seleccionar un ejemplar disponible.';
    } elseif ($fecha_vencimiento < $fecha_prestamo) {
        $error = 'La fecha de vencimiento no puede ser anterior a la fecha de préstamo.';
    } else {
        try {
            $db->beginTransaction();

            $ins = $db->prepare("INSERT INTO prestamo
                (id_pedido, id_ejemplar, fecha_prestamo, fecha_vencimiento, fecha_devolucion, estado, ejemplar_activo)
                VALUES (:id_pedido, :id_ejemplar, :fecha_prestamo, :fecha_vencimiento, NULL, 'ACTIVO', :id_ejemplar)");
            $ins->execute([
                ':id_pedido'         => $id_pedido,
                ':id_ejemplar'       => $id_ejemplar,
                ':fecha_prestamo'    => $fecha_prestamo,
                ':fecha_vencimiento' => $fecha_vencimiento
            ]);

            $updEj = $db->prepare("UPDATE ejemplar SET estado = 'PRESTADO' WHERE id_ejemplar = :id_ejemplar");
            $updEj->execute([':id_ejemplar' => $id_ejemplar]);

            if ($id_pedido !== null) {
                $updPed = $db->prepare("UPDATE pedido SET estado = 'APROBADA' WHERE id_pedido = :id_pedido");
                $updPed->execute([':id_pedido' => $id_pedido]);
            }

            $db->commit();
            header('Location: index.php?msg=registrado');
            exit;
        } catch (Throwable $e) {
            $db->rollBack();
            $error = 'Error al registrar el préstamo: ' . $e->getMessage();
        }
    }
}

iniciarLayoutModulo('Registrar Préstamo', 'prestamos');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="bt-page-title">Registrar Préstamo</h1>
        <p class="bt-page-subtitle mb-0">Asigna un ejemplar físico disponible y define su fecha de entrega.</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="bt-card" style="max-width: 720px;">
    <form method="POST">
        <?php if ($id_pedido_pre > 0): ?>
            <input type="hidden" name="id_pedido" value="<?= $id_pedido_pre ?>">
            <div class="alert alert-primary border-0">
                <i class="bi bi-link-45deg"></i> Préstamo vinculado a la solicitud <strong>#<?= $id_pedido_pre ?></strong>.
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label fw-semibold">Ejemplar disponible</label>
            <select name="id_ejemplar" class="form-select" required>
                <option value="">-- Seleccione un ejemplar --</option>
                <?php foreach ($ejemplares as $ej): ?>
                    <option value="<?= (int)$ej['id_ejemplar'] ?>">
                        <?= htmlspecialchars($ej['titulo'] . ' | Cód: ' . $ej['codigo_ejemplar'] . ' (' . ($ej['ubicacion'] ?? 'Sin ubicación') . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Fecha de préstamo</label>
                <input type="date" name="fecha_prestamo" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold">Fecha de vencimiento</label>
                <input type="date" name="fecha_vencimiento" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Registrar préstamo
            </button>
            <a href="index.php" class="btn btn-light border">Cancelar</a>
        </div>
    </form>
</div>

<?php cerrarLayoutModulo(); ?>
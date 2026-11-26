<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../includes/layout_modulos.php';

$db = $conexion ?? ($pdo ?? ($conn ?? null));
$mensaje = '';

$estudiantes = $db->query("SELECT id_estudiante, codigo_estudiante, nombres, apellidos FROM estudiante WHERE activo = 1 ORDER BY apellidos ASC")->fetchAll(PDO::FETCH_ASSOC);
$libros = $db->query("SELECT id_libro, codigo_libro, titulo FROM libro ORDER BY titulo ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_estudiante = (int)($_POST['id_estudiante'] ?? 0);
    $id_libro      = (int)($_POST['id_libro'] ?? 0);
    $observacion   = trim($_POST['observacion'] ?? '');

    if ($id_estudiante <= 0 || $id_libro <= 0) {
        $mensaje = 'Debes seleccionar un estudiante y un libro.';
    } else {
        $stmt = $db->prepare("INSERT INTO pedido (id_estudiante, id_libro, fecha_solicitud, estado, observacion)
                              VALUES (:id_estudiante, :id_libro, NOW(), 'PENDIENTE', :observacion)");
        $stmt->execute([
            ':id_estudiante' => $id_estudiante,
            ':id_libro'      => $id_libro,
            ':observacion'   => $observacion !== '' ? $observacion : null
        ]);
        header('Location: index.php?msg=registrada');
        exit;
    }
}

iniciarLayoutModulo('Nueva Solicitud', 'solicitudes');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="bt-page-title">Registrar Solicitud</h1>
        <p class="bt-page-subtitle mb-0">Crea un nuevo pedido de libro para un estudiante.</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?php if ($mensaje !== ''): ?>
    <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="bt-card" style="max-width: 720px;">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Estudiante</label>
            <select name="id_estudiante" class="form-select" required>
                <option value="">-- Seleccione un estudiante --</option>
                <?php foreach ($estudiantes as $est): ?>
                    <option value="<?= (int)$est['id_estudiante'] ?>">
                        <?= ht  mlspecialchars($est['codigo_estudiante'] . ' - ' . $est['apellidos'] . ', ' . $est['nombres']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Libro solicitado</label>
            <select name="id_libro" class="form-select" required>
                <option value="">-- Seleccione un libro --</option>
                <?php foreach ($libros as $lib): ?>
                    <option value="<?= (int)$lib['id_libro'] ?>">
                        <?= htmlspecialchars($lib['codigo_libro'] . ' - ' . $lib['titulo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Observación (opcional)</label>
            <textarea name="observacion" class="form-control" rows="3" placeholder="Ej. Solicitado para trabajo de investigación..."></textarea>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Guardar solicitud
            </button>
            <a href="index.php" class="btn btn-light border">Cancelar</a>
        </div>
    </form>
</div>

<?php cerrarLayoutModulo(); ?>
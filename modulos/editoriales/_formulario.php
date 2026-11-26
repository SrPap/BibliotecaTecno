<?php
// Formulario compartido de registrar y editar editorial.
// Variables esperadas: $conexion, $modo ('registrar' | 'editar'), $idEditar, $datos

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validar_csrf();

    $datos['nombre'] = trim($_POST['nombre'] ?? '');
    $datos['pais'] = trim($_POST['pais'] ?? '');

    if ($datos['nombre'] === '') {
        $errores[] = 'El campo nombre es obligatorio.';
    } elseif (mb_strlen($datos['nombre']) > 120) {
        $errores[] = 'El campo nombre no puede superar los 120 caracteres.';
    }

    if (mb_strlen($datos['pais']) > 80) {
        $errores[] = 'El campo país no puede superar los 80 caracteres.';
    }

    if (!$errores && valor_existe($conexion, 'editorial', 'nombre', $datos['nombre'], 'id_editorial', $idEditar)) {
        $errores[] = 'Ya existe una editorial con ese nombre.';
    }

    if (!$errores) {

        try {

            if ($modo === 'registrar') {

                $stmt = $conexion->prepare(
                    "INSERT INTO editorial (nombre, pais) VALUES (?, ?)"
                );
                $stmt->execute([
                    $datos['nombre'],
                    $datos['pais'] !== '' ? $datos['pais'] : null
                ]);

                flash('success', 'Editorial registrada correctamente.');

            } else {

                $stmt = $conexion->prepare(
                    "UPDATE editorial SET nombre = ?, pais = ? WHERE id_editorial = ?"
                );
                $stmt->execute([
                    $datos['nombre'],
                    $datos['pais'] !== '' ? $datos['pais'] : null,
                    $idEditar
                ]);

                flash('success', 'Editorial actualizada correctamente.');
            }

            redireccionar('index.php');

        } catch (PDOException $e) {
            error_log('Error al guardar editorial: ' . $e->getMessage());
            $errores[] = 'No se pudo guardar. Verifica que los datos no estén duplicados e inténtalo nuevamente.';
        }
    }
}

$esEdicion = ($modo === 'editar');
$tituloPagina = $esEdicion ? 'Editar editorial' : 'Registrar editorial';

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="mb-4">
            <h2 class="fw-bold mb-1">
                <i class="bi bi-building me-2"></i><?= limpiar($tituloPagina) ?>
            </h2>
            <p class="text-muted mb-0">
                <a href="index.php">Editoriales</a> / <?= $esEdicion ? 'Editar' : 'Registrar' ?>
            </p>
        </div>

        <?php if ($errores): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $error): ?>
                        <li><?= limpiar($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <form method="POST" action="" data-evitar-doble-envio novalidate>

                    <?= csrf_campo() ?>
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="id" value="<?= (int) $idEditar ?>">
                    <?php endif; ?>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="nombre" name="nombre" class="form-control"
                                maxlength="120" required
                                value="<?= limpiar($datos['nombre'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="pais" class="form-label">País</label>
                            <input type="text" id="pais" name="pais" class="form-control"
                                maxlength="80"
                                value="<?= limpiar($datos['pais'] ?? '') ?>">
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Guardar
                        </button>
                        <a href="index.php" class="btn btn-light">Cancelar</a>
                    </div>

                </form>

            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

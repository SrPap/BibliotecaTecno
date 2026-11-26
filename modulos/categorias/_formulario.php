<?php
// Formulario compartido de registrar y editar categoría.
// Variables esperadas: $conexion, $modo ('registrar' | 'editar'), $idEditar, $datos

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validar_csrf();

    $datos['nombre'] = trim($_POST['nombre'] ?? '');
    $datos['descripcion'] = trim($_POST['descripcion'] ?? '');
    $datos['activo'] = isset($_POST['activo']) ? 1 : 0;

    if ($datos['nombre'] === '') {
        $errores[] = 'El nombre es obligatorio.';
    } elseif (mb_strlen($datos['nombre']) > 80) {
        $errores[] = 'El nombre no puede superar los 80 caracteres.';
    }

    if (mb_strlen($datos['descripcion']) > 250) {
        $errores[] = 'La descripción no puede superar los 250 caracteres.';
    }

    if (!$errores && valor_existe($conexion, 'categoria', 'nombre', $datos['nombre'], 'id_categoria', $idEditar)) {
        $errores[] = 'Ya existe una categoría con ese nombre.';
    }

    if (!$errores) {

        try {

            if ($modo === 'registrar') {

                $stmt = $conexion->prepare(
                    "INSERT INTO categoria (nombre, descripcion, activo) VALUES (?, ?, ?)"
                );
                $stmt->execute([
                    $datos['nombre'],
                    $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                    $datos['activo']
                ]);

                flash('success', 'Categoría registrada correctamente.');

            } else {

                $stmt = $conexion->prepare(
                    "UPDATE categoria SET nombre = ?, descripcion = ?, activo = ? WHERE id_categoria = ?"
                );
                $stmt->execute([
                    $datos['nombre'],
                    $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                    $datos['activo'],
                    $idEditar
                ]);

                flash('success', 'Categoría actualizada correctamente.');
            }

            redireccionar('index.php');

        } catch (PDOException $e) {
            error_log('Error al guardar categoría: ' . $e->getMessage());
            $errores[] = 'No se pudo guardar la categoría. Inténtalo nuevamente.';
        }
    }
}

$esEdicion = ($modo === 'editar');
$tituloPagina = $esEdicion ? 'Editar categoría' : 'Registrar categoría';

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="mb-4">
            <h2 class="fw-bold mb-1">
                <i class="bi bi-grid me-2"></i><?= limpiar($tituloPagina) ?>
            </h2>
            <p class="text-muted mb-0">
                <a href="index.php">Categorías</a> / <?= $esEdicion ? 'Editar' : 'Registrar' ?>
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
                                maxlength="80" required
                                value="<?= limpiar($datos['nombre']) ?>">
                        </div>

                        <div class="col-md-12">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea id="descripcion" name="descripcion" class="form-control"
                                rows="3" maxlength="250"><?= limpiar($datos['descripcion']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    id="activo" name="activo" value="1"
                                    <?= !empty($datos['activo']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="activo">
                                    Categoría activa (disponible al registrar libros)
                                </label>
                            </div>
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

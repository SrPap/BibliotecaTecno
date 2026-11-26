<?php
// Formulario compartido de registrar y editar ejemplar.
// Variables esperadas: $conexion, $modo ('registrar' | 'editar'), $idEditar, $datos

// Los estados SOLICITADO y PRESTADO los controla el flujo de pedidos y préstamos,
// por eso no se pueden asignar ni cambiar a mano desde aquí.
$estadosManuales = ['DISPONIBLE', 'MANTENIMIENTO', 'PERDIDO'];
$estadoOriginal = $datos['estado'];
$estadoBloqueado = ($modo === 'editar' && in_array($estadoOriginal, ['SOLICITADO', 'PRESTADO'], true));

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validar_csrf();

    $datos['codigo_ejemplar'] = trim($_POST['codigo_ejemplar'] ?? '');
    $datos['id_libro'] = (int) ($_POST['id_libro'] ?? 0);
    $datos['ubicacion'] = trim($_POST['ubicacion'] ?? '');

    // Si el estado está bloqueado se conserva el original
    $datos['estado'] = $estadoBloqueado ? $estadoOriginal : trim($_POST['estado'] ?? '');

    if ($datos['codigo_ejemplar'] === '') {
        $errores[] = 'El código del ejemplar es obligatorio.';
    } elseif (mb_strlen($datos['codigo_ejemplar']) > 20) {
        $errores[] = 'El código no puede superar los 20 caracteres.';
    } elseif (valor_existe($conexion, 'ejemplar', 'codigo_ejemplar', $datos['codigo_ejemplar'], 'id_ejemplar', $idEditar)) {
        $errores[] = 'Ya existe un ejemplar con ese código.';
    }

    if ($datos['id_libro'] <= 0 || !valor_existe($conexion, 'libro', 'id_libro', $datos['id_libro'], 'id_libro')) {
        $errores[] = 'Selecciona un libro válido.';
    }

    if (!$estadoBloqueado && !in_array($datos['estado'], $estadosManuales, true)) {
        $errores[] = 'Selecciona un estado válido.';
    }

    if (mb_strlen($datos['ubicacion']) > 80) {
        $errores[] = 'La ubicación no puede superar los 80 caracteres.';
    }

    if (!$errores) {

        $ubicacion = $datos['ubicacion'] !== '' ? $datos['ubicacion'] : null;

        try {

            if ($modo === 'registrar') {

                $stmt = $conexion->prepare(
                    "INSERT INTO ejemplar (codigo_ejemplar, id_libro, estado, ubicacion) VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([$datos['codigo_ejemplar'], $datos['id_libro'], $datos['estado'], $ubicacion]);

                flash('success', 'Ejemplar registrado correctamente.');

            } else {

                $stmt = $conexion->prepare(
                    "UPDATE ejemplar SET codigo_ejemplar = ?, id_libro = ?, estado = ?, ubicacion = ? WHERE id_ejemplar = ?"
                );
                $stmt->execute([$datos['codigo_ejemplar'], $datos['id_libro'], $datos['estado'], $ubicacion, $idEditar]);

                flash('success', 'Ejemplar actualizado correctamente.');
            }

            redireccionar('index.php' . ($modo === 'registrar' ? '?libro=' . $datos['id_libro'] : ''));

        } catch (PDOException $e) {
            error_log('Error al guardar ejemplar: ' . $e->getMessage());
            $errores[] = 'No se pudo guardar el ejemplar. Verifica que el código no esté duplicado.';
        }
    }
}

$libros = $conexion->query("SELECT id_libro, codigo_libro, titulo FROM libro ORDER BY titulo")->fetchAll();

$esEdicion = ($modo === 'editar');
$tituloPagina = $esEdicion ? 'Editar ejemplar' : 'Registrar ejemplar';

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="mb-4">
            <h2 class="fw-bold mb-1">
                <i class="bi bi-journal-bookmark me-2"></i><?= limpiar($tituloPagina) ?>
            </h2>
            <p class="text-muted mb-0">
                <a href="index.php">Ejemplares</a> / <?= $esEdicion ? 'Editar' : 'Registrar' ?>
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

        <?php if (!$libros): ?>
            <div class="alert alert-warning">
                Primero debes <a href="../libros/registrar.php">registrar un libro</a>
                para poder crear ejemplares.
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

                        <div class="col-md-3">
                            <label for="codigo_ejemplar" class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" id="codigo_ejemplar" name="codigo_ejemplar" class="form-control"
                                maxlength="20" required
                                value="<?= limpiar($datos['codigo_ejemplar']) ?>">
                        </div>

                        <div class="col-md-9">
                            <label for="id_libro" class="form-label">Libro <span class="text-danger">*</span></label>
                            <select id="id_libro" name="id_libro" class="form-select" required>
                                <option value="">Selecciona...</option>
                                <?php foreach ($libros as $l): ?>
                                    <option value="<?= (int) $l['id_libro'] ?>"
                                        <?= (int) $datos['id_libro'] === (int) $l['id_libro'] ? 'selected' : '' ?>>
                                        <?= limpiar($l['titulo'] . ' (' . $l['codigo_libro'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado</label>

                            <?php if ($estadoBloqueado): ?>
                                <input type="text" class="form-control" disabled
                                    value="<?= limpiar(ucfirst(strtolower($estadoOriginal))) ?>">
                                <div class="form-text">
                                    Este estado lo controlan los pedidos y préstamos; cambiará al devolver el libro.
                                </div>
                            <?php else: ?>
                                <select id="estado" name="estado" class="form-select">
                                    <?php foreach ($estadosManuales as $e): ?>
                                        <option value="<?= $e ?>" <?= $datos['estado'] === $e ? 'selected' : '' ?>>
                                            <?= ucfirst(strtolower($e)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-8">
                            <label for="ubicacion" class="form-label">Ubicación</label>
                            <input type="text" id="ubicacion" name="ubicacion" class="form-control"
                                maxlength="80" placeholder="Ej.: Estante A - Nivel 2"
                                value="<?= limpiar($datos['ubicacion'] ?? '') ?>">
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" <?= !$libros ? 'disabled' : '' ?>>
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

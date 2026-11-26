<?php
// Formulario compartido de registrar y editar autor.
// Variables esperadas: $conexion, $modo ('registrar' | 'editar'), $idEditar, $datos

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validar_csrf();

    $datos['nombres'] = trim($_POST['nombres'] ?? '');
    $datos['apellidos'] = trim($_POST['apellidos'] ?? '');

    if ($datos['nombres'] === '') {
        $errores[] = 'El campo nombres es obligatorio.';
    } elseif (mb_strlen($datos['nombres']) > 120) {
        $errores[] = 'El campo nombres no puede superar los 120 caracteres.';
    }

    if ($datos['apellidos'] === '') {
        $errores[] = 'El campo apellidos es obligatorio.';
    } elseif (mb_strlen($datos['apellidos']) > 120) {
        $errores[] = 'El campo apellidos no puede superar los 120 caracteres.';
    }

    if (!$errores) {
        $sql = "SELECT COUNT(*) FROM autor WHERE nombres = ? AND apellidos = ?";
        $parametros = [$datos['nombres'], $datos['apellidos']];

        if ($idEditar !== null) {
            $sql .= " AND id_autor <> ?";
            $parametros[] = $idEditar;
        }

        $stmt = $conexion->prepare($sql);
        $stmt->execute($parametros);

        if ((int) $stmt->fetchColumn() > 0) {
            $errores[] = 'Ya existe un autor con esos nombres y apellidos.';
        }
    }

    if (!$errores) {

        try {

            if ($modo === 'registrar') {

                $stmt = $conexion->prepare(
                    "INSERT INTO autor (nombres, apellidos) VALUES (?, ?)"
                );
                $stmt->execute([
                    $datos['nombres'],
                    $datos['apellidos']
                ]);

                flash('success', 'Autor registrado correctamente.');

            } else {

                $stmt = $conexion->prepare(
                    "UPDATE autor SET nombres = ?, apellidos = ? WHERE id_autor = ?"
                );
                $stmt->execute([
                    $datos['nombres'],
                    $datos['apellidos'],
                    $idEditar
                ]);

                flash('success', 'Autor actualizado correctamente.');
            }

            redireccionar('index.php');

        } catch (PDOException $e) {
            error_log('Error al guardar autor: ' . $e->getMessage());
            $errores[] = 'No se pudo guardar. Verifica que los datos no estén duplicados e inténtalo nuevamente.';
        }
    }
}

$esEdicion = ($modo === 'editar');
$tituloPagina = $esEdicion ? 'Editar autor' : 'Registrar autor';

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="mb-4">
            <h2 class="fw-bold mb-1">
                <i class="bi bi-people me-2"></i><?= limpiar($tituloPagina) ?>
            </h2>
            <p class="text-muted mb-0">
                <a href="index.php">Autores</a> / <?= $esEdicion ? 'Editar' : 'Registrar' ?>
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
                            <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" id="nombres" name="nombres" class="form-control"
                                maxlength="120" required
                                value="<?= limpiar($datos['nombres'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" id="apellidos" name="apellidos" class="form-control"
                                maxlength="120" required
                                value="<?= limpiar($datos['apellidos'] ?? '') ?>">
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

<?php
// Formulario compartido de registrar y editar libro.
// Variables esperadas: $conexion, $modo ('registrar' | 'editar'), $idEditar,
//                      $datos, $autoresSeleccionados

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validar_csrf();

    $datos['codigo_libro'] = trim($_POST['codigo_libro'] ?? '');
    $datos['titulo'] = trim($_POST['titulo'] ?? '');
    $datos['isbn'] = trim($_POST['isbn'] ?? '');
    $datos['anio_publicacion'] = trim($_POST['anio_publicacion'] ?? '');
    $datos['id_categoria'] = (int) ($_POST['id_categoria'] ?? 0);
    $datos['id_editorial'] = (int) ($_POST['id_editorial'] ?? 0);

    $autoresSeleccionados = array_values(array_unique(array_filter(
        array_map('intval', (array) ($_POST['autores'] ?? [])),
        fn($id) => $id > 0
    )));

    // Código
    if ($datos['codigo_libro'] === '') {
        $errores[] = 'El código del libro es obligatorio.';
    } elseif (mb_strlen($datos['codigo_libro']) > 20) {
        $errores[] = 'El código no puede superar los 20 caracteres.';
    } elseif (valor_existe($conexion, 'libro', 'codigo_libro', $datos['codigo_libro'], 'id_libro', $idEditar)) {
        $errores[] = 'Ya existe un libro con ese código.';
    }

    // Título
    if ($datos['titulo'] === '') {
        $errores[] = 'El título es obligatorio.';
    } elseif (mb_strlen($datos['titulo']) > 200) {
        $errores[] = 'El título no puede superar los 200 caracteres.';
    }

    // ISBN (opcional, pero único)
    if ($datos['isbn'] !== '') {
        if (mb_strlen($datos['isbn']) > 20) {
            $errores[] = 'El ISBN no puede superar los 20 caracteres.';
        } elseif (valor_existe($conexion, 'libro', 'isbn', $datos['isbn'], 'id_libro', $idEditar)) {
            $errores[] = 'Ya existe un libro con ese ISBN.';
        }
    }

    // Año de publicación (opcional)
    if ($datos['anio_publicacion'] !== '') {
        if (!ctype_digit($datos['anio_publicacion'])
            || (int) $datos['anio_publicacion'] < 1450
            || (int) $datos['anio_publicacion'] > 2100) {
            $errores[] = 'El año de publicación debe estar entre 1450 y 2100.';
        }
    }

    // Categoría (obligatoria; debe estar activa o ser la que ya tenía el libro)
    if ($datos['id_categoria'] <= 0) {
        $errores[] = 'Selecciona una categoría.';
    } else {
        $stmt = $conexion->prepare(
            "SELECT COUNT(*) FROM categoria
             WHERE id_categoria = ?
               AND (activo = 1 OR id_categoria = (SELECT id_categoria FROM libro WHERE id_libro = ?))"
        );
        $stmt->execute([$datos['id_categoria'], (int) $idEditar]);

        if ((int) $stmt->fetchColumn() === 0) {
            $errores[] = 'La categoría seleccionada no es válida o está inactiva.';
        }
    }

    // Editorial (opcional)
    if ($datos['id_editorial'] > 0 && !valor_existe($conexion, 'editorial', 'id_editorial', $datos['id_editorial'], 'id_editorial')) {
        $errores[] = 'La editorial seleccionada no existe.';
    }

    // Autores (opcionales, pero todos deben existir)
    if ($autoresSeleccionados) {
        $marcas = implode(',', array_fill(0, count($autoresSeleccionados), '?'));
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM autor WHERE id_autor IN ($marcas)");
        $stmt->execute($autoresSeleccionados);

        if ((int) $stmt->fetchColumn() !== count($autoresSeleccionados)) {
            $errores[] = 'Alguno de los autores seleccionados no existe.';
        }
    }

    if (!$errores) {

        $isbn = $datos['isbn'] !== '' ? $datos['isbn'] : null;
        $anio = $datos['anio_publicacion'] !== '' ? (int) $datos['anio_publicacion'] : null;
        $editorial = $datos['id_editorial'] > 0 ? $datos['id_editorial'] : null;

        try {

            $conexion->beginTransaction();

            if ($modo === 'registrar') {

                $stmt = $conexion->prepare(
                    "INSERT INTO libro (codigo_libro, titulo, isbn, anio_publicacion, id_categoria, id_editorial)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $datos['codigo_libro'], $datos['titulo'], $isbn, $anio, $datos['id_categoria'], $editorial
                ]);

                $idLibro = (int) $conexion->lastInsertId();

            } else {

                $stmt = $conexion->prepare(
                    "UPDATE libro
                     SET codigo_libro = ?, titulo = ?, isbn = ?, anio_publicacion = ?, id_categoria = ?, id_editorial = ?
                     WHERE id_libro = ?"
                );
                $stmt->execute([
                    $datos['codigo_libro'], $datos['titulo'], $isbn, $anio, $datos['id_categoria'], $editorial, $idEditar
                ]);

                $idLibro = $idEditar;

                $stmt = $conexion->prepare("DELETE FROM libro_autor WHERE id_libro = ?");
                $stmt->execute([$idLibro]);
            }

            $stmt = $conexion->prepare("INSERT INTO libro_autor (id_libro, id_autor) VALUES (?, ?)");
            foreach ($autoresSeleccionados as $idAutor) {
                $stmt->execute([$idLibro, $idAutor]);
            }

            $conexion->commit();

            flash('success', $modo === 'registrar'
                ? 'Libro registrado correctamente.'
                : 'Libro actualizado correctamente.');

            redireccionar('index.php');

        } catch (PDOException $e) {

            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            error_log('Error al guardar libro: ' . $e->getMessage());
            $errores[] = 'No se pudo guardar el libro. Verifica que el código o el ISBN no estén duplicados.';
        }
    }
}

// Listas para los campos de selección
$categoriaActual = (int) ($datos['id_categoria'] ?: 0);

$stmt = $conexion->prepare(
    "SELECT id_categoria, nombre FROM categoria
     WHERE activo = 1 OR id_categoria = ?
     ORDER BY nombre"
);
$stmt->execute([$categoriaActual]);
$categorias = $stmt->fetchAll();

$editoriales = $conexion->query("SELECT id_editorial, nombre FROM editorial ORDER BY nombre")->fetchAll();
$autores = $conexion->query("SELECT id_autor, nombres, apellidos FROM autor ORDER BY apellidos, nombres")->fetchAll();

$esEdicion = ($modo === 'editar');
$tituloPagina = $esEdicion ? 'Editar libro' : 'Registrar libro';

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="mb-4">
            <h2 class="fw-bold mb-1">
                <i class="bi bi-book me-2"></i><?= limpiar($tituloPagina) ?>
            </h2>
            <p class="text-muted mb-0">
                <a href="index.php">Libros</a> / <?= $esEdicion ? 'Editar' : 'Registrar' ?>
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

                        <div class="col-md-3">
                            <label for="codigo_libro" class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" id="codigo_libro" name="codigo_libro" class="form-control"
                                maxlength="20" required
                                value="<?= limpiar($datos['codigo_libro']) ?>">
                        </div>

                        <div class="col-md-9">
                            <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" id="titulo" name="titulo" class="form-control"
                                maxlength="200" required
                                value="<?= limpiar($datos['titulo']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="isbn" class="form-label">ISBN</label>
                            <input type="text" id="isbn" name="isbn" class="form-control"
                                maxlength="20"
                                value="<?= limpiar($datos['isbn'] ?? '') ?>">
                        </div>

                        <div class="col-md-2">
                            <label for="anio_publicacion" class="form-label">Año</label>
                            <input type="number" id="anio_publicacion" name="anio_publicacion" class="form-control"
                                min="1450" max="2100"
                                value="<?= limpiar($datos['anio_publicacion'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="id_categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select id="id_categoria" name="id_categoria" class="form-select" required>
                                <option value="">Selecciona...</option>
                                <?php foreach ($categorias as $c): ?>
                                    <option value="<?= (int) $c['id_categoria'] ?>"
                                        <?= (int) $datos['id_categoria'] === (int) $c['id_categoria'] ? 'selected' : '' ?>>
                                        <?= limpiar($c['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="id_editorial" class="form-label">Editorial</label>
                            <select id="id_editorial" name="id_editorial" class="form-select">
                                <option value="">Sin editorial</option>
                                <?php foreach ($editoriales as $e): ?>
                                    <option value="<?= (int) $e['id_editorial'] ?>"
                                        <?= (int) ($datos['id_editorial'] ?? 0) === (int) $e['id_editorial'] ? 'selected' : '' ?>>
                                        <?= limpiar($e['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Autores</label>

                            <?php if ($autores): ?>

                                <input type="text" class="form-control form-control-sm mb-2"
                                    placeholder="Filtrar autores..."
                                    data-filtro-lista="#listaAutores">

                                <div id="listaAutores" class="border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                                    <?php foreach ($autores as $a): ?>
                                        <?php $nombreAutor = $a['apellidos'] . ', ' . $a['nombres']; ?>
                                        <div class="form-check"
                                            data-texto="<?= limpiar(mb_strtolower($nombreAutor . ' ' . $a['nombres'] . ' ' . $a['apellidos'])) ?>">
                                            <input class="form-check-input" type="checkbox"
                                                name="autores[]" value="<?= (int) $a['id_autor'] ?>"
                                                id="autor<?= (int) $a['id_autor'] ?>"
                                                <?= in_array((int) $a['id_autor'], $autoresSeleccionados, true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="autor<?= (int) $a['id_autor'] ?>">
                                                <?= limpiar($nombreAutor) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="form-text">
                                    Marca uno o varios autores.
                                    ¿Falta alguno? <a href="../autores/registrar.php" target="_blank">Regístralo aquí</a>
                                    y recarga esta página.
                                </div>

                            <?php else: ?>

                                <div class="text-muted">
                                    Aún no hay autores registrados.
                                    <a href="../autores/registrar.php">Registrar autor</a>
                                </div>

                            <?php endif; ?>
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

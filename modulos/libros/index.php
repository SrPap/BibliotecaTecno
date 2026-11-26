<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_sesion.php';

// Administradores y estudiantes consultan el catálogo; solo el administrador gestiona
$rol = obtener_rol();
$esAdmin = ($rol === 'ADMINISTRADOR');

$busqueda = trim($_GET['buscar'] ?? '');
$porPagina = 10;
$pagina = pagina_actual();

$termino = '%' . $busqueda . '%';

// La búsqueda por autor usa EXISTS para no recortar la lista de autores de cada libro
$where = "
    WHERE l.titulo LIKE ?
        OR l.codigo_libro LIKE ?
        OR l.isbn LIKE ?
        OR c.nombre LIKE ?
        OR e.nombre LIKE ?
        OR EXISTS (
            SELECT 1
            FROM libro_autor la2
            INNER JOIN autor a2 ON a2.id_autor = la2.id_autor
            WHERE la2.id_libro = l.id_libro
                AND CONCAT(a2.nombres, ' ', a2.apellidos) LIKE ?
        )
";
$parametros = array_fill(0, 6, $termino);

$from = "
    FROM libro l
    INNER JOIN categoria c ON l.id_categoria = c.id_categoria
    LEFT JOIN editorial e ON l.id_editorial = e.id_editorial
";

$stmt = $conexion->prepare("SELECT COUNT(*) $from $where");
$stmt->execute($parametros);
$totalLibros = (int) $stmt->fetchColumn();

$totalPaginas = max(1, (int) ceil($totalLibros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "
    SELECT
        l.id_libro,
        l.codigo_libro,
        l.titulo,
        l.isbn,
        l.anio_publicacion,
        c.nombre AS categoria,
        e.nombre AS editorial,
        (
            SELECT GROUP_CONCAT(CONCAT(a.nombres, ' ', a.apellidos) ORDER BY a.apellidos SEPARATOR ', ')
            FROM libro_autor la
            INNER JOIN autor a ON a.id_autor = la.id_autor
            WHERE la.id_libro = l.id_libro
        ) AS autores,
        (SELECT COUNT(*) FROM ejemplar ej WHERE ej.id_libro = l.id_libro) AS total_ejemplares,
        (SELECT COUNT(*) FROM ejemplar ej WHERE ej.id_libro = l.id_libro AND ej.estado = 'DISPONIBLE') AS ejemplares_disponibles
    $from
    $where
    ORDER BY l.titulo ASC
    LIMIT $porPagina OFFSET $offset
";

$stmt = $conexion->prepare($sql);
$stmt->execute($parametros);
$libros = $stmt->fetchAll();

$tituloPagina = 'Libros';

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-book me-2"></i>Catálogo de libros</h2>
                <p class="text-muted mb-0">Consulta y administra los libros de la biblioteca.</p>
            </div>

            <?php if ($esAdmin): ?>
                <a href="registrar.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Registrar libro
                </a>
            <?php endif; ?>
        </div>

        <?php mostrar_flash(); ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="index.php">
                    <div class="row g-2">
                        <div class="col-md-10">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="buscar" class="form-control"
                                    placeholder="Buscar por título, código, ISBN, autor, categoría o editorial..."
                                    value="<?= limpiar($busqueda) ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <strong>Libros registrados</strong>
                <span class="badge bg-primary ms-2"><?= $totalLibros ?></span>
            </div>

            <div class="card-body">

                <?php if ($libros): ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Título</th>
                                    <th>Autor(es)</th>
                                    <th>Categoría</th>
                                    <th>Editorial</th>
                                    <th class="text-center">Ejemplares</th>
                                    <th>Disponibilidad</th>
                                    <?php if ($esAdmin): ?>
                                        <th>Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($libros as $libro): ?>
                                    <tr>
                                        <td><?= limpiar($libro['codigo_libro']) ?></td>

                                        <td>
                                            <strong><?= limpiar($libro['titulo']) ?></strong>
                                            <?php if (!empty($libro['anio_publicacion'])): ?>
                                                <small class="text-muted">(<?= (int) $libro['anio_publicacion'] ?>)</small>
                                            <?php endif; ?>
                                            <?php if (!empty($libro['isbn'])): ?>
                                                <br>
                                                <small class="text-muted">ISBN: <?= limpiar($libro['isbn']) ?></small>
                                            <?php endif; ?>
                                        </td>

                                        <td><?= limpiar($libro['autores'] ?: 'Sin autor registrado') ?></td>
                                        <td><?= limpiar($libro['categoria']) ?></td>
                                        <td><?= limpiar($libro['editorial'] ?: 'Sin editorial') ?></td>

                                        <td class="text-center">
                                            <?php if ($esAdmin && (int) $libro['total_ejemplares'] > 0): ?>
                                                <a href="../ejemplares/index.php?libro=<?= (int) $libro['id_libro'] ?>"
                                                    title="Ver ejemplares de este libro">
                                                    <?= (int) $libro['total_ejemplares'] ?>
                                                </a>
                                            <?php else: ?>
                                                <?= (int) $libro['total_ejemplares'] ?>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if ($libro['ejemplares_disponibles'] > 0): ?>
                                                <span class="badge bg-success">
                                                    <?= (int) $libro['ejemplares_disponibles'] ?> disponibles
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No disponible</span>
                                            <?php endif; ?>
                                        </td>

                                        <?php if ($esAdmin): ?>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="editar.php?id=<?= (int) $libro['id_libro'] ?>"
                                                        class="btn btn-sm btn-warning" title="Editar libro">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        title="Eliminar libro"
                                                        data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                                        data-id="<?= (int) $libro['id_libro'] ?>"
                                                        data-nombre="<?= limpiar($libro['titulo']) ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php render_paginacion($pagina, $totalPaginas, ['buscar' => $busqueda]); ?>

                <?php else: ?>

                    <div class="text-center py-5">
                        <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No se encontraron libros</h5>
                        <p class="text-muted">Intenta con otro término de búsqueda.</p>
                    </div>

                <?php endif; ?>

            </div>
        </div>

    </div>
</main>

<?php if ($esAdmin): ?>
    <?php include __DIR__ . '/../../includes/modal_eliminar.php'; ?>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

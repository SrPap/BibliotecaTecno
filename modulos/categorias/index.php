<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

$busqueda = trim($_GET['buscar'] ?? '');
$porPagina = 10;
$pagina = pagina_actual();

$where = "WHERE c.nombre LIKE ? OR c.descripcion LIKE ?";
$termino = '%' . $busqueda . '%';

$stmt = $conexion->prepare("SELECT COUNT(*) FROM categoria c $where");
$stmt->execute([$termino, $termino]);
$totalRegistros = (int) $stmt->fetchColumn();

$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "
    SELECT
        c.id_categoria,
        c.nombre,
        c.descripcion,
        c.activo,
        (SELECT COUNT(*) FROM libro l WHERE l.id_categoria = c.id_categoria) AS total_libros
    FROM categoria c
    $where
    ORDER BY c.nombre ASC
    LIMIT $porPagina OFFSET $offset
";

$stmt = $conexion->prepare($sql);
$stmt->execute([$termino, $termino]);
$categorias = $stmt->fetchAll();

$tituloPagina = 'Categorías';

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-grid me-2"></i>Categorías</h2>
                <p class="text-muted mb-0">Clasifica los libros de la biblioteca por temática.</p>
            </div>

            <a href="registrar.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Registrar categoría
            </a>
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
                                    placeholder="Buscar por nombre o descripción..."
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
                <strong>Categorías registradas</strong>
                <span class="badge bg-primary ms-2"><?= $totalRegistros ?></span>
            </div>

            <div class="card-body">

                <?php if ($categorias): ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Libros</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categorias as $c): ?>
                                    <tr>
                                        <td><strong><?= limpiar($c['nombre']) ?></strong></td>
                                        <td><?= limpiar($c['descripcion'] ?: '—') ?></td>
                                        <td class="text-center"><?= (int) $c['total_libros'] ?></td>
                                        <td>
                                            <?php if ($c['activo']): ?>
                                                <span class="badge bg-success">Activa</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactiva</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="editar.php?id=<?= (int) $c['id_categoria'] ?>"
                                                    class="btn btn-sm btn-warning" title="Editar categoría">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    title="Eliminar categoría"
                                                    data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                                    data-id="<?= (int) $c['id_categoria'] ?>"
                                                    data-nombre="<?= limpiar($c['nombre']) ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php render_paginacion($pagina, $totalPaginas, ['buscar' => $busqueda]); ?>

                <?php else: ?>

                    <div class="text-center py-5">
                        <i class="bi bi-grid text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No se encontraron categorías</h5>
                        <p class="text-muted">Registra una nueva o prueba con otro término de búsqueda.</p>
                    </div>

                <?php endif; ?>

            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/../../includes/modal_eliminar.php'; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

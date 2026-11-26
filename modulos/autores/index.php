<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

$busqueda = trim($_GET['buscar'] ?? '');
$porPagina = 10;
$pagina = pagina_actual();

$where = "WHERE t.nombres LIKE ? OR t.apellidos LIKE ? OR CONCAT(t.nombres, ' ', t.apellidos) LIKE ?";
$termino = '%' . $busqueda . '%';
$parametros = array_fill(0, 3, $termino);

$stmt = $conexion->prepare("SELECT COUNT(*) FROM autor t $where");
$stmt->execute($parametros);
$totalRegistros = (int) $stmt->fetchColumn();

$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "
    SELECT
        t.id_autor, t.nombres, t.apellidos,
        (SELECT COUNT(*) FROM libro_autor la WHERE la.id_autor = t.id_autor) AS total_libros
    FROM autor t
    $where
    ORDER BY t.apellidos ASC, t.nombres ASC
    LIMIT $porPagina OFFSET $offset
";

$stmt = $conexion->prepare($sql);
$stmt->execute($parametros);
$registros = $stmt->fetchAll();

$tituloPagina = 'Autores';

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-people me-2"></i>Autores</h2>
                <p class="text-muted mb-0">Administra los autores de los libros.</p>
            </div>

            <a href="registrar.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Registrar autor
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
                                    placeholder="Buscar por nombres o apellidos..."
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
                <strong>Autores registrados</strong>
                <span class="badge bg-primary ms-2"><?= $totalRegistros ?></span>
            </div>

            <div class="card-body">

                <?php if ($registros): ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Apellidos</th>
                                    <th>Nombres</th>
                                    <th class="text-center">Libros</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registros as $r): ?>
                                    <tr>
                                        <td><strong><?= limpiar($r['apellidos']) ?></strong></td>
                                        <td><?= limpiar($r['nombres']) ?></td>
                                        <td class="text-center"><?= (int) $r['total_libros'] ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="editar.php?id=<?= (int) $r['id_autor'] ?>"
                                                    class="btn btn-sm btn-warning" title="Editar autor">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    title="Eliminar autor"
                                                    data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                                    data-id="<?= (int) $r['id_autor'] ?>"
                                                    data-nombre="<?= limpiar($r['nombres'] . ' ' . $r['apellidos']) ?>">
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
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No se encontraron autores</h5>
                        <p class="text-muted">Registra uno nuevo o prueba con otro término de búsqueda.</p>
                    </div>

                <?php endif; ?>

            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/../../includes/modal_eliminar.php'; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

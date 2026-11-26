<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../../includes/verificar_rol.php';

exigir_rol(['ADMINISTRADOR']);

$estadosValidos = ['DISPONIBLE', 'SOLICITADO', 'PRESTADO', 'MANTENIMIENTO', 'PERDIDO'];
$clasesEstado = [
    'DISPONIBLE' => 'bg-success',
    'SOLICITADO' => 'bg-info',
    'PRESTADO' => 'bg-primary',
    'MANTENIMIENTO' => 'bg-warning text-dark',
    'PERDIDO' => 'bg-danger'
];

$busqueda = trim($_GET['buscar'] ?? '');
$estado = $_GET['estado'] ?? '';
$idLibro = (int) ($_GET['libro'] ?? 0);

if (!in_array($estado, $estadosValidos, true)) {
    $estado = '';
}

$porPagina = 10;
$pagina = pagina_actual();
$termino = '%' . $busqueda . '%';

$condiciones = ["(ej.codigo_ejemplar LIKE ? OR l.titulo LIKE ? OR l.codigo_libro LIKE ? OR ej.ubicacion LIKE ?)"];
$parametros = [$termino, $termino, $termino, $termino];

if ($estado !== '') {
    $condiciones[] = "ej.estado = ?";
    $parametros[] = $estado;
}

if ($idLibro > 0) {
    $condiciones[] = "ej.id_libro = ?";
    $parametros[] = $idLibro;
}

$where = 'WHERE ' . implode(' AND ', $condiciones);

$from = "FROM ejemplar ej INNER JOIN libro l ON l.id_libro = ej.id_libro";

$stmt = $conexion->prepare("SELECT COUNT(*) $from $where");
$stmt->execute($parametros);
$totalRegistros = (int) $stmt->fetchColumn();

$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$stmt = $conexion->prepare("
    SELECT ej.id_ejemplar, ej.codigo_ejemplar, ej.estado, ej.ubicacion,
           l.titulo, l.codigo_libro
    $from
    $where
    ORDER BY l.titulo ASC, ej.codigo_ejemplar ASC
    LIMIT $porPagina OFFSET $offset
");
$stmt->execute($parametros);
$ejemplares = $stmt->fetchAll();

// Si se filtra por libro, mostrar su título
$tituloFiltro = '';
if ($idLibro > 0) {
    $stmt = $conexion->prepare("SELECT titulo FROM libro WHERE id_libro = ?");
    $stmt->execute([$idLibro]);
    $tituloFiltro = (string) $stmt->fetchColumn();
}

$filtros = ['buscar' => $busqueda, 'estado' => $estado, 'libro' => $idLibro ?: ''];

$tituloPagina = 'Ejemplares';

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-journal-bookmark me-2"></i>Ejemplares</h2>
                <p class="text-muted mb-0">Copias físicas de cada libro de la biblioteca.</p>
            </div>

            <a href="registrar.php<?= $idLibro > 0 ? '?libro=' . $idLibro : '' ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Registrar ejemplar
            </a>
        </div>

        <?php mostrar_flash(); ?>

        <?php if ($tituloFiltro !== ''): ?>
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span>Mostrando ejemplares de: <strong><?= limpiar($tituloFiltro) ?></strong></span>
                <a href="index.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="index.php">
                    <?php if ($idLibro > 0): ?>
                        <input type="hidden" name="libro" value="<?= $idLibro ?>">
                    <?php endif; ?>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="buscar" class="form-control"
                                    placeholder="Buscar por código, libro o ubicación..."
                                    value="<?= limpiar($busqueda) ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <select name="estado" class="form-select">
                                <option value="">Todos los estados</option>
                                <?php foreach ($estadosValidos as $e): ?>
                                    <option value="<?= $e ?>" <?= $estado === $e ? 'selected' : '' ?>>
                                        <?= ucfirst(strtolower($e)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <strong>Ejemplares registrados</strong>
                <span class="badge bg-primary ms-2"><?= $totalRegistros ?></span>
            </div>

            <div class="card-body">

                <?php if ($ejemplares): ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Libro</th>
                                    <th>Ubicación</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ejemplares as $ej): ?>
                                    <tr>
                                        <td><strong><?= limpiar($ej['codigo_ejemplar']) ?></strong></td>
                                        <td>
                                            <?= limpiar($ej['titulo']) ?>
                                            <br><small class="text-muted"><?= limpiar($ej['codigo_libro']) ?></small>
                                        </td>
                                        <td><?= limpiar($ej['ubicacion'] ?: '—') ?></td>
                                        <td>
                                            <span class="badge <?= $clasesEstado[$ej['estado']] ?? 'bg-secondary' ?>">
                                                <?= limpiar(ucfirst(strtolower($ej['estado']))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="editar.php?id=<?= (int) $ej['id_ejemplar'] ?>"
                                                    class="btn btn-sm btn-warning" title="Editar ejemplar">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    title="Eliminar ejemplar"
                                                    data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                                    data-id="<?= (int) $ej['id_ejemplar'] ?>"
                                                    data-nombre="<?= limpiar($ej['codigo_ejemplar']) ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php render_paginacion($pagina, $totalPaginas, $filtros); ?>

                <?php else: ?>

                    <div class="text-center py-5">
                        <i class="bi bi-journal-bookmark text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No se encontraron ejemplares</h5>
                        <p class="text-muted">Registra uno nuevo o cambia los filtros.</p>
                    </div>

                <?php endif; ?>

            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/../../includes/modal_eliminar.php'; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../../includes/verificar_sesion.php';
require_once __DIR__ . '/../../config/conexion.php';

$busqueda = trim($_GET['buscar'] ?? '');

if ($busqueda !== '') {
    $like = '%' . $busqueda . '%';
    $sql = "SELECT * FROM estudiante
            WHERE codigo_estudiante LIKE :q1
               OR nombres LIKE :q2
               OR apellidos LIKE :q3
               OR dni LIKE :q4
               OR carrera LIKE :q5
            ORDER BY id_estudiante DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':q1' => $like,
        ':q2' => $like,
        ':q3' => $like,
        ':q4' => $like,
        ':q5' => $like,
    ]);
} else {
    $sql = "SELECT * FROM estudiante ORDER BY id_estudiante DESC";
    $stmt = $conexion->query($sql);
}


$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Estudiantes - BibliotecaTecno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<div class="d-flex">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

    <main class="p-4" style="margin-left: 250px; width: calc(100% - 250px); margin-top: 56px;">
        <h3 class="mb-3">Gestión de Estudiantes</h3>

        <div class="row g-3 mb-4">
            <!-- Formulario (sin foto de perfil ni teléfono) -->
            <div class="col-md-8">
                <form id="formEstudiante" action="registrar.php" method="POST" class="card p-3">
                    <input type="hidden" name="id_estudiante" id="id_estudiante">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo_estudiante" id="codigo" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">DNI</label>
                            <input type="text" name="dni" id="dni" maxlength="8" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="activo" id="activo" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="nombres" id="nombres" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carrera</label>
                            <select name="carrera" id="carrera" class="form-select" required>
                                <option value="">Seleccione carrera...</option>
                                <option value="Arquitectura de Plataformas y Servicios de TI">Arquitectura de Plataformas y Servicios de TI</option>
                                <option value="Asistencia Administrativa">Asistencia Administrativa</option>
                                <option value="Contabilidad">Contabilidad</option>
                                <option value="Electrónica Industrial">Electrónica Industrial</option>
                                <option value="Enfermería Técnica">Enfermería Técnica</option>
                                <option value="Gestión Administrativa">Gestión Administrativa</option>
                                <option value="Guía Oficial de Turismo">Guía Oficial de Turismo</option>
                                <option value="Laboratorio Clínico y Anatomía Patológica">Laboratorio Clínico y Anatomía Patológica</option>
                                <option value="Mecatrónica Automotriz">Mecatrónica Automotriz</option>
                                <option value="Mecánica de Producción Industrial">Mecánica de Producción Industrial</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correo" id="correo" class="form-control">
                        </div>
                    </div>
                </form>
            </div>

            <!-- Botones de acción -->
            <div class="col-md-4">
                <div class="card p-3 d-grid gap-2">
                    <button type="button" id="btnNuevo" class="btn btn-primary">Nuevo</button>
                    <button type="submit" form="formEstudiante" id="btnGuardar" class="btn btn-success">Guardar</button>
                    <button type="button" id="btnModificar" class="btn btn-warning">Modificar</button>
                    <button type="button" id="btnEliminar" class="btn btn-danger">Eliminar</button>
                    <a href="index.php" id="btnActualizar" class="btn btn-secondary">Actualizar</a>
                </div>
            </div>
        </div>

        <!-- Buscador y tabla -->
        <div class="card p-3">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="buscar" value="<?= htmlspecialchars($busqueda) ?>" class="form-control" placeholder="Buscar por código, DNI, nombre o apellido...">
                </div>
                <div class="col-auto">
                    <button type="submit" id="btnBuscar" class="btn btn-outline-primary">Buscar</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>DNI</th>
                            <th>Carrera</th>
                            <th>Correo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $est): ?>
                        <tr class="fila-estudiante"
                            style="cursor:pointer;"
                            data-id="<?= $est['id_estudiante'] ?>"
                            data-codigo="<?= htmlspecialchars($est['codigo_estudiante'] ?? '') ?>"
                            data-nombres="<?= htmlspecialchars($est['nombres'] ?? '') ?>"
                            data-apellidos="<?= htmlspecialchars($est['apellidos'] ?? '') ?>"
                            data-dni="<?= htmlspecialchars($est['dni'] ?? '') ?>"
                            data-carrera="<?= htmlspecialchars($est['carrera'] ?? '') ?>"
                            data-correo="<?= htmlspecialchars($est['correo'] ?? '') ?>"
                            data-activo="<?= $est['activo'] ?? 1 ?>">
                            <td><?= htmlspecialchars($est['codigo_estudiante'] ?? '') ?></td>
                            <td><?= htmlspecialchars($est['nombres'] ?? '') ?></td>
                            <td><?= htmlspecialchars($est['apellidos'] ?? '') ?></td>
                            <td><?= htmlspecialchars($est['dni'] ?? '') ?></td>
                            <td><?= htmlspecialchars($est['carrera'] ?? '') ?></td>
                            <td><?= htmlspecialchars($est['correo'] ?? '') ?></td>
                            <td><?= (($est['activo'] ?? 1) == 1) ? 'Activo' : 'Inactivo' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
const form = document.getElementById('formEstudiante');
const idInput = document.getElementById('id_estudiante');

document.querySelectorAll('.fila-estudiante').forEach(fila => {
    fila.addEventListener('click', () => {
        document.querySelectorAll('.fila-estudiante').forEach(f => f.classList.remove('table-active'));
        fila.classList.add('table-active');

        idInput.value = fila.dataset.id;
        document.getElementById('codigo').value = fila.dataset.codigo;
        document.getElementById('nombres').value = fila.dataset.nombres;
        document.getElementById('apellidos').value = fila.dataset.apellidos;
        document.getElementById('dni').value = fila.dataset.dni;
        document.getElementById('carrera').value = fila.dataset.carrera;
        document.getElementById('correo').value = fila.dataset.correo;
        document.getElementById('activo').value = fila.dataset.activo;
    });
});

document.getElementById('btnNuevo').addEventListener('click', () => {
    form.reset();
    idInput.value = '';
    form.action = 'registrar.php';
    document.getElementById('codigo').focus();
});

document.getElementById('btnModificar').addEventListener('click', () => {
    if (!idInput.value) {
        alert('Selecciona primero un estudiante de la tabla.');
        return;
    }
    form.action = 'editar.php';
    form.submit();
});

document.getElementById('btnEliminar').addEventListener('click', () => {
    if (!idInput.value) {
        alert('Selecciona primero un estudiante de la tabla.');
        return;
    }
    if (confirm('¿Seguro que deseas eliminar este estudiante?')) {
        window.location.href = 'eliminar.php?id=' + encodeURIComponent(idInput.value);
    }
});
</script>
</body>
</html>

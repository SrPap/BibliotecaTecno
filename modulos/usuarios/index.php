<?php
require_once __DIR__ . '/../../includes/verificar_sesion.php';
require_once __DIR__ . '/../../config/conexion.php';

$busqueda = trim($_GET['buscar'] ?? '');
$ok = $_GET['ok'] ?? '';
$error = $_GET['error'] ?? '';

$roles = [];
$estudiantes = [];
$usuarios = [];

try {
    $roles = $conexion->query(
        "SELECT id_rol, nombre FROM rol ORDER BY id_rol ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $estudiantes = $conexion->query(
        "SELECT id_estudiante, codigo_estudiante, nombres, apellidos
         FROM estudiante
         ORDER BY apellidos ASC, nombres ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT u.id_usuario, u.nombre_usuario, u.id_rol, u.id_estudiante, u.activo,
                   r.nombre AS nombre_rol,
                   e.codigo_estudiante, e.nombres, e.apellidos
            FROM usuario u
            INNER JOIN rol r ON u.id_rol = r.id_rol
            LEFT JOIN estudiante e ON u.id_estudiante = e.id_estudiante";

    $params = [];
    if ($busqueda !== '') {
        $like = '%' . $busqueda . '%';
        $sql .= " WHERE u.nombre_usuario LIKE :q1
                   OR r.nombre LIKE :q2
                   OR e.nombres LIKE :q3
                   OR e.apellidos LIKE :q4
                   OR e.codigo_estudiante LIKE :q5";
        $params = [
            ':q1' => $like,
            ':q2' => $like,
            ':q3' => $like,
            ':q4' => $like,
            ':q5' => $like,
        ];
    }

    $sql .= " ORDER BY u.id_usuario DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error en modulos/usuarios/index.php: " . $e->getMessage());
    $error = "Error al cargar los datos de usuarios.";
}

$pageTitle = 'Gestión de Usuarios';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<style>
.contenido-modulo-usuarios {
    margin-left: 250px;
    padding: 24px 28px;
    min-height: calc(100vh - 56px);
    box-sizing: border-box;
}

@media (max-width: 991.98px) {
    .contenido-modulo-usuarios {
        margin-left: 0;
        padding: 16px;
    }
}
</style>

<main class="contenido-modulo-usuarios">
    <div class="container-fluid px-0">
        <h2 class="mb-4">Gestión de Usuarios</h2>

        <?php if ($ok !== ''): ?>
            <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form id="formUsuario" action="registrar.php" method="POST">
                    <input type="hidden" name="id_usuario" id="id_usuario" value="">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="nombre_usuario" id="nombre_usuario" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="activo" id="activo" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select name="id_rol" id="id_rol" class="form-select" required>
                                <option value="">Seleccione un rol...</option>
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= (int)$rol['id_rol'] ?>">
                                        <?= htmlspecialchars($rol['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estudiante Asociado</label>
                            <select name="id_estudiante" id="id_estudiante" class="form-select">
                                <option value="">Ninguno (Personal)</option>
                                <?php foreach ($estudiantes as $est): ?>
                                    <option value="<?= (int)$est['id_estudiante'] ?>">
                                        <?= htmlspecialchars($est['codigo_estudiante'] . ' - ' . $est['apellidos'] . ', ' . $est['nombres']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2 flex-wrap">
                        <button type="button" id="btnNuevo" class="btn btn-primary">Nuevo</button>
                        <button type="submit" id="btnGuardar" class="btn btn-success">Guardar</button>
                        <button type="button" id="btnModificar" class="btn btn-warning">Modificar</button>
                        <button type="button" id="btnCambiarEstado" class="btn btn-danger">Cambiar Estado</button>
                        <a href="index.php" class="btn btn-secondary">Actualizar</a>
                    </div>
                </form>
            </div>
        </div>

        <form method="GET" action="index.php" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="text" name="buscar" value="<?= htmlspecialchars($busqueda) ?>"
                       class="form-control" placeholder="Buscar por usuario, rol o estudiante...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary">Buscar</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estudiante Asociado</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay usuarios registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                            <?php
                            $codigoFormateado = 'USR' . str_pad((string)$u['id_usuario'], 3, '0', STR_PAD_LEFT);
                            $nombreEst = !empty($u['nombres'])
                                ? trim($u['nombres'] . ' ' . $u['apellidos'])
                                : '-';
                            ?>
                            <tr class="fila-usuario"
                                style="cursor: pointer;"
                                data-id="<?= (int)$u['id_usuario'] ?>"
                                data-usuario="<?= htmlspecialchars($u['nombre_usuario'], ENT_QUOTES) ?>"
                                data-rol="<?= (int)$u['id_rol'] ?>"
                                data-estudiante="<?= htmlspecialchars((string)($u['id_estudiante'] ?? ''), ENT_QUOTES) ?>"
                                data-activo="<?= (int)$u['activo'] ?>">
                                <td><?= htmlspecialchars($codigoFormateado) ?></td>
                                <td><?= htmlspecialchars($u['nombre_usuario']) ?></td>
                                <td><?= htmlspecialchars($u['nombre_rol']) ?></td>
                                <td><?= htmlspecialchars($nombreEst) ?></td>
                                <td><?= ((int)$u['activo'] === 1) ? 'Activo' : 'Inactivo' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formUsuario');
    const idInput = document.getElementById('id_usuario');
    const usuarioInput = document.getElementById('nombre_usuario');
    const passInput = document.getElementById('password');
    const rolSelect = document.getElementById('id_rol');
    const estSelect = document.getElementById('id_estudiante');
    const activoSelect = document.getElementById('activo');

    document.querySelectorAll('.fila-usuario').forEach(fila => {
        fila.addEventListener('click', () => {
            document.querySelectorAll('.fila-usuario').forEach(f => f.classList.remove('table-active'));
            fila.classList.add('table-active');

            idInput.value = fila.dataset.id;
            usuarioInput.value = fila.dataset.usuario;
            passInput.value = '';
            rolSelect.value = fila.dataset.rol;
            estSelect.value = fila.dataset.estudiante;
            activoSelect.value = fila.dataset.activo;
        });
    });

    document.getElementById('btnNuevo').addEventListener('click', () => {
        form.action = 'registrar.php';
        idInput.value = '';
        usuarioInput.value = '';
        passInput.value = '';
        rolSelect.value = '';
        estSelect.value = '';
        activoSelect.value = '1';
        document.querySelectorAll('.fila-usuario').forEach(f => f.classList.remove('table-active'));
        usuarioInput.focus();
    });

    document.getElementById('btnModificar').addEventListener('click', () => {
        if (!idInput.value) {
            alert('Selecciona primero un usuario de la tabla.');
            return;
        }
        form.action = 'editar.php';
        form.submit();
    });

    document.getElementById('btnCambiarEstado').addEventListener('click', () => {
        if (!idInput.value) {
            alert('Selecciona primero un usuario de la tabla.');
            return;
        }
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = 'cambiar_estado.php';
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'id_usuario';
        inp.value = idInput.value;
        f.appendChild(inp);
        document.body.appendChild(f);
        f.submit();
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

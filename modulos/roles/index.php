<?php
require_once __DIR__ . '/../../includes/verificar_sesion.php';
require_once __DIR__ . '/../../config/conexion.php';

$busqueda = trim($_GET['buscar'] ?? '');
$ok = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? 'guardar';
    $id_rol = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);
    $nombre = strtoupper(trim($_POST['nombre'] ?? ''));

    if ($nombre === '') {
        $error = 'Debes ingresar el nombre del rol.';
    } else {
        try {
            if ($accion === 'modificar') {
                if (!$id_rol) {
                    $error = 'Selecciona primero un rol de la tabla para modificar.';
                } else {
                    $check = $conexion->prepare(
                        "SELECT id_rol FROM rol WHERE UPPER(nombre) = :nombre AND id_rol <> :id LIMIT 1"
                    );
                    $check->execute([
                        ':nombre' => $nombre,
                        ':id'     => $id_rol,
                    ]);

                    if ($check->fetch()) {
                        $error = 'Ya existe otro rol con ese nombre.';
                    } else {
                        $upd = $conexion->prepare("UPDATE rol SET nombre = :nombre WHERE id_rol = :id");
                        $upd->execute([
                            ':nombre' => $nombre,
                            ':id'     => $id_rol,
                        ]);
                        $ok = 'Rol actualizado correctamente.';
                    }
                }
            } else {
                $check = $conexion->prepare("SELECT id_rol FROM rol WHERE UPPER(nombre) = :nombre LIMIT 1");
                $check->execute([':nombre' => $nombre]);

                if ($check->fetch()) {
                    $error = 'Ese rol ya está registrado.';
                } else {
                    $ins = $conexion->prepare("INSERT INTO rol (nombre) VALUES (:nombre)");
                    $ins->execute([':nombre' => $nombre]);
                    $ok = 'Rol registrado correctamente.';
                }
            }
        } catch (PDOException $e) {
            error_log("Error en modulos/roles/index.php: " . $e->getMessage());
            $error = 'Ocurrió un error al guardar los cambios del rol.';
        }
    }
}

$roles = [];
try {
    $sql = "SELECT r.id_rol, r.nombre, COUNT(u.id_usuario) AS total_usuarios
            FROM rol r
            LEFT JOIN usuario u ON u.id_rol = r.id_rol";
    $params = [];

    if ($busqueda !== '') {
        $sql .= " WHERE r.nombre LIKE :buscar";
        $params[':buscar'] = '%' . $busqueda . '%';
    }

    $sql .= " GROUP BY r.id_rol, r.nombre ORDER BY r.id_rol ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al listar roles: " . $e->getMessage());
    $error = 'Error al cargar la lista de roles.';
}

$pageTitle = 'Gestión de Roles';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<style>
.contenido-modulo-roles {
    margin-left: 250px;
    padding: 24px 28px;
    min-height: calc(100vh - 56px);
    box-sizing: border-box;
}

@media (max-width: 991.98px) {
    .contenido-modulo-roles {
        margin-left: 0;
        padding: 16px;
    }
}
</style>

<main class="contenido-modulo-roles">
    <div class="container-fluid px-0">
        <h2 class="mb-4">Gestión de Roles</h2>

        <?php if ($ok !== ''): ?>
            <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form id="formRol" action="index.php" method="POST">
                    <input type="hidden" name="id_rol" id="id_rol" value="">
                    <input type="hidden" name="accion" id="accion" value="guardar">

                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del Rol</label>
                            <input type="text" name="nombre" id="nombre" class="form-control"
                                   placeholder="Ej. ADMINISTRADOR, BIBLIOTECARIO, ESTUDIANTE" required>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2 flex-wrap">
                        <button type="button" id="btnNuevo" class="btn btn-primary">Nuevo</button>
                        <button type="submit" id="btnGuardar" class="btn btn-success">Guardar</button>
                        <button type="button" id="btnModificar" class="btn btn-warning">Modificar</button>
                        <a href="index.php" class="btn btn-secondary">Actualizar</a>
                    </div>
                </form>
            </div>
        </div>

        <form method="GET" action="index.php" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="text" name="buscar" value="<?= htmlspecialchars($busqueda) ?>"
                       class="form-control" placeholder="Buscar por nombre de rol...">
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
                        <th>Rol</th>
                        <th>Usuarios Asignados</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roles)): ?>
                        <tr>
                            <td colspan="3" class="text-center">No hay roles registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roles as $r): ?>
                            <?php $codigoRol = 'ROL' . str_pad((string)$r['id_rol'], 3, '0', STR_PAD_LEFT); ?>
                            <tr class="fila-rol"
                                style="cursor: pointer;"
                                data-id="<?= (int)$r['id_rol'] ?>"
                                data-nombre="<?= htmlspecialchars($r['nombre'], ENT_QUOTES) ?>">
                                <td><?= htmlspecialchars($codigoRol) ?></td>
                                <td><?= htmlspecialchars($r['nombre']) ?></td>
                                <td><?= (int)$r['total_usuarios'] ?></td>
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
    const form = document.getElementById('formRol');
    const idInput = document.getElementById('id_rol');
    const accionInput = document.getElementById('accion');
    const nombreInput = document.getElementById('nombre');

    document.querySelectorAll('.fila-rol').forEach(fila => {
        fila.addEventListener('click', () => {
            document.querySelectorAll('.fila-rol').forEach(f => f.classList.remove('table-active'));
            fila.classList.add('table-active');

            idInput.value = fila.dataset.id;
            nombreInput.value = fila.dataset.nombre;
            accionInput.value = 'modificar';
        });
    });

    document.getElementById('btnNuevo').addEventListener('click', () => {
        idInput.value = '';
        nombreInput.value = '';
        accionInput.value = 'guardar';
        document.querySelectorAll('.fila-rol').forEach(f => f.classList.remove('table-active'));
        nombreInput.focus();
    });

    document.getElementById('btnGuardar').addEventListener('click', () => {
        idInput.value = '';
        accionInput.value = 'guardar';
    });

    document.getElementById('btnModificar').addEventListener('click', () => {
        if (!idInput.value) {
            alert('Selecciona primero un rol de la tabla.');
            return;
        }
        accionInput.value = 'modificar';
        form.submit();
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

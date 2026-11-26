
<?php
// Iniciar sesión solamente si todavía no existe
function iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Escapar texto para mostrarlo de forma segura en HTML
function limpiar($valor): string
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Redireccionar a otra página
function redireccionar(string $ruta): void
{
    header("Location: " . $ruta);
    exit;
}

// Comprobar si existe una sesión iniciada
function sesion_activa(): bool
{
    iniciar_sesion();

    return isset($_SESSION['id_usuario']);
}

// Obtener el nombre del usuario conectado
function obtener_usuario(): string
{
    iniciar_sesion();
    return $_SESSION['nombre_usuario'] ?? '';
}

// Obtener el rol del usuario conectado
function obtener_rol(): string
{
    iniciar_sesion();
    return $_SESSION['rol'] ?? '';
}

// ---------------------------------------------------------------
// Utilidades compartidas por los módulos de gestión
// ---------------------------------------------------------------

// Marcar como activo el enlace del menú que corresponde a la página actual
function menu_activo(string $href): string
{
    $actual = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

    if (substr($href, -13) === 'principal.php') {
        return $actual === $href ? 'active' : '';
    }

    // Los enlaces "index.php" se marcan en cualquier página de su carpeta
    if (basename($href) === 'index.php') {
        return strpos($actual, dirname($href) . '/') === 0 ? 'active' : '';
    }

    return $actual === $href ? 'active' : '';
}

// Guardar un mensaje que se mostrará en la siguiente página
function flash(string $tipo, string $mensaje): void
{
    iniciar_sesion();
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

// Mostrar (y borrar) los mensajes pendientes
function mostrar_flash(): void
{
    iniciar_sesion();

    foreach ($_SESSION['flash'] ?? [] as $aviso) {
        $tipo = in_array($aviso['tipo'], ['success', 'danger', 'warning', 'info'], true)
            ? $aviso['tipo']
            : 'info';

        echo '<div class="alert alert-' . $tipo . ' alert-dismissible fade show" role="alert">'
            . limpiar($aviso['mensaje'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>'
            . '</div>';
    }

    unset($_SESSION['flash']);
}

// Token CSRF para proteger formularios que modifican datos
function csrf_token(): string
{
    iniciar_sesion();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . limpiar(csrf_token()) . '">';
}

function validar_csrf(): void
{
    iniciar_sesion();

    $recibido = $_POST['csrf_token'] ?? '';

    if (!is_string($recibido) || !hash_equals($_SESSION['csrf_token'] ?? '', $recibido)) {
        http_response_code(419);
        exit('El formulario expiró. Vuelve atrás, recarga la página e inténtalo de nuevo.');
    }
}

// Aceptar únicamente solicitudes POST
function solo_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        exit('Método no permitido.');
    }
}

// Comprobar si un valor ya existe en una tabla (para validar duplicados).
// $tabla, $columna y $columnaId son siempre literales escritos en el código.
function valor_existe(PDO $conexion, string $tabla, string $columna, $valor, string $columnaId, ?int $excluirId = null): bool
{
    $sql = "SELECT COUNT(*) FROM $tabla WHERE $columna = ?";
    $parametros = [$valor];

    if ($excluirId !== null) {
        $sql .= " AND $columnaId <> ?";
        $parametros[] = $excluirId;
    }

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);

    return (int) $stmt->fetchColumn() > 0;
}

// Número de página solicitado (mínimo 1)
function pagina_actual(): int
{
    return max(1, (int) ($_GET['pagina'] ?? 1));
}

// Dibujar la paginación conservando los filtros de la búsqueda
function render_paginacion(int $pagina, int $totalPaginas, array $filtros = []): void
{
    if ($totalPaginas <= 1) {
        return;
    }

    $enlace = function (int $p) use ($filtros): string {
        return '?' . http_build_query(array_merge($filtros, ['pagina' => $p]));
    };

    echo '<nav aria-label="Paginación"><ul class="pagination pagination-sm justify-content-end mb-0 mt-3">';

    $desde = max(1, $pagina - 2);
    $hasta = min($totalPaginas, $pagina + 2);

    echo '<li class="page-item' . ($pagina <= 1 ? ' disabled' : '') . '">'
        . '<a class="page-link" href="' . limpiar($enlace($pagina - 1)) . '">Anterior</a></li>';

    for ($i = $desde; $i <= $hasta; $i++) {
        echo '<li class="page-item' . ($i === $pagina ? ' active' : '') . '">'
            . '<a class="page-link" href="' . limpiar($enlace($i)) . '">' . $i . '</a></li>';
    }

    echo '<li class="page-item' . ($pagina >= $totalPaginas ? ' disabled' : '') . '">'
        . '<a class="page-link" href="' . limpiar($enlace($pagina + 1)) . '">Siguiente</a></li>';

    echo '</ul></nav>';
}

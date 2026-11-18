
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

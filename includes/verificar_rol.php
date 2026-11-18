
<?php

require_once __DIR__ . '/verificar_sesion.php';

// Comprobar si el usuario tiene uno de los roles permitidos
function exigir_rol(array $rolesPermitidos): void
{
    $rolUsuario = obtener_rol();

    if (!in_array($rolUsuario, $rolesPermitidos, true)) {

        http_response_code(403);

        exit('No tienes permisos para acceder a esta sección.');

    }
}

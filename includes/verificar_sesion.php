
<?php

require_once __DIR__ . '/funciones.php';

iniciar_sesion();

// Comprobar si el usuario inició sesión
if (!sesion_activa()) {

    redireccionar('/BibliotecaTecno/login.php');

}

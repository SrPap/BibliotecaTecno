
<?php

require_once __DIR__ . '/includes/funciones.php';

iniciar_sesion();

if (sesion_activa()) {

    redireccionar('principal.php');

} else {

    redireccionar('login.php');

}

?>

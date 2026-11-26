<?php
// Ventana de confirmación para eliminar registros.
// Se incluye al final de cada listado; el formulario envía por POST a eliminar.php
// (misma carpeta que el listado). Los botones "eliminar" deben llevar:
//   data-bs-toggle="modal" data-bs-target="#modalEliminar"
//   data-id="..." data-nombre="..."
?>
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="eliminar.php" class="modal-content">

            <?= csrf_campo() ?>
            <input type="hidden" name="id" id="eliminarId" value="">

            <div class="modal-header">
                <h5 class="modal-title" id="modalEliminarTitulo">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                    Confirmar eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                ¿Seguro que deseas eliminar <strong id="eliminarNombre"></strong>?
                Esta acción no se puede deshacer.
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash me-1"></i>
                    Eliminar
                </button>
            </div>

        </form>
    </div>
</div>

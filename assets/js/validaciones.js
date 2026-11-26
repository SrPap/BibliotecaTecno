document.addEventListener('DOMContentLoaded', function () {

    // ---- Modal de confirmación para eliminar ----
    const modalEliminar = document.getElementById('modalEliminar');

    if (modalEliminar) {
        modalEliminar.addEventListener('show.bs.modal', function (evento) {
            const boton = evento.relatedTarget;

            if (!boton) {
                return;
            }

            document.getElementById('eliminarId').value = boton.dataset.id || '';
            document.getElementById('eliminarNombre').textContent = boton.dataset.nombre || 'este registro';
        });
    }

    // ---- Filtro rápido para listas de selección (autores del libro) ----
    document.querySelectorAll('[data-filtro-lista]').forEach(function (campo) {
        const lista = document.querySelector(campo.dataset.filtroLista);

        if (!lista) {
            return;
        }

        campo.addEventListener('input', function () {
            const texto = campo.value.trim().toLowerCase();

            lista.querySelectorAll('[data-texto]').forEach(function (item) {
                item.classList.toggle('d-none', !item.dataset.texto.includes(texto));
            });
        });
    });

    // ---- Evitar doble envío de formularios ----
    document.querySelectorAll('form[data-evitar-doble-envio]').forEach(function (formulario) {
        formulario.addEventListener('submit', function () {
            const boton = formulario.querySelector('button[type="submit"]');

            if (boton) {
                boton.disabled = true;
            }
        });
    });

});

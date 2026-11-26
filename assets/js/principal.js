
document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.getElementById('sidebar');
    const dashboard = document.querySelector('.dashboard-layout');
    const brandToggle = document.getElementById('brandToggle');

    if (!sidebar || !dashboard || !brandToggle) {
        console.error('No se encontraron los elementos del menu.');
        return;
    }

    const esMovil = window.matchMedia('(max-width: 768px)');

    let menuAbierto = !esMovil.matches;

    function actualizarMenu() {

        // Mostrar u ocultar el menu
        sidebar.style.transform = menuAbierto
            ? 'translateX(0)'
            : 'translateX(-100%)';

        sidebar.style.visibility = menuAbierto
            ? 'visible'
            : 'hidden';

        sidebar.style.pointerEvents = menuAbierto
            ? 'auto'
            : 'none';

        // Ajustar el contenido
        const contenido = document.querySelectorAll(
            '.main-content, .dashboard-content'
        );

        contenido.forEach(function (elemento) {

            if (esMovil.matches || !menuAbierto) {
                elemento.style.marginLeft = '0';
                elemento.style.width = '100%';
            } else {
                elemento.style.marginLeft = '250px';
                elemento.style.width = 'calc(100% - 250px)';
            }

        });

        dashboard.classList.toggle(
            'sidebar-collapsed',
            !menuAbierto
        );

        brandToggle.setAttribute(
            'aria-expanded',
            String(menuAbierto)
        );

    }

    // El logo controla el menu
    brandToggle.addEventListener('click', function (event) {

        event.preventDefault();

        menuAbierto = !menuAbierto;

        actualizarMenu();

    });

    // Cerrar menu al seleccionar una opcion en celulares
    sidebar.querySelectorAll('.sidebar-link').forEach(function (enlace) {

        enlace.addEventListener('click', function () {

            if (esMovil.matches) {
                menuAbierto = false;
                actualizarMenu();
            }

        });

    });

    // Adaptar el menu al cambiar el tamano de pantalla
    esMovil.addEventListener('change', function () {

        menuAbierto = !esMovil.matches;

        actualizarMenu();

    });

    // Estado inicial
    actualizarMenu();

    console.log('Menu BibliotecaTecno iniciado correctamente.');

});


const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');

if (menuToggle && sidebar) {

    menuToggle.addEventListener('click', function () {

        sidebar.classList.toggle('show');

    });

    // Cerrar el menú al seleccionar un apartado
    const enlaces = sidebar.querySelectorAll('.sidebar-link');

    enlaces.forEach(function (enlace) {

        enlace.addEventListener('click', function () {

            sidebar.classList.remove('show');

        });

    });

}

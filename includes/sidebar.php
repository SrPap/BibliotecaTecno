
<aside class="sidebar" id="sidebar">

    <div class="sidebar-heading">
        MENÚ PRINCIPAL
    </div>

    <nav class="sidebar-nav">

        <a href="/BibliotecaTecno/principal.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/principal.php') ?>">
            <i class="bi bi-house-door"></i>
            <span>Inicio</span>
        </a>

        <?php if ($rol === 'ADMINISTRADOR'): ?>

            <div class="sidebar-heading">GESTIÓN DE BIBLIOTECA</div>

            <a href="/BibliotecaTecno/modulos/libros/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/libros/index.php') ?>">
                <i class="bi bi-book"></i>
                <span>Libros</span>
            </a>

            <a href="/BibliotecaTecno/modulos/ejemplares/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/ejemplares/index.php') ?>">
                <i class="bi bi-journal-bookmark"></i>
                <span>Ejemplares</span>
            </a>

            <a href="/BibliotecaTecno/modulos/autores/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/autores/index.php') ?>">
                <i class="bi bi-people"></i>
                <span>Autores</span>
            </a>

            <a href="/BibliotecaTecno/modulos/categorias/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/categorias/index.php') ?>">
                <i class="bi bi-grid"></i>
                <span>Categorías</span>
            </a>

            <a href="/BibliotecaTecno/modulos/editoriales/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/editoriales/index.php') ?>">
                <i class="bi bi-building"></i>
                <span>Editoriales</span>
            </a>

            <div class="sidebar-heading">USUARIOS</div>

            <a href="/BibliotecaTecno/modulos/estudiantes/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/estudiantes/index.php') ?>">
                <i class="bi bi-mortarboard"></i>
                <span>Estudiantes</span>
            </a>

            <a href="/BibliotecaTecno/modulos/usuarios/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/usuarios/index.php') ?>">
                <i class="bi bi-person-gear"></i>
                <span>Usuarios</span>
            </a>

            <a href="/BibliotecaTecno/modulos/roles/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/roles/index.php') ?>">
                <i class="bi bi-shield-lock"></i>
                <span>Roles</span>
            </a>

            <div class="sidebar-heading">PRÉSTAMOS</div>

            <a href="/BibliotecaTecno/modulos/solicitudes/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/solicitudes/index.php') ?>">
                <i class="bi bi-clipboard-check"></i>
                <span>Solicitudes</span>
            </a>

            <a href="/BibliotecaTecno/modulos/prestamos/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/prestamos/index.php') ?>">
                <i class="bi bi-arrow-left-right"></i>
                <span>Préstamos</span>
            </a>

            <a href="/BibliotecaTecno/modulos/reportes/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/reportes/index.php') ?>">
                <i class="bi bi-bar-chart"></i>
                <span>Reportes</span>
            </a>

        <?php else: ?>

            <div class="sidebar-heading">MI BIBLIOTECA</div>

            <a href="/BibliotecaTecno/modulos/libros/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/libros/index.php') ?>">
                <i class="bi bi-search"></i>
                <span>Catálogo de libros</span>
            </a>

            <a href="/BibliotecaTecno/modulos/mis_libros/index.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/mis_libros/index.php') ?>">
                <i class="bi bi-book-half"></i>
                <span>Mis libros</span>
            </a>

            <a href="/BibliotecaTecno/modulos/mis_libros/solicitudes.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/mis_libros/solicitudes.php') ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span>Mis solicitudes</span>
            </a>

            <a href="/BibliotecaTecno/modulos/mis_libros/prestamos.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/mis_libros/prestamos.php') ?>">
                <i class="bi bi-journal-check"></i>
                <span>Mis préstamos</span>
            </a>

            <a href="/BibliotecaTecno/modulos/mis_libros/historial.php" class="sidebar-link <?= menu_activo('/BibliotecaTecno/modulos/mis_libros/historial.php') ?>">
                <i class="bi bi-clock-history"></i>
                <span>Historial</span>
            </a>

        <?php endif; ?>

        <div class="sidebar-bottom">

            <a href="/BibliotecaTecno/logout.php" class="sidebar-link logout-sidebar">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar sesión</span>
            </a>

        </div>

    </nav>

</aside>

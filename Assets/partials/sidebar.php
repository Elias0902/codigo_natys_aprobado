<style>
/* Estilos para el logo en el sidebar */
.sidebar-logo {
    padding: 15px 10px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.sidebar.collapsed .sidebar-logo {
    padding: 10px 5px;
}

.sidebar.collapsed .sidebar-logo-img {
    max-width: 45px;
    height: auto;
}

/* Estilos para el sidebar */
.sidebar {
    position: fixed;
    top: 56px; /* Altura del header */
    left: 0;
    bottom: 0;
    z-index: 1000;
    width: 250px;
    transition: all 0.3s ease;
    overflow-y: auto;
    background-color: #343a40;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}

.sidebar.initial-load {
    transition: none !important;
}

.sidebar.collapsed {
    width: 85px;
}

.sidebar.collapsed .nav-text {
    display: none;
}

/* Estilos para el logo en sidebar colapsado */
.sidebar.collapsed .sidebar-logo {
    height: 0;
    max-width: 80%;
    transition: all 0.3s ease;
}

/* Estilos para el contenedor del logo */
.logo-container {
    user-select: none; /* Evitar selección de texto */
}

.sidebar-logo {
    max-width: 80%;
    height: auto;
    margin: 0 auto;
    display: block;
    transition: all 0.3s ease;
}

.sidebar.collapsed .nav-item {
    text-align: center;
}

.sidebar.collapsed .nav-link {
    justify-content: center;
    padding: 0.75rem 0.5rem;
    border-left: 3px solid transparent;
}

.sidebar.collapsed .nav-link.active {
    border-left: 3px solid #007bff;
    padding-left: 0.5rem;
}

.sidebar.collapsed .nav-link i {
    margin-right: 0;
    font-size: 25px;
}
.nav-item{
    font-size: 25px;
}
/* Estilos consistentes para el contenido principal */
.main-content {
    margin-left: 250px;
    transition: all 0.3s ease;
    padding: 20px;
    padding-top: 76px; /* Altura del header + 20px */
    min-height: calc(100vh - 56px); /* Asegurar que ocupe toda la altura */
    background-color: #f8f9fa;
}

.sidebar.collapsed + .main-content {
    margin-left: 60px;
}

/* Estilos para los enlaces del menú */
.nav-link {
    color: #fff !important;
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    transition: all 0.3s;
    border-left: 3px solid transparent;
    opacity: 0.8;
    text-decoration: none;
}

.nav-link:hover, 
.nav-link.active,
.nav-link:focus {
    opacity: 1;
    background-color: rgba(255, 255, 255, 0.1);
    text-decoration: none;
    border-left: 3px solid #007bff;
    padding-left: calc(1rem - 3px);
    outline: none;
}

.nav-link i {
    margin-right: 10px;
    width: 45px;
    text-align: center;
    color: #fff;
}

/* Asegurar consistencia en todos los módulos */
.sidebar-menu .nav-item {
    margin: 0;
    padding: 0;
}

.sidebar-menu .nav-link {
    border-radius: 0;
    margin: 0;
}

/* Asegurar que los iconos hereden el color del enlace */
.nav-link.active i,
.nav-link:hover i {
    color: #fff;
}

/* Reset de estilos para el menú de usuario */
.sidebar-footer .dropdown-menu {
    background-color: #343a40;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-footer .dropdown-item {
    color: #fff;
    opacity: 0.8;
}

.sidebar-footer .dropdown-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    opacity: 1;
}

.sidebar-header {
    padding: 15px 10px;
    border-bottom: 1px solid #4b545c;
}

/* Barra superior fija para notificaciones y perfil */
.top-navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 56px;
    background-color: #f8f9fa;
    z-index: 1030;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-bottom: 1px solid #dee2e6;
}

.top-navbar .navbar-brand {
    margin-right: 0;
}

.top-navbar .nav-item {
    position: relative;
}

.top-navbar .dropdown-menu {
    position: fixed;
    top: 56px;
    right: 20px;
    left: auto;
    z-index: 1001;
    margin-top: 0;
}

/* Estilos para los dropdowns fijos */
.fixed-dropdown {
    position: fixed;
    top: 56px;
    right: 20px;
    z-index: 1001;
}

@media (max-width: 767.98px) {
    .sidebar {
        transform: translateX(-100%);
        z-index: 1050;
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .sidebar.collapsed {
        transform: translateX(-100%);
    }
    
    .top-navbar {
        padding: 0 15px;
    }
    
    .top-navbar .dropdown-menu {
        right: 15px;
    }
    
    /* Ajuste de margen entre íconos y texto solo en móviles */
    .nav-link i {
        margin-right: 10px; /* Espacio entre ícono y texto */
    }
    
    .nav-link {
        padding-left: 15px; /* Ajustar padding izquierdo para mejor espaciado */
    }
    
    /* Asegurar que el texto tenga margen izquierdo */
    .nav-text {
        margin-left: 10px; /* Añadir margen izquierdo al texto */
    }
}
</style>

<script>
// Estado del sidebar
document.addEventListener('DOMContentLoaded', function() {
    // Función para actualizar el ícono del botón (siempre mostrará la hamburguesa)
    function updateToggleIcon() {
        const toggleBtn = document.getElementById('sidebarToggle');
        if (!toggleBtn) return;
        
        const icon = toggleBtn.querySelector('i');
        if (!icon) return;
        
        // Siempre mostrar el ícono de hamburguesa
        icon.className = 'fas fa-bars';
    }
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    const mobileMenuBtn = document.querySelector('[data-bs-toggle="offcanvas"]');
    
    // Cargar estado del sidebar (por defecto colapsado si no hay preferencia guardada)
    const isCollapsed = localStorage.getItem('sidebarCollapsed') !== 'false';

    // Aplicar estado inicial con clase especial para evitar animaciones
    sidebar.classList.add('initial-load');

    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        if (mainContent) mainContent.classList.add('expanded');
    } else {
        sidebar.classList.remove('collapsed');
        if (mainContent) mainContent.classList.remove('expanded');
    }

    // Forzar reflow inmediato
    sidebar.offsetHeight;

    // Remover la clase initial-load después de que el navegador haya renderizado
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            sidebar.classList.remove('initial-load');
        });
    });

    // Actualizar ícono al cargar
    updateToggleIcon();
    
    // Toggle sidebar en desktop
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const isCollapsing = !sidebar.classList.contains('collapsed');

            // Alternar clases
            sidebar.classList.toggle('collapsed');
            if (mainContent) mainContent.classList.toggle('expanded');

            // No es necesario cambiar el ícono ya que siempre será la hamburguesa

            // Guardar estado
            localStorage.setItem('sidebarCollapsed', isCollapsing);

            // Actualizar ícono
            updateToggleIcon();
        });
    }
    
    // Cerrar sidebar en móvil al hacer clic en un enlace
    const navLinks = document.querySelectorAll('.sidebar-menu .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('sidebarOffcanvas'));
                if (offcanvas) offcanvas.hide();
            }
        });
    });
    
    // Manejar el comportamiento de los dropdowns fijos
    const dropdownToggles = document.querySelectorAll('.fixed-dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const dropdownMenu = this.nextElementSibling;
            dropdownMenu.classList.toggle('show');
            
            // Cerrar otros dropdowns abiertos
            document.querySelectorAll('.fixed-dropdown').forEach(menu => {
                if (menu !== dropdownMenu && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                }
            });
        });
    });
    
    // Cerrar dropdowns al hacer clic fuera de ellos
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.fixed-dropdown-toggle') && !e.target.closest('.fixed-dropdown')) {
            document.querySelectorAll('.fixed-dropdown').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
});
</script>

<!-- Barra superior fija para notificaciones y perfil -->
<nav class="top-navbar">
    <a class="navbar-brand" href="#" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </a>
    
    <ul class="navbar-nav d-flex flex-row">
        <!-- Notificaciones -->
        <li class="nav-item me-3">
            <a class="nav-link fixed-dropdown-toggle" href="#" role="button">
                <i class="fas fa-bell"></i>
                <span class="badge bg-danger rounded-pill">3</span>
            </a>
            <div class="dropdown-menu fixed-dropdown" aria-labelledby="navbarDropdown">
                <h6 class="dropdown-header">Notificaciones</h6>
                <a class="dropdown-item" href="#">Nuevo pedido recibido</a>
                <a class="dropdown-item" href="#">Pago procesado</a>
                <a class="dropdown-item" href="#">Cliente nuevo registrado</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-center" href="#">Ver todas</a>
            </div>
        </li>
        
        <!-- Perfil de usuario -->
        <li class="nav-item">
            <a class="nav-link fixed-dropdown-toggle" href="#" role="button">
                <i class="fas fa-user-circle"></i>
            </a>
            <div class="dropdown-menu fixed-dropdown" aria-labelledby="navbarDropdown">
                <h6 class="dropdown-header">Mi Cuenta</h6>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-user me-2"></i>Mi Perfil
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cog me-2"></i>Configuración
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                </a>
            </div>
        </li>
    </ul>
</nav>

<!-- Sidebar para desktop y móvil -->
<aside class="sidebar bg-dark text-white">
    <!-- Contenido del sidebar -->
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="index.php?url=cliente" style="padding-top: 40px;" class="nav-link <?= ($_GET['url'] ?? '') === 'cliente' ? 'active' : '' ?>">
                    <i class="fas fa-user-tie"></i>
                    <span class="nav-text">Clientes</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?url=pedido" class="nav-link <?= ($_GET['url'] ?? '') === 'pedido' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="nav-text">Pedidos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?url=producto" class="nav-link <?= ($_GET['url'] ?? '') === 'producto' ? 'active' : '' ?>">
                    <i class="fas fa-cookie-bite"></i>
                    <span class="nav-text">Productos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?url=pago" class="nav-link <?= ($_GET['url'] ?? '') === 'pago' ? 'active' : '' ?>">
                    <i class="fas fa-credit-card"></i>
                    <span class="nav-text">Pagos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?url=movimiento" class="nav-link <?= ($_GET['url'] ?? '') === 'movimiento' ? 'active' : '' ?>">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span class="nav-text">Movimientos</span>
                </a>
            </li>
            
            <!-- Logo de Natys (solo visual, no afecta funcionalidad) -->
            <li class="nav-item mt-4">
                <div class="logo-container" style="pointer-events: none; padding: 25px 0; border-top: 1px solid #4b545c;">
                    <div style="display: flex; justify-content: center;">
                        <img src="/Natys/Assets/img/Natys.png" alt="Natys" class="sidebar-logo" style="height: 70px; max-width: 90%; transition: all 0.3s ease;">
                    </div>
                </div>
            </li>
        </ul>
    </div>
    
    <!-- Espacio para que el último ítem no quede pegado al borde -->
    <div class="p-2"></div>
</aside>

<!-- Botón para abrir el menú en móviles -->
<button class="btn btn-primary d-md-none position-fixed" style="bottom: 20px; right: 20px; z-index: 1040; width: 50px; height: 50px; border-radius: 50%;" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
    <i class="fas fa-bars"></i>
</button>

<!-- Offcanvas para móviles -->
<div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header bg-dark text-white">
        <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Menú</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body bg-dark p-0">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="index.php?url=home" class="nav-link text-white <?= ($_GET['url'] ?? '') === 'home' ? 'active bg-primary' : '' ?>">
                    <i class="fas fa-home me-2"></i>Inicio
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?url=cliente" class="nav-link text-white <?= ($_GET['url'] ?? '') === 'cliente' ? 'active bg-primary' : '' ?>">
                    <i class="fas fa-user-tie me-2"></i>Clientes
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?url=pedido" class="nav-link text-white <?= ($_GET['url'] ?? '') === 'pedido' ? 'active bg-primary' : '' ?>">
                    <i class="fas fa-shopping-cart me-2"></i>Pedidos
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?url=producto" class="nav-link text-white <?= ($_GET['url'] ?? '') === 'producto' ? 'active bg-primary' : '' ?>">
                    <i class="fas fa-cookie-bite me-2"></i>Productos
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?url=pago" class="nav-link text-white <?= ($_GET['url'] ?? '') === 'pago' ? 'active bg-primary' : '' ?>">
                    <i class="fas fa-credit-card me-2"></i>Pagos
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?url=movimiento" class="nav-link text-white <?= ($_GET['url'] ?? '') === 'movimiento' ? 'active bg-primary' : '' ?>">
                    <i class="fas fa-random me-2"></i>Movimientos
                </a>
            </li>
        </ul>
    </div>
</div>
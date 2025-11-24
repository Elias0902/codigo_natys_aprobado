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
    width: 280px; /* Ampliado de 250px a 280px */
    transition: all 0.3s ease;
    overflow-y: auto;
    background: linear-gradient(180deg, #212529 0%, #212529 100%);
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
}

.sidebar.initial-load {
    transition: none !important;
}

.sidebar.collapsed {
    width: 70px;
}

.sidebar.collapsed .nav-text {
    display: none;
}

.sidebar.collapsed .submenu {
    display: none !important;
}

.sidebar.collapsed .has-submenu::after {
    display: none;
}

/* Estilos para el contenedor del logo */
.logo-container {
    user-select: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 10px;
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
    border-left: 3px solid #3498db;
    padding-left: 0.5rem;
}

.sidebar.collapsed .nav-link i {
    margin-right: 0;
    font-size: 20px;
}

.nav-item {
    font-size: 14px;
}

/* Estilos consistentes para el contenido principal */
.main-content {
    margin-left: 280px; /* Ajustado al nuevo ancho */
    transition: all 0.3s ease;
    padding: 20px;
    padding-top: 76px;
    min-height: calc(100vh - 56px);
    background-color: #1a1a1a;
}

.sidebar.collapsed + .main-content {
    margin-left: 70px;
}


/* Estilos para los enlaces del menú */
.nav-link {
    color: #bdc3c7 !important;
    display: flex;
    align-items: center;
    padding: 0.8rem 1rem;
    transition: all 0.3s;
    border-left: 3px solid transparent;
    text-decoration: none;
    font-weight: 500;
}

.nav-link:hover, 
.nav-link.active,
.nav-link:focus {
    color: #fff !important;
    background-color: rgba(52, 152, 219, 0.2);
    text-decoration: none;
    border-left: 3px solid #3498db;
    padding-left: calc(1rem - 3px);
    outline: none;
}

.nav-link i {
    margin-right: 12px;
    width: 20px;
    text-align: center;
    color: #bdc3c7;
    transition: all 0.3s;
}

.nav-link:hover i,
.nav-link.active i {
    color: #3498db;
}

/* Submenús */
.has-submenu {
    position: relative;
}

.has-submenu::after {
    content: '\f107';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #bdc3c7;
    transition: all 0.3s;
}

.has-submenu.active::after {
    transform: translateY(-50%) rotate(180deg);
    color: #3498db;
}

.submenu {
    background-color: rgba(0, 0, 0, 0.2);
    border-left: 3px solid #3498db;
    margin-left: 20px;
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.3s ease;
}

.submenu.show {
    max-height: 500px;
}

.submenu .nav-link {
    padding: 0.6rem 1rem 0.6rem 2rem;
    font-size: 13px;
    border-left: none;
    color: #95a5a6 !important;
}

.submenu .nav-link:hover,
.submenu .nav-link.active {
    color: #fff !important;
    background-color: rgba(52, 152, 219, 0.15);
    border-left: none;
}

.submenu .nav-link i {
    font-size: 12px;
    margin-right: 8px;
}

/* Separadores de sección */
.menu-section {
    padding: 1rem 1rem 0.5rem 1rem;
    font-size: 11px;
    text-transform: uppercase;
    color: #7f8c8d;
    font-weight: 600;
    letter-spacing: 1px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 10px;
}

.menu-section:first-child {
    border-top: none;
    margin-top: 0;
}

/* Barra superior fija para notificaciones y perfil */
.top-navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 56px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    z-index: 1030;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    border-bottom: none;
}

.top-navbar .navbar-brand {
    margin-right: 0;
    color: white;
    font-weight: 600;
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
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    border-radius: 8px;
}

.top-navbar .nav-link {
    color: white !important;
    opacity: 0.9;
}

.top-navbar .nav-link:hover {
    opacity: 1;
    color: white !important;
}

/* Badges para notificaciones */
.badge {
    font-size: 10px;
    padding: 4px 6px;
}

/* Dropdowns fijos */
.fixed-dropdown {
    position: fixed;
    top: 56px;
    right: 20px;
    z-index: 1001;
    min-width: 250px;
}

.fixed-dropdown .dropdown-header {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

/* Responsive */
@media (max-width: 767.98px) {
    .sidebar {
        transform: translateX(-100%);
        z-index: 1050;
        width: 280px;
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
}

/* Scrollbar personalizado */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Animaciones suaves */
.nav-link, .submenu {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Estados de los módulos */
.module-status {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: auto;
}

.status-active {
    background-color: #27ae60;
    color: white;
}

.status-inactive {
    background-color: #e74c3c;
    color: white;
}

.status-pending {
    background-color: #f39c12;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    // Cargar estado del sidebar
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

    // Aplicar estado inicial
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        if (mainContent) mainContent.classList.add('expanded');
    }

    // Toggle sidebar
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const isCollapsing = !sidebar.classList.contains('collapsed');

            sidebar.classList.toggle('collapsed');
            if (mainContent) mainContent.classList.toggle('expanded');

            localStorage.setItem('sidebarCollapsed', isCollapsing);
            
            // Cerrar todos los submenús al colapsar
            if (isCollapsing) {
                document.querySelectorAll('.submenu').forEach(submenu => {
                    submenu.classList.remove('show');
                });
                document.querySelectorAll('.has-submenu').forEach(item => {
                    item.classList.remove('active');
                });
            }
        });
    }

    // Manejar submenús
    document.querySelectorAll('.has-submenu > .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!sidebar.classList.contains('collapsed')) {
                e.preventDefault();
                const parent = this.parentElement;
                const submenu = this.nextElementSibling;
                
                // Cerrar otros submenús abiertos
                document.querySelectorAll('.has-submenu').forEach(item => {
                    if (item !== parent) {
                        item.classList.remove('active');
                        item.querySelector('.submenu')?.classList.remove('show');
                    }
                });
                
                // Alternar submenu actual
                parent.classList.toggle('active');
                submenu.classList.toggle('show');
            }
        });
    });

    // Cerrar sidebar en móvil al hacer clic en un enlace
    const navLinks = document.querySelectorAll('.sidebar-menu .nav-link[href]');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768 && !this.parentElement.classList.contains('has-submenu')) {
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('sidebarOffcanvas'));
                if (offcanvas) offcanvas.hide();
            }
        });
    });

    // Manejar dropdowns fijos
    const dropdownToggles = document.querySelectorAll('.fixed-dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            const isShowing = dropdownMenu.classList.contains('show');
            
            // Cerrar otros dropdowns
            document.querySelectorAll('.fixed-dropdown').forEach(menu => {
                menu.classList.remove('show');
            });
            
            // Alternar dropdown actual
            if (!isShowing) {
                dropdownMenu.classList.add('show');
            }
        });
    });

    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.fixed-dropdown-toggle') && !e.target.closest('.fixed-dropdown')) {
            document.querySelectorAll('.fixed-dropdown').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Actualizar estado de módulos en tiempo real
    updateModuleStatus();
    setInterval(updateModuleStatus, 30000); // Actualizar cada 30 segundos
});

function updateModuleStatus() {
    // Simular actualización de estado de módulos
    const statusElements = document.querySelectorAll('.module-status');
    statusElements.forEach(element => {
        const status = Math.random() > 0.3 ? 'active' : 'inactive';
        element.textContent = status === 'active' ? 'Activo' : 'Inactivo';
        element.className = 'module-status ' + (status === 'active' ? 'status-active' : 'status-inactive');
    });
}
</script>

<!-- Barra superior fija -->
<nav class="top-navbar">
    <a class="navbar-brand" href="#" id="sidebarToggle">
        <i class="fas fa-bars me-2"></i>Natys Home
    </a>
    
    <ul class="navbar-nav d-flex flex-row">
        <!-- Notificaciones -->
        <li class="nav-item me-3">
            <a class="nav-link fixed-dropdown-toggle" href="#" role="button">
                <i class="fas fa-bell"></i>
                <span class="badge bg-danger rounded-pill">5</span>
            </a>
            <div class="dropdown-menu fixed-dropdown" aria-labelledby="navbarDropdown">
                <h6 class="dropdown-header">Notificaciones Recientes</h6>
                <a class="dropdown-item" href="index.php?url=pedido">
                    <i class="fas fa-shopping-cart text-primary me-2"></i>
                    <strong>Nuevo pedido #0012</strong>
                    <small class="d-block text-muted">Hace 5 min</small>
                </a>
                <a class="dropdown-item" href="index.php?url=pago">
                    <i class="fas fa-credit-card text-success me-2"></i>
                    <strong>Pago confirmado</strong>
                    <small class="d-block text-muted">Hace 1 hora</small>
                </a>
                <a class="dropdown-item" href="index.php?url=cliente">
                    <i class="fas fa-user-plus text-info me-2"></i>
                    <strong>Cliente nuevo</strong>
                    <small class="d-block text-muted">Hace 2 horas</small>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-center text-primary" href="#">
                    <i class="fas fa-list me-1"></i>Ver todas las notificaciones
                </a>
            </div>
        </li>
        
        <!-- Perfil de usuario -->
        <li class="nav-item">
            <a class="nav-link fixed-dropdown-toggle" href="#" role="button">
                <i class="fas fa-user-circle me-1"></i>
                <span class="d-none d-md-inline">Administrador</span>
            </a>
            <div class="dropdown-menu fixed-dropdown" aria-labelledby="navbarDropdown">
                <h6 class="dropdown-header">Sistema Natys</h6>
                <a class="dropdown-item" href="index.php?url=home">
                    <i class="fas fa-chart-line me-2"></i>Home
                </a>
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

<!-- Sidebar principal -->
<aside class="sidebar bg-dark text-white">
    <!-- Logo -->
    <div class="logo-container">
        <div class="text-center py-5">
            <div class="text-white mt-2 small" style="opacity: 0.8;">Sistema de Gestión Natys</div>
        </div>
    </div>

    <!-- Menú principal -->
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <!-- Home -->
            <li class="nav-item">
                <a href="index.php?url=home" class="nav-link <?= ($_GET['url'] ?? '') === 'Home' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Home</span>
                    <span class="module-status status-active">Activo</span>
                </a>
            </li>

            <!-- Sección: Gestión Comercial -->
            <li class="menu-section">Gestión Comercial</li>
            
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Clientes</span>
                    <span class="module-status status-active">Activo</span>
                </a>
                <ul class="nav flex-column submenu">
                    <li class="nav-item">
                        <a href="index.php?url=cliente" class="nav-link <?= ($_GET['url'] ?? '') === 'cliente' ? 'active' : '' ?>">
                            <i class="fas fa-list"></i>
                            <span class="nav-text">Consultar Clientes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=cliente&action=new" class="nav-link" onclick="$('#modalNuevo').modal('show')">
                            <i class="fas fa-plus"></i>
                            <span class="nav-text">Registrar Cliente</span>
                        </a>
                    </li>

                    <script>
                        $(document).ready(function() {
                            $('#btnNuevoCliente').click(function() {
                                $('#modalNuevo').modal('show');
                            });
                        });
                    </script>
                    <li class="nav-item">
                        <a href="index.php?url=cliente&action=reports" class="nav-link">
                            <i class="fas fa-chart-bar"></i>
                            <span class="nav-text">Reportes</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="nav-text">Pedidos</span>
                    <span class="badge bg-warning float-end">12</span>
                </a>
                <ul class="nav flex-column submenu">
                    <li class="nav-item">
                        <a href="index.php?url=pedido" class="nav-link <?= ($_GET['url'] ?? '') === 'pedido' ? 'active' : '' ?>">
                            <i class="fas fa-list"></i>
                            <span class="nav-text">Todos los Pedidos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=pedido&action=pending" class="nav-link">
                            <i class="fas fa-clock"></i>
                            <span class="nav-text">Pendientes</span>
                            <span class="badge bg-danger">5</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=pedido&action=completed" class="nav-link">
                            <i class="fas fa-check"></i>
                            <span class="nav-text">Completados</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=pedido&action=create" class="nav-link">
                            <i class="fas fa-plus"></i>
                            <span class="nav-text">Nuevo Pedido</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Sección: Gestión de Productos -->
            <li class="menu-section">Gestión de Productos</li>
            
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-cookie-bite"></i>
                    <span class="nav-text">Productos</span>
                    <span class="module-status status-active">Activo</span>
                </a>
                <ul class="nav flex-column submenu">
                    <li class="nav-item">
                        <a href="index.php?url=producto" class="nav-link <?= ($_GET['url'] ?? '') === 'producto' ? 'active' : '' ?>">
                            <i class="fas fa-list"></i>
                            <span class="nav-text">Catálogo</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=producto&action=create" class="nav-link">
                            <i class="fas fa-plus"></i>
                            <span class="nav-text">Nuevo Producto</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=producto&action=categories" class="nav-link">
                            <i class="fas fa-tags"></i>
                            <span class="nav-text">Categorías</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=producto&action=inventory" class="nav-link">
                            <i class="fas fa-boxes"></i>
                            <span class="nav-text">Inventario</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-boxes-stacked"></i>
                    <span class="nav-text">Movimientos</span>
                </a>
                <ul class="nav flex-column submenu">
                    <li class="nav-item">
                        <a href="index.php?url=movimiento" class="nav-link <?= ($_GET['url'] ?? '') === 'movimiento' ? 'active' : '' ?>">
                            <i class="fas fa-list"></i>
                            <span class="nav-text">Historial</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=movimiento&action=entry" class="nav-link">
                            <i class="fas fa-arrow-down"></i>
                            <span class="nav-text">Entradas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=movimiento&action=exit" class="nav-link">
                            <i class="fas fa-arrow-up"></i>
                            <span class="nav-text">Salidas</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Sección: Finanzas -->
            <li class="menu-section">Gestión Financiera</li>
            
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-credit-card"></i>
                    <span class="nav-text">Pagos</span>
                    <span class="badge bg-success">3</span>
                </a>
                <ul class="nav flex-column submenu">
                    <li class="nav-item">
                        <a href="index.php?url=pago" class="nav-link <?= ($_GET['url'] ?? '') === 'pago' ? 'active' : '' ?>">
                            <i class="fas fa-list"></i>
                            <span class="nav-text">Todos los Pagos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=pago&action=pending" class="nav-link">
                            <i class="fas fa-clock"></i>
                            <span class="nav-text">Pendientes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=pago&action=verified" class="nav-link">
                            <i class="fas fa-check-circle"></i>
                            <span class="nav-text">Verificados</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?url=pago&action=reports" class="nav-link">
                            <i class="fas fa-chart-pie"></i>
                            <span class="nav-text">Reportes</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Sección: Sistema -->
            <li class="menu-section">Sistema</li>
            
            <li class="nav-item">
                <a href="index.php?url=users" class="nav-link">
                    <i class="fas fa-user-shield"></i>
                    <span class="nav-text">Usuarios</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="index.php?url=reports" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span class="nav-text">Reportes Avanzados</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="index.php?url=settings" class="nav-link">
                    <i class="fas fa-cogs"></i>
                    <span class="nav-text">Configuración</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Información del sistema -->
    <div class="mt-auto p-3" style="border-top: 1px solid rgba(255, 255, 255, 0.1);">
        <div class="small text-center" style="opacity: 0.7;">
            <div>Natys v1.0</div>
            <img src="/Natys/Assets/img/Natys.png" alt="Natys" class="sidebar-logo" style="height: 60px;">
            <div class="mt-1">Sistema Online</div>
        </div>
    </div>
</aside>

<!-- Botón para móviles -->
<button class="btn btn-primary d-md-none position-fixed" style="bottom: 20px; right: 20px; z-index: 1040; width: 50px; height: 50px; border-radius: 50%;" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
    <i class="fas fa-bars"></i>
</button>

<!-- Offcanvas para móviles (versión simplificada) -->
<div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="sidebarOffcanvas">
    <div class="offcanvas-header bg-dark text-white">
        <h5 class="offcanvas-title">
            <img src="/Natys/Assets/img/Natys.png" alt="Natys" height="30" class="me-2">
            Natys
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body bg-dark p-0">
        <!-- Contenido similar al sidebar pero adaptado para móviles -->
    </div>
</div>
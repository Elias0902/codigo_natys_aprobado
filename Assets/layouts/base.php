<!-- App/Views/layouts/base.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'SISTEMA DE GALLETAS NATYS' ?></title>
    <link rel="icon" href="/Natys/Assets/img/natys.png" type="image/x-icon">
    
    <!-- CSS -->
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.5.2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <!-- Sidebar CSS local -->
    <link href="/Natys/Assets/css/styles.css" rel="stylesheet">
    
    <!-- Estilos para z-index de modales y notificaciones -->
    <style>
        .modal-backdrop { z-index: 1050 !important; }
        .modal { z-index: 1060 !important; }
        .toast-container, #toast-container { z-index: 1080 !important; }
        .navbar.fixed-top { z-index: 1030; }
    </style>
    
    <!-- Estilos adicionales específicos de la vista -->
    <?php if (isset($css)): ?>
        <link rel="stylesheet" href="<?= $css ?>">
    <?php endif; ?>
</head>
<body>
    <?php include '../Natys/Assets/partials/sidebar.php'; ?>
    <?php include '../Natys/Assets/partials/header.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid py-4">
            <?= $content ?>
        </div>
    </div>

    <!-- JavaScript -->
    <!-- jQuery 3.7.1 (requerido por DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5.3.3 Bundle JS (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script del menú de perfil - Versión mejorada para todos los módulos -->
    <script>
    // Función para inicializar el menú de perfil
    function initProfileDropdown() {
        const PROFILE_BUTTON_ID = 'dropdownUser';
        const PROFILE_MENU_ID = 'profile-dropdown-menu';
        const NOTIFICATION_DROPDOWN_ID = 'dropdownNotificaciones';
        
        // Función para configurar el dropdown
        function setupDropdown() {
            const profileButton = document.getElementById(PROFILE_BUTTON_ID);
            if (!profileButton) return false;
            
            // Si ya está configurado, salir
            if (profileButton.__dropdownInitialized) return true;
            
            // Obtener el menú desplegable
            let profileMenu = document.getElementById(PROFILE_MENU_ID);
            
            // Si no se encuentra por ID, buscarlo como hermano del botón
            if (!profileMenu) {
                let parent = profileButton.closest('.dropdown');
                if (parent) {
                    profileMenu = parent.querySelector('.dropdown-menu');
                    if (profileMenu) profileMenu.id = PROFILE_MENU_ID;
                }
            }
            
            if (!profileMenu) return false;
            
            // Marcar como inicializado
            profileButton.__dropdownInitialized = true;
            
            // Configuración básica del menú
            profileMenu.style.display = 'none';
            
            // Función para mostrar/ocultar el menú
            const toggleMenu = (show = null) => {
                const shouldShow = show !== null ? show : !profileMenu.style.display || profileMenu.style.display === 'none';
                
                if (shouldShow) {
                    // Cerrar otros menús
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        if (menu.id !== PROFILE_MENU_ID) {
                            menu.style.display = 'none';
                            menu.classList.remove('show');
                        }
                    });
                    
                    // Cerrar menú de notificaciones si está abierto
                    const notificationMenu = document.getElementById(NOTIFICATION_DROPDOWN_ID);
                    if (notificationMenu && notificationMenu.classList.contains('show')) {
                        notificationMenu.style.display = 'none';
                        notificationMenu.classList.remove('show');
                        const notificationButton = document.getElementById('btnNotificaciones');
                        if (notificationButton) {
                            notificationButton.setAttribute('aria-expanded', 'false');
                        }
                    }
                    
                    // Mostrar este menú
                    profileMenu.style.display = 'block';
                    profileButton.setAttribute('aria-expanded', 'true');
                    
                    // Posicionar el menú de manera fija
                    profileMenu.style.position = 'fixed';
                    profileMenu.style.top = '60px';
                    profileMenu.style.right = '20px';
                    profileMenu.style.left = 'auto';
                    profileMenu.style.zIndex = '10000';
                    profileMenu.style.width = '220px';
                    
                    // Agregar clase para animación
                    profileMenu.classList.add('show');
                } else {
                    profileMenu.style.display = 'none';
                    profileButton.setAttribute('aria-expanded', 'false');
                    profileMenu.classList.remove('show');
                }
            };
            
            // Limpiar manejadores anteriores
            profileButton.onclick = null;
            
            // Manejador de clic en el botón
            profileButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleMenu();
            });
            
            // Cerrar al hacer clic fuera
            const closeOnOutsideClick = (e) => {
                const target = e.target;
                const isProfileButton = profileButton.contains(target);
                const isProfileMenu = profileMenu.contains(target);
                const isNotificationButton = document.getElementById('btnNotificaciones')?.contains(target);
                const isNotificationMenu = document.getElementById(NOTIFICATION_DROPDOWN_ID)?.contains(target);
                
                if (profileMenu.style.display === 'block' && 
                    !isProfileButton && 
                    !isProfileMenu &&
                    !isNotificationButton &&
                    !isNotificationMenu) {
                    toggleMenu(false);
                }
            };
            
            // Agregar manejador de clic global
            document.removeEventListener('click', closeOnOutsideClick);
            document.addEventListener('click', closeOnOutsideClick);
            
            // Cerrar con tecla Escape
            const handleKeyDown = (e) => {
                if (e.key === 'Escape' && profileMenu.style.display === 'block') {
                    toggleMenu(false);
                }
            };
            
            document.removeEventListener('keydown', handleKeyDown);
            document.addEventListener('keydown', handleKeyDown);
            
            return true;
        }
        
        // Manejar la inicialización
        const initialize = () => {
            // Eliminar cualquier instancia de Bootstrap que pueda interferir
            if (window.bootstrap && bootstrap.Dropdown) {
                const btn = document.getElementById(PROFILE_BUTTON_ID);
                if (btn) {
                    const instance = bootstrap.Dropdown.getInstance(btn);
                    if (instance) instance.dispose();
                }
            }
            
            // Configurar el menú
            if (!setupDropdown()) {
                // Reintentar si falla la primera vez
                setTimeout(setupDropdown, 100);
            }
        };
        
        // Inicializar
        initialize();
        
        // Devolver la función de inicialización para llamadas posteriores
        return initialize;
    }
    
    // Inicializar cuando el DOM esté listo
    let initFunction;
    
    const init = () => {
        initFunction = initProfileDropdown();
        
        // Asegurarse de que los menús se cierren al hacer scroll
        window.addEventListener('scroll', function() {
            const profileMenu = document.getElementById('profile-dropdown-menu');
            const notificationMenu = document.getElementById('dropdownNotificaciones');
            
            if (profileMenu && profileMenu.style.display === 'block') {
                // Actualizar la posición del menú de perfil si es necesario
                profileMenu.style.top = '60px';
                profileMenu.style.right = '20px';
            }
            
            if (notificationMenu && notificationMenu.style.display === 'block') {
                // Actualizar la posición del menú de notificaciones si es necesario
                notificationMenu.style.top = '60px';
                notificationMenu.style.right = '20px';
            }
        });
    };
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 0);
    }
    
    // Manejar navegación AJAX
    document.addEventListener('ajaxComplete', () => {
        if (typeof initFunction === 'function') {
            setTimeout(initFunction, 100);
        }
    });
    
    // También exponer para llamadas manuales
    window.NatysApp = window.NatysApp || {};
    window.NatysApp.initProfileDropdown = initProfileDropdown;
    </script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- App JS global: inicializa dropdowns del header y otros componentes -->
    <script src="/Natys/Assets/js/app.js"></script>
    <!-- Fallback independiente para el dropdown del header -->
    <script src="/Natys/Assets/js/header-dropdown.js"></script>

    <!-- Scripts personalizados -->
    <script src="/Natys/Assets/js/main.js"></script>
    <!-- Script para arreglar menús desplegables -->
    <script src="/Natys/Assets/js/fix-dropdowns.js"></script>
    <?php if (isset($js)): ?>
        <script src="<?= $js ?>"></script>
    <?php endif; ?>
</body>
</html>

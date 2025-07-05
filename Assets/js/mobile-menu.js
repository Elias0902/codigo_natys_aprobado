document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const sidebar = document.querySelector('.l-navbar');
    const body = document.body;
    
    // Función para alternar el menú móvil
    function toggleMobileMenu() {
        sidebar.classList.toggle('show');
        body.classList.toggle('sidebar-open');
        
        // Bloquear el scroll del body cuando el menú está abierto
        if (body.classList.contains('sidebar-open')) {
            body.style.overflow = 'hidden';
        } else {
            body.style.overflow = '';
        }
    }
    
    // Evento para el botón de menú móvil
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    }
    
    // Cerrar el menú al hacer clic fuera de él
    document.addEventListener('click', function(e) {
        if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target) && body.classList.contains('sidebar-open')) {
            toggleMobileMenu();
        }
    });
    
    // Cerrar el menú al hacer clic en un enlace
    const navLinks = document.querySelectorAll('.nav__link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                toggleMobileMenu();
            }
        });
    });
    
    // Ajustar el ancho del contenido principal cuando se redimensiona la ventana
    function adjustLayout() {
        if (window.innerWidth > 1024) {
            // En pantallas grandes, asegurarse de que el menú esté visible
            sidebar.classList.remove('show');
            body.classList.remove('sidebar-open');
            body.style.overflow = '';
        }
    }
    
    // Ejecutar al cargar y al redimensionar
    window.addEventListener('resize', adjustLayout);
    adjustLayout();
});

// Assets/js/profile-dropdown.js
// Solución robusta para el menú desplegable del perfil
(function() {
    'use strict';

    // Constantes
    const PROFILE_BUTTON_ID = 'dropdownUser';
    const PROFILE_MENU_ID = 'profile-dropdown-menu';
    
    // Inicialización
    function init() {
        const profileButton = document.getElementById(PROFILE_BUTTON_ID);
        if (!profileButton) return;
        
        // Obtener el menú desplegable
        let profileMenu = document.getElementById(PROFILE_MENU_ID);
        
        // Si no se encuentra por ID, buscarlo como hermano del botón
        if (!profileMenu && profileButton.nextElementSibling && 
            profileButton.nextElementSibling.classList.contains('dropdown-menu')) {
            profileMenu = profileButton.nextElementSibling;
            profileMenu.id = PROFILE_MENU_ID;
        }
        
        if (!profileMenu) return;
        
        // Configurar atributos necesarios
        profileButton.setAttribute('data-bs-toggle', 'dropdown');
        profileButton.setAttribute('aria-expanded', 'false');
        profileButton.setAttribute('data-bs-auto-close', 'outside');
        
        // Remover cualquier manejador de eventos previo
        const newButton = profileButton.cloneNode(true);
        profileButton.parentNode.replaceChild(newButton, profileButton);
        
        // Manejador de clic mejorado
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            const isOpen = profileMenu.classList.contains('show');
            
            // Cerrar todos los demás menús
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                if (menu !== profileMenu) menu.classList.remove('show');
            });
            
            // Alternar el menú actual
            profileMenu.classList.toggle('show', !isOpen);
            newButton.setAttribute('aria-expanded', (!isOpen).toString());
            
            // Posicionar el menú correctamente
            if (!isOpen) {
                positionDropdown(newButton, profileMenu);
            }
        });
        
        // Cerrar al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!profileMenu.classList.contains('show')) return;
            if (!newButton.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.remove('show');
                newButton.setAttribute('aria-expanded', 'false');
            }
        }, true);
        
        // Manejar tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && profileMenu.classList.contains('show')) {
                profileMenu.classList.remove('show');
                newButton.setAttribute('aria-expanded', 'false');
            }
        });
    }
    
    // Posicionar el menú desplegable
    function positionDropdown(button, menu) {
        const rect = button.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.top = `${rect.bottom + window.scrollY}px`;
        menu.style.left = `${rect.left + window.scrollX}px`;
        menu.style.right = 'auto';
        menu.style.zIndex = '1070'; // Mayor que el z-index de Bootstrap
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Hacer la función accesible globalmente para ser llamada desde otros lugares si es necesario
    window.NatysApp = window.NatysApp || {};
    window.NatysApp.initProfileDropdown = init;
    
})();

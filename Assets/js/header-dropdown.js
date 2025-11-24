// Assets/js/header-dropdown.js
// Manejo mejorado de dropdowns para evitar conflictos
(function(){
  'use strict';

  // ID único para el dropdown del perfil
  const PROFILE_DROPDOWN_ID = 'profile-dropdown-menu';
  
  // Función para cerrar todos los dropdowns excepto el especificado
  function closeAll(container, excludeId = null) {
    const menus = (container || document).querySelectorAll('.dropdown-menu.show');
    menus.forEach(function(menu) {
      if (!excludeId || menu.id !== excludeId) {
        menu.classList.remove('show');
      }
    });
    
    const toggles = (container || document).querySelectorAll('[data-bs-toggle="dropdown"][aria-expanded="true"]');
    toggles.forEach(function(toggle) {
      if (!excludeId || toggle.getAttribute('data-target') !== '#' + excludeId) {
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function setup(container){
    var scope = container || document;
    var toggles = scope.querySelectorAll('[data-bs-toggle="dropdown"]');
    toggles.forEach(function(btn){
      // Evitar múltiples bindings
      if (btn.__natysDropdownBound) return;
      btn.__natysDropdownBound = true;

      btn.addEventListener('click', function(ev){
        try {
          console.debug('[header-dropdown] click en dropdown toggle', btn.id || btn.className);
          ev.preventDefault();
          ev.stopPropagation();

          // Si Bootstrap ya está, deja que maneje; como fallback, hacemos manual.
          if (window.bootstrap && bootstrap.Dropdown) {
            var inst = bootstrap.Dropdown.getOrCreateInstance(btn);
            inst.toggle();
            console.debug('[header-dropdown] toggle via Bootstrap.Dropdown');
            return;
          }

          // Manual toggle
          var menu = btn.parentElement ? btn.parentElement.querySelector('.dropdown-menu') : null;
          if (!menu) return;

          var isOpen = menu.classList.contains('show');
          closeAll(document);
          if (!isOpen){
            menu.classList.add('show');
            btn.setAttribute('aria-expanded','true');
            console.debug('[header-dropdown] toggle manual OPEN');
          }
        } catch(e) { /* noop */ }
      });
    });

    // Cerrar al hacer click fuera
    if (!document.__natysDropdownGlobalCloseBound) {
      document.addEventListener('click', function(){ closeAll(document); });
      document.__natysDropdownGlobalCloseBound = true;
    }
  }

  // Inicializar en DOMContentLoaded y en load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function(){ setup(document); });
  } else {
    setup(document);
  }
  window.addEventListener('load', function(){ setup(document); });

  // Exponer para reinicializar tras AJAX
  window.NatysApp = window.NatysApp || {};
  window.NatysApp.bindHeaderDropdownFallback = setup;

  // Manejador mejorado para el dropdown del perfil
  function setupProfileDropdown() {
    const btnUser = document.getElementById('dropdownUser');
    if (!btnUser) return;

    // Asegurarse de que el menú tenga un ID único
    const menu = btnUser.parentElement && btnUser.parentElement.querySelector('.dropdown-menu');
    if (menu && !menu.id) {
      menu.id = PROFILE_DROPDOWN_ID;
    }

    // Configurar el atributo data-target si no existe
    if (!btnUser.getAttribute('data-target') && menu) {
      btnUser.setAttribute('data-target', '#' + menu.id);
    }

    // Evitar múltiples bindings
    if (btnUser.__natysEnhancedBound) return;
    btnUser.__natysEnhancedBound = true;

    // Manejador de clics mejorado
    btnUser.addEventListener('click', function(e) {
      try {
        console.debug('[header-dropdown] Perfil: click en dropdown');
        e.preventDefault();
        e.stopImmediatePropagation();

        // Usar Bootstrap si está disponible
        if (window.bootstrap && bootstrap.Dropdown) {
          const dropdown = bootstrap.Dropdown.getOrCreateInstance(btnUser);
          dropdown.toggle();
          return;
        }

        // Fallback manual
        if (!menu) return;
        const isOpen = menu.classList.contains('show');
        
        // Cerrar otros dropdowns pero mantener este abierto si ya lo está
        closeAll(document, isOpen ? null : PROFILE_DROPDOWN_ID);
        
        if (!isOpen) {
          menu.classList.add('show');
          btnUser.setAttribute('aria-expanded', 'true');
        }
      } catch (error) {
        console.error('Error en el manejador del dropdown:', error);
      }
    });

    // Cerrar al hacer clic fuera
    document.addEventListener('click', function(e) {
      if (!menu || !menu.classList.contains('show')) return;
      if (!btnUser.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('show');
        btnUser.setAttribute('aria-expanded', 'false');
      }
    }, true);
  }

  // Inicializar el dropdown del perfil
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupProfileDropdown);
  } else {
    setupProfileDropdown();
  }
})();

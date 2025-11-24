// Assets/js/app.js
// Inicialización global para componentes Bootstrap usados en el header
(function () {
  'use strict';

  // Función para inicializar los dropdowns, excluyendo el menú de perfil
  function initDropdowns(root) {
    try {
      // Si Bootstrap JS no está presente, no hacer nada
      if (!(window.bootstrap && bootstrap.Dropdown)) {
        return;
      }
      
      // Seleccionar todos los dropdowns excepto el del perfil
      const dropdownToggleList = Array.from((root || document).querySelectorAll('[data-bs-toggle="dropdown"]'));
      
      dropdownToggleList.forEach(function (dropdownToggleEl) {
        // Saltar el menú de perfil
        if (dropdownToggleEl.id === 'dropdownUser') {
          return;
        }
        
        // Evitar crear instancias duplicadas
        const existing = bootstrap.Dropdown.getInstance(dropdownToggleEl);
        if (!existing) {
          const dropdown = new bootstrap.Dropdown(dropdownToggleEl);
          
          // Manejar eventos manualmente para mayor control
          dropdownToggleEl.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropdown.toggle();
          });
        }
      });
    } catch (e) {
      console.error('Error al inicializar dropdowns:', e);
    }
  }

  // Inicializar al cargar el DOM
  function init() {
    initDropdowns(document);
    
    // Si existe el menú de perfil, asegurarse de que no tenga conflictos
    const profileButton = document.getElementById('dropdownUser');
    if (profileButton) {
      // Remover cualquier inicialización automática de Bootstrap
      const existing = bootstrap.Dropdown.getInstance(profileButton);
      if (existing) {
        existing.dispose();
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    setTimeout(init, 100); // Pequeño retraso para asegurar que todo esté listo
  }

  // Reintentar después de la carga completa
  window.addEventListener('load', init);

  // Exponer para re-inicializar después de AJAX
  window.NatysApp = window.NatysApp || {};
  window.NatysApp.initHeaderDropdowns = initDropdowns;
  
  // Manejar eventos de navegación AJAX (si se usa)
  document.addEventListener('ajaxComplete', init);
})();

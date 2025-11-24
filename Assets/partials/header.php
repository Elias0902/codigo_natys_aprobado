<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir helper para obtener el valor del dólar
$dolarHelperPath = dirname(dirname(dirname(__DIR__))) . '/App/Helpers/dolar.php';
$valorDolar = [
    'valor' => '227,00',
    'fecha' => date('d/m/Y H:i'),
    'actualizado' => false
];

// Intentar cargar el valor actualizado
if (file_exists($dolarHelperPath)) {
    require_once $dolarHelperPath;
    $dolarData = @obtenerValorDolar();
    if (is_array($dolarData) && !empty($dolarData['valor'])) {
        $valorDolar = $dolarData;
    }
}

$usuario = $_SESSION['usuario']['usuario'] ?? 'Invitado';
$rol = $_SESSION['usuario']['rol'] ?? '';
$usuarioId = $_SESSION['usuario']['id'] ?? 0;
$imagenDefault = '/Natys/Assets/img/defaultAvatar.jpg';

// Usar la imagen de perfil almacenada en sesión si está disponible
if ($usuarioId && isset($_SESSION['usuario']['imagen_perfil'])) {
    $imagenUrl = $_SESSION['usuario']['imagen_perfil'] . '?t=' . time(); // Añadir timestamp para evitar caché
} else {
    $imagenUrl = $imagenDefault;
}
?>

<!-- Header reutilizable -->
<nav class="navbar navbar-expand-lg navbar-dark border-bottom shadow-sm fixed-top" style="background-color:#d31111; z-index: 1030;">
  <div class="container-fluid px-1">
    <!-- Logo a la izquierda -->

    <!-- Botón de hamburguesa para el sidebar (añadido aquí) -->
    <button class="btn btn-link text-white sidebar-toggle d-none d-md-block ms-2" type="button" style="margin-right: 0px;">
        <i class="fas fa-bars"></i>
    </button>

        <a class="navbar-brand d-flex align-items-center" href="index.php?url=home" style="gap:.5rem; padding-left: .5rem;">
      <img src="/Natys/Assets/img/Natys.png" alt="Logo" style="height:55px; width:auto; object-fit:contain;" onerror="this.style.display='none'">
    </a>

    <div class="d-flex align-items-center ms-auto" style="gap: 1rem;">
      <!-- Mostrar valor del dólar -->
      <div class="d-flex flex-column align-items-end text-white" style="gap: 0.1rem;">
        <div class="d-flex align-items-center" style="gap: 0.3rem;">
          <i class="fas fa-sync-alt fa-spin" style="color: #ffffff; font-size: 16px; display: none;" id="dolar-loading"></i>
          <i class="fas fa-dollar-sign" style="color: #ffd700; font-size: 20px;" id="dolar-icon"></i>
          <span class="fw-bold" style="font-size: 20px;" id="dolar-value">
            <?php echo $valorDolar['valor']; ?> Bs
          </span>
          <?php if (!$valorDolar['actualizado']): ?>
            <i class="fas fa-exclamation-triangle text-warning" title="Valor en caché"></i>
          <?php endif; ?>
        </div>
        <div class="d-flex align-items-center" style="gap: 4px;">
          <small class="text-white-50" style="font-size: 16px; line-height: 1;" id="dolar-date">
            <?php echo $valorDolar['fecha']; ?>
          </small>
          <a href="#" id="refresh-dolar" class="text-white-50" title="Actualizar" style="font-size: 14px;">
            <i class="fas fa-sync-alt"></i>
          </a>
        </div>
      </div>
      
      <script>
      document.addEventListener('DOMContentLoaded', function() {
          const refreshBtn = document.getElementById('refresh-dolar');
          
          if (refreshBtn) {
              refreshBtn.addEventListener('click', function(e) {
                  e.preventDefault();
                  updateDolarValue();
              });
          }
          
          function updateDolarValue() {
              const loadingIcon = document.getElementById('dolar-loading');
              const dolarIcon = document.getElementById('dolar-icon');
              
              // Mostrar carga
              if (loadingIcon) loadingIcon.style.display = 'inline-block';
              if (dolarIcon) dolarIcon.style.display = 'none';
              
              // Hacer la petición
              fetch('/Natys/App/Helpers/get_dolar.php')
                  .then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          document.getElementById('dolar-value').textContent = data.valor + ' Bs';
                          document.getElementById('dolar-date').textContent = data.fecha;
                          
                          // Actualizar el ícono de caché si es necesario
                          const cacheIcon = document.querySelector('#dolar-value ~ .fa-exclamation-triangle');
                          if (data.actualizado) {
                              if (cacheIcon) cacheIcon.remove();
                          } else if (!cacheIcon) {
                              const icon = document.createElement('i');
                              icon.className = 'fas fa-exclamation-triangle text-warning';
                              icon.title = 'Valor en caché';
                              document.getElementById('dolar-value').after(icon);
                          }
                      }
                  })
                  .catch(error => console.error('Error al actualizar el dólar:', error))
                  .finally(() => {
                      // Restaurar íconos
                      if (loadingIcon) loadingIcon.style.display = 'none';
                      if (dolarIcon) dolarIcon.style.display = 'inline-block';
                  });
          }
          
          // Actualizar cada 5 minutos
          setInterval(updateDolarValue, 300000);
      });
      </script>
      
      <!-- Espaciador tipo 'módulo' para separación uniforme -->
      <div id="userSpacer" class="d-none d-sm-block" style="height:1px; width: var(--module-width); flex: 0 0 var(--module-width);"></div>

      <!-- Dropdown Usuario -->
      <div class="dropdown" id="profile-dropdown-container">
        <button class="btn btn-outline-light d-flex align-items-center dropdown-toggle border-0" 
                type="button" 
                id="dropdownUser" 
                data-bs-toggle="dropdown" 
                data-bs-auto-close="outside"
                aria-expanded="false" 
                style="gap:.5rem; background: transparent;"
                data-bs-offset="0,8">
          <?php $usuarioId = $_SESSION['usuario']['id'] ?? 0; ?>
          <img id="header-profile-img" 
               src="<?php echo $imagenUrl; ?>" 
               alt="Foto de perfil" 
               class="rounded-circle" 
               style="width:32px;height:32px;object-fit:cover;"
               onerror="this.src='<?php echo $imagenDefault; ?>'; this.onerror=null;"
               data-usuario-id="<?php echo $usuarioId; ?>">
          <span class="text-start">
            <span class="d-block fw-semibold text-white user-name" style="line-height:1;"><?php echo htmlspecialchars($usuario) ?></span>
            <?php if (!empty($rol)): ?>
              <small class="text-white-50 user-role" style="line-height:1;"><?php echo htmlspecialchars($rol) ?></small>
            <?php endif; ?>
          </span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow" 
            id="profile-dropdown-menu" 
            aria-labelledby="dropdownUser"
            style="min-width: 220px; z-index: 1070; position: fixed; right: 20px; left: auto !important; will-change: transform; font-size:20px"
            data-bs-placement="top"
            data-bs-offset="0,10"
            data-bs-reference="parent"
            data-bs-boundary="viewport">
        <li>
            <a class="dropdown-item <?php echo ((($_GET['url'] ?? '') === 'user' && ($_GET['type'] ?? '') === 'perfil' && !isset($_GET['action'])) ? 'active bg-dark' : '') ?>" href="index.php?url=user&type=perfil">
                <i class="fa-solid fa-user me-2"></i>Mi Perfil
            </a>
        </li>  
          <?php if(in_array($_SESSION['usuario']['rol'], ['admin', 'superadmin'])): ?>
      <li>
        <a class="dropdown-item text-night <?php echo ((($_GET['url'] ?? '') === 'user' && ($_GET['action'] ?? '') === 'listar') ? 'active bg-dark' : '') ?>" href="index.php?url=user&type=perfil&action=listar">
            <i class="fa-solid fa-gear me-2"></i>Gestion Usuarios
        </a>
    </li>
    <?php endif; ?>
          <li>
            <a class="dropdown-item <?php echo (($_GET['url'] ?? '') === 'manual' ? 'active bg-dark' : '') ?>" href="index.php?url=manual">
              <i class="fas fa-question-circle me-2"></i>Ayuda
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-danger" href="index.php?url=user&type=login&action=cerrarSesion">
              <i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar sesión
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<!-- Estilos del dropdown del header (scope: solo dentro de la navbar) -->
<style>
  /* Fondo y bordes del menú desplegable */
  .navbar .dropdown-menu {
    background-color: #343a40;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    margin-top: 0.5rem;
  }

  /* Ítems del menú: texto blanco por defecto */
  .navbar .dropdown-menu .dropdown-item {
    color: #ffffff;
  }

  /* Hover, focus y estados activos: tono más oscuro y texto blanco */
  .navbar .dropdown-menu .dropdown-item:hover,
  .navbar .dropdown-menu .dropdown-item:focus,
  .navbar .dropdown-menu .dropdown-item.active,
  .navbar .dropdown-menu .dropdown-item:active {
    background-color: #7b0a0a;
    color: #ffffff;
  }

  /* Divider más sutil sobre fondo oscuro */
  .navbar .dropdown-divider {
    border-top-color: rgba(255, 255, 255, 0.15);
  }

  /* Asegurar contraste para links marcados como peligrosos */
  .navbar .dropdown-item.text-danger {
    color: #ffb3b3 !important;
  }
  .navbar .dropdown-item.text-danger:hover {
    background-color: #6e0707;
    color: #ffffff !important;
  }

  /* Botón de usuario (recuadro con avatar) en el header */
  /* Mantener apariencia por defecto (btn-outline-light) en reposo y hover */
  /* Aplicar esquema oscuro SOLO cuando el dropdown esté abierto */
  .navbar .dropdown.show #dropdownUser,
  .navbar #dropdownUser[aria-expanded="true"] {
    background-color: #920a0a;
    border-color: #6e0707;
    color: #ffffff;
  }
  .navbar .dropdown.show #dropdownUser:hover,
  .navbar #dropdownUser[aria-expanded="true"]:hover,
  .navbar #dropdownUser[aria-expanded="true"]:focus {
    background-color: #7b0a0a;
    border-color: #6e0707;
    color: #ffffff;
  }
  /* Avatar más legible cuando está abierto */
  .navbar .dropdown.show #dropdownUser .rounded-circle,
  .navbar #dropdownUser[aria-expanded="true"] .rounded-circle {
    background-color: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
  }
  .navbar .dropdown.show #dropdownUser i,
  .navbar #dropdownUser[aria-expanded="true"] i {
    color: #ffffff;
  }

  /* Hover (cuando NO está abierto) con el mismo esquema oscuro */
  .navbar #dropdownUser:hover {
    background-color: #7b0a0a;
    border-color: #6e0707;
    color: #ffffff;
  }
  .navbar #dropdownUser:hover .rounded-circle {
    background-color: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
  }
  .navbar #dropdownUser:hover i {
    color: #ffffff;
  }

  /* Fondo y bordes del menú desplegable */
  .navbar .dropdown-menu {
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    margin-top: 0.5rem;
  }

  /* Asegurar que el dropdown de notificaciones esté por encima de otros elementos */
  #dropdownNotificaciones {
    z-index: 1060;
    display: none;
  }

  #dropdownNotificaciones.show {
    display: block;
  }

  /* Estilo para los elementos de notificación */
  .notificacion-item {
    transition: background-color 0.2s ease;
    white-space: normal;
    word-wrap: break-word;
  }

  .notificacion-item:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
  }

  /* Ajustar el scrollbar del menú de notificaciones */
  #dropdownNotificaciones::-webkit-scrollbar {
    width: 6px;
  }

  #dropdownNotificaciones::-webkit-scrollbar-track {
    background: #343a40;
  }

  #dropdownNotificaciones::-webkit-scrollbar-thumb {
    background: #6c757d;
    border-radius: 3px;
  }

  #dropdownNotificaciones::-webkit-scrollbar-thumb:hover {
    background: #5a6268;
  }

  /* Estilos para el botón de hamburguesa en el header */
  .navbar .sidebar-toggle {
    font-size: 1.25rem;
    padding: 0.25rem 0.5rem;
    margin-right: 0.5rem;
  }
  
  .navbar .sidebar-toggle:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
  }

  /* Overrides tamaño de texto usuario/rol en header */
  .navbar #dropdownUser .user-name {
    font-size: 20px !important;
    line-height: 1;
  }
  .navbar #dropdownUser .user-role {
    font-size: 20px !important;
    line-height: 1;
  }
</style>

<!-- El manejo de notificaciones se encuentra en /Assets/js/notificaciones-header.js -->
class SessionManager {
    constructor() {
        this.timeout = 20 * 1000; // 20 segundos (debe coincidir con PHP)
        this.warningTime = 15 * 1000; // Mostrar advertencia a los 15s
        this.modal = null;
        this.timers = {
            warning: null,
            logout: null,
            checker: null
        };
        
        this.init();
    }
    
    init() {
        this.createModal();
        this.setupEventListeners();
        this.startSessionChecker();
        this.resetTimers();
    }
    
    createModal() {
        this.modal = document.createElement('div');
        this.modal.id = 'session-timeout-modal';
        this.modal.style.cssText = `
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        `;
        
        this.modal.innerHTML = `
            <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 0 20px rgba(0,0,0,0.2);">
                <h3 style="margin-top: 0; color: #d31111;">¡Sesión por expirar!</h3>
                <p>Tu sesión se cerrará en <span id="countdown">5</span> segundos por inactividad.</p>
                <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1.5rem;">
                    <button id="continue-session" style="padding: 0.5rem 1.5rem; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Continuar</button>
                    <button id="logout-now" style="padding: 0.5rem 1.5rem; background: #d31111; color: white; border: none; border-radius: 4px; cursor: pointer;">Salir</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(this.modal);
        
        document.getElementById('continue-session').addEventListener('click', () => this.resetTimers());
        document.getElementById('logout-now').addEventListener('click', () => this.logout());
    }
    
    showWarning() {
        this.modal.style.display = 'flex';
        this.startCountdown();
    }
    
    startCountdown() {
        let seconds = 5;
        const countdown = document.getElementById('countdown');
        countdown.textContent = seconds;
        
        const interval = setInterval(() => {
            seconds--;
            countdown.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(interval);
                this.logout();
            }
        }, 1000);
    }
    
    logout() {
        window.location.href = 'index.php?url=user&type=login&action=cerrarSesion&reason=timeout';
    }
    
    resetTimers() {
        clearTimeout(this.timers.warning);
        clearTimeout(this.timers.logout);
        
        this.timers.warning = setTimeout(() => this.showWarning(), this.warningTime);
        this.timers.logout = setTimeout(() => this.logout(), this.timeout);
        
        this.modal.style.display = 'none';
        this.sendHeartbeat();
    }
    
    sendHeartbeat() {
        fetch('index.php?url=user&type=login&action=heartbeat', {
            method: 'POST',
            credentials: 'same-origin'
        }).catch(console.error);
    }
    
    setupEventListeners() {
        const events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
        events.forEach(evt => {
            window.addEventListener(evt, () => this.resetTimers());
        });
    }
    
    startSessionChecker() {
        this.timers.checker = setInterval(() => {
            this.checkSession();
        }, 1000);
    }
    
    checkSession() {
        fetch('index.php?url=user&type=login&action=check_session', {
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (!data.active) {
                window.location.href = 'index.php?url=user&type=login';
            }
        })
        .catch(console.error);
    }
}

// Iniciar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new SessionManager();
});
// ============================================
// SISTEMA DE CARGA GLOBAL
// ============================================
let loadingTimeout = null;
const LOADING_MAX_DURATION = 8000;

function createLoadingOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.id = 'globalLoadingOverlay';
    overlay.innerHTML = `
        <div class="loading-spinner"></div>
        <div class="loading-text">Cargando...</div>
    `;
    document.body.appendChild(overlay);
    return overlay;
}

function showLoader(message = 'Cargando...') {
    let overlay = document.getElementById('globalLoadingOverlay');
    
    if (!overlay) {
        overlay = createLoadingOverlay();
    }
    
    const textElement = overlay.querySelector('.loading-text');
    if (textElement) {
        textElement.textContent = message;
    }
    
    setTimeout(() => {
        overlay.classList.add('active');
    }, 10);
    
    if (loadingTimeout) {
        clearTimeout(loadingTimeout);
    }
    
    loadingTimeout = setTimeout(() => {
        hideLoader();
        console.warn('Carga automática cerrada después de 8 segundos');
    }, LOADING_MAX_DURATION);
}

function hideLoader() {
    const overlay = document.getElementById('globalLoadingOverlay');
    
    if (loadingTimeout) {
        clearTimeout(loadingTimeout);
        loadingTimeout = null;
    }
    
    if (overlay) {
        overlay.classList.remove('active');
    }
}

// ============================================
// INICIALIZACIÓN PRINCIPAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    initMenuToggle();
    initFlashMessages();
    initActiveNav();
    initPasswordToggles();
    initChangePasswordForm();
    initFormSubmitLoading();
    initSmoothScroll();
    enhanceInteractions();
});

// ============================================
// MENU TOGGLE MEJORADO
// ============================================
function initMenuToggle() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isActive = sidebar.classList.toggle('active');
            menuToggle.classList.toggle('active');
            
            const icon = menuToggle.querySelector('i');
            if (icon) {
                if (isActive) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
            
            if (window.innerWidth <= 768) {
                document.body.style.overflow = isActive ? 'hidden' : '';
            }
        });
        
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    closeMobileMenu();
                }
            }
        });
        
        sidebar.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && e.target.classList.contains('nav-link')) {
                setTimeout(closeMobileMenu, 300);
            }
        });
        
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeMobileMenu();
            }
        });
    }
}

function closeMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    
    if (sidebar && menuToggle) {
        sidebar.classList.remove('active');
        menuToggle.classList.remove('active');
        
        const icon = menuToggle.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
        
        document.body.style.overflow = '';
    }
}

// ============================================
// MENSAJES FLASH CON ANIMACIÓN
// ============================================
function initFlashMessages() {
    const flashMessages = document.querySelectorAll('#flashMessage');
    
    flashMessages.forEach(function(flashMessage) {
        if (flashMessage) {
            flashMessage.style.opacity = '0';
            flashMessage.style.transform = 'translateX(100%)';
            
            setTimeout(() => {
                flashMessage.style.transition = 'opacity 0.5s ease, transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                flashMessage.style.opacity = '1';
                flashMessage.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(function() {
                flashMessage.style.opacity = '0';
                flashMessage.style.transform = 'translateX(100%)';
                
                setTimeout(function() {
                    flashMessage.remove();
                }, 500);
            }, 5000);
            
            flashMessage.addEventListener('click', function() {
                this.style.opacity = '0';
                this.style.transform = 'translateX(100%)';
                setTimeout(() => this.remove(), 500);
            });
        }
    });
}

// ============================================
// NAVEGACIÓN ACTIVA
// ============================================
function initActiveNav() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => link.classList.remove('active'));
    
    let bestMatch = null;
    let bestMatchLength = 0;
    
    navLinks.forEach(function(link) {
        const href = link.getAttribute('href');
        
        if (!href) return;
        
        let linkPath = href;
        try {
            const url = new URL(href, window.location.origin);
            linkPath = url.pathname;
        } catch (e) {
            
        }
        
        if (currentPath === linkPath) {
            bestMatch = link;
            bestMatchLength = linkPath.length;
        }
        else if (currentPath.startsWith(linkPath) && linkPath !== '/' && linkPath.length > bestMatchLength) {
            bestMatch = link;
            bestMatchLength = linkPath.length;
        }
    });
    
    if (bestMatch) {
        bestMatch.classList.add('active');
    }    
}

// ============================================
// TOGGLE DE CONTRASEÑAS
// ============================================
function initPasswordToggles() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            
            if (input) {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                
                const icon = this.querySelector('i');
                if (icon) {
                    if (type === 'password') {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    } else {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                }
                
                this.setAttribute('aria-label', 
                    type === 'password' ? 'Mostrar contraseña' : 'Ocultar contraseña'
                );
            }
        });
    });
}

// ============================================
// VALIDACIÓN FORMULARIO CAMBIO DE CONTRASEÑA
// ============================================
function initChangePasswordForm() {
    const form = document.getElementById('changePasswordForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (!newPassword || !confirmPassword) {
                return;
            }
            
            if (newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                showNotification('Las contraseñas no coinciden', 'error');
                confirmPassword.focus();
                confirmPassword.style.borderColor = '#dc3545';
                return false;
            }
            
            if (newPassword.value.length < 6) {
                e.preventDefault();
                showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
                newPassword.focus();
                newPassword.style.borderColor = '#dc3545';
                return false;
            }
            
            confirmPassword.style.borderColor = '';
            newPassword.style.borderColor = '';
        });
        
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                this.style.borderColor = '';
            });
        }
        
        const newPassword = document.getElementById('new_password');
        if (newPassword) {
            newPassword.addEventListener('input', function() {
                this.style.borderColor = '';
            });
        }
    }
}

// ============================================
// LOADING EN ENVÍO DE FORMULARIOS
// ============================================
function initFormSubmitLoading() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(function(form) {
        if (!form.hasAttribute('data-no-loading')) {
            form.addEventListener('submit', function(e) {
                const submitButton = form.querySelector('button[type="submit"]');
                
                if (submitButton && !submitButton.disabled) {
                    showLoader('Procesando...');
                    
                    submitButton.disabled = true;
                    submitButton.style.opacity = '0.6';
                    submitButton.style.cursor = 'not-allowed';
                }
            });
        }
    });
}

// ============================================
// SCROLL SUAVE
// ============================================
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#' || href === '#!') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ============================================
// MEJORAR INTERACCIONES
// ============================================
function enhanceInteractions() {
    document.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
    
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                width: 20px;
                height: 20px;
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;
            
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

// ============================================
// FUNCIONES DE UTILIDAD
// ============================================
function confirmDelete(message) {
    message = message || '¿Está seguro que desea eliminar este registro?';
    return confirm(message);
}

function formatMoney(amount) {
    const number = parseFloat(amount);
    if (isNaN(number)) return 'Bs 0.00';
    return 'Bs ' + number.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '';
    
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return day + '/' + month + '/' + year;
}

function showNotification(message, type) {
    type = type || 'info';
    
    const iconMap = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    
    const notification = document.createElement('div');
    notification.className = 'alert alert-' + type;
    notification.innerHTML = '<i class="fas ' + iconMap[type] + '"></i><span>' + message + '</span>';
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: -400px;
        min-width: 300px;
        max-width: 500px;
        z-index: 99999;
        cursor: pointer;
        transition: right 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.right = '20px';
    }, 10);
    
    notification.addEventListener('click', function() {
        this.style.right = '-400px';
        setTimeout(() => this.remove(), 500);
    });
    
    setTimeout(function() {
        notification.style.right = '-400px';
        setTimeout(function() {
            notification.remove();
        }, 500);
    }, 5000);
}

function ajaxRequest(url, method, data, successCallback, errorCallback) {
    showLoader('Procesando solicitud...');
    
    const xhr = new XMLHttpRequest();
    
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        hideLoader();
        
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (successCallback) {
                    successCallback(response);
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                if (errorCallback) {
                    errorCallback('Error al procesar la respuesta');
                } else {
                    showNotification('Error al procesar la respuesta del servidor', 'error');
                }
            }
        } else {
            const errorMsg = 'Error en la petición: ' + xhr.status;
            if (errorCallback) {
                errorCallback(errorMsg);
            } else {
                showNotification(errorMsg, 'error');
            }
        }
    };
    
    xhr.onerror = function() {
        hideLoader();
        const errorMsg = 'Error de conexión con el servidor';
        if (errorCallback) {
            errorCallback(errorMsg);
        } else {
            showNotification(errorMsg, 'error');
        }
    };
    
    xhr.ontimeout = function() {
        hideLoader();
        showNotification('La solicitud ha excedido el tiempo de espera', 'warning');
    };
    
    xhr.timeout = 30000;
    
    if (method === 'GET') {
        xhr.send();
    } else {
        xhr.send(JSON.stringify(data));
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        
        clearTimeout(timeout);
        
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    
    if (!form) {
        console.error('Formulario no encontrado:', formId);
        return false;
    }
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    let firstInvalid = null;
    
    inputs.forEach(function(input) {
        if (!input.value.trim()) {
            input.style.borderColor = '#dc3545';
            input.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.2)';
            isValid = false;
            if (!firstInvalid) {
                firstInvalid = input;
            }
        } else {
            input.style.borderColor = '';
            input.style.boxShadow = '';
        }
        
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.style.borderColor = '';
                this.style.boxShadow = '';
            }
        }, { once: true });
    });
    
    if (!isValid) {
        showNotification('Por favor complete todos los campos requeridos', 'error');
        if (firstInvalid) {
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    return isValid;
}

function printElement(elementId) {
    const element = document.getElementById(elementId);
    
    if (!element) {
        showNotification('Elemento no encontrado para imprimir', 'error');
        return;
    }
    
    showLoader('Preparando impresión...');
    
    const printWindow = window.open('', '_blank');
    
    if (!printWindow) {
        hideLoader();
        showNotification('No se pudo abrir la ventana de impresión. Verifique que las ventanas emergentes estén permitidas.', 'warning');
        return;
    }
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Imprimir - JB Acopiadora</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    color: #2c2c2c;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #FFD082;
                    color: #433F4E;
                }
                @media print {
                    body {
                        margin: 0;
                    }
                    @page {
                        margin: 2cm;
                    }
                }
            </style>
        </head>
        <body>
            ${element.innerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    
    setTimeout(function() {
        hideLoader();
        printWindow.focus();
        printWindow.print();
        
        setTimeout(() => {
            printWindow.close();
        }, 100);
    }, 500);
}

// ============================================
// MANEJO DE ERRORES GLOBAL
// ============================================
window.addEventListener('error', function(e) {
    console.error('Error global capturado:', e.error);
    hideLoader();
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Promesa rechazada no manejada:', e.reason);
    hideLoader();
});

// ============================================
// ANIMACIÓN DE CARGA DE PÁGINA
// ============================================
window.addEventListener('load', function() {
    hideLoader();
    
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.3s ease';
        document.body.style.opacity = '1';
    }, 10);
});
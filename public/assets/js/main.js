document.addEventListener('DOMContentLoaded', function() {
    
    initMenuToggle();
    initFlashMessages();
    initActiveNav();
    initPasswordToggles();
    initChangePasswordForm();
    
});

function initMenuToggle() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }
}

function initFlashMessages() {
    const flashMessage = document.getElementById('flashMessage');
    
    if (flashMessage) {
        setTimeout(function() {
            flashMessage.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            flashMessage.style.opacity = '0';
            flashMessage.style.transform = 'translateY(-10px)';
            
            setTimeout(function() {
                flashMessage.remove();
            }, 500);
        }, 5000);
    }
}

function initActiveNav() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(function(link) {
        const href = link.getAttribute('href');
        
        if (currentPath.includes(href) && href !== '/') {
            link.classList.add('active');
        }
    });
}

function initPasswordToggles() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            
            if (input) {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                
                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            }
        });
    });
}

function initChangePasswordForm() {
    const form = document.getElementById('changePasswordForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                showNotification('Las contraseñas no coinciden', 'error');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
                return false;
            }
        });
    }
}

function confirmDelete(message) {
    message = message || '¿Está seguro que desea eliminar este registro?';
    return confirm(message);
}

function showLoader() {
    const loader = document.createElement('div');
    loader.id = 'globalLoader';
    loader.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;
    
    const spinner = document.createElement('div');
    spinner.style.cssText = `
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #FFD082;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    `;
    
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
    
    loader.appendChild(spinner);
    document.body.appendChild(loader);
}

function hideLoader() {
    const loader = document.getElementById('globalLoader');
    if (loader) {
        loader.remove();
    }
}

function formatMoney(amount) {
    return 'Bs ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function formatDate(dateString) {
    const date = new Date(dateString);
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
    notification.innerHTML = '<i class="fas ' + iconMap[type] + '"></i>' + message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        min-width: 300px;
        max-width: 500px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    setTimeout(function() {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 3000);
}

function ajaxRequest(url, method, data, successCallback, errorCallback) {
    const xhr = new XMLHttpRequest();
    
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
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
                }
            }
        } else {
            if (errorCallback) {
                errorCallback('Error en la petición: ' + xhr.status);
            }
        }
    };
    
    xhr.onerror = function() {
        if (errorCallback) {
            errorCallback('Error de conexión');
        }
    };
    
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
        return false;
    }
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(function(input) {
        if (!input.value.trim()) {
            input.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            input.style.borderColor = '';
        }
    });
    
    if (!isValid) {
        showNotification('Por favor complete todos los campos requeridos', 'error');
    }
    
    return isValid;
}

function printElement(elementId) {
    const element = document.getElementById(elementId);
    
    if (!element) {
        return;
    }
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>Imprimir</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(element.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    setTimeout(function() {
        printWindow.print();
        printWindow.close();
    }, 250);
}
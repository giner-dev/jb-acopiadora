// Variables globales
let granoIdParaPrecio = null;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    initGranosModule();
});

function initGranosModule() {
    const formGrano = document.getElementById('formGrano');
    
    if (formGrano) {
        initFormValidation();
    }
}

function initFormValidation() {
    const formGrano = document.getElementById('formGrano');
    
    formGrano.addEventListener('submit', function(e) {
        const nombre = document.getElementById('nombre').value.trim();
        
        if (nombre.length < 3) {
            e.preventDefault();
            if (typeof showNotification === 'function') {
                showNotification('El nombre debe tener al menos 3 caracteres', 'error');
            }
            return false;
        }
    });
}

function abrirModalPrecio(granoId, granoNombre) {
    granoIdParaPrecio = granoId;
    
    document.getElementById('grano_id_precio').value = granoId;
    document.getElementById('granoNombrePrecio').textContent = granoNombre;
    document.getElementById('fecha_precio').value = new Date().toISOString().split('T')[0];
    document.getElementById('precio_valor').value = '';
    
    document.getElementById('modalPrecio').style.display = 'flex';
    
    setTimeout(() => {
        document.getElementById('precio_valor').focus();
    }, 100);
}

function cerrarModalPrecio() {
    document.getElementById('modalPrecio').style.display = 'none';
    granoIdParaPrecio = null;
}

function confirmarRegistroPrecio() {
    const precio = parseFloat(document.getElementById('precio_valor').value);
    const fecha = document.getElementById('fecha_precio').value;
    
    if (!precio || precio <= 0) {
        if (typeof showNotification === 'function') {
            showNotification('El precio debe ser mayor a 0', 'error');
        } else {
            alert('El precio debe ser mayor a 0');
        }
        document.getElementById('precio_valor').focus();
        return;
    }
    
    if (!fecha) {
        if (typeof showNotification === 'function') {
            showNotification('La fecha es obligatoria', 'error');
        } else {
            alert('La fecha es obligatoria');
        }
        document.getElementById('fecha_precio').focus();
        return;
    }
    
    const fechaSeleccionada = new Date(fecha);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    if (fechaSeleccionada > hoy) {
        if (typeof showNotification === 'function') {
            showNotification('No se puede registrar precios futuros', 'error');
        } else {
            alert('No se puede registrar precios futuros');
        }
        return;
    }
    
    if (typeof showLoader === 'function') {
        showLoader();
    }
    
    const formData = new FormData();
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
    formData.append('precio', precio);
    formData.append('fecha', fecha);
    
    fetch(window.PHP_BASE_URL + '/granos/registrar-precio/' + granoIdParaPrecio, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (typeof hideLoader === 'function') {
            hideLoader();
        }
        
        cerrarModalPrecio();
        
        if (data.success) {
            if (typeof showNotification === 'function') {
                showNotification(data.message, 'success');
            } else {
                alert(data.message);
            }
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            if (typeof showNotification === 'function') {
                showNotification(data.message, 'error');
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        if (typeof hideLoader === 'function') {
            hideLoader();
        }
        cerrarModalPrecio();
        if (typeof showNotification === 'function') {
            showNotification('Error al registrar el precio', 'error');
        } else {
            alert('Error al registrar el precio');
        }
        console.error('Error:', error);
    });
}

function eliminarGrano(id, nombre) {
    if (confirm('¿Está seguro que desea eliminar el grano "' + nombre + '"?\n\nEsta acción no se puede deshacer si el grano no tiene acopios registrados.')) {
        if (typeof showLoader === 'function') {
            showLoader();
        }
        
        const formData = new FormData();
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        
        fetch(window.PHP_BASE_URL + '/granos/eliminar/' + id, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (typeof hideLoader === 'function') {
                hideLoader();
            }
            
            if (data.success) {
                if (typeof showNotification === 'function') {
                    showNotification(data.message, 'success');
                } else {
                    alert(data.message);
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(data.message, 'error');
                } else {
                    alert(data.message);
                }
            }
        })
        .catch(error => {
            if (typeof hideLoader === 'function') {
                hideLoader();
            }
            if (typeof showNotification === 'function') {
                showNotification('Error al eliminar el grano', 'error');
            } else {
                alert('Error al eliminar el grano');
            }
            console.error('Error:', error);
        });
    }
}

// Event listener para cerrar modal al hacer click fuera
window.addEventListener('click', function(event) {
    const modalPrecio = document.getElementById('modalPrecio');
    if (modalPrecio && event.target === modalPrecio) {
        cerrarModalPrecio();
    }
});

// Event listener para Enter en el input de precio
document.addEventListener('DOMContentLoaded', function() {
    const precioInput = document.getElementById('precio_valor');
    if (precioInput) {
        precioInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmarRegistroPrecio();
            }
        });
    }
});
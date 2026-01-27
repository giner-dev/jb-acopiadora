document.addEventListener('DOMContentLoaded', function() {
    
    initClientesModule();
    
});

function initClientesModule() {
    const formCliente = document.getElementById('formCliente');
    
    if (formCliente) {
        initFormValidation();
        initCIInput();
    }
}

function initFormValidation() {
    const formCliente = document.getElementById('formCliente');
    
    formCliente.addEventListener('submit', function(e) {
        const ci = document.getElementById('ci').value.trim();
        
        if (!/^[0-9]+$/.test(ci)) {
            e.preventDefault();
            showNotification('El CI debe contener solo números', 'error');
            return false;
        }
        
        if (ci.length < 5 || ci.length > 15) {
            e.preventDefault();
            showNotification('El CI debe tener entre 5 y 15 dígitos', 'error');
            return false;
        }
    });
}

function initCIInput() {
    const ciInput = document.getElementById('ci');
    
    if (ciInput) {
        ciInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
}

function eliminarCliente(id, nombre) {
    if (confirm('¿Está seguro que desea eliminar al cliente "' + nombre + '"?\n\nEsta acción no se puede deshacer.')) {
        showLoader();
        
        const formData = new FormData();
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        
        fetch(window.PHP_BASE_URL + '/clientes/eliminar/' + id, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoader();
            showNotification('Error al eliminar el cliente', 'error');
            console.error('Error:', error);
        });
    }
}
document.addEventListener('DOMContentLoaded', function() {
    
    initProductosModule();
    
});

function initProductosModule() {
    const formProducto = document.getElementById('formProducto');
    
    if (formProducto) {
        initFormValidation();
    }
}

function initFormValidation() {
    const formProducto = document.getElementById('formProducto');
    
    formProducto.addEventListener('submit', function(e) {
        const precioVenta = document.getElementById('precio_venta').value;
        
        if (parseFloat(precioVenta) < 0) {
            e.preventDefault();
            showNotification('El precio de venta debe ser mayor o igual a 0', 'error');
            return false;
        }
        
        const stockIlimitado = document.getElementById('stock_ilimitado');
        if (stockIlimitado && stockIlimitado.checked) {
            return true;
        }
        
        const stockActual = document.getElementById('stock_actual');
        if (stockActual && parseFloat(stockActual.value) < 0) {
            e.preventDefault();
            showNotification('El stock no puede ser negativo', 'error');
            return false;
        }
        
        const stockMinimo = document.getElementById('stock_minimo');
        if (stockMinimo && parseFloat(stockMinimo.value) < 0) {
            e.preventDefault();
            showNotification('El stock mínimo no puede ser negativo', 'error');
            return false;
        }
    });
}

function eliminarProducto(id, nombre) {
    if (confirm('¿Está seguro que desea eliminar el producto "' + nombre + '"?\n\nEsta acción no se puede deshacer si el producto no tiene movimientos de inventario.')) {
        showLoader();
        
        const formData = new FormData();
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        
        fetch(window.PHP_BASE_URL + '/productos/eliminar/' + id, {
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
            showNotification('Error al eliminar el producto', 'error');
            console.error('Error:', error);
        });
    }
}

function toggleStockFields() {
    const checkbox = document.getElementById('stock_ilimitado');
    const stockFields = document.getElementById('stock_fields');
    const stockActual = document.getElementById('stock_actual');
    const stockMinimo = document.getElementById('stock_minimo');
    
    if (checkbox && checkbox.checked) {
        stockFields.style.opacity = '0.5';
        if (stockActual) {
            stockActual.disabled = true;
            stockActual.value = '0';
        }
        if (stockMinimo) {
            stockMinimo.disabled = true;
            stockMinimo.value = '0';
        }
    } else {
        stockFields.style.opacity = '1';
        if (stockActual) stockActual.disabled = false;
        if (stockMinimo) stockMinimo.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initProductosModule();
    
    const stockCheckbox = document.getElementById('stock_ilimitado');
    if (stockCheckbox) {
        toggleStockFields();
    }
});
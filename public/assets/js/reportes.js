// ============================================
// MÓDULO REPORTES - AISLADO
// ============================================

const ReporteModule = (function() {

    function init() {
        initBotonesPeriodo();
    }

    function initBotonesPeriodo() {
        const botones = document.querySelectorAll('.rpt-btn-periodo');

        botones.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const periodo = this.dataset.periodo;
                const fechas  = calcularPeriodo(periodo);

                // Buscar el form padre
                const form = this.closest('form');
                if (!form) return;

                const inputDesde = form.querySelector('input[name="fecha_desde"]');
                const inputHasta = form.querySelector('input[name="fecha_hasta"]');

                if (inputDesde) inputDesde.value = fechas.desde;
                if (inputHasta) inputHasta.value = fechas.hasta;

                form.submit();
            });
        });
    }

    /**
     * Calcula las fechas según el período seleccionado.
     * Retorna objeto con { desde, hasta } en formato YYYY-MM-DD.
     */
    function calcularPeriodo(periodo) {
        const hoy = new Date();
        let desde, hasta;

        hasta = formatearFecha(hoy);

        switch (periodo) {
            case 'mes':
                desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                break;
            case 'trimestre':
                // Trimestre actual: mes actual menos 2 meses, día 1
                desde = new Date(hoy.getFullYear(), hoy.getMonth() - 2, 1);
                break;
            case 'anio':
                desde = new Date(hoy.getFullYear(), 0, 1);
                break;
            default:
                desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                break;
        }

        return {
            desde: formatearFecha(desde),
            hasta: hasta
        };
    }

    /**
     * Formatea un objeto Date a YYYY-MM-DD
     */
    function formatearFecha(fecha) {
        const anio = fecha.getFullYear();
        const mes  = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia  = String(fecha.getDate()).padStart(2, '0');
        return anio + '-' + mes + '-' + dia;
    }

    return {
        init: init
    };
})();

document.addEventListener('DOMContentLoaded', ReporteModule.init);
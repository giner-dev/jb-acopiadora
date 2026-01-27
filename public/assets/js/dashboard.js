document.addEventListener('DOMContentLoaded', function() {
    
    initDashboard();
    
});

function initDashboard() {
    animateNumbers();
    updateRelativeTime();
}

function animateNumbers() {
    const numbers = document.querySelectorAll('.stat-number');
    
    numbers.forEach(function(element) {
        const text = element.textContent.trim();
        
        if (text.includes('Bs')) {
            return;
        }
        
        const finalValue = parseInt(text.replace(/,/g, ''));
        
        if (isNaN(finalValue)) {
            return;
        }
        
        animateValue(element, 0, finalValue, 1000);
    });
}

function animateValue(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(function() {
        current += increment;
        
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

function updateRelativeTime() {
    const timeElements = document.querySelectorAll('[data-time]');
    
    timeElements.forEach(function(element) {
        const timestamp = element.getAttribute('data-time');
        const relativeTime = getRelativeTime(timestamp);
        element.textContent = relativeTime;
    });
}

function getRelativeTime(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diff = now - time;
    
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (days > 0) {
        return days === 1 ? 'Hace 1 día' : 'Hace ' + days + ' días';
    }
    
    if (hours > 0) {
        return hours === 1 ? 'Hace 1 hora' : 'Hace ' + hours + ' horas';
    }
    
    if (minutes > 0) {
        return minutes === 1 ? 'Hace 1 minuto' : 'Hace ' + minutes + ' minutos';
    }
    
    return 'Hace unos segundos';
}
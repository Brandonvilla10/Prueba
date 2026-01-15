// Configuración de API
const API_URL = 'http://localhost/Prueba/backend/api';

// Obtener token de API de localStorage
function getToken() {
    return localStorage.getItem('authToken');
}

// Establecer token de API en localStorage
function setToken(token) {
    localStorage.setItem('authToken', token);
}

// Eliminar token de API de localStorage
function removeToken() {
    localStorage.removeItem('authToken');
}

// Obtener información del usuario actual
function getCurrentUser() {
    return JSON.parse(localStorage.getItem('currentUser') || '{}');
}

// Establecer información del usuario actual
function setCurrentUser(user) {
    localStorage.setItem('currentUser', JSON.stringify(user));
}

// Eliminar información del usuario actual
function removeCurrentUser() {
    localStorage.removeItem('currentUser');
}

// Ayudante de solicitud de API
async function apiRequest(endpoint, method = 'GET', data = null) {
    const token = getToken();
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    // Agregar token en header
    if (token) {
        options.headers['Authorization'] = `Bearer ${token}`;
    }

    // Para GET requests, también agregar token como parámetro query (fallback para FastCGI)
    let url = `${API_URL}${endpoint}`;
    if (token) {
        // Para GET, agregar token en query parameter
        if (method === 'GET') {
            const separator = url.includes('?') ? '&' : '?';
            url += `${separator}token=${encodeURIComponent(token)}`;
        } else {
            // Para POST/PUT/DELETE, si no hay data, crear objeto con token
            if (!data) {
                data = {};
            }
            // Pasar token en body para métodos que no sean GET
            if (typeof data === 'object') {
                data._token = token;
            }
        }
    }

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        
        // Intentar parsear como JSON
        let result;
        try {
            result = await response.json();
        } catch (e) {
            // Si no es JSON válido, crear un objeto de error
            result = {
                success: false,
                message: 'Respuesta inválida del servidor'
            };
        }

        if (!response.ok) {
            if (response.status === 401) {
                // Token expirado o inválido
                logout();
                throw new Error('Sesión expirada. Por favor inicie sesión nuevamente.');
            }
            throw new Error(result.message || 'Error en la solicitud');
        }

        return result;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Mostrar mensaje de alerta
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alertContainer');
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${getAlertIcon(type)}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    alertContainer.innerHTML += alertHtml;

    // Auto-descartar después de 5 segundos
    setTimeout(() => {
        const alerts = alertContainer.querySelectorAll('.alert');
        if (alerts.length > 0) {
            const lastAlert = alerts[alerts.length - 1];
            const bsAlert = new bootstrap.Alert(lastAlert);
            bsAlert.close();
        }
    }, 5000);
}

// Obtener icono apropiado para tipo de alerta
function getAlertIcon(type) {
    const icons = {
        'success': 'check-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Formatear fecha
function formatDate(dateString) {
    if (!dateString) return '';
    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return new Date(dateString).toLocaleDateString('es-CO', options);
}

// Verificar si el usuario está autenticado
function isAuthenticated() {
    return !!getToken();
}

// Mostrar/ocultar secciones según autenticación
function updateUI() {
    const loginSection = document.getElementById('loginSection');
    const dashboardSection = document.getElementById('dashboardSection');
    
    if (isAuthenticated()) {
        const user = getCurrentUser();
        document.getElementById('userInfo').textContent = `Bienvenido, ${user.username}`;
        loginSection.style.display = 'none';
        dashboardSection.style.display = 'block';
    } else {
        loginSection.style.display = 'flex';
        dashboardSection.style.display = 'none';
    }
}

// Inicializar aplicación
document.addEventListener('DOMContentLoaded', () => {
    updateUI();

    if (isAuthenticated()) {
        loadPatients();
        loadLookupData();
    }

    document.getElementById('loginForm')?.addEventListener('submit', login);
});

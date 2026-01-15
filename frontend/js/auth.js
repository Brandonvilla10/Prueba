// Funciones de autenticación

async function login(e) {
    e.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    // Validación del lado del cliente
    if (!username || username.length < 3) {
        showAlert('El usuario debe tener al menos 3 caracteres', 'warning');
        return;
    }

    if (!password || password.length < 8) {
        showAlert('La contraseña debe tener al menos 8 caracteres', 'warning');
        return;
    }

    try {
        const response = await apiRequest('/auth/login', 'POST', {
            username: username,
            password: password
        });

        if (response.success) {
            setToken(response.token);
            setCurrentUser(response.user);
            
            // Clear form
            document.getElementById('loginForm').reset();
            
            // Update UI
            updateUI();
            
            // Load data
            await loadPatients();
            await loadLookupData();
            
            showAlert('¡Iniciaste sesión correctamente!', 'success');
        } else {
            showAlert(response.message || 'Error al iniciar sesión', 'danger');
        }
    } catch (error) {
        showAlert(error.message || 'Error al iniciar sesión', 'danger');
    }
}

function logout() {
    removeToken();
    removeCurrentUser();
    updateUI();
    showAlert('Sesión cerrada correctamente', 'info');
}

async function verifyToken() {
    try {
        const response = await apiRequest('/auth/verify', 'POST');
        return response.success;
    } catch (error) {
        return false;
    }
}

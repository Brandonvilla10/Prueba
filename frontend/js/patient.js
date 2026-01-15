// Funciones de gestión de pacientes

let currentPage = 1;
let currentLimit = 10;
let currentSearch = '';

// Cargar pacientes con paginación
async function loadPatients(page = 1, search = '') {
    currentPage = page;
    currentSearch = search;

    try {
        const response = await apiRequest(
            `/patients?page=${page}&limit=${currentLimit}&search=${encodeURIComponent(search)}`
        );

        if (response.success) {
            displayPatients(response.data);
            displayPagination(response.pagination);
        } else {
            showAlert(response.message || 'Error al cargar pacientes', 'danger');
        }
    } catch (error) {
        showAlert(error.message || 'Error al cargar pacientes', 'danger');
    }
}

// Mostrar pacientes en tabla
function displayPatients(patients) {
    const tbody = document.getElementById('patientTable');

    if (patients.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay pacientes registrados</td></tr>';
        return;
    }

    tbody.innerHTML = patients.map(patient => `
        <tr>
            <td><strong>${patient.tipo_documento}</strong><br>${patient.numero_documento}</td>
            <td>
                <strong>${patient.nombre1} ${patient.nombre2 || ''}</strong><br>
                <small>${patient.apellido1} ${patient.apellido2 || ''}</small>
            </td>
            <td>${patient.correo || '-'}</td>
            <td>${patient.telefono || '-'}</td>
            <td>${patient.genero || '-'}</td>
            <td>${patient.municipio || '-'}</td>
            <td>
                <span class="badge ${patient.estado === 'activo' ? 'bg-success' : 'bg-danger'}">
                    ${patient.estado || 'Desconocido'}
                </span>
            </td>
            <td>
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-info" onclick="editPatient(${patient.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger" onclick="deletePatient(${patient.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Mostrar botones de paginación
function displayPagination(pagination) {
    const container = document.getElementById('paginationContainer');

    if (pagination.pages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '<nav aria-label="Page navigation"><ul class="pagination">';

    // Botón anterior
    if (pagination.page > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadPatients(${pagination.page - 1}, '${currentSearch}')">Anterior</a></li>`;
    } else {
        html += '<li class="page-item disabled"><span class="page-link">Anterior</span></li>';
    }

    // Números de página
    for (let i = 1; i <= pagination.pages; i++) {
        if (i === pagination.page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadPatients(${i}, '${currentSearch}')">${i}</a></li>`;
        }
    }

    // Botón siguiente
    if (pagination.page < pagination.pages) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadPatients(${pagination.page + 1}, '${currentSearch}')">Siguiente</a></li>`;
    } else {
        html += '<li class="page-item disabled"><span class="page-link">Siguiente</span></li>';
    }

    html += '</ul></nav>';
    container.innerHTML = html;
}

// Cargar datos de búsqueda (tipos de documento, géneros, departamentos, municipios)
async function loadLookupData() {
    try {
        const docTypesPromise = apiRequest('/lookup/document-types');
        const gendersPromise = apiRequest('/lookup/genders');
        const departmentsPromise = apiRequest('/lookup/departments');

        const [docTypes, genders, departments] = await Promise.all([
            docTypesPromise,
            gendersPromise,
            departmentsPromise
        ]);

        // Llenar selects
        if (docTypes && docTypes.data) {
            populateSelect('tipoDocumento', docTypes.data);
        }
        if (genders && genders.data) {
            populateSelect('genero', genders.data);
        }
        if (departments && departments.data) {
            populateSelect('departamento', departments.data);
        }
    } catch (error) {
        console.error('Error cargando datos de lookup:', error);
        showAlert('Error al cargar datos de formulario: ' + error.message, 'warning');
    }
}

// Rellenar opciones de selección
function populateSelect(selectId, options) {
    const select = document.getElementById(selectId);
    const currentValue = select.value;

    select.innerHTML = '<option value="">Seleccionar...</option>';

    options.forEach(option => {
        const opt = document.createElement('option');
        opt.value = option.id;
        opt.textContent = option.nombre;
        select.appendChild(opt);
    });

    if (currentValue) select.value = currentValue;
}

// Cargar municipios basados en departamento
async function loadMunicipalities() {
    const departamentoId = document.getElementById('departamento').value;

    if (!departamentoId) {
        document.getElementById('municipio').innerHTML = '<option value="">Seleccionar...</option>';
        return;
    }

    try {
        const response = await apiRequest(`/lookup/municipalities?departamento_id=${departamentoId}`);
        populateSelect('municipio', response.data);
    } catch (error) {
        showAlert('Error al cargar municipios', 'warning');
    }
}

// Abrir formulario de nuevo paciente
function openNewPatientForm() {
    document.getElementById('patientId').value = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Paciente';
    document.getElementById('patientForm').reset();
    document.getElementById('formErrors').style.display = 'none';
}

// Editar paciente
async function editPatient(patientId) {
    try {
        const response = await apiRequest(`/patients/${patientId}`);

        if (response.success) {
            const patient = response.data;

            // Llenar formulario
            document.getElementById('patientId').value = patient.id;
            document.getElementById('modalTitle').textContent = 'Editar Paciente';
            document.getElementById('tipoDocumento').value = patient.tipo_documento_id;
            document.getElementById('numeroDocumento').value = patient.numero_documento;
            document.getElementById('nombre1').value = patient.nombre1;
            document.getElementById('nombre2').value = patient.nombre2 || '';
            document.getElementById('apellido1').value = patient.apellido1;
            document.getElementById('apellido2').value = patient.apellido2 || '';
            document.getElementById('genero').value = patient.genero_id;
            document.getElementById('fechaNacimiento').value = patient.fecha_nacimiento || '';
            document.getElementById('departamento').value = patient.departamento_id;
            document.getElementById('correo').value = patient.correo || '';
            document.getElementById('telefono').value = patient.telefono || '';
            document.getElementById('direccion').value = patient.direccion || '';
            document.getElementById('formErrors').style.display = 'none';

            // Cargar municipios para departamento seleccionado
            await loadMunicipalities();
            document.getElementById('municipio').value = patient.municipio_id;

            // Mostrar modal
            new bootstrap.Modal(document.getElementById('patientModal')).show();
        }
    } catch (error) {
        showAlert(error.message || 'Error al cargar paciente', 'danger');
    }
}

// Guardar paciente (crear o actualizar)
async function savePatient() {
    const patientId = document.getElementById('patientId').value;
    const formErrors = document.getElementById('formErrors');

   
    const errors = validatePatientForm();

    if (errors.length > 0) {
        formErrors.innerHTML = '<strong>Errores:</strong><ul>' + 
            errors.map(err => `<li>${err}</li>`).join('') + 
            '</ul>';
        formErrors.style.display = 'block';
        return;
    }

    formErrors.style.display = 'none';

    const data = {
        tipo_documento_id: parseInt(document.getElementById('tipoDocumento').value),
        numero_documento: document.getElementById('numeroDocumento').value.trim(),
        nombre1: document.getElementById('nombre1').value.trim(),
        nombre2: document.getElementById('nombre2').value.trim() || null,
        apellido1: document.getElementById('apellido1').value.trim(),
        apellido2: document.getElementById('apellido2').value.trim() || null,
        genero_id: parseInt(document.getElementById('genero').value),
        departamento_id: parseInt(document.getElementById('departamento').value),
        municipio_id: parseInt(document.getElementById('municipio').value),
        correo: document.getElementById('correo').value.trim() || null,
        telefono: document.getElementById('telefono').value.trim() || null,
        fecha_nacimiento: document.getElementById('fechaNacimiento').value || null,
        direccion: document.getElementById('direccion').value.trim() || null,
        estado: 'activo'
    };

    try {
        let response;

        if (patientId) {
           
            response = await apiRequest(`/patients/${patientId}`, 'PUT', data);
        } else {
           
            response = await apiRequest('/patients', 'POST', data);
        }

        if (response.success) {
            bootstrap.Modal.getInstance(document.getElementById('patientModal')).hide();
            showAlert(response.message || 'Paciente guardado correctamente', 'success');
            await loadPatients(currentPage, currentSearch);
        } else {
            formErrors.innerHTML = '<strong>Error:</strong> ' + (response.message || 'Error al guardar');
            formErrors.style.display = 'block';
        }
    } catch (error) {
        formErrors.innerHTML = '<strong>Error:</strong> ' + (error.message || 'Error al guardar paciente');
        formErrors.style.display = 'block';
    }
}

// Validar formulario de paciente
function validatePatientForm() {
    const errors = [];

    const tipoDocumento = document.getElementById('tipoDocumento').value;
    const numeroDocumento = document.getElementById('numeroDocumento').value.trim();
    const nombre1 = document.getElementById('nombre1').value.trim();
    const apellido1 = document.getElementById('apellido1').value.trim();
    const genero = document.getElementById('genero').value;
    const departamento = document.getElementById('departamento').value;
    const municipio = document.getElementById('municipio').value;
    const correo = document.getElementById('correo').value.trim();
    const telefono = document.getElementById('telefono').value.trim();

    if (!tipoDocumento) errors.push('El tipo de documento es requerido');
    if (!numeroDocumento) errors.push('El número de documento es requerido');
    if (!/^[0-9]{7,11}$/.test(numeroDocumento)) errors.push('El documento debe tener 7-11 dígitos');
    if (!nombre1 || nombre1.length < 2) errors.push('El primer nombre debe tener al menos 2 caracteres');
    if (!apellido1 || apellido1.length < 2) errors.push('El primer apellido debe tener al menos 2 caracteres');
    if (!genero) errors.push('El género es requerido');
    if (!departamento) errors.push('El departamento es requerido');
    if (!municipio) errors.push('El municipio es requerido');
    if (correo && !isValidEmail(correo)) errors.push('El correo debe ser válido');
    if (telefono && !/^[0-9]{10}$/.test(telefono)) errors.push('El teléfono debe tener 10 dígitos');

    return errors;
}

// Validar formato de correo
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Eliminar paciente
async function deletePatient(patientId) {
    if (!confirm('¿Estás seguro de que deseas eliminar este paciente?')) {
        return;
    }

    try {
        const response = await apiRequest(`/patients/${patientId}`, 'DELETE');

        if (response.success) {
            showAlert('Paciente eliminado correctamente', 'success');
            await loadPatients(currentPage, currentSearch);
        } else {
            showAlert(response.message || 'Error al eliminar paciente', 'danger');
        }
    } catch (error) {
        showAlert(error.message || 'Error al eliminar paciente', 'danger');
    }
}

// Buscar pacientes
function searchPatients() {
    const search = document.getElementById('searchInput').value.trim();
    console.log('Searching for:', search);
    loadPatients(1, search);
}

// Cambiar límite de paginación
function changeLimit() {
    const newLimit = document.getElementById('limitSelect').value;
    console.log('Changing limit to:', newLimit);
    currentLimit = parseInt(newLimit);
    loadPatients(1, currentSearch);
}

// Añadir soporte de tecla Enter para búsqueda
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                searchPatients();
            }
        });
    }
});

// Função para aplicar máscaras nos campos
function applyInputMasks() {
    const cpfCnpjInput = document.getElementById('cpf_cnpj');
    const telefoneInput = document.getElementById('telefone');
    
    if (cpfCnpjInput) {
        VMasker(cpfCnpjInput).maskPattern('999.999.999-99');
        cpfCnpjInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 11) {
                VMasker(this).maskPattern('99.999.999/9999-99');
            } else {
                VMasker(this).maskPattern('999.999.999-99');
            }
        });
    }

    if (telefoneInput) {
        VMasker(telefoneInput).maskPattern('(99) 99999-9999');
    }
}

// Função para validar formulários
function setupFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });
}

// Função para inicializar tooltips do Bootstrap
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Função para mostrar mensagens de feedback
function showFeedback(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }
    
    document.getElementById('toast-container').appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remover o toast depois que ele for fechado
    toast.addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}

// Inicializar todas as funções quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    applyInputMasks();
    setupFormValidation();
    initializeTooltips();
    
    // Verificar se há mensagens de sucesso ou erro na sessão
    const successMessage = document.querySelector('.alert-success');
    const errorMessage = document.querySelector('.alert-danger');
    
    if (successMessage) {
        showFeedback(successMessage.textContent, 'success');
        successMessage.remove();
    }
    
    if (errorMessage) {
        showFeedback(errorMessage.textContent, 'danger');
        errorMessage.remove();
    }
});

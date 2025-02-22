// Funções de Máscara
function maskCPFCNPJ(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    }
    input.value = value;
}

function maskPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length <= 10) {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
    }
    input.value = value;
}

// Funções de Validação
function validateForm(form) {
    let isValid = true;
    form.querySelectorAll('[required]').forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    });
    return isValid;
}

// Inicialização do Flatpickr (se existir)
if (typeof flatpickr !== 'undefined') {
    flatpickr.localize(flatpickr.l10n.pt);
    flatpickr("input[type=date]", {
        dateFormat: "Y-m-d",
        allowInput: true
    });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const mainContent = document.querySelector('.main-content');
    
    // Função para verificar se é mobile
    const isMobile = () => window.innerWidth <= 768;
    
    // Função para alternar o sidebar
    const toggleSidebar = () => {
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('expanded');
    };
    
    // Adicionar evento de clique ao botão toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });
    }
    
    // Fechar sidebar ao clicar no main content em mobile
    if (mainContent) {
        mainContent.addEventListener('click', (e) => {
            if (isMobile() && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    }
    
    // Fechar sidebar ao clicar em links em mobile
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (isMobile() && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    });
    
    // Prevenir que cliques dentro do sidebar o fechem
    sidebar.addEventListener('click', (e) => {
        e.stopPropagation();
    });
    
    // Ajustar sidebar ao redimensionar a janela
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (!isMobile()) {
                sidebar.classList.remove('active');
                mainContent.classList.remove('expanded');
            }
        }, 250);
    });

    // Máscaras
    document.querySelectorAll('[data-mask="cpf-cnpj"]').forEach(input => {
        input.addEventListener('input', () => maskCPFCNPJ(input));
    });

    document.querySelectorAll('[data-mask="phone"]').forEach(input => {
        input.addEventListener('input', () => maskPhone(input));
    });

    // Validação de Formulários
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });

    // Toggle Sidebar on Mobile
    const toggleSidebarMobile = document.querySelector('.toggle-sidebar');
    const sidebarMobile = document.querySelector('.sidebar');
    
    if (toggleSidebarMobile && sidebarMobile) {
        toggleSidebarMobile.addEventListener('click', function() {
            sidebarMobile.classList.toggle('show');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebarMobile.contains(e.target) && !toggleSidebarMobile.contains(e.target)) {
                sidebarMobile.classList.remove('show');
            }
        }
    });

    // Format currency values
    function formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    }

    // Format dates
    function formatDate(date) {
        return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
    }

    // Format percentage
    function formatPercentage(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'percent',
            minimumFractionDigits: 1,
            maximumFractionDigits: 1
        }).format(value / 100);
    }

    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Initialize popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

    // Handle form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.classList.contains('needs-validation')) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        }
    });

    // Handle status badges
    function updateStatusBadge(element, status) {
        const statusClasses = {
            'PENDING': 'badge-warning',
            'RECEIVED': 'badge-success',
            'RECEIVED_IN_CASH': 'badge-success',
            'CONFIRMED': 'badge-success',
            'CANCELED': 'badge-danger',
            'EXPIRED': 'badge-danger'
        };
        
        const statusLabels = {
            'PENDING': 'Pendente',
            'RECEIVED': 'Recebido',
            'RECEIVED_IN_CASH': 'Recebido em Dinheiro',
            'CONFIRMED': 'Confirmado',
            'CANCELED': 'Cancelado',
            'EXPIRED': 'Expirado'
        };
        
        element.className = 'badge ' + (statusClasses[status] || 'badge-secondary');
        element.textContent = statusLabels[status] || status;
    }

    // Handle dynamic table sorting
    function sortTable(table, column, type = 'string') {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            let aValue = a.cells[column].textContent.trim();
            let bValue = b.cells[column].textContent.trim();
            
            if (type === 'number') {
                aValue = parseFloat(aValue.replace(/[^\d.-]/g, ''));
                bValue = parseFloat(bValue.replace(/[^\d.-]/g, ''));
            } else if (type === 'date') {
                aValue = new Date(aValue);
                bValue = new Date(bValue);
            }
            
            if (aValue < bValue) return -1;
            if (aValue > bValue) return 1;
            return 0;
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }

    // Handle file inputs
    document.querySelectorAll('.custom-file-input').forEach(input => {
        input.addEventListener('change', function(e) {
            const fileName = this.files[0]?.name;
            const label = this.nextElementSibling;
            if (label) {
                label.textContent = fileName || 'Nenhum arquivo selecionado';
            }
        });
    });
});

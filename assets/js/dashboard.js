document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Animação suave ao carregar os cards
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('show');
        }, index * 100);
    });

    // Atualizar datas do filtro automaticamente para o mês atual
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');

    if (!dataInicio.value) {
        dataInicio.value = firstDay.toISOString().split('T')[0];
    }
    if (!dataFim.value) {
        dataFim.value = lastDay.toISOString().split('T')[0];
    }

    // Validação de datas
    dataInicio.addEventListener('change', function() {
        if (dataFim.value && this.value > dataFim.value) {
            alert('A data inicial não pode ser maior que a data final');
            this.value = dataFim.value;
        }
    });

    dataFim.addEventListener('change', function() {
        if (dataInicio.value && this.value < dataInicio.value) {
            alert('A data final não pode ser menor que a data inicial');
            this.value = dataInicio.value;
        }
    });

    // Hover effect nos cards de estatísticas
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.querySelector('.icon').style.transform = 'scale(1.1)';
        });
        card.addEventListener('mouseleave', function() {
            this.querySelector('.icon').style.transform = 'scale(1)';
        });
    });
});

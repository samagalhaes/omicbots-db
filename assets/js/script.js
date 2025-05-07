/* assets/js/script.js - Funcionalidades JavaScript */

document.addEventListener('DOMContentLoaded', function() {
    // Validação de formulário
    const filterForm = document.querySelector('form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(event) {
            const categories = document.querySelectorAll('input[name="categories[]"]:checked');
            if (categories.length === 0) {
                event.preventDefault();
                alert('Por favor, selecione pelo menos uma categoria de dados.');
            }
            
            // Validar datas
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            
            if (dateFrom.value && dateTo.value) {
                if (new Date(dateFrom.value) > new Date(dateTo.value)) {
                    event.preventDefault();
                    alert('A data inicial não pode ser posterior à data final.');
                }
            }
        });
    }
    
    // Reset de formulário com confirmação
    const resetButton = document.querySelector('button[type="reset"]');
    if (resetButton) {
        resetButton.addEventListener('click', function(event) {
            if (!confirm('Tem certeza que deseja limpar todos os filtros?')) {
                event.preventDefault();
            }
        });
    }
    
    // Animação de entrada para elementos
    const animateElements = document.querySelectorAll('.card, .alert');
    animateElements.forEach(function(element, index) {
        setTimeout(function() {
            element.classList.add('fade-in');
        }, index * 100);
    });
    
    // Destacar linhas da tabela ao passar o mouse
    const tableRows = document.querySelectorAll('tbody tr');
    if (tableRows) {
        tableRows.forEach(function(row) {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(13, 110, 253, 0.1)';
                this.style.transition = 'background-color 0.2s ease';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    }
    
    // Botão para voltar ao topo quando scrollar para baixo
    const createBackToTopButton = function() {
        const button = document.createElement('button');
        button.innerHTML = '<i class="bi bi-arrow-up"></i>';
        button.setAttribute('id', 'back-to-top');
        button.style.position = 'fixed';
        button.style.bottom = '20px';
        button.style.right = '20px';
        button.style.height = '40px';
        button.style.width = '40px';
        button.style.borderRadius = '50%';
        button.style.backgroundColor = '#0d6efd';
        button.style.color = 'white';
        button.style.border = 'none';
        button.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.3)';
        button.style.cursor = 'pointer';
        button.style.display = 'none';
        button.style.zIndex = '1000';
        
        button.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        document.body.appendChild(button);
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                button.style.display = 'block';
            } else {
                button.style.display = 'none';
            }
        });
    };
    
    createBackToTopButton();
    
    // Verificar se há mensagens de alerta e escondê-las após 5 segundos
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    }
    
    // Adicionar tooltips aos botões
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Compatibilidade para navegadores antigos que não suportam date input
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        if (input.type !== 'date') {
            const placeholder = input.getAttribute('placeholder');
            input.setAttribute('placeholder', placeholder || 'DD/MM/AAAA');
        }
    });
});
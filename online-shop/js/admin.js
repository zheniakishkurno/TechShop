document.addEventListener('DOMContentLoaded', function() {
    // Подтверждение удаления товара
    document.querySelectorAll('button[name="delete_product"]').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Вы уверены, что хотите удалить этот товар?')) {
                e.preventDefault();
            }
        });
    });
    
    // Валидация формы добавления товара
    const addForm = document.querySelector('form[name="add_product"]');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const priceInput = this.querySelector('input[name="price"]');
            if (parseFloat(priceInput.value) <= 0) {
                alert('Цена должна быть больше нуля');
                e.preventDefault();
            }
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Управление вкладками
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Убираем активный класс у всех кнопок и контента
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Добавляем активный класс текущей кнопке
                this.classList.add('active');
                
                // Находим соответствующий контент и делаем его активным
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Подтверждение удаления
        document.querySelectorAll('.delete').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Вы уверены, что хотите удалить этот элемент?')) {
                    e.preventDefault();
                }
            });
        });
    }); 
}); 
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
 
    tabButtons.forEach(button => { 
        button.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');
 
            // Удаляем активные классы с анимацией
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => {
                content.classList.remove('active');
                // Сброс стилей для повторного запуска анимации
                content.style.display = 'none';
            });

            // Активируем текущую кнопку
            this.classList.add('active');

            const activeContent = document.getElementById(tabId);
            activeContent.style.display = 'block';

            // Добавляем задержку перед активацией, чтобы сработала анимация
            setTimeout(() => {
                activeContent.classList.add('active');
            }, 10);
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Добавление товара в корзину
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            fetch('api.php?action=add_to_cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.cart_count);
                    showNotification('Товар добавлен в корзину');
                }
            });
        });
    });
    
    // Обновление счетчика корзины
    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(el => {
            el.textContent = count || '0';
        });
    }
    
    // Показать уведомление
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }
    
    // Инициализация корзины при загрузке страницы
    fetch('api.php?action=get_cart')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount(data.products.reduce((sum, item) => sum + item.quantity, 0));
            }
        });
});

// Поиск товаров (автозаполнение)
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length > 2) {
            fetch(`api.php?action=search_products&query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(results => {
                    // Здесь можно реализовать выпадающий список с результатами
                    console.log(results);
                });
        }
    });
}
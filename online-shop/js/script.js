document.addEventListener('DOMContentLoaded', function() {
    // Инициализация корзины
    updateCartCount();
    
    // Слайдер баннеров
    const banners = document.querySelectorAll('.banner');
    const dots = document.querySelectorAll('.dot');
    let currentBanner = 0;
    
    function showBanner(index) {
        banners.forEach(banner => banner.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        banners[index].classList.add('active');
        dots[index].classList.add('active');
        currentBanner = index;
    }
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => showBanner(index));
    });
    
    // Автоматическая смена баннеров
    setInterval(() => {
        let nextBanner = (currentBanner + 1) % banners.length;
        showBanner(nextBanner);
    }, 5000);
    
    // Добавление в корзину
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            addToCart(productId);
        });
    });
    
    // Быстрый просмотр
    document.querySelectorAll('.quick-view').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            showQuickView(productId);
        });
    });
    
    // Закрытие модального окна
    document.querySelector('.close').addEventListener('click', function() {
        document.getElementById('quick-view-modal').style.display = 'none';
    });
    
    // Функция добавления в корзину
    function addToCart(productId, quantity = 1) {
        fetch('api.php?action=add_to_cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount();
                showNotification('Товар добавлен в корзину', 'success');
            } else {
                showNotification(data.message || 'Ошибка', 'error');
            }
        });
    }
    
    // Функция быстрого просмотра
    function showQuickView(productId) {
        fetch(`api.php?action=get_product&id=${productId}`)
            .then(response => response.json())
            .then(product => {
                const modal = document.getElementById('quick-view-modal');
                const content = modal.querySelector('.quick-view-content');
                
                content.innerHTML = `
                    <div class="quick-view-container">
                        <div class="quick-view-image">
                            <img src="${product.image_url || 'images/no-image.jpg'}" alt="${product.name}">
                        </div>
                        <div class="quick-view-info">
                            <h2>${product.name}</h2>
                            <div class="quick-view-price">
                                ${product.discount > 0 ? `
                                    <span class="current-price">${(product.price * (1 - product.discount/100)).toFixed(2)} ₽</span>
                                    <span class="old-price">${product.price.toFixed(2)} ₽</span>
                                ` : `
                                    <span class="current-price">${product.price.toFixed(2)} ₽</span>
                                `}
                            </div>
                            <div class="quick-view-rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="reviews">(${Math.floor(Math.random() * 100) + 1} отзывов)</span>
                            </div>
                            <div class="quick-view-actions">
                                <button class="btn add-to-cart" data-id="${product.id}">
                                    <i class="fas fa-shopping-cart"></i> Добавить в корзину
                                </button>
                                <button class="btn outline">
                                    <i class="far fa-heart"></i> В избранное
                                </button>
                            </div>
                            <div class="quick-view-description">
                                <h3>Описание</h3>
                                <p>${product.description || 'Описание отсутствует'}</p>
                            </div>
                        </div>
                    </div>
                `;
                
                modal.style.display = 'flex';
                
                // Обработчик для кнопки добавления в корзину
                content.querySelector('.add-to-cart').addEventListener('click', function() {
                    addToCart(productId);
                });
            });
    }
    
    // Обновление счетчика корзины
    function updateCartCount() {
        fetch('api.php?action=get_cart_count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.cart-count').forEach(el => {
                        el.textContent = data.count;
                    });
                }
            });
    }
    
    // Показ уведомлений
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            ${message}
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Закрытие модального окна при клике вне его
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('quick-view-modal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});

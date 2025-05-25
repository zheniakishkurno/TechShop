class Cart {
    constructor() {
        this.cartItems = JSON.parse(localStorage.getItem('cart')) || [];
        this.updateCartCount();
        this.initEventListeners();
    }

    initEventListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
                const button = e.target.classList.contains('add-to-cart') ? e.target : e.target.closest('.add-to-cart');
                const productId = button.dataset.id;
                this.addToCart(productId);
            }
        });

        if (document.querySelector('.cart-page')) {
            this.renderCartItems();

            document.querySelector('.cart-items').addEventListener('click', (e) => {
                const removeBtn = e.target.closest('.remove-item');
                if (removeBtn) {
                    const productId = removeBtn.dataset.id;
                    this.removeFromCart(productId);
                }
            });

            document.querySelector('.cart-items').addEventListener('input', (e) => {
                if (e.target.classList.contains('update-quantity')) {
                    const productId = e.target.dataset.id;
                    const newQuantity = parseInt(e.target.value);
                    this.updateQuantity(productId, newQuantity);
                }
            });
        }
    }

    addToCart(productId, quantity = 1) {
        fetch('add_to_cart.php', {
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
                const existingItem = this.cartItems.find(item => item.id === productId);
                if (existingItem) {
                    existingItem.quantity += quantity;
                } else {
                    this.cartItems.push({ id: productId, quantity });
                }
                this.saveCart();
                this.showNotification('Товар добавлен в корзину');
            } else {
                console.error('Ошибка добавления в корзину:', data.error);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
        });
    }

    removeFromCart(productId) {
        this.cartItems = this.cartItems.filter(item => item.id !== productId);
        this.saveCart();
        this.renderCartItems();
    }

    updateQuantity(productId, newQuantity) {
        if (newQuantity < 1 || isNaN(newQuantity)) {
            this.removeFromCart(productId);
            return;
        }

        const item = this.cartItems.find(i => i.id === productId);
        if (item) {
            item.quantity = newQuantity;
            this.saveCart();
            this.updateItemSubtotal(productId);
            this.updateSummary();
        }
    }

    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.cartItems));
        this.updateCartCount();
    }

    updateCartCount() {
        const totalItems = this.cartItems.reduce((sum, item) => sum + item.quantity, 0);
        document.querySelectorAll('.cart-count').forEach(el => {
            el.textContent = totalItems;
        });
    }

    async renderCartItems() {
        if (this.cartItems.length === 0) {
            document.querySelector('.cart-items').innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Ваша корзина пуста</p>
                    <a href="catalog.php" class="btn btn-primary">Перейти в каталог</a>
                </div>
            `;
            document.querySelector('.cart-summary').style.display = 'none';
            return;
        }

        document.querySelector('.cart-summary').style.display = 'block';

        const productIds = this.cartItems.map(item => item.id);
        const response = await fetch('api.php?action=get_cart_products', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: productIds })
        });

        const products = await response.json();

        const cartProducts = products.map(product => {
            const cartItem = this.cartItems.find(item => item.id === product.id);
            return {
                ...product,
                quantity: cartItem.quantity,
                total: product.price * cartItem.quantity
            };
        });

        let html = '';
        let subtotal = 0;

        cartProducts.forEach(product => {
            subtotal += product.total;

            html += `
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="${product.image}" alt="${product.name}">
                    </div>
                    <div class="cart-item-details">
                        <h3 class="cart-item-title">${product.name}</h3>
                        <p class="cart-item-price">${product.price.toFixed(2)} руб.</p>
                    </div>
                    <div class="cart-item-quantity">
                        <input type="number" min="1" value="${product.quantity}" 
                               class="update-quantity" data-id="${product.id}">
                    </div>
                    <div class="cart-item-total" data-id="${product.id}">
                        ${product.total.toFixed(2)} руб.
                    </div>
                    <div class="cart-item-remove">
                        <button class="remove-item" data-id="${product.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        document.querySelector('.cart-items').innerHTML = html;
        this.updateSummary();
    }

    updateItemSubtotal(productId) {
        fetch('api.php?action=get_cart_products', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: [productId] })
        })
        .then(res => res.json())
        .then(products => {
            const product = products[0];
            const item = this.cartItems.find(i => i.id === product.id);
            const subtotal = product.price * item.quantity;

            const totalEl = document.querySelector(`.cart-item-total[data-id="${product.id}"]`);
            if (totalEl) {
                totalEl.textContent = subtotal.toFixed(2) + ' руб.';
            }
        });
    }

    updateSummary() {
        const ids = this.cartItems.map(i => i.id);

        fetch('api.php?action=get_cart_products', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids })
        })
        .then(res => res.json())
        .then(products => {
            let subtotal = 0;

            products.forEach(product => {
                const item = this.cartItems.find(i => i.id === product.id);
                subtotal += product.price * item.quantity;
            });

            const tax = subtotal * 0.05;
            const shipping = subtotal > 5000 ? 0 : 500;
            const total = subtotal + tax + shipping;

            document.querySelector('.cart-summary').innerHTML = `
                <div class="summary-row">
                    <span>Подытог:</span>
                    <span>${subtotal.toFixed(2)} руб.</span>
                </div>
                <div class="summary-row">
                    <span>Налог (5%):</span>
                    <span>${tax.toFixed(2)} руб.</span>
                </div>
                <div class="summary-row">
                    <span>Доставка:</span>
                    <span>${shipping === 0 ? 'Бесплатно' : shipping.toFixed(2) + ' руб.'}</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Итого:</span>
                    <span>${total.toFixed(2)} руб.</span>
                </div>
                <a href="checkout.php" class="btn btn-primary btn-block">Оформить заказ</a>
            `;
        });
    }

    showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Обработчик для кнопок удаления
    document.addEventListener('click', (e) => {
        if (e.target.closest('.remove-item')) {
            const productId = e.target.closest('.remove-item').dataset.id;
            updateCart(productId, 0); // Устанавливаем количество 0 для удаления
        }
    });

    // Обработчик изменения количества 
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('quantity-input')) {
            const productId = e.target.dataset.id;
            const newQuantity = parseInt(e.target.value);
            updateCart(productId, newQuantity);
        }
    });
});

function updateCart(productId, quantity) {
    fetch('update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Перезагружаем страницу для обновления корзины
        }
    });
}

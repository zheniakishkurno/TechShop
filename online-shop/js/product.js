document.addEventListener('DOMContentLoaded', function() {
    // Кнопки для изменения количества товара
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    const quantityInput = document.querySelector('#quantity-input');

    // Обработчик для кнопки "минус"
    minusBtn.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });

    // Обработчик для кнопки "плюс"
    plusBtn.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        const maxQuantity = parseInt(quantityInput.max);
        if (currentValue < maxQuantity) {
            quantityInput.value = currentValue + 1;
        }
    });

    // Кнопка "В корзину"
    const addToCartBtn = document.querySelector('.btn.add-to-cart');
    
    addToCartBtn.addEventListener('click', function() {
        const productId = addToCartBtn.getAttribute('data-id');
        const quantity = quantityInput.value;
        
        // Отправка запроса на сервер
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
                // Успешно добавлен товар
                alert('Товар добавлен в корзину!');
                
                // Обновляем количество товаров в корзине на странице
                const cartCountElement = document.getElementById('cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.total_items; // Обновляем количество
                }
            } else {
                // Ошибка добавления товара
                alert('Ошибка при добавлении товара в корзину: ' + (data.error || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при добавлении товара в корзину.');
        });
    });
    document.addEventListener("DOMContentLoaded", function () {
        // Обработчик для кнопок - и +
        const minusBtns = document.querySelectorAll('.quantity-btn.minus');
        const plusBtns = document.querySelectorAll('.quantity-btn.plus');
    
        // Обработчик для кнопок уменьшения количества
        minusBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const quantityInput = btn.closest('.quantity').querySelector('input');
                let quantity = parseInt(quantityInput.value);
                if (quantity > 1) {
                    quantityInput.value = quantity - 1;
                }
            });
        });
    
        // Обработчик для кнопок увеличения количества
        plusBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const quantityInput = btn.closest('.quantity').querySelector('input');
                let quantity = parseInt(quantityInput.value);
                const maxQuantity = parseInt(quantityInput.max);
                if (quantity < maxQuantity) {
                    quantityInput.value = quantity + 1;
                }
            });
        });
    }); 
});

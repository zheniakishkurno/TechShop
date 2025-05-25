document.addEventListener("DOMContentLoaded", function () {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const cartContainer = document.getElementById("cart-items");

    function updateCartDisplay() {
        cartContainer.innerHTML = "";
        let total = 0;
 
        cart.forEach((item, index) => {
            total += item.price * item.quantity;
            const itemElement = document.createElement("div");
            itemElement.innerHTML = `
                <p>${item.name} - ${item.price} руб. x ${item.quantity} = ${(item.price * item.quantity).toFixed(2)} руб.</p>
                <button onclick="removeFromCart(${index})">Удалить</button>
                <button onclick="updateQuantity(${index}, 'minus')">-</button>
                <button onclick="updateQuantity(${index}, 'plus')">+</button>
            `;
            cartContainer.appendChild(itemElement);
        });

        const totalElement = document.createElement("p");
        totalElement.innerHTML = `<strong>Итого: ${total.toFixed(2)} руб.</strong>`;
        cartContainer.appendChild(totalElement);
    }

    function addToCart(productId, name, price) {
        const existingItem = cart.find(item => item.id === productId);

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ id: productId, name, price, quantity: 1 });
        }

        localStorage.setItem("cart", JSON.stringify(cart));
        updateCartDisplay();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        localStorage.setItem("cart", JSON.stringify(cart));
        updateCartDisplay();
    }

    function updateQuantity(index, action) {
        if (action === 'minus' && cart[index].quantity > 1) {
            cart[index].quantity -= 1;
        } else if (action === 'plus') {
            cart[index].quantity += 1;
        }

        // Пересчитываем цену для товара с новым количеством
        cart[index].total_price = cart[index].price * cart[index].quantity;

        localStorage.setItem("cart", JSON.stringify(cart));
        updateCartDisplay();
    }

    document.querySelectorAll(".add-to-cart").forEach(button => {
        button.addEventListener("click", function () {
            const productId = this.getAttribute("data-id");
            const productName = this.parentElement.querySelector("h3").innerText;
            const productPrice = parseFloat(this.parentElement.querySelector("p").innerText.replace(" руб.", ""));
            addToCart(productId, productName, productPrice);
        });
    });

    document.getElementById("checkout-btn").addEventListener("click", function () {
        if (cart.length === 0) {
            alert("Ваша корзина пуста!");
            return;
        }

        fetch("checkout.php", {
            method: "POST",
            body: JSON.stringify({ cart }),
            headers: { "Content-Type": "application/json" }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                localStorage.removeItem("cart");
                alert("Заказ успешно оформлен!");
                updateCartDisplay();
            } else {
                alert("Ошибка при оформлении заказа.");
            }
        });
    });

    updateCartDisplay();

});

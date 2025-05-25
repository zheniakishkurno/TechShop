<?php
require_once 'functions.php';

$current_user = getCurrentUser();

// Если пользователь не авторизован, перенаправляем его на страницу входа
if (!$current_user) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['tab']) && $_GET['tab'] === 'settings') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);

    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
    $stmt->execute([$first_name, $last_name, $phone, $current_user['id']]);

    // Обновим текущего пользователя
    $current_user['first_name'] = $first_name;
    $current_user['last_name'] = $last_name;
    $current_user['phone'] = $phone;

    $success_message = 'Данные успешно обновлены';
}

redirectIfNotLoggedIn();
 
// Получаем заказы пользователя
$orders = getUserOrders($current_user['id']);

$page_title = 'Мои заказы';
require_once 'header.php';

function getUserOrders($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            o.id, 
            o.created_at, 
            o.total, 
            o.status,
            o.payment_method,
            o.delivery_date,
            o.delivery_address,
            o.notes,
            (
                SELECT GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ' шт.)') SEPARATOR '<br>')
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = o.id
            ) as products_list,
            (
                SELECT GROUP_CONCAT(p.image_url SEPARATOR '|||')
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = o.id
            ) as products_images
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        JOIN users u ON c.email = u.email
        WHERE u.id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderStatusBadge($status) {
    $statuses = [
        'processing' => ['text' => 'В обработке', 'class' => 'processing'],
        'shipped' => ['text' => 'Отправлен', 'class' => 'shipped'],
        'delivered' => ['text' => 'Доставлен', 'class' => 'delivered'],
        'cancelled' => ['text' => 'Отменен', 'class' => 'cancelled']
    ];
    
    return $statuses[$status] ?? ['text' => $status, 'class' => 'default'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Онлайн магазин' ?></title>
    
    <!-- Подключение CSS -->
    <link rel="stylesheet" href="css/profile.css">
        
    <!-- Подключение иконок (если необходимо) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="js/profile.js" defer></script>
</head>

<div class="profile-page">
    <div class="container">
        <h1 class="page-title">Личный кабинет</h1>

        <div class="profile-content"> 
            <!-- Боковое меню -->
            <div class="profile-sidebar">
                <div class="user-info">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2 class="user-name"><?= htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']) ?></h2>
                    <p class="user-email"><?= htmlspecialchars($current_user['email']) ?></p>
                    <p class="user-phone"><?= htmlspecialchars($current_user['phone']) ?></p>
                </div>

                <nav class="profile-menu">
                    <ul>
                        <li class="active"><a href="profile.php"><i class="fas fa-user"></i> Профиль</a></li>
                        <li><a href="profile.php?tab=orders"><i class="fas fa-shopping-bag"></i> Мои заказы</a></li>
                        <li><a href="profile.php?tab=settings"><i class="fas fa-cog"></i> Настройки</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Выход</a></li>
                    </ul>
                </nav>

                <?php if (isAdmin()): ?>
                    <!-- Кнопка для администратора -->
                    <div class="admin-button">
                        <a href="admin.php" class="btn btn-admin">Панель администратора</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Основной контент -->
            <div class="profile-main">
                <?php if (!isset($_GET['tab']) || $_GET['tab'] === 'orders'): ?>
                    <h2 class="section-title">Мои заказы</h2>

                    <?php if (empty($orders)): ?>
                        <div class="empty-orders">
                            <i class="fas fa-shopping-bag"></i>
                            <p>У вас пока нет заказов</p>
                            <a href="catalog.php" class="btn">Перейти в каталог</a>
                        </div>
                <?php else: ?>
                    <div class="orders-container">
                        <?php foreach ($orders as $order): 
                            $status = getOrderStatusBadge($order['status']);
                            $productImages = !empty($order['products_images']) ? explode('|||', $order['products_images']) : [];
                        ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-meta">
                                        <span class="order-number">Заказ №<?= $order['id'] ?></span>
                                        <span class="order-date"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span>
                                    </div>
                                    <div class="order-status <?= $status['class'] ?>">
                                        <?= $status['text'] ?>
                                    </div>
                                </div>

                                <div class="order-body">
                                    <div class="order-products">
                                        <?php if (!empty($productImages)): ?>
                                            <div class="product-images">
                                                <?php foreach ($productImages as $image): ?>
                                                    <img src="<?= htmlspecialchars($image) ?>" alt="Товар" class="product-thumb">
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="products-list">
                                            <?= $order['products_list'] ?>
                                        </div>
                                    </div>

                                    <div class="order-summary">
    <div class="summary-row">
        <span>Сумма заказа:</span>
        <span class="order-total"><?= number_format($order['total'], 2, '.', ' ') ?> ₽</span>
    </div>
    <div class="summary-row">
        <span>Способ оплаты:</span>
        <span><?= htmlspecialchars($order['payment_method']) ?></span>
    </div>
    <?php if ($order['delivery_date']): ?>
        <div class="summary-row">
            <span>Дата доставки:</span>
            <span><?= date('d.m.Y', strtotime($order['delivery_date'])) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($order['delivery_address'])): ?>
        <div class="summary-row">
            <span>Адрес доставки:</span>
            <span><?= htmlspecialchars($order['delivery_address']) ?></span>
        </div>
    <?php endif; ?>
</div>

                                </div>

                                <div class="order-footer">
                                    <?php if ($order['status'] === 'processing'): ?>
                                     <button class="btn btn-cancel cancel-order-btn" data-id="<?= $order['id'] ?>">Отменить заказ</button>

                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php elseif ($_GET['tab'] === 'settings'): ?>
                    <h2 class="section-title">Настройки профиля</h2>

                   <form class="profile-form" method="POST" action="profile.php?tab=settings">
    <div class="form-group">
        <label for="first_name">Имя:</label>
        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($current_user['first_name']) ?>">
    </div>

    <div class="form-group">
        <label for="last_name">Фамилия:</label>
        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($current_user['last_name']) ?>">
    </div>

    <div class="form-group">
        <label for="phone">Телефон:</label>
        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($current_user['phone']) ?>">
    </div>

    <button type="submit" class="btn">Сохранить изменения</button>
</form>


                    <div class="change-password">
                        <h3 class="sub-title">Смена пароля</h3>
                        <form class="password-form">
                            <div class="form-group">
                                <label for="current_password">Текущий пароль:</label>
                                <input type="password" id="current_password" name="current_password">
                            </div>

                            <div class="form-group">
                                <label for="new_password">Новый пароль:</label>
                                <input type="password" id="new_password" name="new_password">
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Подтвердите пароль:</label>
                                <input type="password" id="confirm_password" name="confirm_password">
                            </div>

                            <button type="submit" class="btn">Изменить пароль</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div> 
</div>

<?php require_once 'footer.php'; ?>

<style>
/* Карточка заказа */
.order-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 20px;
    overflow: hidden;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
}

.order-meta {
    display: flex;
    flex-direction: column;
}

.order-number {
    font-weight: 600;
    color: #333;
}

.order-date {
    font-size: 13px;
    color: #666;
}

.order-status {
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
}

.order-status.processing {
    background-color: #fff3cd;
    color: #856404;
}

.order-status.shipped {
    background-color: #cce5ff;
    color: #004085;
}

.order-status.delivered {
    background-color: #d4edda;
    color: #155724;
}

.order-status.cancelled {
    background-color: #f8d7da;
    color: #721c24;
}

.order-body {
    padding: 20px;
    display: flex;
    gap: 30px;
}

.order-products {
    flex: 1;
}

.product-images {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.product-thumb {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #eee;
}

.products-list {
    line-height: 1.6;
    color: #555;
}

.order-summary {
    width: 250px;
    border-left: 1px solid #eee;
    padding-left: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 14px;
}

.summary-row span:first-child {
    color: #666;
}

.order-total {
    font-weight: 600;
    color: #333;
    font-size: 16px;
}

.order-footer {
    padding: 15px 20px;
    background-color: #f8f9fa;
    border-top: 1px solid #e0e0e0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Кнопки */
.btn {
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-outline {
    border: 1px solid #007bff;
    color: #007bff;
    background: white;
}

.btn-outline:hover {
    background-color: #f0f7ff;
}

.btn-cancel {
    border: 1px solid #dc3545;
    color: #dc3545;
    background: white;
}

.btn-cancel:hover {
    background-color: #fff0f0;
}

</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cancel-order-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const orderId = this.dataset.id;
            if (confirm('Вы уверены, что хотите отменить этот заказ?')) {
                fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'order_id=' + encodeURIComponent(orderId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Заказ отменён');
                        location.reload();
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Ошибка при отмене заказа.');
                    console.error(error);
                });
            }
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordForm = document.querySelector('.password-form');

    if (passwordForm) {
        passwordForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(passwordForm);
            fetch('change_password.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    passwordForm.reset();
                }
            })
            .catch(error => {
                alert('Ошибка при смене пароля.');
                console.error(error);
            });
        });
    }
});
</script>

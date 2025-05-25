<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Проверка авторизации и прав администратора
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Доступ запрещен');
}

// Создаем папку для загрузок, если ее нет
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Обработка товаров
        if (isset($_POST['add_product'])) {
            $image_url = null;
            if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
                $image_name = uniqid() . '_' . basename($_FILES['image_url']['name']);
                $image_url = 'uploads/' . $image_name;
                move_uploaded_file($_FILES['image_url']['tmp_name'], $image_url);
            }
            
            $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, description, stock, image_url, discount) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['category_id'],
                $_POST['price'],
                $_POST['description'],
                $_POST['stock'],
                $image_url,
                $_POST['discount'] ?? 0
            ]);
            $_SESSION['message'] = "Товар успешно добавлен!";
        }

        if (isset($_POST['update_product'])) {
            $image_url = $_POST['current_image'];
            if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
                // Удаляем старое изображение, если оно есть
                if ($image_url && file_exists($image_url)) {
                    unlink($image_url);
                }
                
                $image_name = uniqid() . '_' . basename($_FILES['image_url']['name']);
                $image_url = 'uploads/' . $image_name;
                move_uploaded_file($_FILES['image_url']['tmp_name'], $image_url);
            }
            
            $stmt = $pdo->prepare("UPDATE products SET name=?, category_id=?, price=?, description=?, stock=?, image_url=?, discount=? WHERE id=?");
            $stmt->execute([
                $_POST['name'],
                $_POST['category_id'],
                $_POST['price'],
                $_POST['description'],
                $_POST['stock'],
                $image_url,
                $_POST['discount'] ?? 0,
                $_POST['product_id']
            ]);
            $_SESSION['message'] = "Товар успешно обновлен!";
        }

        if (isset($_POST['delete_product'])) {
            // Удаляем изображение товара
            $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id=?");
            $stmt->execute([$_POST['product_id']]);
            $product = $stmt->fetch();
            
            if ($product['image_url'] && file_exists($product['image_url'])) {
                unlink($product['image_url']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
            $stmt->execute([$_POST['product_id']]);
            $_SESSION['message'] = "Товар успешно удален!";
        }

        // Обработка категорий
        if (isset($_POST['add_category'])) {
            $image_url = null;
            if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
                $image_name = uniqid() . '_' . basename($_FILES['image_url']['name']);
                $image_url = 'uploads/' . $image_name;
                move_uploaded_file($_FILES['image_url']['tmp_name'], $image_url);
            }
            
            $stmt = $pdo->prepare("INSERT INTO categories (name, image_url) VALUES (?, ?)");
            $stmt->execute([$_POST['name'], $image_url]);
            $_SESSION['message'] = "Категория успешно добавлена!";
        }

        if (isset($_POST['update_category'])) {
            $image_url = $_POST['current_image'];
            if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
                // Удаляем старое изображение
                if ($image_url && file_exists($image_url)) {
                    unlink($image_url);
                }
                
                $image_name = uniqid() . '_' . basename($_FILES['image_url']['name']);
                $image_url = 'uploads/' . $image_name;
                move_uploaded_file($_FILES['image_url']['tmp_name'], $image_url);
            }
            
            $stmt = $pdo->prepare("UPDATE categories SET name=?, image_url=? WHERE id=?");
            $stmt->execute([$_POST['name'], $image_url, $_POST['category_id']]);
            $_SESSION['message'] = "Категория успешно обновлена!";
        }

        if (isset($_POST['delete_category'])) {
            // Удаляем изображение категории
            $stmt = $pdo->prepare("SELECT image_url FROM categories WHERE id=?");
            $stmt->execute([$_POST['category_id']]);
            $category = $stmt->fetch();
            
            if ($category['image_url'] && file_exists($category['image_url'])) {
                unlink($category['image_url']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
            $stmt->execute([$_POST['category_id']]);
            $_SESSION['message'] = "Категория успешно удалена!";
        }

        // Обработка пользователей
        if (isset($_POST['add_user'])) {
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?,? )");
            $stmt->execute([
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['password'],
                $_POST['role']
            ]);
            $_SESSION['message'] = "Пользователь успешно добавлен!";
        }

        if (isset($_POST['update_user'])) {
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, role=? WHERE id=?");
            $stmt->execute([
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['role'],
                $_POST['user_id']
            ]);
            $_SESSION['message'] = "Пользователь успешно обновлен!";
        }

        if (isset($_POST['delete_user'])) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id=?"); 
            $stmt->execute([$_POST['user_id']]);
            $_SESSION['message'] = "Пользователь успешно удален!";
        }

        // Обработка заказов
        if (isset($_POST['update_order'])) {
            $stmt = $pdo->prepare("UPDATE orders SET status=?, notes=? WHERE id=?");
            $stmt->execute([$_POST['status'], $_POST['notes'], $_POST['order_id']]);
            $_SESSION['message'] = "Заказ успешно обновлен!";
        }

if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];

    // Удаляем связанные элементы заказа
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id=?");
    $stmt->execute([$order_id]);

    // Удаляем заказ
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id=?");
    $stmt->execute([$order_id]);

    $_SESSION['message'] = "Заказ успешно удален!";
}


        // Обработка отзывов
        if (isset($_POST['update_review'])) {
            $stmt = $pdo->prepare("UPDATE reviews SET rating=?, comment=? WHERE id=?");
            $stmt->execute([
                $_POST['rating'],
                $_POST['comment'],
                $_POST['review_id']
            ]);
            $_SESSION['message'] = "Отзыв успешно обновлен!";
        }

        if (isset($_POST['delete_review'])) {
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id=?");
            $stmt->execute([$_POST['review_id']]);
            $_SESSION['message'] = "Отзыв успешно удален!";
        }

        header("Location: admin.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка: " . $e->getMessage();
        header("Location: admin.php");
        exit();
    }
    
}

// Получение данных для отображения
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$users = $pdo->query("SELECT * FROM users")->fetchAll();
$orders = $pdo->query("SELECT 
    o.id, 
    o.total, 
    o.status, 
    o.payment_method, 
    o.created_at, 
    o.notes,
    o.delivery_date,
    o.delivery_address,
    c.name as customer_name, 
    c.email as customer_email
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    ORDER BY o.created_at DESC")->fetchAll();
$customers = $pdo->query("SELECT * FROM customers")->fetchAll();
$reviews = $pdo->query("SELECT 
    r.id, r.rating, r.comment, r.created_at,
    u.email as user_email,
    p.name as product_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN products p ON r.product_id = p.id
    ORDER BY r.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Админ панель</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/admin.css" />
    <script src="js/admin.js" defer></script>
</head>
<body>
<div class="container">
    <h1>Административная панель</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert success"><?= htmlspecialchars($_SESSION['message']) ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Навигация по разделам -->
    <div class="tabs">
        <button class="tab-button active" data-tab="products">Товары</button>
        <button class="tab-button" data-tab="categories">Категории</button>
        <button class="tab-button" data-tab="users">Пользователи</button>
        <button class="tab-button" data-tab="orders">Заказы</button>
        <button class="tab-button" data-tab="reviews">Отзывы</button>
    </div>
    <a href="index.php" class="back-button">← Назад на сайт</a>

    <!-- Управление товарами -->
    <div id="products" class="tab-content active">
        <h2>Управление товарами</h2>

        <!-- Добавление товара -->
       <form method="POST" enctype="multipart/form-data" class="form">
    <h3>Добавить новый товар</h3>
    <input type="text" name="name" placeholder="Название" required />
    <select name="category_id" required>
        <option value="">Выберите категорию</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" name="price" placeholder="Цена" step="0.01" min="0" required />
    <textarea name="description" placeholder="Описание" required></textarea>
    <input type="number" name="stock" placeholder="Количество" min="0" required />
    <input type="number" name="discount" placeholder="Скидка (%)" min="0" max="100" />
    <!-- Добавленные поля -->
    <input type="number" name="views" placeholder="Количество просмотров" min="0" value="0" />
    <input type="number" name="reviews_count" placeholder="Количество отзывов" min="0" value="0" />
    <input type="file" name="image_url" required />
    <button type="submit" name="add_product">Добавить</button>
</form>
<div class="table-wrapper">
    <table>
        <!-- Список товаров -->
        <h3>Список товаров</h3>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Категория</th>
                <th>Цена</th>
                <th>Скидка</th>
                <th>На складе</th>
                <th>Изображение</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <form method="POST" enctype="multipart/form-data">
                        <td><?= $product['id'] ?></td>
                        <td><input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required /></td>
                        <td>
                            <select name="category_id" required>
                                <option value="">Без категории</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="price" value="<?= $product['price'] ?>" step="0.01" min="0" required /></td>
                        <td><input type="number" name="discount" value="<?= $product['discount'] ?>" min="0" max="100" /></td>
                        <td><input type="number" name="stock" value="<?= $product['stock'] ?>" min="0" required /></td>
                        <td>
                            <?php if ($product['image_url']): ?>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="" style="height:40px;vertical-align:middle" />
                            <?php endif; ?>
                            <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['image_url']) ?>" />
                            <input type="file" name="image_url" />
                        </td>
                        <td class="actions">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>" />
                            <button type="submit" name="update_product">Обновить</button>
                            <button type="submit" name="delete_product" class="delete">Удалить</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
  </table>
</div>
    <!-- Управление категориями -->
    <div id="categories" class="tab-content">
        <h2>Управление категориями</h2>

        <form method="POST" enctype="multipart/form-data" class="form">
            <h3>Добавить новую категорию</h3>
            <input type="text" name="name" placeholder="Название категории" required />
            <input type="file" name="image_url" />
            <button type="submit" name="add_category">Добавить</button>
        </form>

        <h3>Список категорий</h3>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Изображение</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <form method="POST" enctype="multipart/form-data">
                        <td><?= $category['id'] ?></td>
                        <td><input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required /></td>
                        <td>
                            <?php if ($category['image_url']): ?>
                                <img src="<?= htmlspecialchars($category['image_url']) ?>" alt="" style="height:40px;vertical-align:middle" />
                            <?php endif; ?>
                            <input type="hidden" name="current_image" value="<?= htmlspecialchars($category['image_url']) ?>" />
                            <input type="file" name="image_url" />
                        </td>
                      
                            <td class="actions">
                                <input type="hidden" name="category_id" value="<?= $category['id'] ?>" />
                                <button type="submit" name="update_category">Обновить</button>
                                <button type="submit" name="delete_category" class="delete">Удалить</button>
                            </td>
                      
                    </form>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Управление пользователями -->
    <div id="users" class="tab-content">
        <h2>Управление пользователями</h2>

       <form method="POST" class="form">
    <h3>Добавить нового пользователя</h3>
    <input type="text" name="first_name" placeholder="Имя" required />
    <input type="text" name="last_name" placeholder="Фамилия" required />
    <input type="email" name="email" placeholder="Email" required />
    <input type="text" name="phone" placeholder="Телефон" required />
    <input type="password" name="password" placeholder="Пароль" required />
    <select name="role">
        <option value="user">Пользователь</option>
        <option value="admin">Администратор</option>
    </select>
    <button type="submit" name="add_user">Добавить</button>
</form>

        <h3>Список пользователей</h3>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Фамилия</th>
                <th>Email</th>
                <th>Телефон</th>
                <th>Роль</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <form method="POST">
                        <td><?= $user['id'] ?></td>
                        <td><input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required /></td>
                        <td><input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required /></td>
                        <td><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required /></td>
                        <td><input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" /></td>
                        <td>
                            <select name="role">
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Пользователь</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
                            </select>
                        </td>
                     
                            <td class="actions">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>" />
                            <button type="submit" name="update_user">Обновить</button>
                            <button type="submit" name="delete_user" class="delete">Удалить</button>
                       
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Управление заказами -->
    <div id="orders" class="tab-content">
        <h2>Управление заказами</h2>

        <h3>Список заказов</h3>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Клиент</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Метод оплаты</th>
                <th>Дата</th>
                <th>Дата доставки</th>
                <th>Адрес доставки</th>
                <th>Примечания</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
    <form method="POST">
        <td><?= $order['id'] ?></td>
        <td><?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars($order['customer_email']) ?>)</td>
        <td><?= $order['total'] ?></td>
        <td>
            <select name="status">
                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>В обработке</option>
                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Отправлен</option>
                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Доставлен</option>
                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Отменен</option>
            </select>
        </td>
        <td><?= htmlspecialchars($order['payment_method']) ?></td>
        <td><?= $order['created_at'] ?></td>
<td><?= isset($order['delivery_date']) ? $order['delivery_date'] : 'Не указана' ?></td>
<td><?= isset($order['delivery_address']) ? htmlspecialchars($order['delivery_address']) : 'Не указан' ?></td>
        <td><textarea name="notes"><?= htmlspecialchars($order['notes']) ?></textarea></td>
        <td class="actions">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>" />
            <button type="submit" name="update_order">Обновить</button>
            <button type="submit" name="delete_order" class="delete">Удалить</button>
        </td>
    </form>
</tr>

            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Управление отзывами -->
    <div id="reviews" class="tab-content">
        <h2>Управление отзывами</h2>

        <h3>Список отзывов</h3>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Пользователь</th>
                <th>Товар</th>
                <th>Оценка</th>
                <th>Комментарий</th>
                <th>Дата</th>
                <th>Действия</th> 
            </tr>
            </thead>
            <tbody>
            <?php foreach ($reviews as $review): ?>
                <tr>
                    <form method="POST">
                        <td><?= $review['id'] ?></td>
                        <td><?= htmlspecialchars($review['user_email']) ?></td>
                        <td><?= htmlspecialchars($review['product_name']) ?></td>
                        <td>
                            <select name="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= $review['rating'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                        <td><textarea name="comment" required><?= htmlspecialchars($review['comment']) ?></textarea></td>
                        <td><?= $review['created_at'] ?></td>
                      
                             <td class="actions">
                            <input type="hidden" name="review_id" value="<?= $review['id'] ?>" />
                            <button type="submit" name="update_review">Обновить</button>
                            <button type="submit" name="delete_review" class="delete">Удалить</button>
                       
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?> 
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php
ob_start();
session_start();
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// Инициализация активной вкладки
if (!isset($_SESSION['active_tab'])) {
    $_SESSION['active_tab'] = 'all_tables';
}

// Обновление активной вкладки при отправке формы
if (isset($_POST['current_tab'])) {
    $_SESSION['active_tab'] = $_POST['current_tab'];
}

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

// Создание необходимых директорий с правами доступа
$upload_dirs = ['images'];
foreach ($upload_dirs as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0777, true)) {
            $_SESSION['error'] = "Не удалось создать директорию: " . $dir;
            error_log("Failed to create directory: " . $dir);
        } else {
            chmod($dir, 0777); // Установка прав доступа после создания
        }
    }
}

// Функция для безопасной загрузки изображения
function handleImageUpload($file, $old_image = null) {
    try {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Некорректные параметры файла.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return null;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('Превышен размер файла.');
            default:
                throw new Exception('Неизвестная ошибка загрузки.');
        }

        if ($file['size'] > 5242880) { // 5MB
            throw new Exception('Файл слишком большой.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);

        $allowed_types = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];

        if (!array_key_exists($mime_type, $allowed_types)) {
            throw new Exception('Неверный формат файла. Разрешены только JPG, PNG и GIF.');
        }

        // Генерируем уникальное имя файла
        $extension = $allowed_types[$mime_type];
        $image_name = uniqid() . '_' . time() . '.' . $extension;
        $upload_path = 'images/' . $image_name;

        // Удаляем старое изображение, если оно существует
        if ($old_image && file_exists($old_image)) {
            unlink($old_image);
            error_log("Deleted old image: " . $old_image);
        }

        // Перемещаем загруженный файл
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception('Не удалось сохранить файл.');
        }

        error_log("Successfully uploaded image: " . $upload_path);
        return $upload_path;

    } catch (Exception $e) {
        error_log("Image upload error: " . $e->getMessage());
        throw $e;
    }
}

// Обработка загрузки изображений для продуктов
if (isset($_POST['add_product']) || isset($_POST['update_product'])) {
    try {
        $image_url = null;
        
        if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] !== UPLOAD_ERR_NO_FILE) {
            $old_image = isset($_POST['current_image']) ? $_POST['current_image'] : null;
            $image_url = handleImageUpload($_FILES['image_url'], $old_image);
        } elseif (isset($_POST['current_image'])) {
            $image_url = $_POST['current_image'];
        }

        if (isset($_POST['add_product'])) {
            $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, description, stock, image_url, discount) VALUES (?, ?, ?, ?, ?, ?, ?) RETURNING id");
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
        } else {
            $stmt = $pdo->prepare("UPDATE products SET 
                name = ?, 
                category_id = ?, 
                price = ?, 
                description = ?,
                stock = ?, 
                image_url = ?, 
                discount = ?,
                views = ?,
                reviews_count = ?
                WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['category_id'],
                $_POST['price'],
                $_POST['description'],
                $_POST['stock'],
                $image_url,
                $_POST['discount'] ?? 0,
                $_POST['views'],
                $_POST['reviews_count'],
                $_POST['product_id']
            ]);
            $_SESSION['message'] = "Товар успешно обновлен!";
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Ошибка: " . $e->getMessage();
        error_log("Product operation error: " . $e->getMessage());
    }
    
    header("Location: admin.php");
    exit;
}

// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Обработка товаров
        if (isset($_POST['add_product'])) {
            $image_url = null;
            if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
                $image_name = uniqid() . '_' . basename($_FILES['image_url']['name']);
                $upload_path = 'images/' . $image_name;
                
                if (move_uploaded_file($_FILES['image_url']['tmp_name'], $upload_path)) {
                    $image_url = $upload_path;
                    $_SESSION['message'] = "Товар и изображение успешно добавлены!";
                } else {
                    $_SESSION['error'] = "Ошибка при загрузке изображения.";
                }
            }
            
            try {
                $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, description, stock, image_url, discount) VALUES (?, ?, ?, ?, ?, ?, ?) RETURNING id");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['category_id'],
                    $_POST['price'],
                    $_POST['description'],
                    $_POST['stock'],
                    $image_url,
                    $_POST['discount'] ?? 0
                ]);
                
                if (!isset($_SESSION['message'])) {
                    $_SESSION['message'] = "Товар успешно добавлен!";
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Ошибка при добавлении товара: " . $e->getMessage();
                // Удаляем загруженное изображение в случае ошибки
                if ($image_url && file_exists($image_url)) {
                    unlink($image_url);
                }
            }
            
            header("Location: admin.php");
            exit;
        }

        if (isset($_POST['update_product'])) {
            $product_id = $_POST['product_id'];
            $name = $_POST['name'];
            $category_id = $_POST['category_id'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $discount = $_POST['discount'] ?? 0;

            // Обновляем основные данные товара
            $stmt = $pdo->prepare("UPDATE products SET 
                name = ?, 
                category_id = ?, 
                price = ?, 
                stock = ?, 
                discount = ? 
                WHERE id = ?");
            $stmt->execute([$name, $category_id, $price, $stock, $discount, $product_id]);

            // Обработка загруженного изображения
            if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
                $image_url = handleImageUpload($_FILES['image_url'], $_POST['current_image']);
                if ($image_url) {
                    $stmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
                    $stmt->execute([$image_url, $product_id]);
                }
            }

            $_SESSION['message'] = "Товар успешно обновлен!";
            header("Location: admin.php");
            exit;
        }

        if (isset($_POST['delete_product'])) {
            $product_id = $_POST['product_id'];
            
            // Получаем информацию о товаре перед удалением
            $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            // Удаляем изображение, если оно существует
            if ($product && !empty($product['image_url']) && file_exists($product['image_url'])) {
                unlink($product['image_url']);
            }

            // Удаляем запись из базы данных
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);

            $_SESSION['message'] = "Товар успешно удален!";
            header("Location: admin.php");
            exit;
        }

        // Обработка категорий
        if (isset($_POST['add_category'])) {
            $image_url = null;
            if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
                $image_name = uniqid() . '_' . basename($_FILES['image_url']['name']);
                $upload_path = 'images/' . $image_name;
                
                if (move_uploaded_file($_FILES['image_url']['tmp_name'], $upload_path)) {
                    $image_url = $upload_path;
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO categories (name, image_url) VALUES (?, ?)");
            $stmt->execute([$_POST['name'], $image_url]);
            $_SESSION['message'] = "Категория успешно добавлена!";
        }

        if (isset($_POST['update_category'])) {
            $category_id = $_POST['category_id'];
            $name = $_POST['name'];
            
            // Обновляем основные данные категории
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $category_id]);

            // Обработка загруженного изображения
            if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
                $image_url = handleImageUpload($_FILES['image_url'], $_POST['current_image']);
                if ($image_url) {
                    $stmt = $pdo->prepare("UPDATE categories SET image_url = ? WHERE id = ?");
                    $stmt->execute([$image_url, $category_id]);
                }
            }

            $_SESSION['message'] = "Категория успешно обновлена!";
            header("Location: admin.php");
            exit;
        }

        if (isset($_POST['delete_category'])) {
            $category_id = $_POST['category_id'];
            
            // Получаем информацию о категории перед удалением
            $stmt = $pdo->prepare("SELECT image_url FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $category = $stmt->fetch();

            // Удаляем изображение, если оно существует
            if ($category && !empty($category['image_url'])) {
                if (file_exists($category['image_url'])) {
                    unlink($category['image_url']);
                }
            }

            // Удаляем запись из базы данных
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);

            $_SESSION['message'] = "Категория успешно удалена!";
            header("Location: admin.php");
            exit;
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
        exit;
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
    
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    
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
        <button class="tab-button <?= $_SESSION['active_tab'] == 'all_tables' ? 'active' : '' ?>" data-tab="all_tables">Все таблицы</button>
        <button class="tab-button <?= $_SESSION['active_tab'] == 'products' ? 'active' : '' ?>" data-tab="products">Товары</button>
        <button class="tab-button <?= $_SESSION['active_tab'] == 'categories' ? 'active' : '' ?>" data-tab="categories">Категории</button>
        <button class="tab-button <?= $_SESSION['active_tab'] == 'users' ? 'active' : '' ?>" data-tab="users">Пользователи</button>
        <button class="tab-button <?= $_SESSION['active_tab'] == 'orders' ? 'active' : '' ?>" data-tab="orders">Заказы</button>
        <button class="tab-button <?= $_SESSION['active_tab'] == 'reviews' ? 'active' : '' ?>" data-tab="reviews">Отзывы</button>
    </div>
    <a href="index.php" class="back-button">← Назад на сайт</a>

    <!-- Вкладка со всеми таблицами -->
    <div id="all_tables" class="tab-content <?= $_SESSION['active_tab'] == 'all_tables' ? 'active' : '' ?>">
        <h2>Обзор таблиц</h2>
        
        <div class="dashboard-grid">
            <!-- Блок товаров -->
            <div class="dashboard-card">
                <h3>Товары</h3>
                <div class="stats">
                    <p>Всего товаров: <strong><?= count($products) ?></strong></p>
                    <?php
                    $total_stock = array_sum(array_column($products, 'stock'));
                    $out_of_stock = count(array_filter($products, function($p) { return $p['stock'] == 0; }));
                    ?>
                    <p>Общее количество на складе: <strong><?= $total_stock ?></strong></p>
                    <p>Нет в наличии: <strong><?= $out_of_stock ?></strong></p>
                </div>
                <a href="#" class="dashboard-link" onclick="switchTab('products')">Управление товарами →</a>
            </div>

            <!-- Блок категорий -->
            <div class="dashboard-card">
                <h3>Категории</h3>
                <div class="stats">
                    <p>Всего категорий: <strong><?= count($categories) ?></strong></p>
                    <?php
                    $categories_with_products = array_filter($categories, function($c) use ($products) {
                        return count(array_filter($products, function($p) use ($c) {
                            return $p['category_id'] == $c['id'];
                        })) > 0;
                    });
                    ?>
                    <p>Активных категорий: <strong><?= count($categories_with_products) ?></strong></p>
                </div>
                <a href="#" class="dashboard-link" onclick="switchTab('categories')">Управление категориями →</a>
            </div>

            <!-- Блок пользователей -->
            <div class="dashboard-card">
                <h3>Пользователи</h3>
                <div class="stats">
                    <p>Всего пользователей: <strong><?= count($users) ?></strong></p>
                    <?php
                    $admins = count(array_filter($users, function($u) { return $u['role'] === 'admin'; }));
                    ?>
                    <p>Администраторов: <strong><?= $admins ?></strong></p>
                    <p>Клиентов: <strong><?= count($users) - $admins ?></strong></p>
                </div>
                <a href="#" class="dashboard-link" onclick="switchTab('users')">Управление пользователями →</a>
            </div>

            <!-- Блок заказов -->
            <div class="dashboard-card">
                <h3>Заказы</h3>
                <div class="stats">
                    <p>Всего заказов: <strong><?= count($orders) ?></strong></p>
                    <?php
                    $processing = count(array_filter($orders, function($o) { return $o['status'] === 'processing'; }));
                    $completed = count(array_filter($orders, function($o) { return $o['status'] === 'delivered'; }));
                    ?>
                    <p>В обработке: <strong><?= $processing ?></strong></p>
                    <p>Выполнено: <strong><?= $completed ?></strong></p>
                </div>
                <a href="#" class="dashboard-link" onclick="switchTab('orders')">Управление заказами →</a>
            </div>

            <!-- Блок отзывов -->
            <div class="dashboard-card">
                <h3>Отзывы</h3>
                <div class="stats">
                    <p>Всего отзывов: <strong><?= count($reviews) ?></strong></p>
                    <?php
                    $avg_rating = count($reviews) > 0 ? 
                        round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0;
                    ?>
                    <p>Средняя оценка: <strong><?= $avg_rating ?></strong></p>
                </div>
                <a href="#" class="dashboard-link" onclick="switchTab('reviews')">Управление отзывами →</a>
            </div>
        </div>
    </div>

    <style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }

    .dashboard-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .dashboard-card h3 {
        color: #2c3e50;
        margin-top: 0;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e1e8ed;
    }

    .stats {
        margin-bottom: 20px;
    }

    .stats p {
        margin: 8px 0;
        color: #666;
    }

    .stats strong {
        color: #2c3e50;
        font-size: 1.1em;
    }

    .dashboard-link {
        display: inline-block;
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .dashboard-link:hover {
        color: #0056b3;
    }

    .input-hint {
        display: block;
        font-size: 0.85em;
        color: #666;
        margin-top: 5px;
        font-style: italic;
    }

    .file-input-wrapper {
        margin: 10px 0;
    }
    </style>

    <script>
    function switchTab(tabId) {
        const button = document.querySelector(`[data-tab="${tabId}"]`);
        if (button) {
            button.click();
        }
    }
    </script>

    <!-- Обновляем остальные вкладки -->
    <div id="products" class="tab-content <?= $_SESSION['active_tab'] == 'products' ? 'active' : '' ?>">
        <h2>Управление товарами</h2>
        
        <!-- Форма добавления товара -->
        <form method="POST" enctype="multipart/form-data" class="form" accept-charset="utf-8">
            <h3>Добавить новый товар</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Название</label>
                    <input type="text" name="name" required />
                </div>
                <div class="form-group">
                    <label>Категория</label>
                    <select name="category_id" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Цена</label>
                    <input type="number" name="price" step="0.01" min="0" required />
                </div>
                <div class="form-group">
                    <label>Количество на складе</label>
                    <input type="number" name="stock" min="0" required />
                </div>
                <div class="form-group">
                    <label>Скидка (%)</label>
                    <input type="number" name="discount" min="0" max="100" value="0" />
                </div>
            </div>
            <div class="form-group">
                <label>Описание</label>
                <textarea name="description" required></textarea>
            </div>
            <div class="form-group">
                <label>Изображение</label>
                <div class="file-input-wrapper">
                    <button type="button" class="file-input-button">Выберите файл</button>
                    <input type="file" name="image_url" required />
                    <span class="input-hint">Допустимые форматы: JPG, PNG, GIF. Максимальный размер: 5MB</span>
                </div>
            </div>
            <button type="submit" name="add_product">Добавить товар</button>
        </form>

        <!-- Таблица товаров -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th>Описание</th>
                        <th>Скидка</th>
                        <th>На складе</th>
                        <th>Просмотры</th>
                        <th>Кол-во отзывов</th>
                        <th>Дата создания</th>
                        <th>Изображение</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <form method="POST" enctype="multipart/form-data" accept-charset="utf-8">
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
                        <td><textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea></td>
                        <td><input type="number" name="discount" value="<?= $product['discount'] ?>" min="0" max="100" /></td>
                        <td><input type="number" name="stock" value="<?= $product['stock'] ?>" min="0" required /></td>
                        <td><input type="number" name="views" value="<?= $product['views'] ?>" min="0" readonly /></td>
                        <td><input type="number" name="reviews_count" value="<?= $product['reviews_count'] ?>" min="0" readonly /></td>
                        <td><?= $product['created_at'] ?></td>
                        <td>
                            <?php if ($product['image_url']): ?>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="" style="height:40px;vertical-align:middle" />
                            <?php endif; ?>
                            <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['image_url']) ?>" />
                            <div class="file-input-wrapper">
                                <button type="button" class="file-input-button">Изменить</button>
                                <input type="file" name="image_url" />
                                <span class="input-hint">Допустимые форматы: JPG, PNG, GIF. Максимальный размер: 5MB</span>
                            </div>
                        </td>
                        <td class="actions">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>" />
                            <button type="submit" name="update_product" class="update-btn">Обновить</button>
                        </form>
                        <form method="POST" style="display: inline;" accept-charset="utf-8">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>" />
                            <button type="submit" name="delete_product" class="delete" onclick="return confirm('Вы уверены, что хотите удалить этот товар?');">Удалить</button>
                        </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="categories" class="tab-content <?= $_SESSION['active_tab'] == 'categories' ? 'active' : '' ?>">
        <h2>Управление категориями</h2>

        <form method="POST" enctype="multipart/form-data" class="form" accept-charset="utf-8">
            <h3>Добавить новую категорию</h3>
            <input type="text" name="name" placeholder="Название категории" required />
            <div class="file-input-wrapper">
                <input type="file" name="image_url" />
                <span class="input-hint">Допустимые форматы: JPG, PNG, GIF. Максимальный размер: 5MB</span>
            </div>
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
                    <form method="POST" enctype="multipart/form-data" accept-charset="utf-8">
                        <td><?= $category['id'] ?></td>
                        <td><input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required /></td>
                        <td>
                            <?php if ($category['image_url']): ?>
                                <img src="<?= htmlspecialchars($category['image_url']) ?>" alt="" style="height:40px;vertical-align:middle" />
                            <?php endif; ?>
                            <input type="hidden" name="current_image" value="<?= htmlspecialchars($category['image_url']) ?>" />
                            <div class="file-input-wrapper">
                                <button type="button" class="file-input-button">Изменить</button>
                                <input type="file" name="image_url" />
                                <span class="input-hint">Допустимые форматы: JPG, PNG, GIF. Максимальный размер: 5MB</span>
                            </div>
                        </td>
                        <td class="actions">
                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>" />
                            <button type="submit" name="update_category">Обновить</button>
                        </form>
                        <form method="POST" style="display: inline;" accept-charset="utf-8">
                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>" />
                            <button type="submit" name="delete_category" class="delete" onclick="return confirm('Вы уверены, что хотите удалить эту категорию?');">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="users" class="tab-content <?= $_SESSION['active_tab'] == 'users' ? 'active' : '' ?>">
        <h2>Управление пользователями</h2>

        <form method="POST" class="form">
            <h3>Добавить нового пользователя</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">Имя</label>
                    <div class="input-wrapper">
                        <input type="text" id="first_name" name="first_name" 
                               pattern="[А-Яа-яЁё\s-]{2,50}" 
                               title="Имя должно содержать только кириллицу, дефис и пробелы (от 2 до 50 символов)"
                               required />
                        <span class="error-message">Введите корректное имя (только кириллица, от 2 до 50 символов)</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="last_name">Фамилия</label>
                    <div class="input-wrapper">
                        <input type="text" id="last_name" name="last_name" 
                               pattern="[А-Яа-яЁё\s-]{2,50}" 
                               title="Фамилия должна содержать только кириллицу, дефис и пробелы (от 2 до 50 символов)"
                               required />
                        <span class="error-message">Введите корректную фамилию (только кириллица, от 2 до 50 символов)</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" 
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                           title="Введите корректный email адрес"
                           required />
                    <span class="error-message">Введите корректный email адрес</span>
                </div>
            </div>

            <div class="form-group">
                <label for="phone">Телефон</label>
                <div class="input-wrapper">
                    <input type="tel" id="phone" name="phone" 
                           pattern="\+375\s?\(?(17|29|33|44|25)\)?\s?\d{3}[-\s]?\d{2}[-\s]?\d{2}"
                           title="Введите номер в формате: +375 (29) 999-99-99"
                           placeholder="+375 (29) 999-99-99"
                           required />
                    <span class="input-hint">Формат: +375 (29/33/44/25/17) XXX-XX-XX</span>
                    <span class="error-message">Введите корректный номер телефона</span>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" 
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                           title="Пароль должен содержать минимум 6 символов, включая цифры, строчные и заглавные буквы"
                           required />
                    <span class="input-hint">Минимум 6 символов, включая цифры, строчные и заглавные буквы</span>
                    <span class="error-message">Пароль должен соответствовать требованиям безопасности</span>
                </div>
            </div>

            <div class="form-group">
                <label for="role">Роль</label>
                <div class="input-wrapper">
                    <select id="role" name="role" required>
                        <option value="">Выберите роль</option>
                        <option value="user">Пользователь</option>
                        <option value="admin">Администратор</option>
                    </select>
                    <span class="error-message">Выберите роль пользователя</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="add_user">Добавить</button>
            </div>
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
                        <td>
                            <input type="tel" name="phone" 
                                   value="<?= htmlspecialchars($user['phone']) ?>" 
                                   pattern="\+375\s?\(?(17|29|33|44|25)\)?\s?\d{3}[-\s]?\d{2}[-\s]?\d{2}" 
                                   title="Введите номер в формате: +375 (29) 999-99-99" 
                                   placeholder="+375 (29) 999-99-99" required />
                            <span class="phone-hint">Формат: +375 (29/33/44/25/17) XXX-XX-XX</span>
                        </td>
                        <td>
                            <select name="role">
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Пользователь</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
                            </select>
                        </td>
                        <td class="actions">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>" />
                            <button type="submit" name="update_user">Обновить</button>
                            <button type="submit" name="delete_user" class="delete" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');">Удалить</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="orders" class="tab-content <?= $_SESSION['active_tab'] == 'orders' ? 'active' : '' ?>">
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

    <div id="reviews" class="tab-content <?= $_SESSION['active_tab'] == 'reviews' ? 'active' : '' ?>">
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

<script>
    // Переключение между вкладками
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.dataset.tab;
            
            // Убираем активный класс у всех кнопок и контента
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Добавляем активный класс нажатой кнопке и соответствующему контенту
            button.classList.add('active');
            document.getElementById(tabId).classList.add('active');
            
            // Сохраняем активную вкладку в сессии через AJAX
            fetch('admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'current_tab=' + tabId
            });
        });
    });

    // Добавляем скрытое поле current_tab ко всем формам
    document.querySelectorAll('form').forEach(form => {
        if (!form.querySelector('input[name="current_tab"]')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'current_tab';
            input.value = '<?= $_SESSION['active_tab'] ?>';
            form.appendChild(input);
        }
    });
</script>
</body>
</html>

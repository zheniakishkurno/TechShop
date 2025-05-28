<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';


// ------------------ Функции для работы с пользователями ------------------

// Проверка авторизации
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Проверка роли администратора
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Получение текущего пользователя
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Авторизация пользователя
// Авторизация пользователя
function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "Пользователь не найден<br>";
    } elseif (!password_verify($password, $user['password'])) {
        echo "Пароль не совпадает<br>";
    }

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        return true;
    }

    return false;
}



// Выход пользователя
function logout() {
    session_unset();
    session_destroy();
}

// Перенаправление для неавторизованных
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }
}

// Перенаправление для неадминов
function redirectIfNotAdmin() {
    redirectIfNotLoggedIn();
    if (!isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        exit('Доступ запрещен');
    }
}

// ------------------ Функции для работы с заказами и товарами ------------------

// Получение всех категорий
function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT id, name, image_url FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as &$category) {
        $category['image_url'] = formatImagePath($category['image_url']);
    }

    return $categories;
}

// Форматирование пути к изображению
function formatImagePath($path) {
    if (empty($path)) {
        return '/images/no-image.png';
    }
    $path = str_replace('\\', '/', $path);
    $filename = basename($path);
    return '/images/' . $filename;
}



// Получение товаров по категории
function getProducts($category_id = null, $limit = null) {
    global $pdo;
    
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id";
    
    if ($category_id) {
        $sql .= " WHERE p.category_id = :category_id";
    }
    
    $sql .= " ORDER BY p.created_at DESC"; // Выводим товары по дате создания (или можете изменить на сортировку по другим полям)
    
if ($limit) {
    $sql .= " LIMIT " . intval($limit);
}

    
    $stmt = $pdo->prepare($sql);
    
    if ($category_id && $limit) {
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    } elseif ($category_id) {
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    } elseif ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    } 

    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Получение товара по ID
function getProductById($product_id) {
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT p.id, p.name, p.description, p.price, p.image_url, p.stock, p.category_id,
               c.name AS category_name, p.discount, p.reviews_count
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = :id
    ');
    $stmt->execute(['id' => $product_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}



// Получение популярных товаров
function getPopularProducts($limit = 4) {
    global $pdo;
    $limit = (int)$limit; // безопасно вручную вставляем число
$stmt = $pdo->query("SELECT * FROM products ORDER BY views DESC LIMIT $limit");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Увеличение счетчика просмотров
function incrementProductViews($product_id) {
    global $pdo;

    // Увеличиваем счетчик просмотров
    $stmt = $pdo->prepare('UPDATE products SET views = views + 1 WHERE id = :id');
    $stmt->execute(['id' => $product_id]);
}


// ------------------ Функции для работы с заказами ------------------

// Создание заказа
// Создание заказа
function createOrder($user_id, $items, $total, $payment_method, $delivery_method, $address = '', $notes = '') {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Получаем данные пользователя
        $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $user_stmt->execute([$user_id]);
        $user = $user_stmt->fetch();

        if (!$user) {
            throw new Exception("Пользователь не найден");
        }

        // Создаем или находим клиента
        $customer_stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $customer_stmt->execute([$user['email']]);
        $customer = $customer_stmt->fetch();

        if (!$customer) {
            // Создаем нового клиента
            $insert_customer = $pdo->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
            $insert_customer->execute([
                $user['first_name'] . ' ' . $user['last_name'],
                $user['email'],
                $user['phone'],
                $address
            ]);
            $customer_id = $pdo->lastInsertId();
        } else {
            $customer_id = $customer['id'];
        }

        // Если способ доставки — самовывоз, устанавливаем адрес вручную
        if ($delivery_method === 'pickup') {
            $address = 'Уручье 4';
        }

        // Устанавливаем дату доставки - следующий день
        $delivery_date = date('Y-m-d H:i:s', strtotime('+1 day'));

        // Создаем заказ с датой доставки
$order_stmt = $pdo->prepare("
    INSERT INTO orders 
    (customer_id, total, status, payment_method, shipping_address, delivery_address, delivery_date, notes)
    VALUES (?, ?, 'processing', ?, ?, ?, ?, ?) RETURNING id
");
$order_stmt->execute([
    $customer_id,
    $total,
    $payment_method,
    $address,
    $address,
    $delivery_date,
    $notes
]);

$order_id = $order_stmt->fetchColumn(); // вместо lastInsertId()


        // Добавляем товары в заказ
        $items_stmt = $pdo->prepare("INSERT INTO order_items 
                                    (order_id, product_id, quantity, price) 
                                    VALUES (?, ?, ?, ?)");

        foreach ($items as $item) {
            $items_stmt->execute([
                $order_id,
                $item['id'],
                $item['quantity'],
                $item['price']
            ]);

            // Обновляем количество товара на складе
            $update_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $update_stmt->execute([$item['quantity'], $item['id']]);
        }

        $pdo->commit();
        return ['success' => true, 'order_id' => $order_id];

    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}


function searchProductsByName($query) {
    $db = getDBConnection();
    $query = "%$query%";
    $stmt = $db->prepare("SELECT * FROM products WHERE LOWER(name) LIKE LOWER(?)");
    $stmt->execute([$query]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function highlightSearchQuery($text, $query) {
    return preg_replace("/($query)/iu", '<span class="highlight">$1</span>', htmlspecialchars($text));
}

// Функции безопасности
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

?>

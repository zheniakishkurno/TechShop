<?php
// Подключаем конфигурацию и функции
require_once 'config.php';
require_once 'functions.php';

// Если пользователь уже авторизован, перенаправляем на главную страницу
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Расширенная валидация
    if (empty($first_name)) {
        $error = 'Пожалуйста, введите ваше имя';
    } elseif (strlen($first_name) < 2) {
        $error = 'Имя должно содержать минимум 2 символа';
    } elseif (empty($last_name)) {
        $error = 'Пожалуйста, введите вашу фамилию';
    } elseif (strlen($last_name) < 2) {
        $error = 'Фамилия должна содержать минимум 2 символа';
    } elseif (empty($email)) {
        $error = 'Пожалуйста, введите email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Пожалуйста, введите корректный email';
    } elseif (empty($phone)) {
        $error = 'Пожалуйста, введите номер телефона';
    } elseif (!preg_match('/^\+375\s?\(?(17|25|29|33|44)\)?\s?[0-9]{3}[-\s]?[0-9]{2}[-\s]?[0-9]{2}$/', trim($phone))) {
        $error = 'Пожалуйста, введите корректный белорусский номер телефона (+375)';
    } elseif (empty($password)) {
        $error = 'Пожалуйста, введите пароль';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } else {
        global $pdo;

        // Проверка существования email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Пользователь с таким email уже существует';
        } else {
            // Регистрация
            $hashed_password = hashPassword($password);
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            
             if ($stmt->execute([$first_name, $last_name, $email, $phone, $hashed_password])) {
                // Регистрация прошла успешно, перенаправляем на страницу входа
                $_SESSION['registration_success'] = 'Регистрация прошла успешно! Теперь вы можете войти.';
                header('Location: login.php');
                exit;
            }
            else {
                $error = 'Ошибка при регистрации. Пожалуйста, попробуйте позже.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />

    <title>Регистрация | TechShop</title>
    
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="auth-form">
        <h1>Регистрация</h1>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="first_name">Имя:</label>
                <input type="text" id="first_name" name="first_name" 
                       pattern=".{2,}" 
                       value="<?= htmlspecialchars($first_name ?? '') ?>"
                       required>
                <div class="error-message">Имя должно содержать минимум 2 символа</div>
            </div>

            <div class="form-group">
                <label for="last_name">Фамилия:</label>
                <input type="text" id="last_name" name="last_name" 
                       pattern=".{2,}" 
                       value="<?= htmlspecialchars($last_name ?? '') ?>"
                       required>
                <div class="error-message">Фамилия должна содержать минимум 2 символа</div>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?= htmlspecialchars($email ?? '') ?>"
                       required>
                <div class="error-message">Введите корректный email адрес</div>
            </div>

            <div class="form-group">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" 
                       pattern="\+375(17|25|29|33|44)[0-9]{7}"
                       value="<?= htmlspecialchars($phone ?? '') ?>"
                       required>
                <div class="error-message">Введите корректный белорусский номер телефона</div>
                <div class="input-hint">Формат: +375 (xx) xxx-xx-xx, где xx: 17, 25, 29, 33 или 44</div>
            </div>

            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" 
                       minlength="6"
                       required>
                <div class="input-hint">Минимум 6 символов</div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Подтвердите пароль:</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       minlength="6"
                       required>
                <div class="error-message">Пароли должны совпадать</div>
            </div>

            <button type="submit" class="btn">Зарегистрироваться</button>
        </form>

        <div class="auth-links">
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </div>
    </div>
</body>
</html>

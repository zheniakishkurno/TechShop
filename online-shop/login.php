<?php
require_once 'functions.php';
$current_user = getCurrentUser();

// Если пользователь уже авторизован, перенаправляем его на профиль
if ($current_user) {
    header('Location: profile.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Пожалуйста, заполните все поля';
    } elseif (login($email, $password)) {
        // Перенаправляем на предыдущую страницу или на главную
        $redirect_url = $_SESSION['redirect_url'] ?? 'index.php';
        unset($_SESSION['redirect_url']);
        header("Location: $redirect_url");
        exit;
    } else {
        $error = 'Неверный email или пароль';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | TechShop</title>
    <link rel="stylesheet" href="/online-shop/css/login.css">
</head>
<body>
    <div class="auth-form">
        <h1>Вход в систему</h1>
        
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST"> 
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
             
            <button type="submit" class="btn">Войти</button>
        </form>
        
        <div class="auth-links">
            <p>Нет аккаунта?<a href="register.php">Зарегистрироваться</a></p>
        </div>
    </div>
</body>
</html>
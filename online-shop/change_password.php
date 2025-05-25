<?php
require_once 'functions.php';

header('Content-Type: application/json');

$current_user = getCurrentUser();

if (!$current_user) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован']);
    exit;
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Пароли не совпадают']);
    exit;
}

global $pdo;

// Получаем хэш текущего пароля из БД
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$current_user['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($currentPassword, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Неверный текущий пароль']);
    exit;
}

// Обновляем пароль
$newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
$update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$update->execute([$newPasswordHash, $current_user['id']]);

echo json_encode(['success' => true, 'message' => 'Пароль успешно обновлён']);

<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    unset($_SESSION['cart'][$id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

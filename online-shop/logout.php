<?php
require_once 'functions.php';

if (isLoggedIn()) {
    logout();
}

header('Location: login.php');
exit;
?>
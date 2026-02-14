<?php
$host_name = 'sql308.infinityfree.com';
$db__name = 'if0_41128424_shoes_store';
$user_name = 'if0_41128424';
$passwor = 'Zikonarca700';

try {
    $pdo = new PDO("mysql:host=$host_name;dbname=$db__name;charset=utf8", $user_name, $passwor);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection Error: " . $e->getMessage());
}
?>
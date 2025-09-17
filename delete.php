<?php

require_once 'config.php';

if (!isset($pdo)) {
    die("Ошибка: Не удалось подключиться к базе данных");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM gifts WHERE id = :id");
        $stmt->execute([':id' => $id]);

        header('Location: index.php?message=deleted');
        exit;
    } catch (Exception $e) {
        die("Ошибка при удалении: " . $e->getMessage());
    }
}

header('Location: index.php');
<?php
require_once 'config.php';

// Проверяем что $pdo создан
if (!isset($pdo)) {
    die("Ошибка: Не удалось подключиться к базе данных");
}

// Обрабатываем как GET, так и POST запросы
if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') && isset($_REQUEST['id']) && isset($_REQUEST['status'])) {
    $id = (int)$_REQUEST['id'];
    $status = $_REQUEST['status'] === 'отправлен' ? 'отправлен' : 'не отправлен';

    try {
        // Обновляем статус в БД
        $stmt = $pdo->prepare("UPDATE gifts SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);

        // Редирект с сообщением об успехе
        header('Location: index.php?message=status_updated&id=' . $id . '&status=' . $status);
        exit;

    } catch (Exception $e) {
        // Редирект с сообщением об ошибке
        header('Location: index.php?error=status_update_failed&id=' . $id);
        exit;
    }
}

// Если нет необходимых параметров - редирект на главную
header('Location: index.php');
exit;
?>
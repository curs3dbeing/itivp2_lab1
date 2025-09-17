<?php
require_once 'config.php';


if (!isset($pdo)) {
    die("Ошибка: Не удалось подключиться к базе данных");
}


if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') && isset($_REQUEST['id']) && isset($_REQUEST['status'])) {
    $id = (int)$_REQUEST['id'];
    $status = $_REQUEST['status'] === 'отправлен' ? 'отправлен' : 'не отправлен';

    try {

        $stmt = $pdo->prepare("UPDATE gifts SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);


        header('Location: index.php?message=status_updated&id=' . $id . '&status=' . $status);
        exit;

    } catch (Exception $e) {

        header('Location: index.php?error=status_update_failed&id=' . $id);
        exit;
    }
}


header('Location: index.php');
exit;
?>
<?php
require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $for_whom = $_POST['for_whom'] ?? '';
    $budget = $_POST['budget'] ?? 0;
    $status = $_POST['status'] ?? 'не отправлен';

    try {

        if (empty($for_whom)) {
            throw new Exception("Поле 'Для кого' обязательно для заполнения");
        }

        if (!is_numeric($budget) || $budget < 0) {
            throw new Exception("Бюджет должен быть положительным числом");
        }

        $stmt = $pdo->prepare("
            INSERT INTO gifts (for_whom, budget, status) 
            VALUES (:for_whom, :budget, :status)
        ");

        $stmt->execute([
            ':for_whom' => $for_whom,
            ':budget' => $budget,
            ':status' => $status
        ]);

        $success = "Подарок успешно добавлен!";

        $_POST = [];

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить подарок - Gift Manager</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
            padding: 10px 15px;
            border: 1px solid #007bff;
            border-radius: 5px;
        }
        .back-link:hover {
            background-color: #007bff;
            color: white;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Добавить новый подарок</h1>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="for_whom">Для кого *</label>
            <input type="text" id="for_whom" name="for_whom"
                   value="<?= htmlspecialchars($_POST['for_whom'] ?? '') ?>"
                   required placeholder="Имя получателя">
        </div>

        <div class="form-group">
            <label for="budget">Бюджет (руб)</label>
            <input type="number" id="budget" name="budget"
                   value="<?= htmlspecialchars($_POST['budget'] ?? '') ?>"
                   step="0.01" min="0" placeholder="0.00">
        </div>

        <div class="form-group">
            <label for="status">Статус</label>
            <select id="status" name="status">
                <option value="не отправлен" <?= ($_POST['status'] ?? 'не отправлен') === 'не отправлен' ? 'selected' : '' ?>>Не отправлен</option>
                <option value="отправлен" <?= ($_POST['status'] ?? '') === 'отправлен' ? 'selected' : '' ?>>Отправлен</option>
            </select>
        </div>

        <button type="submit"> Добавить подарок</button>
    </form>

    <div class="actions">
        <a href="index.php" class="back-link">← Назад к списку</a>
        <a href="view.php" class="back-link">Посмотреть все подарки</a>
    </div>
</div>

<script>
    // Простая валидация на клиенте
    document.querySelector('form').addEventListener('submit', function(e) {
        const forWhom = document.getElementById('for_whom').value.trim();
        const budget = document.getElementById('budget').value;

        if (!forWhom) {
            alert('Поле "Для кого" обязательно для заполнения');
            e.preventDefault();
            return;
        }

        if (budget && (isNaN(budget) || parseFloat(budget) < 0)) {
            alert('Бюджет должен быть положительным числом');
            e.preventDefault();
        }
    });
</script>
</body>
</html>
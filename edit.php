<?php
require_once 'config.php';

if (!isset($pdo)) {
    die("Ошибка: Не удалось подключиться к базе данных");
}

// Проверяем наличие ID в запросе
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM gifts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $gift = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gift) {
        throw new Exception("Подарок с ID $id не найден");
    }

} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $for_whom = $_POST['for_whom'] ?? '';
    $budget = $_POST['budget'] ?? 0;
    $status = $_POST['status'] ?? 'не отправлен';

    try {
        // Валидация данных
        if (empty($for_whom)) {
            throw new Exception("Поле 'Для кого' обязательно для заполнения");
        }

        if (!is_numeric($budget) || $budget < 0) {
            throw new Exception("Бюджет должен быть положительным числом");
        }

        // Обновляем запись в БД
        $stmt = $pdo->prepare("
            UPDATE gifts 
            SET for_whom = :for_whom, budget = :budget, status = :status 
            WHERE id = :id
        ");

        $stmt->execute([
            ':for_whom' => $for_whom,
            ':budget' => $budget,
            ':status' => $status,
            ':id' => $id
        ]);

        $success = "Подарок успешно обновлен!";


        $gift['for_whom'] = $for_whom;
        $gift['budget'] = $budget;
        $gift['status'] = $status;

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
    <title>Редактировать подарок - Gift Manager</title>
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
            background-color: #2196F3;
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
            background-color: #1976D2;
        }
        .delete-btn {
            background-color: #f44336;
            margin-top: 10px;
        }
        .delete-btn:hover {
            background-color: #d32f2f;
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
        .info-row {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
    </style>
</head>
<body>
<div class="container">
    <h1> Редактировать подарок</h1>

    <!-- Информация о записи -->
    <div class="info-row">
        <div><span class="info-label">ID:</span> <?= $gift['id'] ?></div>
        <div><span class="info-label">Создан:</span> <?= date('d.m.Y H:i', strtotime($gift['created_at'])) ?></div>
    </div>

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
                   value="<?= htmlspecialchars($gift['for_whom']) ?>"
                   required placeholder="Имя получателя">
        </div>

        <div class="form-group">
            <label for="budget">Бюджет (руб)</label>
            <input type="number" id="budget" name="budget"
                   value="<?= htmlspecialchars($gift['budget']) ?>"
                   step="0.01" min="0" placeholder="0.00">
        </div>

        <div class="form-group">
            <label for="status">Статус</label>
            <select id="status" name="status">
                <option value="не отправлен" <?= $gift['status'] === 'не отправлен' ? 'selected' : '' ?>>Не отправлен</option>
                <option value="отправлен" <?= $gift['status'] === 'отправлен' ? 'selected' : '' ?>>Отправлен</option>
            </select>
        </div>

        <button type="submit"> Сохранить изменения</button>
    </form>

    <!-- Форма для удаления -->
    <form method="POST" action="delete.php" onsubmit="return confirm('Вы уверены, что хотите удалить этот подарок?')">
        <input type="hidden" name="id" value="<?= $gift['id'] ?>">
        <button type="submit" class="delete-btn"> Удалить подарок</button>
    </form>

    <div class="actions">
        <a href="index.php" class="back-link">← Назад к списку</a>
        <a href="view.php" class="back-link"> Посмотреть все подарки</a>
    </div>
</div>

<script>

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
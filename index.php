<?php
require_once 'config.php';

if (!isset($pdo)) {
    die("Ошибка: Не удалось подключиться к базе данных");
}

// Обработка сообщений
$message = '';
$message_type = '';

if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'deleted':
            $message = 'Подарок успешно удален!';
            $message_type = 'success';
            break;
        case 'status_updated':
            $message = 'Статус подарка обновлен!';
            $message_type = 'success';
            break;
        case 'added':
            $message = 'Подарок успешно добавлен!';
            $message_type = 'success';
            break;
        case 'updated':
            $message = 'Подарок успешно обновлен!';
            $message_type = 'success';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'delete_failed':
            $message = 'Ошибка при удалении подарка!';
            $message_type = 'error';
            break;
        case 'status_update_failed':
            $message = 'Ошибка при обновлении статуса!';
            $message_type = 'error';
            break;
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM gifts ORDER BY created_at DESC");
    $gifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Ошибка при получении данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gift Manager - Управление подарками</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-sent {
            background: #d4edda;
            color: #155724;
        }

        .status-not-sent {
            background: #fff3cd;
            color: #856404;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 8px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        .btn-edit {
            background: #ffc107;
            color: #000;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-status {
            background: #28a745;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .budget {
            font-weight: 600;
            color: #2c5282;
        }

        @media (max-width: 768px) {
            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                justify-content: center;
            }

            .action-buttons {
                flex-direction: column;
            }

            th, td {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🎁 Gift Manager</h1>
        <p>Управление вашими подарками и бюджетом</p>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="add.php" class="btn btn-primary">
                ➕ Добавить новый подарок
            </a>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?= count($gifts) ?></div>
                    <div class="stat-label">Всего подарков</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number">
                        <?= count(array_filter($gifts, fn($g) => $g['status'] === 'отправлен')) ?>
                    </div>
                    <div class="stat-label">Отправлено</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number">
                        ₽<?= number_format(array_sum(array_column($gifts, 'budget')), 2) ?>
                    </div>
                    <div class="stat-label">Общий бюджет</div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <?php if (empty($gifts)): ?>
                <div class="empty-state">
                    <div>🎁</div>
                    <h3>Подарков пока нет</h3>
                    <p>Добавьте первый подарок, чтобы начать работу</p>
                    <a href="add.php" class="btn btn-primary" style="margin-top: 20px;">
                        ➕ Добавить подарок
                    </a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Для кого</th>
                        <th>Бюджет</th>
                        <th>Статус</th>
                        <th>Дата создания</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($gifts as $gift): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($gift['for_whom']) ?></strong></td>
                            <td class="budget">₽<?= number_format($gift['budget'], 2) ?></td>
                            <td>
                                        <span class="status status-<?= $gift['status'] === 'отправлен' ? 'sent' : 'not-sent' ?>">
                                            <?= $gift['status'] ?>
                                        </span>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($gift['created_at'])) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?= $gift['id'] ?>" class="btn btn-sm btn-edit">
                                         Редактировать
                                    </a>

                                    <form action="delete.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= $gift['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-delete"
                                                onclick="return confirm('Удалить подарок для <?= htmlspecialchars($gift['for_whom']) ?>?')">
                                             Удалить
                                        </button>
                                    </form>

                                    <?php if ($gift['status'] === 'не отправлен'): ?>
                                        <form action="update_status.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $gift['id'] ?>">
                                            <input type="hidden" name="status" value="отправлен">
                                            <button type="submit" class="btn btn-sm btn-status">
                                                 Отправить
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="update_status.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $gift['id'] ?>">
                                            <input type="hidden" name="status" value="не отправлен">
                                            <button type="submit" class="btn btn-sm" style="background: #6c757d; color: white;">
                                                 Вернуть
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Автоматическое скрытие сообщений через 5 секунд
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
</script>
</body>
</html>
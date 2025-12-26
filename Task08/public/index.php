<?php
require_once 'db.php';

$pdo = getPDO();
$stmt = $pdo->query("SELECT * FROM doctors ORDER BY last_name, first_name");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клиника - Список врачей</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Список врачей клиники</h1>
    
    <?php if (empty($doctors)): ?>
        <p>Врачи не найдены.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Фамилия</th>
                    <th>Имя</th>
                    <th>Отчество</th>
                    <th>Специализация</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $doctor): ?>
                <tr>
                    <td><?= htmlspecialchars($doctor['last_name']) ?></td>
                    <td><?= htmlspecialchars($doctor['first_name']) ?></td>
                    <td><?= htmlspecialchars($doctor['middle_name']) ?></td>
                    <td><?= htmlspecialchars($doctor['specialization']) ?></td>
                    <td><?= htmlspecialchars($doctor['phone']) ?></td>
                    <td><?= htmlspecialchars($doctor['email']) ?></td>
                    <td class="actions">
                        <a href="edit.php?id=<?= $doctor['id'] ?>">Редактировать</a>
                        <a href="delete.php?id=<?= $doctor['id'] ?>">Удалить</a>
                        <a href="schedule.php?doctor_id=<?= $doctor['id'] ?>">График</a>
                        <a href="services.php?doctor_id=<?= $doctor['id'] ?>">Услуги</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <button onclick="location.href='add.php'">Добавить врача</button>
</body>
</html>
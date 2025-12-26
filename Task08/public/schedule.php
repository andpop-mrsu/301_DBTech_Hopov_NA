<?php
require_once 'db.php';

$pdo = getPDO();
$doctor_id = $_GET['doctor_id'] ?? null;

if (!$doctor_id) {
    header('Location: index.php');
    exit;
}

// Получаем данные врача
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    header('Location: index.php');
    exit;
}

// Обработка CRUD для графика
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO schedules (doctor_id, day_of_week, start_time, end_time, office) 
                                     VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $doctor_id,
                    $_POST['day_of_week'],
                    $_POST['start_time'],
                    $_POST['end_time'],
                    $_POST['office']
                ]);
                break;
                
            case 'edit':
                $stmt = $pdo->prepare("UPDATE schedules SET day_of_week = ?, start_time = ?, end_time = ?, office = ? 
                                     WHERE id = ?");
                $stmt->execute([
                    $_POST['day_of_week'],
                    $_POST['start_time'],
                    $_POST['end_time'],
                    $_POST['office'],
                    $_POST['schedule_id']
                ]);
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
                $stmt->execute([$_POST['schedule_id']]);
                break;
        }
    }
    header("Location: schedule.php?doctor_id=$doctor_id");
    exit;
}

// Получаем график врача
$stmt = $pdo->prepare("SELECT * FROM schedules WHERE doctor_id = ? ORDER BY day_of_week, start_time");
$stmt->execute([$doctor_id]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

$days = [
    1 => 'Понедельник',
    2 => 'Вторник',
    3 => 'Среда',
    4 => 'Четверг',
    5 => 'Пятница',
    6 => 'Суббота',
    7 => 'Воскресенье'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>График работы врача</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>График работы врача</h1>
    
    <div class="doctor-info">
        <h2><?= htmlspecialchars($doctor['last_name']) ?> <?= htmlspecialchars($doctor['first_name']) ?> <?= htmlspecialchars($doctor['middle_name']) ?></h2>
        <p><strong>Специализация:</strong> <?= htmlspecialchars($doctor['specialization']) ?></p>
    </div>
    
    <button onclick="location.href='index.php'" class="back-btn">← Назад к списку врачей</button>
    
    <h3>Текущий график:</h3>
    <?php if (empty($schedules)): ?>
        <p>График работы не установлен.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>День недели</th>
                    <th>Начало работы</th>
                    <th>Окончание работы</th>
                    <th>Кабинет</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $schedule): ?>
                <tr>
                    <td><?= $days[$schedule['day_of_week']] ?></td>
                    <td><?= htmlspecialchars($schedule['start_time']) ?></td>
                    <td><?= htmlspecialchars($schedule['end_time']) ?></td>
                    <td><?= htmlspecialchars($schedule['office']) ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">
                            <button type="submit" onclick="return confirm('Удалить запись графика?')">Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <div class="add-form">
        <h3>Добавить время работы:</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="day_of_week">День недели:</label>
                <select name="day_of_week" id="day_of_week" required>
                    <?php foreach ($days as $key => $day): ?>
                        <option value="<?= $key ?>"><?= $day ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="start_time">Начало работы:</label>
                <input type="time" name="start_time" id="start_time" required>
            </div>
            
            <div class="form-group">
                <label for="end_time">Окончание работы:</label>
                <input type="time" name="end_time" id="end_time" required>
            </div>
            
            <div class="form-group">
                <label for="office">Кабинет:</label>
                <input type="text" name="office" id="office">
            </div>
            
            <button type="submit">Добавить в график</button>
        </form>
    </div>
</body>
</html>
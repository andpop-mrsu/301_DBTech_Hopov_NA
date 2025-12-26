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

// Обработка CRUD для услуг
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO services (doctor_id, service_name, service_date, price, patient_name) 
                                     VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $doctor_id,
                    $_POST['service_name'],
                    $_POST['service_date'],
                    $_POST['price'],
                    $_POST['patient_name']
                ]);
                break;
                
            case 'edit':
                $stmt = $pdo->prepare("UPDATE services SET service_name = ?, service_date = ?, price = ?, patient_name = ? 
                                     WHERE id = ?");
                $stmt->execute([
                    $_POST['service_name'],
                    $_POST['service_date'],
                    $_POST['price'],
                    $_POST['patient_name'],
                    $_POST['service_id']
                ]);
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
                $stmt->execute([$_POST['service_id']]);
                break;
        }
    }
    header("Location: services.php?doctor_id=$doctor_id");
    exit;
}

// Получаем услуги врача
$stmt = $pdo->prepare("SELECT * FROM services WHERE doctor_id = ? ORDER BY service_date DESC");
$stmt->execute([$doctor_id]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Сумма услуг
$total = 0;
foreach ($services as $service) {
    $total += $service['price'];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оказанные услуги</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Оказанные услуги врача</h1>
    
    <div class="doctor-info">
        <h2><?= htmlspecialchars($doctor['last_name']) ?> <?= htmlspecialchars($doctor['first_name']) ?> <?= htmlspecialchars($doctor['middle_name']) ?></h2>
        <p><strong>Специализация:</strong> <?= htmlspecialchars($doctor['specialization']) ?></p>
    </div>
    
    <button onclick="location.href='index.php'" class="back-btn">← Назад к списку врачей</button>
    
    <h3>Список оказанных услуг:</h3>
    <?php if (empty($services)): ?>
        <p>Нет данных об оказанных услугах.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Наименование услуги</th>
                    <th>Пациент</th>
                    <th>Стоимость</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= htmlspecialchars($service['service_date']) ?></td>
                    <td><?= htmlspecialchars($service['service_name']) ?></td>
                    <td><?= htmlspecialchars($service['patient_name']) ?></td>
                    <td><?= number_format($service['price'], 2) ?> руб.</td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                            <button type="submit" onclick="return confirm('Удалить запись об услуге?')">Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="total">
            Общая сумма оказанных услуг: <?= number_format($total, 2) ?> руб.
        </div>
    <?php endif; ?>
    
    <div class="add-form">
        <h3>Добавить оказанную услугу:</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="service_date">Дата оказания услуги:</label>
                <input type="date" name="service_date" id="service_date" required value="<?= date('Y-m-d') ?>">
            </div>
            
            <div class="form-group">
                <label for="service_name">Наименование услуги:</label>
                <input type="text" name="service_name" id="service_name" required>
            </div>
            
            <div class="form-group">
                <label for="patient_name">ФИО пациента:</label>
                <input type="text" name="patient_name" id="patient_name" required>
            </div>
            
            <div class="form-group">
                <label for="price">Стоимость (руб.):</label>
                <input type="number" name="price" id="price" step="0.01" min="0" required>
            </div>
            
            <button type="submit">Добавить услугу</button>
        </form>
    </div>
</body>
</html>
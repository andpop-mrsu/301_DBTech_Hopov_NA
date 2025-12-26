<?php
require_once 'db.php';

$pdo = getPDO();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE doctors SET last_name = ?, first_name = ?, middle_name = ?, 
                           specialization = ?, phone = ?, email = ? WHERE id = ?");
    
    $stmt->execute([
        $_POST['last_name'],
        $_POST['first_name'],
        $_POST['middle_name'],
        $_POST['specialization'],
        $_POST['phone'],
        $_POST['email'],
        $id
    ]);
    
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать врача</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Редактировать врача</h1>
    
    <div class="form-container">
        <form method="POST">
            <div class="form-group">
                <label for="last_name">Фамилия *</label>
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($doctor['last_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="first_name">Имя *</label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($doctor['first_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="middle_name">Отчество</label>
                <input type="text" id="middle_name" name="middle_name" value="<?= htmlspecialchars($doctor['middle_name']) ?>">
            </div>
            
            <div class="form-group">
                <label for="specialization">Специализация *</label>
                <input type="text" id="specialization" name="specialization" value="<?= htmlspecialchars($doctor['specialization']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($doctor['phone']) ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($doctor['email']) ?>">
            </div>
            
            <button type="submit">Сохранить изменения</button>
            <button type="button" onclick="location.href='index.php'">Отмена</button>
        </form>
    </div>
</body>
</html>
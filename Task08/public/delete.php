<?php
require_once 'db.php';

$pdo = getPDO();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->execute([$id]);
    }
    
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
    <title>Удалить врача</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Удаление врача</h1>
    
    <div class="confirmation">
        <p>Вы уверены, что хотите удалить этого врача?</p>
        
        <div class="doctor-info">
            <p><strong>Врач:</strong> <?= htmlspecialchars($doctor['last_name']) ?> <?= htmlspecialchars($doctor['first_name']) ?> <?= htmlspecialchars($doctor['middle_name']) ?></p>
            <p><strong>Специализация:</strong> <?= htmlspecialchars($doctor['specialization']) ?></p>
        </div>
        
        <form method="POST">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit">Да, удалить</button>
            <button type="button" onclick="location.href='index.php'">Нет, отмена</button>
        </form>
    </div>
</body>
</html>
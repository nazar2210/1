<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Логирование в файл
file_put_contents('error_log.txt', date('Y-m-d H:i:s')." - Начало обработки\n", FILE_APPEND);

// Получаем данные
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

// Проверка обязательных полей
$required = ['name', 'phone', 'service'];
$missing = [];
foreach ($required as $field) {
    if (empty($data[$field])) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    $error = 'Не заполнены поля: ' . implode(', ', $missing);
    file_put_contents('error_log.txt', $error."\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $error]);
    exit;
}

// Формируем сообщение для Telegram
$message = "📌 Новая запись в Ногтевой рай:\n";
$message .= "👤 Имя: " . htmlspecialchars($data['name']) . "\n";
$message .= "📞 Телефон: " . htmlspecialchars($data['phone']) . "\n";
$message .= "💅 Услуга: " . htmlspecialchars($data['service']) . "\n";
if (!empty($data['date'])) $message .= "📅 Дата: " . $data['date'] . "\n";
if (!empty($data['time'])) $message .= "⏰ Время: " . $data['time'] . "\n";
if (!empty($data['notes'])) $message .= "💬 Пожелания: " . htmlspecialchars($data['notes']) . "\n";

// Настройки бота
$botToken = 'ВАШ_ТОКЕН'; // Замените на реальный
$chatId = 'ВАШ_CHAT_ID'; // Замените на реальный
$url = "https://api.telegram.org/bot$botToken/sendMessage";

// Отправка через cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Логирование ответа
file_put_contents('error_log.txt', "HTTP код: $httpCode\nОтвет: $response\n", FILE_APPEND);

if ($httpCode == 200 && json_decode($response)->ok) {
    echo json_encode(['success' => true, 'message' => '✅ Запись успешно отправлена!']);
} else {
    $error = 'Ошибка Telegram API: ' . ($response ?: curl_error($ch));
    file_put_contents('error_log.txt', $error."\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => '❌ ' . $error]);
}
?>

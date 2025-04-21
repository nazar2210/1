<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain'); // Упростим вывод

// Получаем данные из формы
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$service = $_POST['service'] ?? '';
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$notes = $_POST['notes'] ?? '';

// Проверяем обязательные поля
if (empty($name) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Заполните имя и телефон']);
    exit;
}

// Формируем сообщение для Telegram
$message = "🎉 *Новая запись!*\n\n";
$message .= "👤 *Имя:* $name\n";
$message .= "📞 *Телефон:* `$phone`\n";
if (!empty($email)) $message .= "📧 *Email:* $email\n";
if (!empty($service)) $message .= "💅 *Услуга:* $service\n";
if (!empty($date)) $message .= "📅 *Дата:* $date\n";
if (!empty($time)) $message .= "⏰ *Время:* $time\n";
if (!empty($notes)) $message .= "✏️ *Пожелания:* $notes";


$botToken = '7896695898:AAHOe0mIRVNweYoRSQ9z9E_si0y4YnIp9mA';
$chatId = '5421268585';
$url = "https://api.telegram.org/bot$botToken/sendMessage";
$url = "https://api.telegram.org/bot$botToken/sendMessage";
$data = [
    'chat_id' => $chatId,
    'text' => '✅ Бот работает! ' . date('Y-m-d H:i:s')
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

header('Content-Type: application/json');
echo json_encode([
    'status' => $result ? 'success' : 'error',
    'response' => $result ? json_decode($result, true) : error_get_last()
]);

// Отправляем данные в Telegram
$data = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'Markdown',
    'disable_web_page_preview' => true
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Запись отправлена! Мы скоро свяжемся.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка. Попробуйте позже или позвоните нам.']);
}
?>

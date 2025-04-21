<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Логирование
file_put_contents('form_log.txt', date('Y-m-d H:i:s')." - Старт\n", FILE_APPEND);

// Получаем данные
$input = file_get_contents('php://input');
$data = json_decode($input, true) ?: $_POST;
file_put_contents('form_log.txt', print_r($data, true), FILE_APPEND);

// Проверка данных
if (empty($data['name']) || empty($data['phone'])) {
    $error = 'Заполните имя и телефон';
    file_put_contents('form_log.txt', $error."\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $error]);
    exit;
}

// Настройки бота
$botToken = 'ВАШ_ТОКЕН'; // Например: '1234567890:ABCdefGHIJKlmNoPQRSTuVWXYZ'
$chatId = 'ВАШ_CHAT_ID'; // Например: '584902187'
$message = "📌 Новая запись:\n👤 Имя: {$data['name']}\n📞 Телефон: {$data['phone']}";

// Отправка в Telegram
$url = "https://api.telegram.org/bot$botToken/sendMessage";
$postData = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML'
];

// Используем cURL вместо file_get_contents
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Только для теста!

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Логируем ответ
file_put_contents('form_log.txt', "Response ($httpCode): $response\n", FILE_APPEND);

if ($httpCode == 200 && json_decode($response)->ok) {
    echo json_encode(['success' => true, 'message' => 'Запись успешно отправлена!']);
} else {
    $error = 'Ошибка отправки: '.$response;
    file_put_contents('form_log.txt', $error."\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $error]);
}
?>

<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Настройки бота (ЗАМЕНИТЕ НА РЕАЛЬНЫЕ ДАННЫЕ)
$botToken = '7896695898:AAHOe0mIRVNweYoRSQ9z9E_si0y4YnIp9mA'; 
$chatId = '5421268585'; 

// Логирование
file_put_contents('form_log.txt', "\n\n".date('Y-m-d H:i:s')." - Новый запрос\n", FILE_APPEND);

// Получаем данные
$input = file_get_contents('php://input');
$data = json_decode($input, true) ?: $_POST;
file_put_contents('form_log.txt', "Данные формы:\n".print_r($data, true), FILE_APPEND);

// Проверка обязательных полей
if (empty($data['name']) || empty($data['phone'])) {
    $error = 'Не заполнены обязательные поля: имя и телефон';
    file_put_contents('form_log.txt', "Ошибка: $error\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => '❌ '.$error
    ]);
    exit;
}

// Формируем сообщение для Telegram
$message = "🎀 *Новая запись в Ногтевой рай* 🎀\n\n";
$message .= "👩 *Имя:* ".htmlspecialchars($data['name'])."\n";
$message .= "📱 *Телефон:* `".htmlspecialchars($data['phone'])."`\n";
if (!empty($data['email'])) $message .= "📧 *Email:* ".htmlspecialchars($data['email'])."\n";
if (!empty($data['service'])) $message .= "💅 *Услуга:* ".htmlspecialchars($data['service'])."\n";
if (!empty($data['date'])) $message .= "📅 *Дата:* ".$data['date']."\n";
if (!empty($data['time'])) $message .= "⏰ *Время:* ".$data['time']."\n";
if (!empty($data['notes'])) $message .= "✏️ *Пожелания:* ".htmlspecialchars($data['notes'])."\n";

// Отправка в Telegram через cURL
$url = "https://api.telegram.org/bot$botToken/sendMessage";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Логирование ответа
file_put_contents('form_log.txt', "Ответ Telegram (код $httpCode):\n$response\n", FILE_APPEND);

// Формируем ответ
if ($httpCode == 200 && json_decode($response)->ok) {
    echo json_encode([
        'success' => true,
        'message' => '✅ Запись успешно отправлена! Мы свяжемся с вами для подтверждения.'
    ]);
} else {
    $error = 'Ошибка при отправке в Telegram';
    file_put_contents('form_log.txt', "Ошибка: $error\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => '❌ '.$error.' Позвоните нам: +7 (999) 141-70-17'
    ]);
}
?>

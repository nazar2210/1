<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Включить логирование ошибок
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/php_errors.log');

// Логирование входящего запроса
file_put_contents('form_log.txt', "\n\n".date('Y-m-d H:i:s')." - Новый запрос\n", FILE_APPEND);

// Получаем данные
$data = $_POST;
file_put_contents('form_log.txt', "Данные формы:\n".print_r($data, true)."\n", FILE_APPEND);

// Проверка обязательных полей
$required = ['name', 'phone', 'service'];
$missing = [];
foreach ($required as $field) {
    if (empty($data[$field])) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    $error = 'Не заполнены обязательные поля: '.implode(', ', $missing);
    file_put_contents('form_log.txt', "Ошибка: $error\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => $error
    ]);
    exit;
}

// Формируем сообщение для Telegram
$message = "📌 *Новая запись в Ногтевой рай* \n\n";
$message .= "👤 *Имя:* ".htmlspecialchars($data['name'])."\n";
$message .= "📞 *Телефон:* `".htmlspecialchars($data['phone'])."`\n";
$message .= "💅 *Услуга:* ".htmlspecialchars($data['service'])."\n";

if (!empty($data['date'])) $message .= "📅 *Дата:* ".$data['date']."\n";
if (!empty($data['time'])) $message .= "⏰ *Время:* ".$data['time']."\n";
if (!empty($data['notes'])) $message .= "💬 *Пожелания:* ".htmlspecialchars($data['notes'])."\n";

// Настройки Telegram бота
$botToken = 'ВАШ_ТОКЕН'; // Замените на реальный токен
$chatId = 'ВАШ_CHAT_ID'; // Замените на реальный chat_id
$telegramUrl = "https://api.telegram.org/bot$botToken/sendMessage";

// Подготовка данных для отправки
$postData = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'Markdown',
    'disable_web_page_preview' => true
];

// Настройка cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $telegramUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ["Content-Type: multipart/form-data"],
    CURLOPT_SSL_VERIFYPEER => false // Только для тестирования!
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Логирование ответа
file_put_contents('form_log.txt', "Ответ Telegram (код $httpCode):\n$response\n", FILE_APPEND);

// Проверка результата
if ($httpCode == 200) {
    $responseData = json_decode($response, true);
    if ($responseData && $responseData['ok']) {
        echo json_encode([
            'success' => true,
            'message' => '✅ Ваша запись успешно отправлена! Мы свяжемся с вами в ближайшее время.'
        ]);
    } else {
        $error = 'Ошибка Telegram API: '.($responseData['description'] ?? 'Неизвестная ошибка');
        file_put_contents('form_log.txt', "Ошибка API: $error\n", FILE_APPEND);
        echo json_encode([
            'success' => false,
            'message' => '❌ Ошибка отправки. Пожалуйста, позвоните нам: +7 (999) 141-70-17'
        ]);
    }
} else {
    $error = "HTTP код: $httpCode, Ошибка cURL: $curlError";
    file_put_contents('form_log.txt', "Ошибка соединения: $error\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => '❌ Проблемы с соединением. Пожалуйста, попробуйте позже или позвоните нам.'
    ]);
}
?>

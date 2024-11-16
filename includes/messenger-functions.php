<?php

function send_telegram_auth_code($user_id = null) {
    $bot_token = get_option('messenger_bot_token'); // Получаем токен из настроек
    $chat_id = get_option('messenger_chat_id'); // Получаем chat_id из настроек админки

    if (!$bot_token) {
        error_log('Ошибка: токен бота не установлен.');
        return false;
    }

    if (!$chat_id) {
        error_log("Ошибка: chat_id не установлен в настройках.");
        return false;
    }

    // Генерируем случайный код для авторизации
    $auth_code = rand(100000, 999999);
    
    if ($user_id) {
        // Сохраняем код и временную метку в мета-поле пользователя
        update_user_meta($user_id, 'telegram_auth_code', $auth_code);
        update_user_meta($user_id, 'telegram_auth_code_timestamp', time());
		error_log("Сохраненный код: {$auth_code} для пользователя ID: {$user_id} с временной меткой " . time());
		error_log("Проверка \$user_id: " . print_r($user_id, true));
    } else {
        // Если пользователь не авторизован, сохраняем код и временную метку в опциях
        update_option('telegram_auth_code_' . $chat_id, $auth_code);
        update_option('telegram_auth_code_timestamp_' . $chat_id, time());
		error_log("Сохраненный код: {$auth_code} для пользователя ID: {$user_id} с временной меткой " . time());
		error_log("Проверка \$user_id: " . print_r($user_id, true));
    }


    // Сообщение с кодом авторизации
    $message = "Ваш код для авторизации на сайте: {$auth_code}";

    // URL для отправки сообщения через API Telegram
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $args = array(
        'body' => array(
            'chat_id' => $chat_id,
            'text'    => $message,
        ),
    );

    // Отправка запроса к API Telegram
    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        error_log('Ошибка отправки сообщения в Telegram: ' . $response->get_error_message());
        return false;
    }

    return true;
}

// Функция для проверки кода авторизации
function verify_telegram_auth_code($user_id, $input_code, $chat_id) {
    $expiration_time = 300; // Время действия кода в секундах (5 минут)
    $current_time = time();

    if ($user_id) {
        // Проверка для авторизованных пользователей
        $saved_code = get_user_meta($user_id, 'telegram_auth_code', true);
        $saved_time = get_user_meta($user_id, 'telegram_auth_code_timestamp', true);
		error_log("Проверка кода: введенный код {$input_code}, сохраненный код {$saved_code}, временная метка {$saved_time}, текущее время " . time());
		error_log("Проверка \$user_id: " . print_r($user_id, true));

        // Проверяем, что код существует, совпадает и не истек
        if ($saved_code && $input_code == $saved_code && ($current_time - $saved_time) <= $expiration_time) {
            // Успешная авторизация
            error_log("Проверка кода: введенный код {$input_code}, сохраненный код {$saved_code}, временная метка {$saved_time}, текущее время " . time());
			error_log("Проверка \$user_id: " . print_r($user_id, true));
            delete_user_meta($user_id, 'telegram_auth_code');
            delete_user_meta($user_id, 'telegram_auth_code_timestamp');
            wp_set_auth_cookie($user_id, true);
            return true;
        }
    } else {
        // Проверка для неавторизованных пользователей (по chat_id)
        $saved_code = get_option('telegram_auth_code_' . $chat_id);
        $saved_time = get_option('telegram_auth_code_timestamp_' . $chat_id);

        if ($saved_code && $input_code == $saved_code && ($current_time - $saved_time) <= $expiration_time) {
			error_log("Проверка кода: введенный код {$input_code}, сохраненный код {$saved_code}, временная метка {$saved_time}, текущее время " . time());
			error_log("Проверка \$user_id: " . print_r($user_id, true));
            // Успешная авторизация
            delete_option('telegram_auth_code_' . $chat_id);
            delete_option('telegram_auth_code_timestamp_' . $chat_id);
            // Авторизация через куки или перенаправление на вход для дальнейшей авторизации
            return true;
        }
    }

    return false; // Ошибка авторизации: неверный код или истек срок действия
}



// AJAX обработчик для отправки кода в Telegram
add_action('wp_ajax_start_telegram_auth', 'start_telegram_auth_handler');
add_action('wp_ajax_nopriv_start_telegram_auth', 'start_telegram_auth_handler'); // Делаем обработчик доступным для неавторизованных пользователей

function start_telegram_auth_handler() {
    $user_id = get_current_user_id();

    if (!$user_id) {
        // Если пользователь не авторизован, передаем null, чтобы использовать chat_id из настроек
        $sent = send_telegram_auth_code();
    } else {
        // Если пользователь авторизован, передаем user_id
        $sent = send_telegram_auth_code($user_id);
    }

    if ($sent) {
        wp_send_json_success();
    } else {
        wp_send_json_error(['message' => 'Ошибка: не удалось отправить код авторизации. Проверьте, что chat_id пользователя установлен и токен бота корректен.']);
    }
}

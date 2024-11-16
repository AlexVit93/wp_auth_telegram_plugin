<?php
// Функция для проверки кода и создания авторизационной сессии
function authorize_user_via_code() {
    if (isset($_POST['auth_code']) && !is_user_logged_in()) {
        $input_code = sanitize_text_field($_POST['auth_code']);
        $chat_id = get_option('messenger_chat_id'); // Получаем chat_id из настроек

        if (!$chat_id) {
            echo '<p>Ошибка: chat_id не установлен.</p>';
            return;
        }

        // Пытаемся найти пользователя по мета-полю chat_id
        $user_query = new WP_User_Query(array(
            'meta_key' => 'telegram_chat_id',
            'meta_value' => $chat_id,
            'number' => 1
        ));

        $users = $user_query->get_results();

        // Сохраняем код для пользователя сразу после его создания
        if (empty($users)) {
            // Создаём нового пользователя
            $user_id = wp_create_user("telegram_user_{$chat_id}", wp_generate_password(), "user{$chat_id}@example.com");

            if (is_wp_error($user_id)) {
                echo '<p>Ошибка при создании пользователя.</p>';
                return;
            }

            // Привязываем новый chat_id и код авторизации к созданному пользователю
            update_user_meta($user_id, 'telegram_chat_id', $chat_id);
            update_user_meta($user_id, 'telegram_auth_code', $input_code);
            update_user_meta($user_id, 'telegram_auth_code_timestamp', time()); // Сохраняем временную метку
            error_log("Новый пользователь создан с ID $user_id и привязан к chat_id $chat_id");
        } else {
            // Если пользователь найден, берем его ID
            $user_id = $users[0]->ID;
            // Обновляем код и временную метку для существующего пользователя
            update_user_meta($user_id, 'telegram_auth_code', $input_code);
            update_user_meta($user_id, 'telegram_auth_code_timestamp', time());
        }

        // Проверка авторизационного кода
        if (verify_telegram_auth_code($user_id, $input_code, $chat_id)) {
            // Устанавливаем сессию авторизации
            wp_clear_auth_cookie();
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id, true);

            // Устанавливаем временную роль, если нужно
            $user = get_user_by('id', $user_id);
            if (!in_array('administrator', $user->roles)) {
                $user->set_role('editor'); // Пример временной роли
            }

            // Перенаправляем в админку
            wp_safe_redirect(admin_url());
            exit;
        } else {
            echo '<p>Неверный код или срок действия кода истек.</p>';
        }
    }
}

// Добавляем обработчик формы на хук 'template_redirect'
add_action('template_redirect', 'authorize_user_via_code');

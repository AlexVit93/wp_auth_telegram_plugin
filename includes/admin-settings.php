<?php

// Создание страницы настроек
function messenger_auth_settings_menu() {
    add_options_page(
        'Настройки Messenger Auth',
        'Messenger Auth',
        'manage_options',
        'messenger-auth-settings',
        'messenger_auth_settings_page'
    );
}
add_action('admin_menu', 'messenger_auth_settings_menu');

// Рендеринг страницы настроек
function messenger_auth_settings_page() {
    ?>
    <div class="wrap messenger-auth-settings">
        <h1>Настройки Messenger Auth</h1>
        <?php settings_errors(); // Выводим системные уведомления WordPress ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('messenger_auth_settings_group');
            do_settings_sections('messenger-auth-settings');
            submit_button('Сохранить настройки');
            ?>
        </form>
    </div>
    <?php
}

// Регистрация настроек
function messenger_auth_settings_init() {
    register_setting('messenger_auth_settings_group', 'messenger_bot_token'); // Токен для Telegram бота
    register_setting('messenger_auth_settings_group', 'messenger_button_color');
    register_setting('messenger_auth_settings_group', 'messenger_button_size');
    register_setting('messenger_auth_settings_group', 'messenger_chat_id'); // Новое поле для chat_id

    add_settings_section(
        'messenger_auth_main_section',
        'Настройки кнопки авторизации',
        null,
        'messenger-auth-settings'
    );

    add_settings_field(
        'messenger_bot_token',
        'Токен бота Telegram',
        'messenger_bot_token_field',
        'messenger-auth-settings',
        'messenger_auth_main_section'
    );

    add_settings_field(
        'messenger_button_color',
        'Цвет кнопки',
        'messenger_button_color_field',
        'messenger-auth-settings',
        'messenger_auth_main_section'
    );

    add_settings_field(
        'messenger_button_size',
        'Размер кнопки',
        'messenger_button_size_field',
        'messenger-auth-settings',
        'messenger_auth_main_section'
    );

    // Добавляем поле chat_id
    add_settings_field(
        'messenger_chat_id',
        'Chat ID пользователя',
        'messenger_chat_id_field',
        'messenger-auth-settings',
        'messenger_auth_main_section'
    );
}
add_action('admin_init', 'messenger_auth_settings_init');

// Поля для настройки
function messenger_bot_token_field() {
    $value = get_option('messenger_bot_token', '');
    echo '<input type="text" name="messenger_bot_token" value="' . esc_attr($value) . '" placeholder="Введите токен бота Telegram" style="width: 100%;">';
}

function messenger_button_color_field() {
    $value = get_option('messenger_button_color', '#0073aa');
    echo '<input type="color" name="messenger_button_color" value="' . esc_attr($value) . '">';
}

function messenger_button_size_field() {
    $value = get_option('messenger_button_size', 'medium');
    echo '<select name="messenger_button_size">
            <option value="small" ' . selected($value, 'small', false) . '>Маленький</option>
            <option value="medium" ' . selected($value, 'medium', false) . '>Средний</option>
            <option value="large" ' . selected($value, 'large', false) . '>Большой</option>
          </select>';
}

// Функция для поля chat_id
function messenger_chat_id_field() {
    $value = get_option('messenger_chat_id', '');
    echo '<input type="text" name="messenger_chat_id" value="' . esc_attr($value) . '" placeholder="Введите chat ID пользователя Telegram" style="width: 100%;">';
}

function load_messenger_auth_admin_styles($hook) {
    // Подключаем стили только на странице настроек Messenger Auth
    if ($hook != 'settings_page_messenger-auth-settings') {
        return;
    }
    wp_enqueue_style('messenger-auth-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'load_messenger_auth_admin_styles');

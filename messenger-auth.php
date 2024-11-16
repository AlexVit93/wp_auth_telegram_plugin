<?php
/*
Plugin Name: Messenger Auth
Description: Плагин для авторизации через мессенджер с временным доступом.
Version: 1.0
Author: Kapral Prod.
*/

if (!defined('ABSPATH')) {
    exit; // Запрет прямого доступа
}

// Подключаем файлы с настройками и функциями
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/messenger-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/session-handler.php';


// Подключаем стили для админки
function messenger_auth_admin_assets() {
    wp_enqueue_style('messenger-auth-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'messenger_auth_admin_assets');

// Подключаем скрипты для фронтенда с передачей данных
function messenger_auth_front_assets() {
    wp_enqueue_script('messenger-auth-front-js', plugin_dir_url(__FILE__) . 'assets/js/front-auth.js', array('jquery'), null, true);

    // Передаем параметры из настроек админки в JavaScript
    $localize_data = array(
        'botToken' => get_option('messenger_bot_token'), // Токен из настроек
        'ajaxUrl' => admin_url('admin-ajax.php'),        // URL для AJAX
        'authRedirectUrl' => site_url('/enter-auth-code'), // URL для редиректа после авторизации
        'buttonColor' => get_option('messenger_button_color', '#0073aa'), // Цвет кнопки из настроек
        'buttonSize' => get_option('messenger_button_size', 'medium')     // Размер кнопки из настроек
    );

    wp_localize_script('messenger-auth-front-js', 'messengerAuthSettings', $localize_data);
}
add_action('wp_enqueue_scripts', 'messenger_auth_front_assets');


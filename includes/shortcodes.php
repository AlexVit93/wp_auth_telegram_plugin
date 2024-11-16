<?php
// Функция для отображения формы ввода кода
function render_auth_code_form() {
    if (is_user_logged_in()) {
        return '<p>Вы уже авторизованы.</p>';
    }

    ob_start();
    ?>
    <form method="post" action="">
        <label for="auth_code">Введите код из Telegram:</label>
        <input type="text" name="auth_code" id="auth_code" required>
        <button type="submit">Авторизоваться</button>
    </form>
    <?php
    return ob_get_clean();
}

// Регистрация шорткода
function register_auth_code_shortcode() {
    add_shortcode('auth_code_form', 'render_auth_code_form');
}
add_action('init', 'register_auth_code_shortcode');

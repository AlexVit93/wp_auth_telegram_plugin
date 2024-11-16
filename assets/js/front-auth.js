jQuery(document).ready(function ($) {
  // Получаем переданные параметры из настроек плагина
  const buttonSize = messengerAuthSettings.buttonSize;
  const buttonColor = messengerAuthSettings.buttonColor;

  const button = $("<a>", {
    href: "#",
    id: "messenger-auth-button",
    text: "Войти через Telegram",
    css: {
      position: "fixed",
      right: "20px",
      bottom: "20px",
      padding:
        buttonSize === "large"
          ? "15px 30px"
          : buttonSize === "small"
          ? "5px 10px"
          : "10px 20px",
      backgroundColor: buttonColor,
      color: "#fff",
      borderRadius: "4px",
      fontSize:
        buttonSize === "large"
          ? "18px"
          : buttonSize === "small"
          ? "12px"
          : "16px",
      textAlign: "center",
      zIndex: 1000,
      cursor: "pointer",
    },
    click: function (e) {
      e.preventDefault();
      $.ajax({
        url: messengerAuthSettings.ajaxUrl,
        type: "POST",
        data: {
          action: "start_telegram_auth",
        },
        success: function (response) {
          if (response.success) {
            alert(
              "Код отправлен на ваш Telegram. Пожалуйста, проверьте сообщения."
            );
            window.location.href = messengerAuthSettings.authRedirectUrl;
          } else {
            alert(
              response.data.message ||
                "Ошибка при отправке кода. Попробуйте снова."
            );
          }
        },
      });
    },
  });

  $("body").append(button);
});

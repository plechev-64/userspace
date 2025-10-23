<?php
/**
 * Общий шаблон-обертка для HTML-писем.
 *
 * @var string $content HTML-содержимое письма, которое будет вставлено в шаблон.
 * @var string $site_title Название сайта.
 * @var string $subject Тема письма (для тега <title>).
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr(get_locale()); ?>">
<head>
    <meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($subject); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            color: #333;
            background-color: #f7f7f7;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .email-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
<div class="email-container">
    <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is pre-sanitized HTML ?>
</div>
<div class="email-footer">
    <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html($site_title); ?></p>
</div>
</body>
</html>
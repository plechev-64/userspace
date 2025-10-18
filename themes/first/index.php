<?php
/**
 * Theme Name: First
 *
 * Точка входа для инициализации темы личного кабинета "First".
 * * Этот файл отвечает за регистрацию сервисов и "мест вывода", специфичных для темы.
 */

use UserSpace\Plugin;
use UserSpace\Theme\First\Service\TabLocationService;
use UserSpace\Theme\First\Service\ThemeServiceProvider;

if (!defined('ABSPATH')) {
    exit;
}

// 1. Инициализируем сервисы темы, чтобы они были доступны как на фронте, так и в админ-панели.
require_once __DIR__ . '/services/ThemeServiceProvider.php';
require_once __DIR__ . '/services/TabLocationService.php';
require_once __DIR__ . '/services/ViewDataProvider.php';

$pluginContainer = Plugin::getInstance()->getContainer();
$themeServiceProvider = new ThemeServiceProvider($pluginContainer);
$themeContainer = $themeServiceProvider->getContainer();

/** @var TabLocationService $tabLocationService */
$tabLocationService = $themeContainer->get(TabLocationService::class);
$tabLocationService->registerThemeLocations();
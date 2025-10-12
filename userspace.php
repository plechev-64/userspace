<?php
/*
  Plugin Name: UserSpace
  Plugin URI: http://user-space.com/
  Description: Login & registration form, profile fields, front-end profile, user account and core for WordPress membership.
  Version: 0.1
  Author: UserSpace
  Author URI: http://user-space.com/
  Text Domain: userspace
  License: GPLv2 or later (license.txt)
 */

/*  Copyright 2024  UserSpace  (email : support {at} user-space.com)  */

use USP\Core\Install;
use USP\Core\Container;
use USP\UserSpace;

require_once 'vendor/autoload.php';

register_activation_hook( __FILE__, [ Install::class, 'install' ] );

// Инициализируем контейнер зависимостей
$container = Container::getInstance();

// Получаем главный класс плагина из контейнера и запускаем его
$GLOBALS['userspace'] = $container->get( UserSpace::class );
$GLOBALS['userspace']->run();

function USP(): UserSpace {
	return $GLOBALS['userspace'];
}

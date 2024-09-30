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

use USP\UserSpace;

require_once 'vendor/autoload.php';

function USP(): ?UserSpace {
	return UserSpace::getInstance();
}

$GLOBALS['userspace'] = USP();

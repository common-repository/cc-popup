<?php

/*
	Plugin Name: CC-Popup
	Plugin URI: https://wordpress.org/plugins/cc-popup
	Description: This plugin allows you to create and display popups on selected Posts, Pages and/or other public Custom Post Types.
	Version: 1.0.0
	Author: Clearcode | Piotr Niewiadomski
	Author URI: http://clearcode.cc
	Text Domain: cc-popup
	Domain Path: /languages/
	License: GPLv3
	License URI: http://www.gnu.org/licenses/gpl-3.0.txt

	Copyright (C) 2016 by Clearcode <http://clearcode.cc>
	and associates (see AUTHORS.txt file).

	This file is part of CC-Popup.

	CC-Popup is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	CC-Popup is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with CC-Popup; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace Clearcode\Popup;

use Clearcode\Popup;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'get_plugin_data' ) ) require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

foreach ( array( 'singleton', 'filterer', 'magic', 'plugin' ) as $file ) require_once( sprintf( "%s/framework/%s.php", __DIR__, $file ) );

require_once( __DIR__ . '/includes/popup.php' );

try {
	Popup::set( 'file', __FILE__ );
	Popup::set( 'dir',  __DIR__  );

	spl_autoload_register( __NAMESPACE__ . '::autoload' );

	if ( ! has_action( __NAMESPACE__ ) ) do_action( __NAMESPACE__, Popup::instance() );
} catch ( Exception $exception ) {
	if ( WP_DEBUG && WP_DEBUG_DISPLAY ) echo $exception->getMessage();
}

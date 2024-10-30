<?php

/*
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

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Type' ) ) {
	class Type extends Magic {
		protected static $name     = 'popup';
		protected static $url      = '';
		protected static $icon     = 'dashicons-testimonial';
		protected static $supports = array(
			'title',
			'editor',
			'author',
			'revisions'
		);

		protected function __construct() {
			static::$url = self::url( self::$name );

			parent::__construct();
		}

		public static function get( $post = null ) {
			if ( null !== $post and ! is_numeric( $post ) and ! ( $post instanceof WP_Post ) ) return parent::get( $post );

			return self::$name;
		}

		public static function url( $post_type = null ) {
			$url = 'edit.php?post_type=';

			if ( is_string( $post_type ) && ! is_numeric( $post_type ) ) return $url . $post_type;
			if ( ! $post = get_post( $post_type ) ) return '';

			return $url . $post->post_type;
		}

		public function action_init () {
			register_post_type(
				self::$name,
				array(
					'labels'            => array(
						'name'          => Popup::__( 'Popups' ),
						'singular_name' => Popup::__( 'Popup' )
					),
					'public'            => false,
					'show_ui'           => true,
					'menu_icon'         => self::$icon,
					'menu_position'     => 20,
					'show_in_admin_bar' => true,
					'show_in_json'      => true,
					'has_archive'       => false,
					'supports'          => self::$supports
				)
			);
		}
	}
}

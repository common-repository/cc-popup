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

namespace Clearcode;

use Clearcode\Popup\Plugin;
use Clearcode\Popup\Settings;
use Clearcode\Popup\Type;
use Clearcode\Popup\Meta;
use Clearcode\Popup\Status;

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Popup' ) ) {
	class Popup extends Plugin {
		public function __construct() {
			Type::instance();
			Settings::instance();
			Meta::instance();
			Status::instance();

			parent::__construct();
		}

		public function activation() {
			update_option( self::get( 'slug' ), array( 'post_types' => Settings::instance()->post_types ), false );
		}

		public function deactivation() {
			delete_option( self::get( 'slug' ) );
		}

		public function action_wp_enqueue_scripts() {
			if ( ! self::get() ) return;

			wp_register_style( self::get( 'slug' ), self::get( 'url' ) . '/assets/css/style.css', array(), self::get( 'Version' ) );
			wp_enqueue_style(  self::get( 'slug' ) );

			wp_register_script( self::get( 'slug' ), self::get( 'url' ) . '/assets/js/script.js', array( 'jquery' ), self::get( 'Version' ), true );
			wp_enqueue_script(  self::get( 'slug' ) );
		}

		public function action_admin_enqueue_scripts( $page ) {
			global $post;
			if ( ! in_array( $page, array( 'post.php', 'post-new.php' ) ) ) return;
			if ( ! in_array( $post->post_type, Settings::instance()->post_types ) ) return;

			wp_register_style( self::get( 'slug' ), self::get( 'url' ) . '/assets/css/admin.css', array(), self::get( 'Version' ) );
			wp_enqueue_style(  self::get( 'slug' ) );
		}

		public function filter_the_content( $content ) {
			if ( self::is_cookie() ) return $content;

			return $content . self::get();
		}

		public function plugin_action_links( $links ) {
			array_unshift( $links, self::get_template( 'link', array(
				'url'  => get_admin_url( null, Settings::instance()->url ),
				'link' => self::__( 'Settings' )
			) ) );

			return $links;
		}

		static public function is_cookie( $post = null ) {
			if ( ! $post_id = Meta::get( $post ) ) return '';

			return isset( $_COOKIE["cc-popup-$post_id"] ) ? true : false;
		}

		static public function get_template( $template, $vars = array() ) {
			return parent::get_template( self::get( 'dir' ) . '/templates/' . $template . '.php', $vars );
		}

		static public function get( $post = null ) {
			if ( null !== $post and ! is_numeric( $post ) and ! ( $post instanceof WP_Post ) ) return parent::get( $post );

			if ( ! $post = Meta::get( $post ) ) return '';

			$post = get_post( $post );
			return 'publish' == Status::get( $post ) ? self::get_template( 'popup', array(
				'id'      => $post->ID,
				'content' => $post->post_content
			) ) : '';
		}

		static public function render( $post = null ) {
			echo self::get( $post );
		}
	}
}

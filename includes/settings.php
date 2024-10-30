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

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Settings' ) ) {
	class Settings extends Magic {
		protected static $url = '';
		protected $post_types = array();

		protected function __construct() {
			static::$url = Type::instance()->url . '&page=settings';

			$settings = get_option( Popup::get( 'slug' ) );
			if ( ! empty( $settings['post_types'] ) ) $this->post_types = $settings['post_types'];

			parent::__construct();
		}

		protected function get_post_types() {
			$post_types = array();
			foreach( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ) $post_types[$post_type->name] = $post_type->label;

			return $post_types;
		}

		public function action_admin_init() {
			register_setting(     Popup::get( 'slug' ), Popup::get( 'slug' ), array( $this, 'sanitize' ) );
			add_settings_section( Popup::get( 'slug' ), Popup::__( 'Popup' ), array( $this, 'section' ), Popup::get( 'slug' ) );

			add_settings_field( 'post_types', Popup::__( 'Post Types' ), array( $this, 'post_types' ), Popup::get( 'slug' ), Popup::get( 'slug' ) );
		}

		public function action_admin_menu() {
			add_submenu_page(
				Type::instance()->url,
				Popup::__( 'Settings' ),
				Popup::__( 'Settings' ),
				'manage_options',
				'settings',
				array( $this, 'page' )
			);
		}

		public function page() {
			echo Popup::get_template( 'page', array(
				'option_group' => Popup::get( 'slug' ),
				'page'         => Popup::get( 'slug' )
			) );
		}

		public function section() {
			echo Popup::get_template( 'section', array(
				'section' => Popup::__( 'Settings' )
			) );
		}

		public function sanitize( $settings ) {
			$sanitized_settings = array( 'post_types' => array() );
			$post_types = $this->get_post_types();

			if ( ! empty( $settings['post_types'] ) )
				foreach( $settings['post_types'] as $post_type )
					if ( in_array( $post_type, array_keys( $post_types ) ) )
						$sanitized_settings['post_types'][] = $post_type;

			return $sanitized_settings;
		}

		static public function input( $type, $name, $value, $label = '', $checked = '' ) {
			return Popup::get_template( 'input', array(
				'type'    => $type,
				'name'    => $name,
				'value'   => $value,
				'label'   => $label,
				'checked' => $checked
			) );
		}

		public function post_types() {
			$post_types = $this->get_post_types();
			foreach( $post_types as $post_type => $label ) {
				echo self::input( 'checkbox',
					Popup::get( 'slug' ) . "[post_types][$post_type]",
					$post_type,
					ucfirst( $label ),
					checked( in_array( $post_type, $this->post_types ), true, false )
				);
			}
		}
	}
}

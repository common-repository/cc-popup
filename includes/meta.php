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
if ( ! class_exists( __NAMESPACE__ . '\Meta' ) ) {
	class Meta extends Magic {
		protected static $key   = '';
		protected static $value = '';

		protected function __construct( $post = null ) {
			self::$key   = '_' . Popup::get( 'slug' );
			self::$value = self::get( $post );

			parent::__construct();
		}

		public static function get( $post = null ) {
			if ( null !== $post and ! is_numeric( $post ) and ! ( $post instanceof WP_Post ) ) return parent::get( $post );

			if ( $post = get_post( $post ) ) return (string)get_post_meta( $post->ID, self::$key, true );
			return '';
		}

		public static function update( $value, $post = null ) {
			if ( $post = get_post( $post ) ) return update_post_meta( $post->ID, self::$key, $value );
			return false;
		}

		public static function delete( $post = null ) {
			if ( $post = get_post( $post ) ) return delete_post_meta( $post->ID, self::$key );
			return false;
		}

		public function action_add_meta_boxes() {
			$post_types = Settings::instance()->post_types;
			foreach ( $post_types as $post_type ) {
				add_meta_box( Popup::get( 'slug' ),
					Popup::get_template( 'span', array(
						'class'   => 'dashicons-before ' . Type::instance()->icon,
						'content' => Popup::__( 'Popup' )
					) ),
					array( $this, 'meta_box' ),
					$post_type,
					'side'
				);
			}
		}

		public function meta_box( $post ) {
			wp_nonce_field( Popup::get( 'slug' ), Popup::get( 'slug' ) );

			$options = Popup::get_template( 'option', array(
				'value'  => '',
				'option' => Popup::__( 'Select' )
			) );

			$posts = get_posts( array(
				'posts_per_page' => - 1,
				'post_type'      => Type::get(),
				'post_status'    => 'publish'
			) );

			$post_id = 0;
			foreach ( $posts as $post ) {
				if ( $selected = selected( (string)$post->ID === self::get(), true, false ) ) $post_id = $post->ID;
				$options .= Popup::get_template( 'option', array(
					'value'    => $post->ID,
					'option'   => $post->post_title,
					'selected' => $selected
				) );
			}

			echo Popup::get_template( 'select', array(
				'name'    => self::$key,
				'options' => $options
			) );

			if ( $post_id ) {
				$status = Popup::get_template( 'span', array(
					'class' => 'dashicons-before ' . Status::icon( Status::get( $post_id ) ),
				) );

				$label = Popup::get_template( 'span', array(
					'content' => Status::label( $post_id ),
				) );

				echo Popup::get_template( 'div', array(
					'content' => $status . $label
				) );

				$icon = Popup::get_template( 'span', array(
					'class' => 'dashicons-before dashicons-edit',
				) );

				$link = Popup::get_template( 'link', array(
					'url'  => get_edit_post_link( $post_id ),
					'link' => get_the_title( $post_id ),
				) );

				echo Popup::get_template( 'div', array(
					'content' => $icon . $link
				) );
			}
		}

		public function action_current_screen( $screen ) {
			if ( 'post' != $screen->base ) return;

			add_action( 'save_post', array( $this, 'save_post' ) );
		}

		public function save_post( $post_id ) {
			$post = get_post( $post_id );
			if ( ! in_array( $post->post_type, Settings::instance()->post_types ) ) return;

			// nonce is set
			if ( ! isset( $_REQUEST[Popup::get( 'slug' )] ) ) return;

			// nonce is valid
			if ( ! check_admin_referer( Popup::get( 'slug' ), Popup::get( 'slug' ) ) ) return;

			// is autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

			// user can
			$post_type = get_post_type_object( $post->post_type );
			if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) return;

			// save
			$value = ! empty( $_REQUEST[self::$key] ) ? $_REQUEST[self::$key] : null;
			if ( $value ) self::update( $value, $post );
			else self::delete( $post );
		}
	}
}

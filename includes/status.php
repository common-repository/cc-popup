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
if ( ! class_exists( __NAMESPACE__ . '\Status' ) ) {
	class Status extends Magic {
		protected static $url = '';

		protected static $icons = array(
			'publish'    => 'dashicons dashicons-yes',
			'pending'    => 'dashicons dashicons-hidden',
			'draft'      => 'dashicons dashicons-edit',
			'auto-draft' => 'dashicons dashicons-edit',
			'future'     => 'dashicons dashicons-clock',
			'private'    => 'dashicons dashicons-lock',
			'inherit'    => 'dashicons dashicons-backup',
			'trash'      => 'dashicons dashicons-trash',
			'password'   => 'dashicons dashicons-shield-alt',
			false        => 'dashicons dashicons-no'
		);

		protected function __construct() {
			static::$url = Type::instance()->url . '&post_status=';

			$post_types = Settings::instance()->post_types;
			foreach( $post_types as $post_type ) {
				add_filter( sprintf( 'manage_%s_posts_columns',       $post_type ), array( $this, 'manage_posts_columns' ) );
				add_action( sprintf( 'manage_%s_posts_custom_column', $post_type ), array( $this, 'manage_posts_custom_column' ), 10, 2 );
			}

			add_filter( sprintf( 'manage_%s_posts_columns',       Type::get() ), array( $this, 'manage_popup_posts_columns' ) );
			add_action( sprintf( 'manage_%s_posts_custom_column', Type::get() ), array( $this, 'manage_popup_posts_custom_column' ), 10, 2 );

			parent::__construct();
		}

		public static function get( $post = null ) {
			if ( null !== $post and ! is_numeric( $post ) and ! ( $post instanceof WP_Post ) ) return parent::get( $post );
			if ( $post = get_post( $post ) ) return post_password_required( $post->ID ) ? 'password' : get_post_status( $post->ID );
			return false;
		}

		public static function icon( $status ) {
			return isset( self::$icons[$status] ) ? self::$icons[$status] : self::$icons[false];
		}

		// TODO unify like Type::url() method
		public static function url( $post = null ) {
			if ( ! $post = get_post( $post ) ) return '';
			if ( ! $status = get_post_status_object( $post->post_status ) ) return '';
			if ( $status->show_in_admin_status_list )
				return get_admin_url( null, Type::url( $post->post_type ) . '&post_status=' . $post->post_status );
			return get_admin_url( null, Type::url( $post->post_type ) );
		}

		public static function label( $post = null ) {
			if ( ! $post = get_post( $post ) ) return Popup::__( 'Deleted' );
			if ( post_password_required( $post->ID ) ) return __( 'Password protected' );
			if ( $status = get_post_status_object( $post->post_status ) ) return __( $status->label );
			return '';
		}

		public function manage_posts_columns( $columns ) {
			return array_merge( $columns, array(
				'popup' => Popup::get_template( 'span', array(
					'class'   => 'dashicons-before ' . Type::instance()->icon,
					'content' => Popup::__( 'Popup' )
			) ) ) );
		}

		public function manage_posts_custom_column( $column, $post_id ) {
			if ( 'popup' !== $column ) return;

			if ( $popup_id = Meta::get( $post_id ) ) {
				echo Popup::get_template( 'span', array(
					'class' => self::icon( self::get( $popup_id ) )
				) );
				echo Popup::get_template( 'link', array(
					'url'   => get_edit_post_link( $popup_id ),
					'link'  => get_the_title( $popup_id )
				) );

				$link = Popup::get_template( 'link', array(
					'url'   => self::url( $popup_id ),
					'link'  => self::label( $popup_id )
				) );
				$span = Popup::get_template( 'span', array(
					'content' => $link
				) );
				echo Popup::get_template( 'div', array(
					'class'   => 'row-actions',
					'content' => $span
				) );
			} else echo Popup::get_template( 'span', array( 'content'  => '—' ) );
		}

		public function manage_popup_posts_columns( $columns ) {
			return array_merge( $columns, array(
				'popup' => Popup::get_template( 'span', array(
					'class'   => 'dashicons-before ' . Type::instance()->icon,
					'content' => Popup::__( 'Included' )
				) ) ) );
		}

		public function manage_popup_posts_custom_column( $column, $post_id ) {
			if ( 'popup' !== $column ) return;

			$posts = get_posts( array(
				'posts_per_page' => -1,
				'meta_key'       => Meta::instance()->key,
				'meta_value'     => $post_id,
				'post_type'      => Settings::instance()->post_types,
				'post_status'    => get_post_stati()
			) );

			if ( $posts ) foreach( $posts as $post ) {
				echo Popup::get_template( 'span', array(
					'class' => self::icon( self::get( $post ) )
				) );
				echo Popup::get_template( 'link', array(
					'url'   => get_edit_post_link( $post ),
					'link'  => get_the_title( $post )
				) );

				$link = Popup::get_template( 'link', array(
					'url'   => self::url( $post ),
					'link'  => self::label( $post )
				) );
				$span = Popup::get_template( 'span', array(
					'content' => $link
				) );
				echo Popup::get_template( 'div', array(
					'class'   => 'row-actions',
					'content' => $span
				) );
			} else echo Popup::get_template( 'span', array( 'content'  => '—' ) );
		}
	}
}

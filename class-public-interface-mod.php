<?php
// Modified for generic usage
// https://github.com/mundschenk-at/wp-typography/blob/71a04e9f7237308acb2e50b2a5ee1b0d2240eb27/includes/wp-typography/components/class-public-interface.php


/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2018 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  ***
 *
 *  @package mundschenk-at/wp-typography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * The public (non-admin) functionality of the plugin.
 *
 * @since 5.1.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Public_Interface_Mod {

	/**
	 * The priority for our filter hooks.
	 *
	 * @var int
	 */
	private $filter_priority = PHP_INT_MAX;

	/**
	 * The plugin configuration.
	 *
	 * @var array
	 */
	//protected $config;

	/**
	 * Set up the various hooks for the admin side.
	 *
	 * @param \WP_Typography $plugin The plugin object.
	 */
	public function run( $plugin ) {
		$this->plugin = $plugin;

		if ( ! \is_admin() ) {
			// Load settings.
			\add_action( 'init', [ $this, 'init' ] );
		}
	}

	/**
	 * Sets up filters and actions.
	 */
	public function init() {
		/*
		$this->config = $this->plugin->get_config();

		// Disable wptexturize filter if it conflicts with our settings.
		if ( $this->config[ Config::SMART_CHARACTERS ] ) {
			\add_filter( 'run_wptexturize', '__return_false' );

			// Ensure that wptexturize is actually off by forcing a re-evaluation (some plugins call it too early).
			\wptexturize( ' ', true ); // Argument must not be empty string!
		}
		*/

		// Check for NextGEN Gallery and use insane filter priority if activated.
		if ( \class_exists( 'C_NextGEN_Bootstrap' ) ) {
			$this->filter_priority = PHP_INT_MAX;
		}

		// Apply our filters.
		$this->add_content_filters();

		// Grab body classes via hook.
		\add_filter( 'body_class', [ $this->plugin, 'filter_body_class' ], $this->filter_priority );
	}

	/**
	 * Adds content filter handlers.
	 */
	public function add_content_filters() {
		/**
		 * Filters the priority used for wp-Typography's text processing filters.
		 *
		 * When NextGen Gallery is detected, the priority is set to PHP_INT_MAX.
		 *
		 * @since 3.2.0
		 *
		 * @param int $priority The filter priority. Default 9999.
		 */
		$priority = \apply_filters( 'typo_filter_priority', $this->filter_priority );

		/**
		 * The available filters.
		 *
		 * @var array
		 */
		$filters = [
			// Add filters for "full" content.
			'content'     => [
				'enable'  => 'enable_content_filters',
			],
			// Add filters for headings.
			'heading'     => [
				'enable'  => 'enable_heading_filters',
			],
			// Extra care needs to be taken with the <title> tag.
			'title'       => [
				'enable'  => 'enable_title_filters',
			],
			// Add filters for third-party plugins.
			'acf'         => [
				'enable'  => 'enable_acf_filters',
				'check'   => function() {
					return \class_exists( 'acf' ) && \function_exists( 'acf_get_setting' );
				},
			],
			'woocommerce' => [
				'enable'  => 'enable_woocommerce_filters',
				'check'   => function() {
					return \class_exists( 'WooCommerce' );
				},
			],
		];

		foreach ( $filters as $tag => $filter ) {
			/**
			 * Disables automatic filtering by wp-Typography.
			 *
			 * @since 3.6.0
			 * @since 5.2.0 WooCommerce support added ($filter_group 'woocommerce').
			 *
			 * @param bool   $disable      Whether to disable automatic filtering. Default false.
			 * @param string $filter_group Which filters to disable. Possible values 'content', 'heading', 'title', 'acf', 'woocommerce'.
			 */
			if ( ( ! isset( $filter['check'] ) || $filter['check']() ) && ! \apply_filters( 'typo_disable_filtering', false, $tag ) ) {
				$this->{$filter['enable']}( $priority );
			}
		}
	}

	/**
	 * Enable the content (body) filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_content_filters( $priority ) {
		\add_filter( 'comment_author',    [ $this->plugin, 'process' ], $priority );
		\add_filter( 'comment_text',      [ $this->plugin, 'process' ], $priority );
		\add_filter( 'comment_text',      [ $this->plugin, 'process' ], $priority );
		\add_filter( 'the_content',       [ $this->plugin, 'process' ], $priority );
		\add_filter( 'term_name',         [ $this->plugin, 'process' ], $priority );
		\add_filter( 'term_description',  [ $this->plugin, 'process' ], $priority );
		\add_filter( 'link_name',         [ $this->plugin, 'process' ], $priority );
		\add_filter( 'the_excerpt',       [ $this->plugin, 'process' ], $priority );
		\add_filter( 'the_excerpt_embed', [ $this->plugin, 'process' ], $priority );

		// Preserve shortcode handling on WordPress 4.8+.
		if ( \version_compare( \get_bloginfo( 'version' ), '4.8', '>=' ) ) {
			\add_filter( 'widget_text_content', [ $this->plugin, 'process' ], $priority );
		} else {
			\add_filter( 'widget_text', [ $this->plugin, 'process' ], $priority );
		}
	}

	/**
	 * Enable the heading filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_heading_filters( $priority ) {
		\add_filter( 'the_title',            [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'single_post_title',    [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'single_cat_title',     [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'single_tag_title',     [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'single_month_title',   [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'single_month_title',   [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'nav_menu_attr_title',  [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'nav_menu_description', [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'widget_title',         [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'list_cats',            [ $this->plugin, 'process_title' ], $priority );
	}

	/**
	 * Enable the title (not heading) filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_title_filters( $priority ) {
		\add_filter( 'wp_title',             [ $this->plugin, 'process_feed' ],        $priority ); // WP < 4.4.
		\add_filter( 'document_title_parts', [ $this->plugin, 'process_title_parts' ], $priority );
		\add_filter( 'wp_title_parts',       [ $this->plugin, 'process_title_parts' ], $priority ); // WP < 4.4.
	}

	/**
	 * Enable the Advanced Custom Fields (https://www.advancedcustomfields.com) filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_acf_filters( $priority ) {
		$acf_version = \intval( /* @scrutinizer ignore-call */ \acf_get_setting( 'version' ) );

		if ( 5 === $acf_version ) {
			// Advanced Custom Fields Pro (version 5).
			$acf_prefix = 'acf/format_value';
		} elseif ( 4 === $acf_version ) {
			// Advanced Custom Fields (version 4).
			$acf_prefix = 'acf/format_value_for_api';
		}

		// Other ACF versions (i.e. < 4) are not supported.
		if ( ! empty( $acf_prefix ) ) {
			\add_filter( "{$acf_prefix}/type=wysiwyg",  [ $this->plugin, 'process' ],       $priority );
			\add_filter( "{$acf_prefix}/type=textarea", [ $this->plugin, 'process' ],       $priority );
			\add_filter( "{$acf_prefix}/type=text",     [ $this->plugin, 'process_title' ], $priority );
		}
	}

	/**
	 * Enable the WooCommerce (https://github.com/woocommerce/woocommerce) filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_woocommerce_filters( $priority ) {
		// Page descriptions.
		\add_filter( 'woocommerce_format_content', [ $this->plugin, 'process' ], $priority );

		// Shop notices.
		\add_filter( 'woocommerce_add_error',      [ $this->plugin, 'process' ], $priority );
		\add_filter( 'woocommerce_add_success',    [ $this->plugin, 'process' ], $priority );
		\add_filter( 'woocommerce_add_notice',     [ $this->plugin, 'process' ], $priority );

		// Demo store banner.
		\add_filter( 'woocommerce_demo_store',     [ $this->plugin, 'process' ], $priority );
	}
}

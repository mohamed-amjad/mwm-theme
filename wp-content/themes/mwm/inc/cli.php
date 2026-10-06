<?php
/**
 * WP-CLI: `wp mwm seed` — idempotent site setup from a content file kept outside the theme.
 *
 * Content file returns an array: media_dir, media, settings, options, records, pages, menus, logo.
 * String tokens inside it are resolved at seed time:
 *   @media:key  attachment ID     @img:key  Elementor media array {id,url}
 *   @post:key   seeded post ID    @ids:k1,k2  array of seeded post IDs (strings, ACF style)
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Site seeding commands.
 */
class Mwm_Cli_Command {

	/**
	 * Media key => attachment ID.
	 *
	 * @var array<string,int>
	 */
	private $media = array();

	/**
	 * Seed key => post ID.
	 *
	 * @var array<string,int>
	 */
	private $posts = array();

	/**
	 * Overwrite existing content.
	 *
	 * @var bool
	 */
	private $force = false;

	/**
	 * Seed media, settings, records, Elementor pages, menus and the Kit.
	 *
	 * ## OPTIONS
	 *
	 * [--content=<file>]
	 * : Content definition file. Default: tools/seed-content.php in the WordPress root.
	 *
	 * [--force]
	 * : Overwrite seeded posts, pages, menus and settings that already exist.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mwm seed
	 *     wp mwm seed --force
	 *
	 * @param array $args  Positional args.
	 * @param array $assoc Assoc args.
	 */
	public function seed( $args, $assoc ) {
		$file = $assoc['content'] ?? ABSPATH . 'tools/seed-content.php';
		if ( ! is_readable( $file ) ) {
			WP_CLI::error( "Content file not found: {$file}" );
		}
		$content     = require $file;
		$this->force = ! empty( $assoc['force'] ) || ! get_option( 'mwm_seeded' );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$this->import_media( (string) ( $content['media_dir'] ?? '' ), (array) ( $content['media'] ?? array() ) );

		foreach ( (array) ( $content['records'] ?? array() ) as $type => $records ) {
			foreach ( $records as $key => $def ) {
				$this->upsert_post( $key, $type, $def );
			}
		}
		WP_CLI::log( sprintf( 'Records: %d', count( $this->posts ) ) );

		$roles = array();
		foreach ( (array) ( $content['pages'] ?? array() ) as $key => $def ) {
			$id = $this->upsert_post( $key, 'page', $def );
			if ( ! empty( $def['role'] ) ) {
				$roles[ 'role_' . $def['role'] ] = (string) $id;
			}
			if ( ! empty( $def['front'] ) ) {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $id );
			}
			if ( 'privacy' === ( $def['role'] ?? '' ) ) {
				update_option( 'wp_page_for_privacy_policy', $id );
			}
		}

		$this->seed_settings( (array) ( $content['settings'] ?? array() ) + $roles, array_keys( $roles ) );

		if ( $this->force ) {
			foreach ( (array) ( $content['options'] ?? array() ) as $name => $value ) {
				update_option( $name, $this->resolve( $value ) );
			}
		}

		if ( ! empty( $content['logo'] ) && wp_attachment_is_image( (int) $content['logo'] ) && ! get_theme_mod( 'custom_logo' ) ) {
			set_theme_mod( 'custom_logo', (int) $content['logo'] );
		}

		$this->seed_menus( (array) ( $content['menus'] ?? array() ) );
		$this->trash_defaults();

		if ( ! get_option( 'permalink_structure' ) ) {
			global $wp_rewrite;
			$wp_rewrite->set_permalink_structure( '/%postname%/' );
		}
		mwm_register_post_types();
		flush_rewrite_rules( true );

		if ( function_exists( 'mwm_apply_elementor_baseline' ) ) {
			mwm_apply_elementor_baseline( true );
		}
		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		update_option( 'mwm_seeded', gmdate( 'c' ) );
		WP_CLI::success( 'Site seeded.' );
	}

	/**
	 * Import photos once (tracked by `_mwm_source`), keep alt/title in sync.
	 *
	 * @param string $dir   Source directory.
	 * @param array  $media key => [file, name, alt].
	 */
	private function import_media( $dir, array $media ) {
		$dir = rtrim( $dir, '/\\' );
		foreach ( $media as $key => $def ) {
			$file     = $def['file'] ?? $key . '.jpg';
			$existing = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_status'    => 'any',
					'fields'         => 'ids',
					'posts_per_page' => 1,
					'meta_key'       => '_mwm_source', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'     => $file, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			$id = $existing ? (int) $existing[0] : 0;

			if ( ! $id ) {
				$path = $dir . '/' . $file;
				if ( ! is_readable( $path ) ) {
					WP_CLI::warning( "Missing photo: {$path}" );
					continue;
				}
				$tmp = wp_tempnam( $file );
				copy( $path, $tmp );
				$ext = pathinfo( $file, PATHINFO_EXTENSION );
				$id  = media_handle_sideload(
					array(
						'name'     => sanitize_file_name( ( $def['name'] ?? $key ) . '.' . $ext ),
						'tmp_name' => $tmp,
					),
					0,
					(string) ( $def['alt'] ?? '' )
				);
				if ( is_wp_error( $id ) ) {
					WP_CLI::warning( "{$file}: " . $id->get_error_message() );
					continue;
				}
				update_post_meta( $id, '_mwm_source', $file );
				WP_CLI::log( "Imported {$file} → #{$id}" );
			}

			if ( isset( $def['alt'] ) ) {
				update_post_meta( $id, '_wp_attachment_image_alt', $def['alt'] );
				wp_update_post(
					array(
						'ID'         => $id,
						'post_title' => $def['alt'],
					)
				);
			}
			$this->media[ $key ] = (int) $id;
		}
		WP_CLI::log( sprintf( 'Media: %d', count( $this->media ) ) );
	}

	/**
	 * Create or update one seeded post.
	 *
	 * @param string $key  Seed key.
	 * @param string $type Post type.
	 * @param array  $def  Definition.
	 * @return int
	 */
	private function upsert_post( $key, $type, array $def ) {
		$found = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
				'fields'         => 'ids',
				'posts_per_page' => 1,
				'meta_key'       => '_mwm_seed', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		$id = $found ? (int) $found[0] : 0;

		if ( $id && ! $this->force ) {
			$this->posts[ $key ] = $id;
			return $id;
		}

		$postarr = array(
			'post_type'    => $type,
			'post_status'  => 'publish',
			'post_title'   => (string) ( $def['title'] ?? $key ),
			'post_name'    => (string) ( $def['slug'] ?? '' ),
			'post_excerpt' => (string) ( $def['excerpt'] ?? '' ),
			'post_content' => (string) ( $def['content'] ?? '' ),
			'menu_order'   => (int) ( $def['order'] ?? 0 ),
		);
		if ( $id ) {
			$postarr['ID'] = $id;
		}
		$id = wp_insert_post( wp_slash( $postarr ), true );
		if ( is_wp_error( $id ) ) {
			WP_CLI::error( "{$key}: " . $id->get_error_message() );
		}
		update_post_meta( $id, '_mwm_seed', $key );
		$this->posts[ $key ] = (int) $id;

		if ( ! empty( $def['thumbnail'] ) ) {
			$thumb = (int) $this->resolve( $def['thumbnail'] );
			if ( $thumb ) {
				set_post_thumbnail( $id, $thumb );
			}
		}

		foreach ( (array) ( $def['meta'] ?? array() ) as $name => $value ) {
			update_post_meta( $id, $name, $this->resolve( $value ) );
		}

		foreach ( (array) ( $def['acf'] ?? array() ) as $name => $value ) {
			update_post_meta( $id, $name, $this->resolve( $value ) );
			update_post_meta( $id, '_' . $name, 'field_mwm_' . $this->acf_group( $type, $name ) . '_' . $name );
		}

		if ( ! empty( $def['elementor'] ) ) {
			$this->save_elementor( $id, $key, (array) $def['elementor'] );
		}

		WP_CLI::log( "{$type}: {$key} → #{$id}" );
		return (int) $id;
	}

	/**
	 * ACF group slug owning a field.
	 *
	 * @param string $type Post type.
	 * @param string $name Field name.
	 * @return string
	 */
	private function acf_group( $type, $name ) {
		if ( in_array( $name, array( 'seo_title', 'seo_description', 'seo_image', 'header_style' ), true ) ) {
			return 'seo';
		}
		return str_replace( 'mwm_', '', $type );
	}

	/**
	 * Save an Elementor layout: one full-width container per section widget.
	 *
	 * @param int    $post_id  Post.
	 * @param string $key      Seed key (stable element IDs).
	 * @param array  $sections List of [widgetType, settings].
	 */
	private function save_elementor( $post_id, $key, array $sections ) {
		$data = array();
		foreach ( $sections as $i => $section ) {
			list( $widget, $settings ) = $section;
			$settings = $this->with_row_ids( (array) $this->resolve( $settings ), $key . $i );
			$data[]   = array(
				'id'       => $this->el_id( $key . 'c' . $i ),
				'elType'   => 'container',
				'isInner'  => false,
				'settings' => array(
					'content_width'  => 'full',
					'flex_direction' => 'column',
					'flex_gap'       => array(
						'column'   => '0',
						'row'      => '0',
						'isLinked' => true,
						'unit'     => 'px',
						'size'     => 0,
					),
					'padding'        => array(
						'unit'     => 'px',
						'top'      => '0',
						'right'    => '0',
						'bottom'   => '0',
						'left'     => '0',
						'isLinked' => true,
					),
				),
				'elements' => array(
					array(
						'id'         => $this->el_id( $key . 'w' . $i ),
						'elType'     => 'widget',
						'widgetType' => 'mwm-' . $widget,
						'isInner'    => false,
						'settings'   => $settings,
						'elements'   => array(),
					),
				),
			);
		}

		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-' . get_post_type( $post_id ) );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '4.3.3' );
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		update_post_meta( $post_id, '_wp_page_template', 'default' );
		delete_post_meta( $post_id, '_elementor_css' );
		delete_post_meta( $post_id, '_elementor_element_cache' );
	}

	/**
	 * Give repeater rows stable `_id`s.
	 *
	 * @param array  $settings Settings.
	 * @param string $seed     Seed.
	 * @return array
	 */
	private function with_row_ids( array $settings, $seed ) {
		foreach ( $settings as $name => $value ) {
			if ( is_array( $value ) && array_is_list( $value ) && $value && is_array( $value[0] ) && ! isset( $value[0]['id'] ) ) {
				foreach ( $value as $n => $row ) {
					$settings[ $name ][ $n ]['_id'] = $row['_id'] ?? $this->el_id( $seed . $name . $n );
				}
			}
		}
		return $settings;
	}

	/**
	 * Deterministic 7-char Elementor element ID.
	 *
	 * @param string $seed Seed.
	 * @return string
	 */
	private function el_id( $seed ) {
		return substr( md5( $seed ), 0, 7 );
	}

	/**
	 * Resolve tokens recursively.
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	private function resolve( $value ) {
		if ( is_array( $value ) ) {
			return array_map( array( $this, 'resolve' ), $value );
		}
		if ( ! is_string( $value ) || ! preg_match( '/^@(media|img|post|ids):(.+)$/', $value, $m ) ) {
			return $value;
		}
		switch ( $m[1] ) {
			case 'media':
				return $this->media[ $m[2] ] ?? 0;
			case 'img':
				$id = $this->media[ $m[2] ] ?? 0;
				return $id ? array(
					'id'  => $id,
					'url' => (string) wp_get_attachment_url( $id ),
				) : array(
					'id'  => '',
					'url' => '',
				);
			case 'post':
				return $this->posts[ $m[2] ] ?? 0;
			case 'ids':
				$ids = array();
				foreach ( array_map( 'trim', explode( ',', $m[2] ) ) as $k ) {
					if ( ! empty( $this->posts[ $k ] ) ) {
						$ids[] = (string) $this->posts[ $k ];
					}
				}
				return $ids;
		}
		return $value;
	}

	/**
	 * Merge settings: fill empty values (or overwrite with --force / first run).
	 *
	 * @param array $values Values.
	 * @param array $always Keys always written (page roles).
	 */
	private function seed_settings( array $values, array $always ) {
		$current = mwm_settings();
		foreach ( $values as $key => $value ) {
			$value = $this->resolve( $value );
			if ( $this->force || in_array( $key, $always, true ) || ! isset( $current[ $key ] ) || '' === $current[ $key ] ) {
				$current[ $key ] = is_array( $value ) ? $value : (string) $value;
			}
		}
		update_option( 'mwm_settings', $current );
		WP_CLI::log( sprintf( 'Settings: %d keys', count( $current ) ) );
	}

	/**
	 * Build menus and assign locations.
	 *
	 * @param array $menus location => [name, items].
	 */
	private function seed_menus( array $menus ) {
		$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
		foreach ( $menus as $location => $def ) {
			$menu = wp_get_nav_menu_object( $def['name'] );
			if ( $menu && ! $this->force ) {
				$locations[ $location ] = $menu->term_id;
				continue;
			}
			if ( $menu ) {
				wp_delete_nav_menu( $menu->term_id );
			}
			if ( ! empty( $locations[ $location ] ) && ( ! $menu || (int) $locations[ $location ] !== (int) $menu->term_id ) && wp_get_nav_menu_object( (int) $locations[ $location ] ) ) {
				wp_delete_nav_menu( (int) $locations[ $location ] );
			}
			$menu_id = wp_create_nav_menu( $def['name'] );
			if ( is_wp_error( $menu_id ) ) {
				WP_CLI::warning( $menu_id->get_error_message() );
				continue;
			}
			$this->add_menu_items( $menu_id, (array) $def['items'], 0 );
			$locations[ $location ] = $menu_id;
			WP_CLI::log( "Menu: {$def['name']}" );
		}
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Add menu items recursively.
	 *
	 * @param int   $menu_id Menu.
	 * @param array $items   Items: post (seed key), title, children.
	 * @param int   $parent  Parent item.
	 */
	private function add_menu_items( $menu_id, array $items, $parent ) {
		foreach ( $items as $pos => $item ) {
			$post_id = $this->posts[ $item['post'] ] ?? 0;
			if ( ! $post_id ) {
				continue;
			}
			$item_id = wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => (string) ( $item['title'] ?? '' ),
					'menu-item-object'    => get_post_type( $post_id ),
					'menu-item-object-id' => $post_id,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => $parent,
					'menu-item-position'  => $pos + 1,
				)
			);
			if ( ! is_wp_error( $item_id ) && ! empty( $item['children'] ) ) {
				$this->add_menu_items( $menu_id, (array) $item['children'], (int) $item_id );
			}
		}
	}

	/**
	 * Trash WordPress sample content.
	 */
	private function trash_defaults() {
		// WordPress installs "Hello world" as post 1 and the sample page as page 2 (slugs are localized).
		foreach ( array( 1 => 'post', 2 => 'page' ) as $id => $type ) {
			$post = get_post( $id );
			if ( $post && $type === $post->post_type && 'trash' !== $post->post_status && ! get_post_meta( $id, '_mwm_seed', true ) ) {
				wp_trash_post( $id );
			}
		}
	}
}

WP_CLI::add_command( 'mwm', 'Mwm_Cli_Command' );

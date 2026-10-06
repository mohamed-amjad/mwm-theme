<?php
/**
 * Theme supports, menus, image sizes, cleanup.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme supports and menus.
 */
function mwm_setup() {
	load_theme_textdomain( 'mwm', MWM_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 315,
			'width'       => 285,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'القائمة الرئيسية', 'mwm' ),
			'footer'  => __( 'روابط الفوتر', 'mwm' ),
		)
	);

	add_image_size( 'mwm-card', 640, 480, true );
	add_image_size( 'mwm-thumb', 400, 300, true );
	add_image_size( 'mwm-wide', 1280, 0, false );
	add_image_size( 'mwm-logo', 0, 120, false );
}
add_action( 'after_setup_theme', 'mwm_setup' );

/**
 * Elementor's default content width.
 */
function mwm_content_width() {
	$GLOBALS['content_width'] = 1240; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
}
add_action( 'after_setup_theme', 'mwm_content_width', 0 );

/**
 * New uploads get WebP sub-sizes; the original stays as "full".
 *
 * @param array $formats Map.
 * @return array
 */
function mwm_output_formats( $formats ) {
	$formats['image/jpeg'] = 'image/webp';
	// Site icon crops stay PNG: apple-touch-icon and some favicon consumers reject WebP.
	if ( ! doing_action( 'wp_ajax_crop-image' ) ) {
		$formats['image/png'] = 'image/webp';
	}
	return $formats;
}
add_filter( 'image_editor_output_format', 'mwm_output_formats' );

/**
 * WebP sub-sizes at 78: visually indistinguishable on field photos, ~20% lighter than core's default.
 *
 * @param int    $quality   Quality.
 * @param string $mime_type Output mime.
 * @return int
 */
function mwm_webp_quality( $quality, $mime_type = '' ) {
	return 'image/webp' === $mime_type ? 78 : $quality;
}
add_filter( 'wp_editor_set_quality', 'mwm_webp_quality', 10, 2 );

/*
 * Core guesses fetchpriority=high for the first large image, which in these layouts is often below the fold
 * on phones. Hero templates set it explicitly where the image really is the LCP.
 */
add_filter( 'wp_min_priority_img_pixels', static fn() => PHP_INT_MAX );
add_filter( 'elementor/image-loading-optimization/min_priority_img_pixels', static fn() => PHP_INT_MAX );

/**
 * Size labels in the media picker.
 *
 * @param array $sizes Sizes.
 * @return array
 */
function mwm_size_names( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'mwm-card'  => __( 'بطاقة MWM', 'mwm' ),
			'mwm-thumb' => __( 'مصغرة MWM', 'mwm' ),
			'mwm-wide'  => __( 'عريضة MWM', 'mwm' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'mwm_size_names' );

/**
 * Body classes.
 *
 * @param array $classes Classes.
 * @return array
 */
function mwm_body_class( $classes ) {
	$classes[] = 'mwm';
	if ( mwm_has_hero() ) {
		$classes[] = 'mwm-has-hero';
	}
	return $classes;
}
add_filter( 'body_class', 'mwm_body_class' );

/**
 * Does the current page open with a hero that sits under a transparent header?
 *
 * @return bool
 */
function mwm_has_hero() {
	if ( is_singular( 'mwm_service' ) ) {
		return true;
	}
	if ( ! is_singular() ) {
		return true;
	}
	$post_id = get_queried_object_id();
	$mode    = get_post_meta( $post_id, 'header_style', true );
	if ( 'solid' === $mode ) {
		return false;
	}
	if ( 'transparent' === $mode ) {
		return true;
	}
	if ( ! mwm_uses_elementor( $post_id ) ) {
		return is_page();
	}
	return in_array( mwm_elementor_first_widget( $post_id ), array( 'mwm-hero', 'mwm-page-hero' ), true );
}

/**
 * First widget type in an Elementor document.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function mwm_elementor_first_widget( $post_id ) {
	$data = json_decode( (string) get_post_meta( $post_id, '_elementor_data', true ), true );
	while ( is_array( $data ) && $data ) {
		$first = reset( $data );
		if ( isset( $first['elType'] ) && 'widget' === $first['elType'] ) {
			return isset( $first['widgetType'] ) ? (string) $first['widgetType'] : '';
		}
		$data = isset( $first['elements'] ) ? $first['elements'] : array();
	}
	return '';
}

/**
 * Remove front-end weight this theme never uses.
 */
function mwm_cleanup_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'mwm_cleanup_head' );

/**
 * Block library CSS is unused (pages are built with Elementor / theme templates).
 */
function mwm_dequeue_block_css() {
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'core-block-supports' ) as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'mwm_dequeue_block_css', 100 );

/**
 * No comments anywhere on this corporate site.
 */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );

/**
 * Core sitemaps: no users, no blog posts.
 */
add_filter(
	'wp_sitemaps_add_provider',
	static function ( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);
add_filter(
	'wp_sitemaps_post_types',
	static function ( $types ) {
		unset( $types['post'] );
		return $types;
	}
);

/**
 * Author archives are off (no author enumeration).
 */
function mwm_disable_author_archives() {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'mwm_disable_author_archives' );

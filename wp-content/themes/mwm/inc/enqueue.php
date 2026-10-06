<?php
/**
 * Front-end assets.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache-busting version from file mtime.
 *
 * @param string $rel Relative path.
 * @return string
 */
function mwm_asset_version( $rel ) {
	$path = MWM_DIR . '/' . $rel;
	return file_exists( $path ) ? (string) filemtime( $path ) : MWM_VERSION;
}

/**
 * A theme stylesheet's contents for inline printing, with relative font URLs made absolute.
 *
 * @param string $rel Relative path.
 * @return string Empty when unreadable (caller falls back to a <link>).
 */
function mwm_inline_css( $rel ) {
	$path = MWM_DIR . '/' . $rel;
	$css  = is_readable( $path ) ? (string) file_get_contents( $path ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	return str_replace( 'url("../', 'url("' . MWM_URI . '/assets/', $css );
}

/**
 * Preload the hero heading font (Arabic subset only).
 */
function mwm_preload_fonts() {
	echo "<script>document.documentElement.classList.add('mwm-js')</script>\n";
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( MWM_URI . '/assets/fonts/alexandria-arabic-wght-normal.woff2' )
	);
}
add_action( 'wp_head', 'mwm_preload_fonts', 1 );

/**
 * Styles and scripts.
 */
function mwm_enqueue_assets() {
	// Small files are inlined: each extra render-blocking request costs more than its bytes on mobile.
	$styles = array(
		'mwm-tokens'   => array( 'assets/css/tokens.css', array(), true ),
		'mwm-base'     => array( 'assets/css/base.css', array( 'mwm-tokens' ), true ),
		'mwm-layout'   => array( 'assets/css/layout.css', array( 'mwm-base' ), false ),
		'mwm-sections' => array( 'assets/css/sections.css', array( 'mwm-layout' ), false ),
		'mwm-motion'   => array( 'assets/css/motion.css', array( 'mwm-sections' ), true ),
	);
	foreach ( $styles as $handle => $style ) {
		$css = $style[2] ? mwm_inline_css( $style[0] ) : '';
		if ( '' === $css ) {
			wp_enqueue_style( $handle, MWM_URI . '/' . $style[0], $style[1], mwm_asset_version( $style[0] ) );
			continue;
		}
		wp_register_style( $handle, false, $style[1], mwm_asset_version( $style[0] ) );
		wp_enqueue_style( $handle );
		wp_add_inline_style( $handle, $css );
	}

	// Kit globals (--e-global-*) must exist on every page, not only Elementor-built ones.
	if ( did_action( 'elementor/loaded' ) ) {
		\Elementor\Plugin::$instance->kits_manager->frontend_before_enqueue_styles();
	}

	wp_enqueue_script(
		'mwm-theme',
		MWM_URI . '/assets/js/theme.js',
		array(),
		mwm_asset_version( 'assets/js/theme.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_localize_script(
		'mwm-theme',
		'mwmData',
		array(
			'ajax' => admin_url( 'admin-post.php' ),
			'i18n' => array(
				'required' => __( 'هذا الحقل مطلوب.', 'mwm' ),
				'phone'    => __( 'اكتب رقم جوال سعودي صحيح مثل 05XXXXXXXX.', 'mwm' ),
				'email'    => __( 'اكتب بريداً إلكترونياً صحيحاً.', 'mwm' ),
				'consent'  => __( 'يلزم الموافقة على معالجة البيانات لإرسال الطلب.', 'mwm' ),
				'sending'  => __( 'جارٍ الإرسال…', 'mwm' ),
				'error'    => __( 'تعذّر إرسال الطلب. حاول مرة أخرى أو تواصل معنا عبر واتساب.', 'mwm' ),
				'prev'     => __( 'السابق', 'mwm' ),
				'next'     => __( 'التالي', 'mwm' ),
				'close'    => __( 'إغلاق', 'mwm' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'mwm_enqueue_assets' );

/**
 * jQuery Migrate only shims pre-3.0 plugin code; Elementor 4 and the theme don't need it.
 *
 * @param WP_Scripts $scripts Registry.
 */
function mwm_drop_jquery_migrate( $scripts ) {
	if ( is_admin() || ! isset( $scripts->registered['jquery'] ) ) {
		return;
	}
	$scripts->registered['jquery']->deps = array_values( array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) ) );
}
add_action( 'wp_default_scripts', 'mwm_drop_jquery_migrate' );

const MWM_HTACCESS_VERSION = 1;

/**
 * Compression + long-lived caching for static assets, kept in its own marked block of the
 * root .htaccess (Apache/LiteSpeed). Assets are versioned by mtime, so a year is safe.
 */
function mwm_write_htaccess() {
	if ( (int) get_option( 'mwm_htaccess' ) >= MWM_HTACCESS_VERSION ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	$file = get_home_path() . '.htaccess';
	if ( file_exists( $file ) ? ! wp_is_writable( $file ) : ! wp_is_writable( dirname( $file ) ) ) {
		return;
	}
	$rules = array(
		'<IfModule mod_deflate.c>',
		'AddOutputFilterByType DEFLATE text/html text/plain text/css text/xml application/xml application/javascript text/javascript application/json application/ld+json image/svg+xml font/ttf',
		'</IfModule>',
		'<IfModule mod_expires.c>',
		'ExpiresActive On',
		'ExpiresByType text/css "access plus 1 year"',
		'ExpiresByType application/javascript "access plus 1 year"',
		'ExpiresByType text/javascript "access plus 1 year"',
		'ExpiresByType font/woff2 "access plus 1 year"',
		'ExpiresByType image/webp "access plus 1 year"',
		'ExpiresByType image/avif "access plus 1 year"',
		'ExpiresByType image/jpeg "access plus 1 year"',
		'ExpiresByType image/png "access plus 1 year"',
		'ExpiresByType image/svg+xml "access plus 1 year"',
		'ExpiresByType image/x-icon "access plus 1 year"',
		'</IfModule>',
		'<IfModule mod_headers.c>',
		'<FilesMatch "\.(css|js|woff2|webp|avif|jpe?g|png|svg|ico)$">',
		'Header set Cache-Control "public, max-age=31536000, immutable"',
		'</FilesMatch>',
		'</IfModule>',
	);
	if ( insert_with_markers( $file, 'MWM Performance', $rules ) ) {
		update_option( 'mwm_htaccess', MWM_HTACCESS_VERSION );
	}
}
add_action( 'after_switch_theme', 'mwm_write_htaccess' );
add_action( 'admin_init', 'mwm_write_htaccess' );

/**
 * Editor preview gets the same styles.
 */
function mwm_elementor_preview_styles() {
	wp_enqueue_style( 'mwm-editor-preview', MWM_URI . '/assets/css/editor-preview.css', array( 'mwm-sections' ), mwm_asset_version( 'assets/css/editor-preview.css' ) );
}
add_action( 'elementor/preview/enqueue_styles', 'mwm_elementor_preview_styles' );

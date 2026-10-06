<?php
/**
 * MWM theme bootstrap.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

define( 'MWM_VERSION', '1.0.0' );
define( 'MWM_DIR', get_template_directory() );
define( 'MWM_URI', get_template_directory_uri() );

$mwm_modules = array(
	'helpers',
	'icons',
	'setup',
	'settings',
	'post-types',
	'acf',
	'gallery-metabox',
	'data',
	'enqueue',
	'elementor-config',
	'elementor',
	'elementor-layouts',
	'leads',
	'chrome',
	'seo',
	'security',
);

foreach ( $mwm_modules as $mwm_module ) {
	require_once MWM_DIR . '/inc/' . $mwm_module . '.php';
}
unset( $mwm_modules, $mwm_module );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once MWM_DIR . '/inc/cli.php';
}

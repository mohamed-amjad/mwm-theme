<?php
/**
 * Light hardening that doesn't interfere with Elementor or the editor.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'xmlrpc_enabled', '__return_false' );

add_filter(
	'wp_headers',
	static function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

/**
 * Basic security headers on the front end.
 */
function mwm_security_headers() {
	if ( is_admin() || headers_sent() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()' );
}
add_action( 'send_headers', 'mwm_security_headers' );

/**
 * Hide the user list from the public REST API (no author enumeration).
 *
 * @param array $endpoints Endpoints.
 * @return array
 */
function mwm_rest_users( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'mwm_rest_users' );

/**
 * Generic login error (no username hints).
 *
 * @return string
 */
function mwm_login_error() {
	return __( 'بيانات الدخول غير صحيحة.', 'mwm' );
}
add_filter( 'login_errors', 'mwm_login_error' );

/**
 * ?author=N enumeration on the front end.
 */
function mwm_block_author_query() {
	if ( ! is_admin() && isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'mwm_block_author_query', 1 );

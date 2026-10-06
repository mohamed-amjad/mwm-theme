<?php
/**
 * Data access: read-only, cached per request.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Published posts of a type in menu order.
 *
 * @param string $type Post type.
 * @param array  $args Extra query args.
 * @return WP_Post[]
 */
function mwm_records( $type, array $args = array() ) {
	static $cache = array();
	$key = $type . md5( wp_json_encode( $args ) );
	if ( ! isset( $cache[ $key ] ) ) {
		$cache[ $key ] = get_posts(
			array_merge(
				array(
					'post_type'        => $type,
					'post_status'      => 'publish',
					'posts_per_page'   => 100,
					'orderby'          => array(
						'menu_order' => 'ASC',
						'date'       => 'ASC',
					),
					'suppress_filters' => false,
				),
				$args
			)
		);
	}
	return $cache[ $key ];
}

/**
 * Services.
 *
 * @return WP_Post[]
 */
function mwm_get_services() {
	return mwm_records( 'mwm_service' );
}

/**
 * Items of one service (or all).
 *
 * @param int $service_id Service ID (0 = all).
 * @return WP_Post[]
 */
function mwm_get_service_items( $service_id = 0 ) {
	$args = array();
	if ( $service_id ) {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'   => 'service',
				'value' => (int) $service_id,
			),
		);
	}
	return mwm_records( 'mwm_service_item', $args );
}

/**
 * Branches.
 *
 * @return WP_Post[]
 */
function mwm_get_branches() {
	return mwm_records( 'mwm_branch' );
}

/**
 * Licenses.
 *
 * @return WP_Post[]
 */
function mwm_get_licenses() {
	return mwm_records( 'mwm_license' );
}

/**
 * IDs stored by an ACF relationship field.
 *
 * @param int    $post_id Post.
 * @param string $key     Field.
 * @return int[]
 */
function mwm_relation_ids( $post_id, $key ) {
	$value = get_post_meta( (int) $post_id, $key, true );
	return is_array( $value ) ? array_map( 'intval', $value ) : array();
}

/**
 * Licenses linked to a service.
 *
 * @param int $service_id Service.
 * @return WP_Post[]
 */
function mwm_service_licenses( $service_id ) {
	return array_values(
		array_filter(
			mwm_get_licenses(),
			static fn( $license ) => in_array( (int) $service_id, mwm_relation_ids( $license->ID, 'services' ), true )
		)
	);
}

/**
 * FAQs, optionally limited to those linked to a service.
 *
 * @param int $service_id Service (0 = all).
 * @return WP_Post[]
 */
function mwm_get_faqs( $service_id = 0 ) {
	$faqs = mwm_records( 'mwm_faq' );
	if ( ! $service_id ) {
		return $faqs;
	}
	return array_values( array_filter( $faqs, static fn( $faq ) => in_array( (int) $service_id, mwm_relation_ids( $faq->ID, 'services' ), true ) ) );
}

/**
 * Lines of a textarea as a clean array.
 *
 * @param string $text Text.
 * @return string[]
 */
function mwm_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', (string) $text ) ) ) );
}

/**
 * Card data for a service.
 *
 * @param WP_Post $post Service.
 * @return array
 */
function mwm_service_card( $post ) {
	return array(
		'id'         => $post->ID,
		'title'      => get_the_title( $post ),
		'url'        => get_permalink( $post ),
		'excerpt'    => has_excerpt( $post ) ? get_the_excerpt( $post ) : '',
		'image'      => (int) get_post_thumbnail_id( $post ),
		'icon'       => (string) ( get_post_meta( $post->ID, 'icon', true ) ?: 'leaf' ),
		'kicker'     => (string) get_post_meta( $post->ID, 'kicker', true ),
		'highlights' => mwm_lines( (string) get_post_meta( $post->ID, 'highlights', true ) ),
		'licensed'   => (bool) mwm_service_licenses( $post->ID ),
		'cta_label'  => (string) get_post_meta( $post->ID, 'cta_label', true ),
		'type'       => (string) get_post_meta( $post->ID, 'request_type', true ),
	);
}

/**
 * Request types for the form (id => label).
 *
 * @return array<string,string>
 */
function mwm_request_types() {
	$out = array();
	foreach ( mwm_lines( (string) mwm_setting( 'request_types' ) ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$id    = sanitize_key( $parts[0] );
		if ( '' === $id ) {
			continue;
		}
		$out[ $id ] = $parts[1] ?? $parts[0];
	}
	return $out;
}

/**
 * Quote page URL preset for a service / request type.
 *
 * @param int    $service_id Service.
 * @param string $type       Request type.
 * @return string
 */
function mwm_quote_url( $service_id = 0, $type = '' ) {
	$url = mwm_page_url( 'quote' );
	if ( '' === $url ) {
		return '';
	}
	$args = array();
	if ( $service_id ) {
		$args['service'] = get_post_field( 'post_name', $service_id );
	}
	if ( $type ) {
		$args['type'] = $type;
	}
	return $args ? add_query_arg( $args, $url ) : $url;
}

/**
 * Social links that are filled.
 *
 * @return array<string,string> network => url.
 */
function mwm_socials() {
	$out = array();
	foreach ( array( 'x', 'linkedin', 'instagram', 'snapchat', 'tiktok', 'youtube', 'facebook' ) as $net ) {
		$url = (string) mwm_setting( 'social_' . $net );
		if ( '' !== $url ) {
			$out[ $net ] = $url;
		}
	}
	return $out;
}

/**
 * Image IDs grouped by service for the gallery (only services with photos).
 *
 * @return array<int,array{title:string,slug:string,ids:int[]}>
 */
function mwm_gallery_groups() {
	$groups = array();
	foreach ( mwm_get_services() as $service ) {
		$ids = mwm_gallery_ids( $service->ID );
		if ( $ids ) {
			$groups[ $service->ID ] = array(
				'title' => get_the_title( $service ),
				'slug'  => $service->post_name,
				'ids'   => $ids,
			);
		}
	}
	return $groups;
}

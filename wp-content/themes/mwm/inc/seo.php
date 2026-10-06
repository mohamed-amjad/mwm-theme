<?php
/**
 * Breadcrumbs, meta description, Open Graph, JSON-LD @graph.
 * Meta/OG/Organization back off when Yoast or Rank Math is active; branch LocalBusiness nodes stay.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is a dedicated SEO plugin handling meta?
 *
 * @return bool
 */
function mwm_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * Breadcrumb trail for a post.
 *
 * @param int    $post_id Post.
 * @param string $title   Current title override.
 * @return array<int,array{name:string,url:string}>
 */
function mwm_breadcrumb_trail( $post_id = 0, $title = '' ) {
	$post_id = $post_id ? (int) $post_id : get_queried_object_id();
	$trail   = array(
		array(
			'name' => get_the_title( mwm_page_id( 'home' ) ) ?: __( 'الرئيسية', 'mwm' ),
			'url'  => home_url( '/' ),
		),
	);
	if ( is_404() ) {
		$trail[] = array(
			'name' => __( 'الصفحة غير موجودة', 'mwm' ),
			'url'  => '',
		);
		return $trail;
	}
	if ( ! $post_id || (int) get_option( 'page_on_front' ) === $post_id ) {
		return $trail;
	}
	if ( 'mwm_service' === get_post_type( $post_id ) ) {
		$services = mwm_page_id( 'services' );
		if ( $services ) {
			$trail[] = array(
				'name' => get_the_title( $services ),
				'url'  => (string) get_permalink( $services ),
			);
		}
	}
	foreach ( array_reverse( get_post_ancestors( $post_id ) ) as $ancestor ) {
		$trail[] = array(
			'name' => get_the_title( $ancestor ),
			'url'  => (string) get_permalink( $ancestor ),
		);
	}
	$trail[] = array(
		'name' => '' !== $title ? wp_strip_all_tags( $title ) : get_the_title( $post_id ),
		'url'  => (string) get_permalink( $post_id ),
	);
	return $trail;
}

/**
 * Print breadcrumbs.
 *
 * @param int    $post_id Post.
 * @param string $title   Current title override.
 */
function mwm_breadcrumbs( $post_id = 0, $title = '' ) {
	$trail = mwm_breadcrumb_trail( $post_id, $title );
	if ( count( $trail ) < 2 ) {
		return;
	}
	$last = count( $trail ) - 1;
	echo '<nav class="mwm-crumbs" aria-label="' . esc_attr__( 'مسار التنقل', 'mwm' ) . '"><ol>';
	foreach ( $trail as $i => $crumb ) {
		if ( $i === $last || '' === $crumb['url'] ) {
			echo '<li><span aria-current="page">' . esc_html( $crumb['name'] ) . '</span></li>';
		} else {
			echo '<li><a href="' . esc_url( $crumb['url'] ) . '">' . esc_html( $crumb['name'] ) . '</a></li>';
		}
	}
	echo '</ol></nav>';
}

/**
 * SEO title override.
 *
 * @param array $parts Title parts.
 * @return array
 */
function mwm_document_title( $parts ) {
	if ( is_singular() ) {
		$custom = (string) get_post_meta( get_queried_object_id(), 'seo_title', true );
		if ( '' !== $custom ) {
			$parts['title'] = $custom;
		}
	}
	if ( is_front_page() ) {
		$slogan = (string) mwm_setting( 'slogan' );
		if ( '' !== $slogan ) {
			$parts['tagline'] = $slogan;
		}
	}
	return $parts;
}
add_filter( 'document_title_parts', 'mwm_document_title' );
add_filter( 'document_title_separator', static fn() => '|' );

/**
 * Meta description for the current view.
 *
 * @return string
 */
function mwm_meta_description() {
	$desc = '';
	if ( is_singular() ) {
		$id   = get_queried_object_id();
		$desc = (string) get_post_meta( $id, 'seo_description', true );
		if ( '' === $desc && has_excerpt( $id ) ) {
			$desc = get_the_excerpt( $id );
		}
	}
	if ( '' === $desc && ( is_front_page() || is_home() ) ) {
		$desc = (string) mwm_setting( 'footer_about', get_bloginfo( 'description' ) );
	}
	return trim( wp_html_excerpt( wp_strip_all_tags( $desc ), 300, '…' ) );
}

/**
 * Share image ID for the current view.
 *
 * @return int
 */
function mwm_share_image() {
	if ( is_singular() ) {
		$id    = get_queried_object_id();
		$image = (int) get_post_meta( $id, 'seo_image', true );
		if ( ! $image ) {
			$image = (int) get_post_thumbnail_id( $id );
		}
		if ( $image ) {
			return $image;
		}
	}
	return (int) mwm_setting( 'og_image', 0 );
}

/**
 * Print meta description + Open Graph + Twitter card.
 */
function mwm_print_meta() {
	if ( mwm_seo_plugin_active() || is_404() ) {
		return;
	}
	$desc  = mwm_meta_description();
	$title = wp_get_document_title();
	$url   = is_singular() ? (string) get_permalink( get_queried_object_id() ) : mwm_current_url();
	$image = mwm_share_image();

	if ( '' !== $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( str_replace( '-', '_', get_bloginfo( 'language' ) ) ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_front_page() ? 'website' : 'article' );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	if ( '' !== $desc ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	if ( $image ) {
		$src = wp_get_attachment_image_src( $image, 'full' );
		if ( $src ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $src[0] ) );
			printf( '<meta property="og:image:width" content="%d">' . "\n", (int) $src[1] );
			printf( '<meta property="og:image:height" content="%d">' . "\n", (int) $src[2] );
			$alt = (string) get_post_meta( $image, '_wp_attachment_image_alt', true );
			if ( '' !== $alt ) {
				printf( '<meta property="og:image:alt" content="%s">' . "\n", esc_attr( $alt ) );
			}
		}
	}
	printf( '<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary' );
}
add_action( 'wp_head', 'mwm_print_meta', 2 );

/**
 * Organization node.
 *
 * @return array
 */
function mwm_schema_organization() {
	$name    = trim( (string) mwm_setting( 'company_name', get_bloginfo( 'name' ) ) . ' ' . (string) mwm_setting( 'company_subname' ) );
	$node    = array(
		'@type'      => 'Organization',
		'@id'        => home_url( '/#org' ),
		'name'       => $name,
		'url'        => home_url( '/' ),
		'areaServed' => array(
			'@type' => 'Country',
			'name'  => 'SA',
		),
	);
	$slogan  = (string) mwm_setting( 'slogan' );
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	$email   = sanitize_email( (string) mwm_setting( 'email' ) );
	$phones  = mwm_phones();
	$socials = array_values( mwm_socials() );
	if ( '' !== $slogan ) {
		$node['slogan'] = $slogan;
	}
	if ( $logo_id && wp_get_attachment_url( $logo_id ) ) {
		$node['logo'] = wp_get_attachment_url( $logo_id );
	}
	if ( $email ) {
		$node['email'] = $email;
	}
	if ( $phones ) {
		$node['telephone']    = mwm_phone_e164( $phones[0]['number'] );
		$node['contactPoint'] = array_map(
			static fn( $p ) => array(
				'@type'             => 'ContactPoint',
				'telephone'         => mwm_phone_e164( $p['number'] ),
				'contactType'       => 'customer service',
				'areaServed'        => 'SA',
				'availableLanguage' => array( 'ar' ),
			),
			$phones
		);
	}
	if ( $socials ) {
		$node['sameAs'] = $socials;
	}
	return $node;
}

/**
 * LocalBusiness nodes — only for branches with a real address.
 *
 * @return array[]
 */
function mwm_schema_branches() {
	$nodes = array();
	foreach ( mwm_get_branches() as $branch ) {
		$address = trim( (string) get_post_meta( $branch->ID, 'address', true ) );
		if ( '' === $address ) {
			continue;
		}
		$node  = array(
			'@type'              => 'LocalBusiness',
			'@id'                => home_url( '/#branch-' . $branch->ID ),
			'name'               => trim( (string) mwm_setting( 'company_name', get_bloginfo( 'name' ) ) . ' — ' . get_the_title( $branch ) ),
			'parentOrganization' => array( '@id' => home_url( '/#org' ) ),
			'url'                => mwm_page_url( 'contact' ) ?: home_url( '/' ),
			'address'            => array_filter(
				array(
					'@type'           => 'PostalAddress',
					'streetAddress'   => $address,
					'addressLocality' => get_the_title( $branch ),
					'addressRegion'   => (string) get_post_meta( $branch->ID, 'region', true ),
					'addressCountry'  => 'SA',
				)
			),
		);
		$phone = (string) get_post_meta( $branch->ID, 'phone', true );
		if ( '' === $phone ) {
			$phones = mwm_phones();
			$phone  = $phones ? $phones[0]['number'] : '';
		}
		if ( '' !== $phone ) {
			$node['telephone'] = mwm_phone_e164( $phone );
		}
		$lat = (string) get_post_meta( $branch->ID, 'latitude', true );
		$lng = (string) get_post_meta( $branch->ID, 'longitude', true );
		if ( is_numeric( $lat ) && is_numeric( $lng ) ) {
			$node['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $lat,
				'longitude' => (float) $lng,
			);
		}
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$node['image'] = wp_get_attachment_url( $logo_id );
		}
		$nodes[] = $node;
	}
	return $nodes;
}

/**
 * Print the JSON-LD graph.
 */
function mwm_print_schema() {
	if ( is_404() ) {
		return;
	}
	$graph  = array();
	$plugin = mwm_seo_plugin_active();

	if ( ! $plugin ) {
		$graph[] = mwm_schema_organization();
		$graph[] = array(
			'@type'      => 'WebSite',
			'@id'        => home_url( '/#website' ),
			'url'        => home_url( '/' ),
			'name'       => get_bloginfo( 'name' ),
			'inLanguage' => get_bloginfo( 'language' ),
			'publisher'  => array( '@id' => home_url( '/#org' ) ),
		);
	}
	$contact_id = mwm_page_id( 'contact' );
	if ( is_front_page() || ( $contact_id && is_page( $contact_id ) ) ) {
		$graph = array_merge( $graph, mwm_schema_branches() );
	}

	if ( is_singular( 'mwm_service' ) && ! $plugin ) {
		$id      = get_queried_object_id();
		$service = array(
			'@type'      => 'Service',
			'@id'        => get_permalink( $id ) . '#service',
			'name'       => get_the_title( $id ),
			'url'        => get_permalink( $id ),
			'provider'   => array( '@id' => home_url( '/#org' ) ),
			'areaServed' => array(
				'@type' => 'Country',
				'name'  => 'SA',
			),
		);
		$desc    = mwm_meta_description();
		if ( '' !== $desc ) {
			$service['description'] = $desc;
		}
		$thumb = (int) get_post_thumbnail_id( $id );
		if ( $thumb ) {
			$service['image'] = wp_get_attachment_url( $thumb );
		}
		$items = mwm_get_service_items( $id );
		if ( $items ) {
			$service['hasOfferCatalog'] = array(
				'@type'           => 'OfferCatalog',
				'name'            => get_the_title( $id ),
				'itemListElement' => array_map(
					static fn( $item ) => array(
						'@type'       => 'Offer',
						'itemOffered' => array(
							'@type' => 'Service',
							'name'  => get_the_title( $item ),
						),
					),
					$items
				),
			);
		}
		$graph[] = $service;
	}

	if ( is_singular() && ! is_front_page() && ! $plugin ) {
		$trail = mwm_breadcrumb_trail();
		if ( count( $trail ) > 1 ) {
			$list = array();
			foreach ( $trail as $i => $crumb ) {
				$list[] = array_filter(
					array(
						'@type'    => 'ListItem',
						'position' => $i + 1,
						'name'     => $crumb['name'],
						'item'     => $crumb['url'],
					)
				);
			}
			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $list,
			);
		}
	}

	if ( ! $graph ) {
		return;
	}
	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		)
	);
}
add_action( 'wp_head', 'mwm_print_schema', 30 );

<?php
/**
 * Ready-made Elementor layouts: services, plain pages and the header/footer templates,
 * so "Edit with Elementor" always opens on the real design instead of a blank canvas.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

const MWM_LAYOUTS_VERSION = 3;

/**
 * Is the Elementor editor currently editing a saved template (header/footer)?
 *
 * @return bool
 */
function mwm_editing_template() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return false;
	}
	$document = \Elementor\Plugin::$instance->documents->get_current();
	if ( ! $document ) {
		$post_id  = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$document = $post_id ? \Elementor\Plugin::$instance->documents->get( $post_id ) : null;
	}
	return ! $document || 'elementor_library' === get_post_type( $document->get_main_id() );
}

/**
 * Elementor tree: one full-width container per MWM section widget.
 *
 * @param array  $sections List of [widget slug without "mwm-", settings].
 * @param string $seed     Seed for stable element IDs.
 * @return array
 */
function mwm_layout_tree( array $sections, $seed ) {
	$id   = static fn( $s ) => substr( md5( $seed . '|' . $s ), 0, 7 );
	$tree = array();
	foreach ( array_values( $sections ) as $i => $section ) {
		list( $widget, $settings ) = $section;
		foreach ( $settings as $name => $value ) {
			if ( is_array( $value ) && array_is_list( $value ) && $value && is_array( $value[0] ) && ! isset( $value[0]['id'] ) ) {
				foreach ( $value as $n => $row ) {
					$settings[ $name ][ $n ]['_id'] = $row['_id'] ?? $id( "{$i}{$name}{$n}" );
				}
			}
		}
		$tree[] = array(
			'id'       => $id( "c{$i}" ),
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
					'id'         => $id( "w{$i}" ),
					'elType'     => 'widget',
					'widgetType' => 'mwm-' . $widget,
					'isInner'    => false,
					'settings'   => $settings,
					'elements'   => array(),
				),
			),
		);
	}
	return $tree;
}

/**
 * Store a layout on a post and mark it as built with Elementor.
 *
 * @param int   $post_id  Post.
 * @param array $sections Sections for mwm_layout_tree().
 */
function mwm_save_layout( $post_id, array $sections ) {
	$post_id = (int) $post_id;
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	if ( 'elementor_library' !== get_post_type( $post_id ) ) {
		update_post_meta( $post_id, '_elementor_template_type', 'wp-' . get_post_type( $post_id ) );
		update_post_meta( $post_id, '_wp_page_template', 'default' );
	}
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( mwm_layout_tree( $sections, 'post' . $post_id ) ) ) );
	delete_post_meta( $post_id, '_elementor_css' );
	delete_post_meta( $post_id, '_elementor_element_cache' );
}

/**
 * Media control value for an attachment.
 *
 * @param int $id Attachment ID.
 * @return array
 */
function mwm_layout_media( $id ) {
	$id = (int) $id;
	return array(
		'id'  => $id ? $id : '',
		'url' => $id ? (string) wp_get_attachment_url( $id ) : '',
	);
}

/**
 * Body HTML for the prose widget (blocks and auto-paragraphs resolved once).
 *
 * @param WP_Post $post Post.
 * @return string
 */
function mwm_layout_body( WP_Post $post ) {
	$content = trim( (string) $post->post_content );
	return '' === $content ? '' : trim( wpautop( do_blocks( $content ) ) );
}

/**
 * Gallery repeater rows (one tab per service) from the services' photos.
 *
 * @param WP_Post[] $services   Services.
 * @param bool      $keep_empty Keep a tab even when the service has no photos yet.
 * @return array
 */
function mwm_layout_gallery_groups( array $services, $keep_empty = false ) {
	$rows = array();
	foreach ( $services as $service ) {
		$ids = mwm_gallery_ids( $service->ID );
		if ( $ids || $keep_empty ) {
			$rows[] = array(
				'_id'    => substr( md5( 'gallery-group-' . $service->ID ), 0, 7 ),
				'title'  => get_the_title( $service ),
				'images' => array_map( 'mwm_layout_media', $ids ),
			);
		}
	}
	return $rows;
}

/**
 * Move gallery widgets that read the service photo boxes to tabs edited in Elementor (same photos).
 *
 * @param int $post_id  Post (or autosave revision) with an Elementor layout.
 * @param int $owner_id Post the layout belongs to; defaults to $post_id.
 * @return int Widgets migrated.
 */
function mwm_migrate_gallery_widgets( $post_id, $owner_id = 0 ) {
	$data = json_decode( (string) get_post_meta( $post_id, '_elementor_data', true ), true );
	if ( ! is_array( $data ) ) {
		return 0;
	}
	$owner_id = $owner_id ? $owner_id : $post_id;
	$count    = 0;
	$walk     = static function ( array &$elements ) use ( &$walk, &$count, $owner_id ) {
		foreach ( $elements as &$el ) {
			if ( 'mwm-gallery' === ( $el['widgetType'] ?? '' ) && 'custom' !== ( $el['settings']['service'] ?? '' ) ) {
				$source = (string) ( $el['settings']['service'] ?? '' );
				if ( is_numeric( $source ) && (int) $source > 0 ) {
					$services = array_filter( array( get_post( (int) $source ) ) );
				} elseif ( '' === $source && 'mwm_service' === get_post_type( $owner_id ) ) {
					$services = array( get_post( $owner_id ) );
				} else {
					$services = mwm_get_services();
				}
				$el['settings']['service'] = 'custom';
				$el['settings']['groups']  = mwm_layout_gallery_groups( $services, 1 === count( $services ) );
				++$count;
			}
			if ( ! empty( $el['elements'] ) ) {
				$walk( $el['elements'] );
			}
		}
	};
	$walk( $data );
	if ( $count ) {
		update_metadata( 'post', $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		delete_post_meta( $post_id, '_elementor_css' );
		delete_post_meta( $post_id, '_elementor_element_cache' );
	}
	return $count;
}

/**
 * The same sections single-mwm_service.php composes from the service record.
 *
 * @param WP_Post $post Service.
 * @return array
 */
function mwm_service_layout( WP_Post $post ) {
	$card     = mwm_service_card( $post );
	$licenses = mwm_service_licenses( $post->ID );
	$buttons  = array();
	if ( '' !== $card['cta_label'] ) {
		$buttons[] = array(
			'label' => $card['cta_label'],
			'link'  => 'role:quote',
			'style' => 'primary',
			'icon'  => 'arrow',
		);
	}
	$wa = mwm_phones_for( 'whatsapp' );
	if ( $wa ) {
		$buttons[] = array(
			'label' => __( 'تواصل عبر واتساب', 'mwm' ),
			'link'  => 'wa:' . $wa[0]['slot'],
			'style' => 'ghost-light',
			'icon'  => 'whatsapp',
		);
	}

	$sections = array(
		array(
			'page-hero',
			array(
				'kicker'  => $card['kicker'],
				'title'   => '',
				'text'    => $card['excerpt'],
				'crumbs'  => 'yes',
				'badge'   => $licenses ? get_the_title( $licenses[0] ) : '',
				'buttons' => $buttons,
				'image'   => mwm_layout_media( $card['image'] ),
			),
		),
	);

	$body = mwm_layout_body( $post );
	if ( '' !== $body ) {
		$sections[] = array( 'prose', array( 'content' => $body ) );
	}

	$sections[] = array(
		'service-items',
		array(
			'kicker'     => '',
			'title'      => (string) get_post_meta( $post->ID, 'items_title', true ),
			'text'       => '',
			'service'    => '',
			'layout'     => 'cards',
			'columns'    => '3',
			'show_image' => 'yes',
		),
	);
	$sections[] = array(
		'gallery',
		array(
			'kicker'    => '',
			'title'     => (string) get_post_meta( $post->ID, 'gallery_title', true ),
			'text'      => '',
			'service'   => 'custom',
			'groups'    => mwm_layout_gallery_groups( array( $post ), true ),
			'tabs'      => 'yes',
			'all_label' => __( 'الكل', 'mwm' ),
			'limit'     => 0,
			'columns'   => '4',
			'buttons'   => array(),
			'tone'      => 'tint',
		),
	);
	$sections[] = array(
		'faq',
		array(
			'kicker'     => '',
			'title'      => __( 'الأسئلة الشائعة', 'mwm' ),
			'text'       => '',
			'service'    => '',
			'limit'      => 0,
			'open_first' => 'yes',
			'buttons'    => array(),
		),
	);
	$sections[] = array(
		'services',
		array(
			'kicker'          => '',
			'title'           => __( 'خدمات أخرى', 'mwm' ),
			'text'            => '',
			'exclude_current' => 'yes',
			'columns'         => '3',
			'show_image'      => 'yes',
			'show_highlights' => '',
			'link_label'      => __( 'تفاصيل الخدمة', 'mwm' ),
			'tone'            => 'green',
		),
	);
	return $sections;
}

/**
 * The same sections page.php shows for a page without a layout.
 *
 * @param WP_Post $post Page.
 * @return array
 */
function mwm_page_layout( WP_Post $post ) {
	$sections = array(
		array(
			'page-hero',
			array(
				'kicker'  => '',
				'title'   => '',
				'text'    => has_excerpt( $post ) ? get_the_excerpt( $post ) : '',
				'crumbs'  => 'yes',
				'badge'   => '',
				'buttons' => array(),
				'image'   => mwm_layout_media( get_post_thumbnail_id( $post ) ),
			),
		),
	);
	$body = mwm_layout_body( $post );
	if ( '' !== $body ) {
		$sections[] = array( 'prose', array( 'content' => $body ) );
	}
	return $sections;
}

/**
 * Give a service/page its ready layout if it isn't built with Elementor yet.
 *
 * @param int $post_id Post.
 * @return bool Whether a layout was written.
 */
function mwm_prefill_layout( $post_id ) {
	$post = get_post( (int) $post_id );
	if ( ! $post || ! in_array( $post->post_type, array( 'page', 'mwm_service' ), true ) || mwm_uses_elementor( $post->ID ) ) {
		return false;
	}
	if ( '' !== trim( (string) get_post_meta( $post->ID, '_elementor_data', true ), " \t\n\r[]" ) ) {
		return false;
	}
	mwm_save_layout( $post->ID, 'mwm_service' === $post->post_type ? mwm_service_layout( $post ) : mwm_page_layout( $post ) );
	return true;
}

/**
 * "Edit with Elementor" on a service/page without a layout: write the ready layout first.
 */
function mwm_prefill_on_edit() {
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id && current_user_can( 'edit_post', $post_id ) ) {
		mwm_prefill_layout( $post_id );
	}
}
add_action( 'admin_action_elementor', 'mwm_prefill_on_edit', 5 );

/**
 * Header template widget settings (copied from the current settings).
 *
 * @return array
 */
function mwm_header_layout() {
	return array(
		array(
			'site-header',
			array(
				'phone_slot'      => (string) (int) mwm_setting( 'header_phone_slot', 0 ),
				'cta_label'       => (string) mwm_setting( 'header_cta_label' ),
				'cta_link'        => (string) mwm_setting( 'header_cta_link', 'role:quote' ),
				'drawer_whatsapp' => 'yes',
			),
		),
	);
}

/**
 * Footer template widget settings (copied from the current settings).
 *
 * @return array
 */
function mwm_footer_layout() {
	return array(
		array(
			'site-footer',
			array(
				'about'          => (string) mwm_setting( 'footer_about' ),
				'show_socials'   => 'yes',
				'show_menu'      => 'yes',
				'menu_title'     => '',
				'show_services'  => 'yes',
				'services_title' => (string) mwm_setting( 'footer_services_title', get_post_type_object( 'mwm_service' )->labels->name ),
				'contact_title'  => (string) mwm_setting( 'footer_contact_title', __( 'تواصل معنا', 'mwm' ) ),
				'show_phones'    => 'yes',
				'show_email'     => 'yes',
				'show_coverage'  => 'yes',
				'copyright'      => (string) mwm_setting( 'copyright' ),
				'show_ids'       => 'yes',
				'show_privacy'   => 'yes',
			),
		),
	);
}

/**
 * Create the header/footer Elementor templates (once) and select them in the settings.
 */
function mwm_ensure_site_templates() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	$settings = mwm_settings();
	$titles   = array(
		'header' => __( 'هيدر الموقع', 'mwm' ),
		'footer' => __( 'فوتر الموقع', 'mwm' ),
	);
	$changed  = false;
	foreach ( $titles as $slot => $title ) {
		$current = (int) ( $settings[ $slot . '_template' ] ?? 0 );
		if ( $current && get_post( $current ) ) {
			continue;
		}
		$document = \Elementor\Plugin::$instance->documents->create(
			'section',
			array(
				'post_title'  => $title,
				'post_status' => 'publish',
			)
		);
		if ( is_wp_error( $document ) || ! $document ) {
			continue;
		}
		mwm_save_layout( $document->get_main_id(), 'header' === $slot ? mwm_header_layout() : mwm_footer_layout() );
		$settings[ $slot . '_template' ] = (string) $document->get_main_id();
		$changed                         = true;
	}
	if ( $changed ) {
		update_option( 'mwm_settings', $settings );
	}
}

/**
 * One-time upgrade: services/pages without a layout get one, header/footer move to templates.
 *
 * @param bool $force Run even if already done.
 */
function mwm_apply_layouts( $force = false ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	if ( ! $force && (int) get_option( 'mwm_layouts_version' ) >= MWM_LAYOUTS_VERSION ) {
		return;
	}
	$posts = get_posts(
		array(
			'post_type'      => array( 'page', 'mwm_service' ),
			'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
			'posts_per_page' => -1,
		)
	);
	foreach ( $posts as $post ) {
		mwm_prefill_layout( $post->ID );
		mwm_migrate_gallery_widgets( $post->ID );
		// The editor opens a newer autosave instead of the saved layout, so autosaves need the same move.
		foreach ( wp_get_post_revisions( $post->ID, array( 'posts_per_page' => -1 ) ) as $revision ) {
			if ( wp_is_post_autosave( $revision ) ) {
				mwm_migrate_gallery_widgets( $revision->ID, $post->ID );
			}
		}
	}
	mwm_ensure_site_templates();
	update_option( 'mwm_layouts_version', MWM_LAYOUTS_VERSION );
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}
add_action(
	'admin_init',
	static function () {
		if ( current_user_can( 'manage_options' ) ) {
			mwm_apply_layouts();
		}
	},
	20
);

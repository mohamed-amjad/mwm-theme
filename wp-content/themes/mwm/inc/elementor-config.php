<?php
/**
 * Elementor baseline applied from code + Kit globals bridged to theme tokens.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

const MWM_ELEMENTOR_BASELINE = 4;

/**
 * Elementor options the theme depends on.
 *
 * @return array
 */
function mwm_elementor_options() {
	return array(
		'elementor_disable_color_schemes'           => 'yes',
		'elementor_disable_typography_schemes'      => 'yes',
		'elementor_google_font'                     => '0',
		'elementor_load_fa4_shim'                   => '',
		'elementor_css_print_method'                => 'external',
		'elementor_font_display'                    => 'swap',
		'elementor_unfiltered_files_upload'         => '',
		// Its inline `* { background-image: none !important }` erases the theme's gradient panels until scrolled into view.
		'elementor_lazy_load_background_images'     => '0',
		'elementor_experiment-e_atomic_elements'    => 'inactive',
		'elementor_experiment-e_opt_in_v4'          => 'inactive',
		'elementor_experiment-e_font_icon_svg'      => 'active',
		'elementor_experiment-e_optimized_markup'   => 'active',
		'elementor_experiment-container'            => 'active',
		'elementor_experiment-e_local_google_fonts' => 'inactive',
		'elementor_onboarded'                       => '1',
		'elementor_tracker_notice'                  => '1',
		'elementor_allow_tracking'                  => 'no',
	);
}

/**
 * Brand palette: Kit system colors + custom colors (ids are part of the CSS contract).
 *
 * @return array
 */
function mwm_brand_colors() {
	return array(
		'system' => array(
			array( '_id' => 'primary', 'title' => __( 'أخضر أساسي', 'mwm' ), 'color' => '#28864B' ),
			array( '_id' => 'secondary', 'title' => __( 'أخضر داكن', 'mwm' ), 'color' => '#194223' ),
			array( '_id' => 'text', 'title' => __( 'لون النص', 'mwm' ), 'color' => '#3A423C' ),
			array( '_id' => 'accent', 'title' => __( 'أخضر الورقة (زخرفة فقط)', 'mwm' ), 'color' => '#5BBF75' ),
		),
		'custom' => array(
			array( '_id' => 'mwmmist', 'title' => __( 'أبيض مطفأ', 'mwm' ), 'color' => '#EAEAEA' ),
			array( '_id' => 'mwmsky', 'title' => __( 'سماوي (هواء)', 'mwm' ), 'color' => '#A5D7FC' ),
			array( '_id' => 'mwmroyal', 'title' => __( 'أزرق ملكي', 'mwm' ), 'color' => '#1A2BC3' ),
			array( '_id' => 'mwmwhite', 'title' => __( 'أبيض', 'mwm' ), 'color' => '#FFFFFF' ),
		),
	);
}

/**
 * Kit typography.
 *
 * @return array
 */
function mwm_brand_typography() {
	$t = static function ( $id, $title, $family, $weight ) {
		return array(
			'_id'                    => $id,
			'title'                  => $title,
			'typography_typography'  => 'custom',
			'typography_font_family' => $family,
			'typography_font_weight' => $weight,
		);
	};
	return array(
		$t( 'primary', __( 'العناوين', 'mwm' ), 'Alexandria', '700' ),
		$t( 'secondary', __( 'العناوين الفرعية', 'mwm' ), 'Alexandria', '600' ),
		$t( 'text', __( 'النص', 'mwm' ), 'Readex Pro', '400' ),
		$t( 'accent', __( 'الأزرار والتسميات', 'mwm' ), 'Readex Pro', '600' ),
	);
}

/**
 * Apply options + Kit once per baseline version.
 *
 * @param bool $force Re-apply even if current.
 */
function mwm_apply_elementor_baseline( $force = false ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	if ( ! $force && (int) get_option( 'mwm_elementor_baseline' ) >= MWM_ELEMENTOR_BASELINE ) {
		return;
	}

	foreach ( mwm_elementor_options() as $key => $value ) {
		update_option( $key, $value );
	}

	$support = get_option( 'elementor_cpt_support' );
	$support = is_array( $support ) ? $support : array( 'page' );
	update_option( 'elementor_cpt_support', array_values( array_unique( array_merge( array_diff( $support, array( 'post' ) ), array( 'page', 'mwm_service' ) ) ) ) );

	mwm_sync_kit();
	update_option( 'mwm_elementor_baseline', MWM_ELEMENTOR_BASELINE );
}
add_action( 'after_switch_theme', 'mwm_apply_elementor_baseline' );
add_action(
	'admin_init',
	static function () {
		mwm_apply_elementor_baseline();
	}
);

/**
 * Write brand colors, fonts, container width and breakpoints into the active Kit.
 * Only fills globals that are missing, so client edits survive.
 *
 * @param bool $overwrite Replace existing globals.
 */
function mwm_sync_kit( $overwrite = false ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	$plugin = \Elementor\Plugin::$instance;
	$kit    = $plugin->kits_manager->get_active_kit();
	if ( ! $kit || ! $kit->get_id() ) {
		$plugin->kits_manager->create_default();
		$kit = $plugin->kits_manager->get_active_kit();
	}
	if ( ! $kit || ! $kit->get_id() ) {
		return;
	}

	$current = $kit->get_meta( '_elementor_page_settings' );
	$current = is_array( $current ) ? $current : array();
	$brand   = mwm_brand_colors();

	$merge = static function ( array $existing, array $defaults ) use ( $overwrite ) {
		if ( $overwrite || ! $existing ) {
			return $defaults;
		}
		$ids = wp_list_pluck( $existing, '_id' );
		foreach ( $defaults as $item ) {
			if ( ! in_array( $item['_id'], $ids, true ) ) {
				$existing[] = $item;
			}
		}
		return $existing;
	};

	$settings = $current;

	$settings['system_colors']     = $overwrite || empty( $current['__mwm_seeded'] ) ? $brand['system'] : $current['system_colors'];
	$settings['custom_colors']     = $merge( $current['custom_colors'] ?? array(), $brand['custom'] );
	$settings['system_typography'] = $overwrite || empty( $current['__mwm_seeded'] ) ? mwm_brand_typography() : $current['system_typography'];
	$settings['custom_typography'] = $current['custom_typography'] ?? array();

	$settings['container_width']        = array( 'unit' => 'px', 'size' => 1240, 'sizes' => array() );
	$settings['space_between_widgets']  = array( 'column' => '0', 'row' => '0', 'isLinked' => true, 'unit' => 'px', 'size' => 0 );
	$settings['container_padding']      = array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true );
	$settings['page_title_selector']    = 'h1.entry-title';
	$settings['default_generic_fonts']  = 'Tahoma, sans-serif';
	$settings['active_breakpoints']     = array( 'viewport_mobile', 'viewport_tablet', 'viewport_laptop' );
	$settings['viewport_mobile']        = 767;
	$settings['viewport_tablet']        = 1024;
	$settings['viewport_laptop']        = 1366;
	$settings['viewport_md']            = 768;
	$settings['viewport_lg']            = 1025;
	$settings['__mwm_seeded']           = 'yes';

	$kit->update_meta( '_elementor_page_settings', $settings );
	$plugin->files_manager->clear_cache();
}

/**
 * Self-hosted families appear in Elementor's font picker without Google loading.
 *
 * @param array $fonts Fonts.
 * @return array
 */
function mwm_elementor_fonts( $fonts ) {
	return array_replace(
		$fonts,
		array(
			'Alexandria' => 'system',
			'Readex Pro' => 'system',
		)
	);
}
add_filter( 'elementor/fonts/additional_fonts', 'mwm_elementor_fonts' );

add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

/**
 * Don't let Elementor print its own Font Awesome / eicons on the front end unless a native widget needs them.
 */
function mwm_elementor_dequeue_icons() {
	if ( mwm_is_editor() ) {
		return;
	}
	wp_dequeue_style( 'elementor-icons' );
}
add_action( 'elementor/frontend/after_enqueue_styles', 'mwm_elementor_dequeue_icons', 20 );

/**
 * Theme locations hook (harmless in Free).
 *
 * @param object $manager Locations manager.
 */
function mwm_elementor_locations( $manager ) {
	$manager->register_location( 'header' );
	$manager->register_location( 'footer' );
}
add_action( 'elementor/theme/register_locations', 'mwm_elementor_locations' );

<?php
/**
 * Elementor integration: "MWM" widget category and widget registration.
 * Every widget renders the same template part the PHP fallback uses.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the MWM category and move it to the top of the panel
 * (Elementor has no ordering API, so the private list is reordered in scope).
 *
 * @param \Elementor\Elements_Manager $manager Manager.
 */
function mwm_elementor_category( $manager ) {
	$manager->add_category(
		'mwm',
		array(
			'title' => __( 'أقسام MWM', 'mwm' ),
			'icon'  => 'eicon-leaf',
		)
	);
	$reorder = function () {
		if ( is_array( $this->categories ) && isset( $this->categories['mwm'] ) ) {
			$this->categories = array( 'mwm' => $this->categories['mwm'] ) + $this->categories;
		}
	};
	\Closure::bind( $reorder, $manager, get_class( $manager ) )();
}
add_action( 'elementor/elements/categories_registered', 'mwm_elementor_category' );

/**
 * Widget class map: class => file slug.
 *
 * @return array<string,string>
 */
function mwm_elementor_widget_classes() {
	return array(
		'Mwm_Widget_Hero'          => 'hero',
		'Mwm_Widget_Page_Hero'     => 'page-hero',
		'Mwm_Widget_Text_Media'    => 'text-media',
		'Mwm_Widget_Services'      => 'services',
		'Mwm_Widget_Service_Items' => 'service-items',
		'Mwm_Widget_Features'      => 'features',
		'Mwm_Widget_Process'       => 'process',
		'Mwm_Widget_Gallery'       => 'gallery',
		'Mwm_Widget_Licenses'      => 'licenses',
		'Mwm_Widget_Branches'      => 'branches',
		'Mwm_Widget_Faq'           => 'faq',
		'Mwm_Widget_Cta'           => 'cta',
		'Mwm_Widget_Contact'       => 'contact',
		'Mwm_Widget_Quote_Form'    => 'quote-form',
		'Mwm_Widget_Prose'         => 'prose',
		'Mwm_Widget_Site_Header'   => 'site-header',
		'Mwm_Widget_Site_Footer'   => 'site-footer',
	);
}

/**
 * Register widgets.
 *
 * @param \Elementor\Widgets_Manager $manager Manager.
 */
function mwm_elementor_register_widgets( $manager ) {
	require_once MWM_DIR . '/inc/elementor/class-mwm-widget.php';
	require_once MWM_DIR . '/inc/elementor/widgets.php';
	foreach ( array_keys( mwm_elementor_widget_classes() ) as $class ) {
		if ( class_exists( $class ) ) {
			$manager->register( new $class() );
		}
	}
}
add_action( 'elementor/widgets/register', 'mwm_elementor_register_widgets' );

/**
 * Core widgets that render without Elementor's frontend JS.
 *
 * @return string[]
 */
function mwm_elementor_js_free_widgets() {
	return (array) apply_filters(
		'mwm_elementor_js_free_widgets',
		array( 'heading', 'text-editor', 'button', 'divider', 'spacer', 'icon', 'icon-box', 'image-box', 'icon-list', 'html', 'shortcode', 'google_maps', 'social-icons', 'star-rating', 'image' )
	);
}

/**
 * Whether an Elementor element tree can run without elementor-frontend.js
 * (MWM widgets carry their own JS; animations, background video/slideshow,
 * lightbox links and any other widget keep Elementor's runtime).
 *
 * @param array $elements Elements data.
 * @return bool
 */
function mwm_elementor_tree_is_js_free( array $elements ) {
	$free = mwm_elementor_js_free_widgets();
	foreach ( $elements as $el ) {
		$s = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();
		foreach ( array( '_animation', 'animation' ) as $key ) {
			if ( ! empty( $s[ $key ] ) && 'none' !== $s[ $key ] ) {
				return false;
			}
		}
		if ( in_array( $s['background_background'] ?? '', array( 'video', 'slideshow' ), true ) || in_array( $s['background_overlay_background'] ?? '', array( 'video', 'slideshow' ), true ) ) {
			return false;
		}
		foreach ( array_keys( $s ) as $key ) {
			if ( 0 === strpos( (string) $key, 'motion_fx' ) || 0 === strpos( (string) $key, 'sticky' ) ) {
				return false;
			}
		}
		$type = $el['elType'] ?? '';
		if ( 'widget' === $type ) {
			$widget = (string) ( $el['widgetType'] ?? '' );
			if ( 0 !== strpos( $widget, 'mwm-' ) && ! in_array( $widget, $free, true ) ) {
				return false;
			}
			if ( 'image' === $widget && 'file' === ( $s['link_to'] ?? '' ) ) {
				return false;
			}
		} elseif ( ! in_array( $type, array( 'container', 'section', 'column' ), true ) ) {
			return false;
		}
		if ( ! empty( $el['elements'] ) && ! mwm_elementor_tree_is_js_free( (array) $el['elements'] ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Drop elementor-frontend.js (and with it jQuery + the Elementor runtime, ~200 KB)
 * on pages built only from JS-free elements. Runs after Elementor's wp_footer
 * enqueue and before footer scripts print.
 */
function mwm_elementor_maybe_drop_frontend_js() {
	static $drop = null;
	if ( null === $drop ) {
		$drop = false;
		if ( did_action( 'elementor/loaded' ) && is_singular() && ! is_admin_bar_showing() ) {
			$plugin   = \Elementor\Plugin::$instance;
			$document = $plugin->preview->is_preview_mode() || $plugin->editor->is_edit_mode() ? null : $plugin->documents->get( get_queried_object_id() );
			$drop     = $document && $document->is_built_with_elementor() && mwm_elementor_tree_is_js_free( (array) $document->get_elements_data() );
		}
	}
	if ( $drop ) {
		wp_dequeue_script( 'elementor-frontend' );
	}
}
// Head pass stops jQuery printing as a header dependency; footer pass catches render-time enqueues.
add_action( 'wp_print_scripts', 'mwm_elementor_maybe_drop_frontend_js', 0 );
add_action( 'wp_footer', 'mwm_elementor_maybe_drop_frontend_js', 19 );

/**
 * Editor panel tweaks: brand the MWM category icon color.
 */
function mwm_elementor_editor_css() {
	wp_add_inline_style(
		'elementor-editor',
		'#elementor-panel-category-mwm .elementor-panel-category-title{color:#28864B;font-weight:700}#elementor-panel-category-mwm .elementor-element .icon{color:#28864B}'
	);
}
add_action( 'elementor/editor/after_enqueue_styles', 'mwm_elementor_editor_css' );

<?php
/**
 * Content model: services, service items, branches, licenses, FAQs, requests.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register post types.
 */
function mwm_register_post_types() {
	$admin_only = array(
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_nav_menus'   => false,
		'show_in_rest'        => false,
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
	);

	register_post_type(
		'mwm_service',
		array(
			'labels'        => array(
				'name'          => __( 'الخدمات', 'mwm' ),
				'singular_name' => __( 'خدمة', 'mwm' ),
				'add_new'       => __( 'إضافة خدمة', 'mwm' ),
				'add_new_item'  => __( 'إضافة خدمة جديدة', 'mwm' ),
				'edit_item'     => __( 'تعديل الخدمة', 'mwm' ),
				'all_items'     => __( 'كل الخدمات', 'mwm' ),
				'menu_name'     => __( 'الخدمات', 'mwm' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'has_archive'   => false,
			'hierarchical'  => false,
			'menu_position' => 4,
			'menu_icon'     => 'dashicons-admin-tools',
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'elementor' ),
			'rewrite'       => array(
				'slug'       => 'services',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'mwm_service_item',
		array_merge(
			$admin_only,
			array(
				'labels'       => array(
					'name'          => __( 'بنود الخدمات', 'mwm' ),
					'singular_name' => __( 'بند خدمة', 'mwm' ),
					'add_new'       => __( 'إضافة بند', 'mwm' ),
					'add_new_item'  => __( 'إضافة بند خدمة', 'mwm' ),
					'edit_item'     => __( 'تعديل البند', 'mwm' ),
					'all_items'     => __( 'بنود الخدمات', 'mwm' ),
				),
				'show_in_menu' => 'edit.php?post_type=mwm_service',
				'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'mwm_branch',
		array_merge(
			$admin_only,
			array(
				'labels'        => array(
					'name'          => __( 'الفروع', 'mwm' ),
					'singular_name' => __( 'فرع', 'mwm' ),
					'add_new'       => __( 'إضافة فرع', 'mwm' ),
					'add_new_item'  => __( 'إضافة فرع جديد', 'mwm' ),
					'edit_item'     => __( 'تعديل الفرع', 'mwm' ),
				),
				'menu_position' => 5,
				'menu_icon'     => 'dashicons-location',
				'supports'      => array( 'title', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'mwm_license',
		array_merge(
			$admin_only,
			array(
				'labels'        => array(
					'name'          => __( 'التراخيص', 'mwm' ),
					'singular_name' => __( 'ترخيص', 'mwm' ),
					'add_new'       => __( 'إضافة ترخيص', 'mwm' ),
					'add_new_item'  => __( 'إضافة ترخيص جديد', 'mwm' ),
					'edit_item'     => __( 'تعديل الترخيص', 'mwm' ),
				),
				'menu_position' => 6,
				'menu_icon'     => 'dashicons-awards',
				'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'mwm_faq',
		array_merge(
			$admin_only,
			array(
				'labels'        => array(
					'name'          => __( 'الأسئلة الشائعة', 'mwm' ),
					'singular_name' => __( 'سؤال', 'mwm' ),
					'add_new'       => __( 'إضافة سؤال', 'mwm' ),
					'add_new_item'  => __( 'إضافة سؤال جديد', 'mwm' ),
					'edit_item'     => __( 'تعديل السؤال', 'mwm' ),
				),
				'menu_position' => 7,
				'menu_icon'     => 'dashicons-editor-help',
				'supports'      => array( 'title', 'editor', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'mwm_request',
		array_merge(
			$admin_only,
			array(
				'labels'          => array(
					'name'          => __( 'طلبات العملاء', 'mwm' ),
					'singular_name' => __( 'طلب', 'mwm' ),
					'edit_item'     => __( 'تفاصيل الطلب', 'mwm' ),
					'all_items'     => __( 'كل الطلبات', 'mwm' ),
					'not_found'     => __( 'لا توجد طلبات بعد.', 'mwm' ),
				),
				'menu_position'   => 2,
				'menu_icon'       => 'dashicons-email-alt',
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		)
	);
}
add_action( 'init', 'mwm_register_post_types' );

/**
 * Flush rewrites once after the theme is switched on.
 */
function mwm_flush_rewrites() {
	mwm_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'mwm_flush_rewrites' );

/**
 * Admin lists ordered by menu_order like the site.
 *
 * @param WP_Query $query Query.
 */
function mwm_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$type = $query->get( 'post_type' );
	if ( in_array( $type, array( 'mwm_service', 'mwm_service_item', 'mwm_branch', 'mwm_license', 'mwm_faq' ), true ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
}
add_action( 'pre_get_posts', 'mwm_admin_order' );

/**
 * Service column on the service-items list.
 *
 * @param array $cols Columns.
 * @return array
 */
function mwm_service_item_columns( $cols ) {
	$out = array();
	foreach ( $cols as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'title' === $key ) {
			$out['mwm_service'] = __( 'الخدمة', 'mwm' );
			$out['menu_order']  = __( 'الترتيب', 'mwm' );
		}
	}
	return $out;
}
add_filter( 'manage_mwm_service_item_posts_columns', 'mwm_service_item_columns' );

/**
 * Column values.
 *
 * @param string $col     Column.
 * @param int    $post_id Post.
 */
function mwm_service_item_column_value( $col, $post_id ) {
	if ( 'mwm_service' === $col ) {
		$service = (int) get_post_meta( $post_id, 'service', true );
		echo $service ? esc_html( get_the_title( $service ) ) : '—';
	} elseif ( 'menu_order' === $col ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}
add_action( 'manage_mwm_service_item_posts_custom_column', 'mwm_service_item_column_value', 10, 2 );

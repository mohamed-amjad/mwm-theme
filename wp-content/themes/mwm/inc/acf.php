<?php
/**
 * ACF Free field groups, registered in PHP under stable keys.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field helper.
 *
 * @param string $group Group slug.
 * @param string $name  Field name (meta key).
 * @param string $label Label.
 * @param string $type  ACF type.
 * @param array  $extra Extra args.
 * @return array
 */
function mwm_acf_field( $group, $name, $label, $type = 'text', array $extra = array() ) {
	return array_merge(
		array(
			'key'   => 'field_mwm_' . $group . '_' . $name,
			'name'  => $name,
			'label' => $label,
			'type'  => $type,
		),
		$extra
	);
}

/**
 * Location rule for a post type.
 *
 * @param string $type Post type.
 * @return array
 */
function mwm_acf_on( $type ) {
	return array(
		array(
			array(
				'param'    => 'post_type',
				'operator' => '==',
				'value'    => $type,
			),
		),
	);
}

/**
 * Register groups.
 */
function mwm_acf_register() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$icons = mwm_icon_choices();

	acf_add_local_field_group(
		array(
			'key'      => 'group_mwm_service',
			'title'    => __( 'بيانات الخدمة', 'mwm' ),
			'location' => mwm_acf_on( 'mwm_service' ),
			'position' => 'normal',
			'fields'   => array(
				mwm_acf_field( 'service', 'icon', __( 'الأيقونة', 'mwm' ), 'select', array( 'choices' => $icons, 'default_value' => 'leaf', 'ui' => 0 ) ),
				mwm_acf_field( 'service', 'kicker', __( 'العنوان الصغير فوق اسم الخدمة', 'mwm' ) ),
				mwm_acf_field( 'service', 'highlights', __( 'أبرز النقاط (سطر لكل نقطة)', 'mwm' ), 'textarea', array( 'rows' => 5, 'instructions' => __( 'تظهر في بطاقة الخدمة.', 'mwm' ) ) ),
				mwm_acf_field( 'service', 'cta_label', __( 'نص زر طلب الخدمة', 'mwm' ) ),
				mwm_acf_field( 'service', 'request_type', __( 'نوع الطلب المختار تلقائياً في النموذج', 'mwm' ), 'text', array( 'instructions' => __( 'معرّف من «أنواع الطلب» في إعدادات الموقع، مثل: quote', 'mwm' ) ) ),
				mwm_acf_field( 'service', 'items_title', __( 'عنوان قسم البنود في صفحة الخدمة', 'mwm' ) ),
				mwm_acf_field( 'service', 'gallery_title', __( 'عنوان معرض الصور في صفحة الخدمة', 'mwm' ) ),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_mwm_service_item',
			'title'    => __( 'بيانات البند', 'mwm' ),
			'location' => mwm_acf_on( 'mwm_service_item' ),
			'position' => 'side',
			'fields'   => array(
				mwm_acf_field( 'service_item', 'service', __( 'الخدمة', 'mwm' ), 'post_object', array( 'post_type' => array( 'mwm_service' ), 'return_format' => 'id', 'required' => 1, 'ui' => 1 ) ),
				mwm_acf_field( 'service_item', 'icon', __( 'الأيقونة', 'mwm' ), 'select', array( 'choices' => $icons, 'default_value' => 'check' ) ),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_mwm_branch',
			'title'    => __( 'بيانات الفرع', 'mwm' ),
			'location' => mwm_acf_on( 'mwm_branch' ),
			'fields'   => array(
				mwm_acf_field( 'branch', 'region', __( 'المنطقة', 'mwm' ) ),
				mwm_acf_field( 'branch', 'address', __( 'العنوان التفصيلي', 'mwm' ), 'textarea', array( 'rows' => 2, 'instructions' => __( 'اتركه فارغاً إذا لم يتوفر — لن يظهر شيء.', 'mwm' ) ) ),
				mwm_acf_field( 'branch', 'phone', __( 'هاتف الفرع', 'mwm' ) ),
				mwm_acf_field( 'branch', 'email', __( 'بريد الفرع', 'mwm' ), 'email' ),
				mwm_acf_field( 'branch', 'hours', __( 'أوقات العمل', 'mwm' ) ),
				mwm_acf_field( 'branch', 'map_url', __( 'رابط الموقع على خرائط Google', 'mwm' ), 'url' ),
				mwm_acf_field( 'branch', 'latitude', __( 'خط العرض (اختياري)', 'mwm' ) ),
				mwm_acf_field( 'branch', 'longitude', __( 'خط الطول (اختياري)', 'mwm' ) ),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_mwm_license',
			'title'    => __( 'بيانات الترخيص', 'mwm' ),
			'location' => mwm_acf_on( 'mwm_license' ),
			'fields'   => array(
				mwm_acf_field( 'license', 'issuer', __( 'الجهة المانحة', 'mwm' ) ),
				mwm_acf_field( 'license', 'field', __( 'مجال الترخيص', 'mwm' ) ),
				mwm_acf_field( 'license', 'number', __( 'رقم الترخيص', 'mwm' ) ),
				mwm_acf_field( 'license', 'issue_date', __( 'تاريخ الإصدار', 'mwm' ), 'date_picker', array( 'display_format' => 'Y/m/d', 'return_format' => 'Ymd' ) ),
				mwm_acf_field( 'license', 'expiry_date', __( 'تاريخ الانتهاء', 'mwm' ), 'date_picker', array( 'display_format' => 'Y/m/d', 'return_format' => 'Ymd' ) ),
				mwm_acf_field( 'license', 'verify_url', __( 'رابط التحقق من الترخيص', 'mwm' ), 'url' ),
				mwm_acf_field( 'license', 'document', __( 'صورة / ملف الترخيص', 'mwm' ), 'file', array( 'return_format' => 'id' ) ),
				mwm_acf_field( 'license', 'services', __( 'الخدمات المرخّصة', 'mwm' ), 'relationship', array( 'post_type' => array( 'mwm_service' ), 'return_format' => 'id', 'filters' => array(), 'instructions' => __( 'تظهر شارة «مرخّص» على هذه الخدمات فقط.', 'mwm' ) ) ),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_mwm_faq',
			'title'    => __( 'ربط السؤال', 'mwm' ),
			'location' => mwm_acf_on( 'mwm_faq' ),
			'position' => 'side',
			'fields'   => array(
				mwm_acf_field( 'faq', 'services', __( 'يظهر في صفحات الخدمات', 'mwm' ), 'relationship', array( 'post_type' => array( 'mwm_service' ), 'return_format' => 'id', 'filters' => array() ) ),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_mwm_seo',
			'title'    => __( 'المشاركة ومحركات البحث', 'mwm' ),
			'location' => array_merge( mwm_acf_on( 'page' ), mwm_acf_on( 'mwm_service' ) ),
			'position' => 'side',
			'fields'   => array(
				mwm_acf_field( 'seo', 'seo_title', __( 'عنوان مخصص', 'mwm' ) ),
				mwm_acf_field( 'seo', 'seo_description', __( 'وصف مختصر (150 حرفاً تقريباً)', 'mwm' ), 'textarea', array( 'rows' => 3 ) ),
				mwm_acf_field( 'seo', 'seo_image', __( 'صورة المشاركة', 'mwm' ), 'image', array( 'return_format' => 'id', 'preview_size' => 'thumbnail' ) ),
				mwm_acf_field(
					'seo',
					'header_style',
					__( 'شكل الهيدر', 'mwm' ),
					'select',
					array(
						'choices'       => array(
							''            => __( 'تلقائي', 'mwm' ),
							'transparent' => __( 'شفاف فوق الغلاف', 'mwm' ),
							'solid'       => __( 'ثابت أبيض', 'mwm' ),
						),
						'default_value' => '',
						'allow_null'    => 0,
					)
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'mwm_acf_register' );

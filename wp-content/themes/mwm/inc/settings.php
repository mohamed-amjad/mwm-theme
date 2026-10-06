<?php
/**
 * «إعدادات الموقع» — one Settings API screen for every company-wide value.
 * Stored in a single option `mwm_settings`.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings schema: tab => [ label, fields[] ].
 *
 * @return array
 */
function mwm_settings_schema() {
	$phone_fields = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$phone_fields[ "phone_{$i}_label" ]    = array(
			/* translators: %d: phone slot. */
			'label'       => sprintf( __( 'الرقم %d — التسمية', 'mwm' ), $i ),
			'type'        => 'text',
			'description' => __( 'تظهر للزائر عند اختيار الرقم، مثل: المبيعات أو الدعم الفني.', 'mwm' ),
		);
		$phone_fields[ "phone_{$i}_number" ]   = array(
			/* translators: %d: phone slot. */
			'label'       => sprintf( __( 'الرقم %d', 'mwm' ), $i ),
			'type'        => 'phone',
			'description' => __( 'اكتبه بالصيغة المحلية مثل 0573825004 — الموقع يحوّله تلقائياً إلى +966 في روابط الاتصال والواتساب.', 'mwm' ),
		);
		$phone_fields[ "phone_{$i}_whatsapp" ] = array(
			/* translators: %d: phone slot. */
			'label' => sprintf( __( 'الرقم %d متاح على واتساب', 'mwm' ), $i ),
			'type'  => 'checkbox',
		);
		$phone_fields[ "phone_{$i}_call" ]     = array(
			/* translators: %d: phone slot. */
			'label' => sprintf( __( 'الرقم %d يظهر في أزرار الاتصال', 'mwm' ), $i ),
			'type'  => 'checkbox',
		);
	}

	$role_fields = array();
	foreach ( mwm_page_roles() as $role => $label ) {
		if ( 'home' === $role ) {
			continue;
		}
		$role_fields[ 'role_' . $role ] = array(
			'label' => $label,
			'type'  => 'page',
		);
	}

	return array(
		'company'  => array(
			'label'  => __( 'بيانات المؤسسة', 'mwm' ),
			'fields' => array(
				'company_name'    => array(
					'label'       => __( 'الاسم الظاهر بجوار الشعار', 'mwm' ),
					'type'        => 'text',
					'description' => __( 'السطر الأول بجوار الشعار في الهيدر والفوتر.', 'mwm' ),
				),
				'company_subname' => array(
					'label' => __( 'السطر الثاني بجوار الشعار', 'mwm' ),
					'type'  => 'text',
				),
				'slogan'          => array(
					'label' => __( 'الشعار اللفظي (السلوجان)', 'mwm' ),
					'type'  => 'text',
				),
				'footer_about'    => array(
					'label' => __( 'نبذة الفوتر', 'mwm' ),
					'type'  => 'textarea',
				),
				'coverage'        => array(
					'label'       => __( 'نطاق التغطية', 'mwm' ),
					'type'        => 'text',
					'description' => __( 'سطر قصير يظهر في الفوتر وصفحة الفروع.', 'mwm' ),
				),
				'cr_number'       => array(
					'label' => __( 'رقم السجل التجاري', 'mwm' ),
					'type'  => 'text',
				),
				'unified_number'  => array(
					'label' => __( 'الرقم الموحد للمنشأة (700)', 'mwm' ),
					'type'  => 'text',
				),
				'vat_number'      => array(
					'label' => __( 'الرقم الضريبي', 'mwm' ),
					'type'  => 'text',
				),
				'profile_pdf'     => array(
					'label'       => __( 'ملف تعريف الشركة (PDF)', 'mwm' ),
					'type'        => 'media',
					'media'       => 'application/pdf',
					'description' => __( 'يظهر زر «تحميل ملف الشركة» فقط عند اختيار ملف.', 'mwm' ),
				),
				'copyright'       => array(
					'label'       => __( 'نص حقوق النشر', 'mwm' ),
					'type'        => 'text',
					'description' => __( 'استخدم {year} لإظهار السنة الحالية.', 'mwm' ),
				),
			),
		),
		'contact'  => array(
			'label'  => __( 'التواصل', 'mwm' ),
			'fields' => array_merge(
				$phone_fields,
				array(
					'email'         => array(
						'label' => __( 'البريد الإلكتروني', 'mwm' ),
						'type'  => 'email',
					),
					'wa_message'    => array(
						'label'       => __( 'رسالة واتساب الجاهزة', 'mwm' ),
						'type'        => 'textarea',
						'description' => __( 'تُكتب تلقائياً عند فتح واتساب. استخدم {page} لاسم الصفحة أو الخدمة الحالية.', 'mwm' ),
					),
					'float_enabled'   => array(
						'label' => __( 'إظهار زر واتساب العائم', 'mwm' ),
						'type'  => 'checkbox',
					),
					'float_title'     => array(
						'label' => __( 'عنوان قائمة أرقام واتساب', 'mwm' ),
						'type'  => 'text',
					),
					'bar_enabled'     => array(
						'label' => __( 'إظهار شريط التواصل السفلي على الجوال', 'mwm' ),
						'type'  => 'checkbox',
					),
					'bar_quote_label' => array(
						'label' => __( 'نص زر الطلب في الشريط السفلي', 'mwm' ),
						'type'  => 'text',
					),
				)
			),
		),
		'header'   => array(
			'label'  => __( 'الهيدر والفوتر', 'mwm' ),
			'intro'  => __( 'الهيدر والفوتر يُعدّلان من Elementor بزر «تعديل بـ Elementor» بجوار كل قالب. الحقول النصية هنا تُستخدم فقط إذا اخترت «تصميم الثيم» بدلاً من القالب.', 'mwm' ),
			'fields' => array(
				'header_cta_label'  => array(
					'label' => __( 'نص زر الهيدر', 'mwm' ),
					'type'  => 'text',
				),
				'header_cta_link'   => array(
					'label'   => __( 'وجهة زر الهيدر', 'mwm' ),
					'type'    => 'select',
					'choices' => 'mwm_link_source_choices',
				),
				'header_phone_slot' => array(
					'label'   => __( 'رقم الاتصال في الهيدر', 'mwm' ),
					'type'    => 'select',
					'choices' => array(
						''  => __( 'بدون', 'mwm' ),
						'1' => __( 'الرقم 1', 'mwm' ),
						'2' => __( 'الرقم 2', 'mwm' ),
						'3' => __( 'الرقم 3', 'mwm' ),
					),
				),
				'header_template'   => array(
					'label'       => __( 'قالب Elementor للهيدر (اختياري)', 'mwm' ),
					'type'        => 'template',
					'description' => __( 'اتركه على «هيدر الثيم» لاستخدام الهيدر المصمم مع الثيم.', 'mwm' ),
				),
				'footer_template'   => array(
					'label' => __( 'قالب Elementor للفوتر (اختياري)', 'mwm' ),
					'type'  => 'template',
				),
				'footer_services_title' => array(
					'label' => __( 'عنوان عمود الخدمات في الفوتر', 'mwm' ),
					'type'  => 'text',
				),
				'footer_contact_title'  => array(
					'label' => __( 'عنوان عمود التواصل في الفوتر', 'mwm' ),
					'type'  => 'text',
				),
			),
		),
		'social'   => array(
			'label'  => __( 'التواصل الاجتماعي', 'mwm' ),
			'fields' => array(
				'social_x'         => array(
					'label' => 'X (Twitter)',
					'type'  => 'url',
				),
				'social_linkedin'  => array(
					'label' => 'LinkedIn',
					'type'  => 'url',
				),
				'social_instagram' => array(
					'label' => 'Instagram',
					'type'  => 'url',
				),
				'social_snapchat'  => array(
					'label' => 'Snapchat',
					'type'  => 'url',
				),
				'social_tiktok'    => array(
					'label' => 'TikTok',
					'type'  => 'url',
				),
				'social_youtube'   => array(
					'label' => 'YouTube',
					'type'  => 'url',
				),
				'social_facebook'  => array(
					'label' => 'Facebook',
					'type'  => 'url',
				),
			),
		),
		'pages'    => array(
			'label'  => __( 'أدوار الصفحات', 'mwm' ),
			'intro'  => __( 'حدد الصفحة المسؤولة عن كل دور. الأزرار والروابط في الموقع تعتمد على هذا الاختيار، لذلك تغيير اسم الصفحة لا يكسر أي رابط.', 'mwm' ),
			'fields' => $role_fields,
		),
		'requests' => array(
			'label'  => __( 'الطلبات والإشعارات', 'mwm' ),
			'fields' => array(
				'notify_emails'      => array(
					'label'       => __( 'بريد استلام الطلبات', 'mwm' ),
					'type'        => 'text',
					'description' => __( 'بريد واحد أو أكثر مفصولة بفاصلة.', 'mwm' ),
				),
				'request_types'      => array(
					'label'       => __( 'أنواع الطلب في النموذج', 'mwm' ),
					'type'        => 'textarea',
					'description' => __( 'نوع في كل سطر بالصيغة: المعرّف|الاسم الظاهر — مثال: maintenance|عقد صيانة', 'mwm' ),
				),
				'privacy_notice'     => array(
					'label'       => __( 'تنبيه الخصوصية أسفل النموذج', 'mwm' ),
					'type'        => 'textarea',
					'description' => __( 'يظهر قبل خانة الموافقة. يمكن إضافة رابط سياسة الخصوصية من أدوار الصفحات.', 'mwm' ),
				),
				'consent_label'      => array(
					'label' => __( 'نص خانة الموافقة', 'mwm' ),
					'type'  => 'text',
				),
				'success_message'    => array(
					'label' => __( 'رسالة النجاح بعد الإرسال', 'mwm' ),
					'type'  => 'textarea',
				),
				'retention_days'     => array(
					'label'       => __( 'حذف الطلبات بعد (يوم)', 'mwm' ),
					'type'        => 'number',
					'description' => __( '0 = عدم الحذف التلقائي.', 'mwm' ),
				),
				'license_alert_days' => array(
					'label'       => __( 'تنبيه انتهاء الترخيص قبل (يوم)', 'mwm' ),
					'type'        => 'number',
					'description' => __( 'يظهر تنبيه في لوحة التحكم عندما يقترب تاريخ انتهاء أي ترخيص.', 'mwm' ),
				),
			),
		),
		'seo'      => array(
			'label'  => __( 'المشاركة والتحليلات', 'mwm' ),
			'fields' => array(
				'og_image'     => array(
					'label'       => __( 'صورة المشاركة الافتراضية', 'mwm' ),
					'type'        => 'media',
					'media'       => 'image',
					'description' => __( 'المقاس المثالي 1200×630. تظهر عند مشاركة رابط صفحة ليس لها صورة.', 'mwm' ),
				),
				'analytics_id' => array(
					'label'       => __( 'معرّف Google Analytics / Tag Manager', 'mwm' ),
					'type'        => 'text',
					'description' => __( 'مثل G-XXXXXXX أو GTM-XXXXXX. يُحمّل الكود فقط عند تعبئة الحقل.', 'mwm' ),
				),
			),
		),
	);
}

/**
 * Flat key => field map.
 *
 * @return array
 */
function mwm_settings_fields() {
	$out = array();
	foreach ( mwm_settings_schema() as $tab ) {
		$out += $tab['fields'];
	}
	return $out;
}

/**
 * Register the menu page and option.
 */
function mwm_settings_admin_menu() {
	add_menu_page(
		__( 'إعدادات الموقع', 'mwm' ),
		__( 'إعدادات الموقع', 'mwm' ),
		'manage_options',
		'mwm-settings',
		'mwm_settings_render_page',
		'dashicons-admin-site-alt3',
		3
	);
}
add_action( 'admin_menu', 'mwm_settings_admin_menu' );

/**
 * Register the option with the Settings API.
 */
function mwm_settings_register() {
	register_setting(
		'mwm_settings_group',
		'mwm_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'mwm_settings_sanitize',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'mwm_settings_register' );

/**
 * Sanitize every field by type.
 *
 * @param mixed $input Raw input.
 * @return array
 */
function mwm_settings_sanitize( $input ) {
	$input = is_array( $input ) ? $input : array();
	$out   = mwm_settings();

	foreach ( mwm_settings_fields() as $key => $field ) {
		$raw = isset( $input[ $key ] ) ? wp_unslash( $input[ $key ] ) : '';
		switch ( $field['type'] ) {
			case 'checkbox':
				$out[ $key ] = empty( $raw ) ? '0' : '1';
				break;
			case 'email':
				$out[ $key ] = sanitize_email( $raw );
				break;
			case 'url':
				$out[ $key ] = esc_url_raw( trim( $raw ) );
				break;
			case 'textarea':
				$out[ $key ] = sanitize_textarea_field( $raw );
				break;
			case 'number':
			case 'page':
			case 'media':
			case 'template':
				$out[ $key ] = '' === $raw ? '' : (string) absint( $raw );
				break;
			case 'phone':
				$out[ $key ] = preg_replace( '/[^\d+\s\-]/', '', sanitize_text_field( $raw ) );
				break;
			case 'select':
				$choices     = mwm_settings_choices( $field );
				$out[ $key ] = array_key_exists( $raw, $choices ) ? $raw : '';
				break;
			default:
				$out[ $key ] = sanitize_text_field( $raw );
		}
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return $out;
}

/**
 * Choices for a select field.
 *
 * @param array $field Field.
 * @return array
 */
function mwm_settings_choices( array $field ) {
	$choices = $field['choices'] ?? array();
	return is_callable( $choices ) ? call_user_func( $choices ) : (array) $choices;
}

/**
 * Admin assets for the settings screen.
 *
 * @param string $hook Hook suffix.
 */
function mwm_settings_admin_assets( $hook ) {
	if ( 'toplevel_page_mwm-settings' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'mwm-admin', MWM_URI . '/assets/css/admin.css', array(), MWM_VERSION );
	wp_enqueue_script( 'mwm-admin', MWM_URI . '/assets/js/admin.js', array(), MWM_VERSION, true );
	wp_localize_script(
		'mwm-admin',
		'mwmAdmin',
		array(
			'choose' => __( 'اختيار ملف', 'mwm' ),
			'remove' => __( 'إزالة', 'mwm' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'mwm_settings_admin_assets' );

/**
 * Render the settings page.
 */
function mwm_settings_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$schema   = mwm_settings_schema();
	$settings = mwm_settings();
	?>
	<div class="wrap mwm-settings">
		<h1><?php esc_html_e( 'إعدادات الموقع', 'mwm' ); ?></h1>
		<p class="mwm-settings__lead"><?php esc_html_e( 'كل بيانات التواصل والهوية هنا تُستخدم تلقائياً في الهيدر والفوتر وأزرار واتساب والاتصال والنموذج. الحقل الفارغ يختفي من الموقع.', 'mwm' ); ?></p>
		<?php settings_errors(); ?>
		<nav class="nav-tab-wrapper mwm-settings__tabs" role="tablist">
			<?php
			$first = true;
			foreach ( $schema as $tab_id => $tab ) :
				?>
				<a href="#mwm-tab-<?php echo esc_attr( $tab_id ); ?>" class="nav-tab<?php echo $first ? ' nav-tab-active' : ''; ?>" role="tab" data-tab="<?php echo esc_attr( $tab_id ); ?>"><?php echo esc_html( $tab['label'] ); ?></a>
				<?php
				$first = false;
			endforeach;
			?>
		</nav>
		<form method="post" action="options.php">
			<?php settings_fields( 'mwm_settings_group' ); ?>
			<?php
			$first = true;
			foreach ( $schema as $tab_id => $tab ) :
				?>
				<section id="mwm-tab-<?php echo esc_attr( $tab_id ); ?>" class="mwm-settings__panel" role="tabpanel"<?php echo $first ? '' : ' hidden'; ?>>
					<?php if ( ! empty( $tab['intro'] ) ) : ?>
						<p class="description"><?php echo esc_html( $tab['intro'] ); ?></p>
					<?php endif; ?>
					<table class="form-table" role="presentation">
						<?php foreach ( $tab['fields'] as $key => $field ) : ?>
							<tr>
								<th scope="row"><label for="mwm-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
								<td>
									<?php mwm_settings_render_field( $key, $field, $settings[ $key ] ?? '' ); ?>
									<?php if ( ! empty( $field['description'] ) ) : ?>
										<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				</section>
				<?php
				$first = false;
			endforeach;
			?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * One field control.
 *
 * @param string $key   Key.
 * @param array  $field Field.
 * @param mixed  $value Value.
 */
function mwm_settings_render_field( $key, array $field, $value ) {
	$name = 'mwm_settings[' . $key . ']';
	$id   = 'mwm-' . $key;

	switch ( $field['type'] ) {
		case 'textarea':
			printf( '<textarea id="%1$s" name="%2$s" rows="3" class="large-text">%3$s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( (string) $value ) );
			break;

		case 'checkbox':
			printf( '<input type="hidden" name="%1$s" value="0"><label><input type="checkbox" id="%2$s" name="%1$s" value="1"%3$s> %4$s</label>', esc_attr( $name ), esc_attr( $id ), checked( '1', (string) $value, false ), esc_html__( 'نعم', 'mwm' ) );
			break;

		case 'number':
			printf( '<input type="number" min="0" id="%1$s" name="%2$s" value="%3$s" class="small-text">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
			break;

		case 'page':
			wp_dropdown_pages(
				array(
					'name'              => esc_attr( $name ),
					'id'                => esc_attr( $id ),
					'selected'          => (int) $value,
					'show_option_none'  => esc_html__( '— غير محدد —', 'mwm' ),
					'option_none_value' => '',
				)
			);
			break;

		case 'select':
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( mwm_settings_choices( $field ) as $opt => $label ) {
				printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $opt ), selected( (string) $value, (string) $opt, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;

		case 'template':
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( mwm_elementor_template_choices() as $opt => $label ) {
				printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( (string) $opt ), selected( (string) $value, (string) $opt, false ), esc_html( $label ) );
			}
			echo '</select>';
			$edit = (int) $value && did_action( 'elementor/loaded' ) ? \Elementor\Plugin::$instance->documents->get( (int) $value ) : null;
			if ( $edit ) {
				printf( ' <a class="button button-primary" href="%1$s">%2$s</a>', esc_url( $edit->get_edit_url() ), esc_html__( 'تعديل بـ Elementor', 'mwm' ) );
			}
			break;

		case 'media':
			$att  = (int) $value;
			$url  = $att ? wp_get_attachment_url( $att ) : '';
			$name_label = $url ? wp_basename( $url ) : '';
			printf(
				'<div class="mwm-media" data-type="%5$s"><input type="hidden" id="%1$s" name="%2$s" value="%3$s"><span class="mwm-media__name">%4$s</span> <button type="button" class="button mwm-media__pick">%6$s</button> <button type="button" class="button-link mwm-media__clear"%7$s>%8$s</button></div>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $att ? (string) $att : '' ),
				esc_html( $name_label ),
				esc_attr( $field['media'] ?? 'image' ),
				esc_html__( 'اختيار ملف', 'mwm' ),
				$att ? '' : ' hidden',
				esc_html__( 'إزالة', 'mwm' )
			);
			break;

		default:
			$type = in_array( $field['type'], array( 'email', 'url' ), true ) ? $field['type'] : 'text';
			$dir  = in_array( $field['type'], array( 'email', 'url', 'phone' ), true ) ? ' dir="ltr"' : '';
			printf( '<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text"%5$s>', esc_attr( $type ), esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ), $dir ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attribute.
	}
}

/**
 * Elementor saved templates usable as header/footer.
 *
 * @return array<int,string>
 */
function mwm_elementor_template_choices() {
	$choices = array( 0 => __( 'تصميم الثيم', 'mwm' ) );
	if ( ! post_type_exists( 'elementor_library' ) ) {
		return $choices;
	}
	$posts = get_posts(
		array(
			'post_type'      => 'elementor_library',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_elementor_template_type',
					'value'   => array( 'kit' ),
					'compare' => 'NOT IN',
				),
			),
		)
	);
	foreach ( $posts as $post ) {
		$choices[ $post->ID ] = $post->post_title;
	}
	return $choices;
}

/**
 * Shortcodes: settings values inside Elementor's own widgets.
 */
function mwm_register_shortcodes() {
	add_shortcode(
		'mwm_phone',
		static function ( $atts ) {
			$atts   = shortcode_atts( array( 'slot' => 1, 'link' => 'yes' ), $atts );
			$number = (string) mwm_setting( 'phone_' . absint( $atts['slot'] ) . '_number' );
			if ( '' === $number ) {
				return '';
			}
			$text = mwm_bdi( mwm_phone_display( $number ) );
			return 'yes' === $atts['link'] ? '<a href="' . esc_url( mwm_tel_url( $number ) ) . '">' . $text . '</a>' : $text;
		}
	);
	add_shortcode(
		'mwm_wa',
		static function ( $atts ) {
			$atts   = shortcode_atts( array( 'slot' => 1, 'text' => '' ), $atts );
			$number = (string) mwm_setting( 'phone_' . absint( $atts['slot'] ) . '_number' );
			if ( '' === $number ) {
				return '';
			}
			$label = '' !== $atts['text'] ? esc_html( $atts['text'] ) : mwm_bdi( mwm_phone_display( $number ) );
			return '<a href="' . esc_url( mwm_wa_url( $number, mwm_wa_message() ) ) . '" target="_blank" rel="noopener">' . $label . '</a>';
		}
	);
	add_shortcode(
		'mwm_email',
		static function () {
			$email = sanitize_email( (string) mwm_setting( 'email' ) );
			return $email ? '<a href="' . esc_url( 'mailto:' . $email ) . '">' . mwm_bdi( $email ) . '</a>' : '';
		}
	);
}
add_action( 'init', 'mwm_register_shortcodes' );

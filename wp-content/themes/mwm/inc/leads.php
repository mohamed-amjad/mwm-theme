<?php
/**
 * Quote / site-visit requests: form, validation, anti-spam, private inbox, email, export, retention.
 * The inbox (mwm_request) is the source of truth — a lead is stored even when email fails.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

const MWM_LEAD_MIN_SECONDS = 3;
const MWM_LEAD_RATE_LIMIT  = 5;
const MWM_LEAD_RATE_WINDOW = 900;

/**
 * Lead statuses.
 *
 * @return array<string,string>
 */
function mwm_lead_statuses() {
	return array(
		'new'       => __( 'جديد', 'mwm' ),
		'contacted' => __( 'تم التواصل', 'mwm' ),
		'closed'    => __( 'مغلق', 'mwm' ),
	);
}

/**
 * Saudi mobile check (05XXXXXXXX / 9665XXXXXXXX / +9665XXXXXXXX).
 *
 * @param string $phone Phone.
 * @return bool
 */
function mwm_is_saudi_mobile( $phone ) {
	return (bool) preg_match( '/^\+9665\d{8}$/', mwm_phone_e164( $phone ) );
}

/**
 * Signed render timestamp for the time trap.
 *
 * @return string
 */
function mwm_lead_token() {
	$ts = (string) time();
	return $ts . '.' . substr( wp_hash( 'mwm_lead|' . $ts, 'nonce' ), 0, 20 );
}

/**
 * Render the form.
 *
 * @param array $args Labels/options from the widget.
 */
function mwm_render_quote_form( array $args = array() ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only presets/state from the URL.
	$preset_service = isset( $_GET['service'] ) ? sanitize_title( wp_unslash( $_GET['service'] ) ) : '';
	$preset_type    = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
	$sent           = isset( $_GET['mwm_sent'] );
	$error          = isset( $_GET['mwm_error'] ) ? sanitize_key( wp_unslash( $_GET['mwm_error'] ) ) : '';
	// phpcs:enable

	if ( is_singular( 'mwm_service' ) && '' === $preset_service ) {
		$preset_service = (string) get_post_field( 'post_name', get_queried_object_id() );
	}

	$label = static function ( $key, $fallback ) use ( $args ) {
		$value = (string) mwm_arg( $args, $key, '' );
		return '' !== $value ? $value : $fallback;
	};

	$services = mwm_get_services();
	$types    = mwm_request_types();
	$notice   = (string) mwm_setting( 'privacy_notice' );
	$consent  = (string) mwm_setting( 'consent_label', __( 'أوافق على معالجة بياناتي للرد على طلبي.', 'mwm' ) );
	$success  = (string) mwm_setting( 'success_message', __( 'تم استلام طلبك بنجاح، وسنتواصل معك قريباً.', 'mwm' ) );
	$privacy  = mwm_page_url( 'privacy' );
	$wa       = mwm_phones_for( 'whatsapp' );
	$uid      = 'mwm-q' . wp_unique_id();
	?>
	<div class="mwm-form-wrap" data-mwm-form-wrap>
		<div class="mwm-form-success" role="status" tabindex="-1" <?php echo $sent ? '' : 'hidden'; ?> data-mwm-success>
			<span class="mwm-form-success__icon"><?php echo mwm_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<h3><?php echo esc_html( $label( 'success_title', __( 'شكراً لتواصلك', 'mwm' ) ) ); ?></h3>
			<p><?php echo esc_html( $success ); ?></p>
			<?php if ( $wa ) : ?>
				<a class="mwm-btn" href="<?php echo esc_url( mwm_wa_url( $wa[0]['number'], mwm_wa_message() ) ); ?>" target="_blank" rel="noopener"><?php echo mwm_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $label( 'success_wa', __( 'تابع معنا عبر واتساب', 'mwm' ) ) ); ?></span></a>
			<?php endif; ?>
		</div>

		<form class="mwm-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate data-mwm-form <?php echo $sent ? 'hidden' : ''; ?>>
			<?php if ( '' !== $error ) : ?>
				<p class="mwm-form__alert" role="alert"><?php echo esc_html( mwm_lead_error_message( $error ) ); ?></p>
			<?php else : ?>
				<p class="mwm-form__alert" role="alert" hidden data-mwm-alert></p>
			<?php endif; ?>

			<input type="hidden" name="action" value="mwm_quote">
			<input type="hidden" name="mwm_token" value="<?php echo esc_attr( mwm_lead_token() ); ?>">
			<input type="hidden" name="mwm_source" value="<?php echo esc_url( mwm_current_url() ); ?>">
			<input type="hidden" name="mwm_utm" value="" data-mwm-utm>
			<input type="hidden" name="mwm_ref" value="" data-mwm-ref>
			<?php wp_nonce_field( 'mwm_quote', 'mwm_nonce' ); ?>
			<div class="mwm-hp" aria-hidden="true">
				<label for="<?php echo esc_attr( $uid ); ?>-website"><?php esc_html_e( 'اترك هذا الحقل فارغاً', 'mwm' ); ?></label>
				<input type="text" id="<?php echo esc_attr( $uid ); ?>-website" name="website" tabindex="-1" autocomplete="off">
			</div>

			<div class="mwm-form__grid">
				<div class="mwm-field">
					<label for="<?php echo esc_attr( $uid ); ?>-name"><?php echo esc_html( $label( 'label_name', __( 'الاسم', 'mwm' ) ) ); ?> <span class="mwm-req" aria-hidden="true">*</span></label>
					<input type="text" id="<?php echo esc_attr( $uid ); ?>-name" name="name" required autocomplete="name" maxlength="120" aria-describedby="<?php echo esc_attr( $uid ); ?>-name-e">
					<span class="mwm-field__error" id="<?php echo esc_attr( $uid ); ?>-name-e"></span>
				</div>
				<div class="mwm-field">
					<label for="<?php echo esc_attr( $uid ); ?>-phone"><?php echo esc_html( $label( 'label_phone', __( 'رقم الجوال', 'mwm' ) ) ); ?> <span class="mwm-req" aria-hidden="true">*</span></label>
					<input type="tel" id="<?php echo esc_attr( $uid ); ?>-phone" name="phone" required autocomplete="tel" inputmode="tel" dir="ltr" placeholder="05XXXXXXXX" maxlength="20" data-mwm-phone aria-describedby="<?php echo esc_attr( $uid ); ?>-phone-e">
					<span class="mwm-field__error" id="<?php echo esc_attr( $uid ); ?>-phone-e"></span>
				</div>
				<div class="mwm-field">
					<label for="<?php echo esc_attr( $uid ); ?>-company"><?php echo esc_html( $label( 'label_company', __( 'اسم المنشأة', 'mwm' ) ) ); ?></label>
					<input type="text" id="<?php echo esc_attr( $uid ); ?>-company" name="company" autocomplete="organization" maxlength="160">
				</div>
				<div class="mwm-field">
					<label for="<?php echo esc_attr( $uid ); ?>-city"><?php echo esc_html( $label( 'label_city', __( 'المدينة / موقع المشروع', 'mwm' ) ) ); ?></label>
					<input type="text" id="<?php echo esc_attr( $uid ); ?>-city" name="city" autocomplete="address-level2" maxlength="120">
				</div>
				<div class="mwm-field">
					<label for="<?php echo esc_attr( $uid ); ?>-email"><?php echo esc_html( $label( 'label_email', __( 'البريد الإلكتروني', 'mwm' ) ) ); ?></label>
					<input type="email" id="<?php echo esc_attr( $uid ); ?>-email" name="email" autocomplete="email" dir="ltr" maxlength="160" aria-describedby="<?php echo esc_attr( $uid ); ?>-email-e">
					<span class="mwm-field__error" id="<?php echo esc_attr( $uid ); ?>-email-e"></span>
				</div>
				<?php if ( $services ) : ?>
					<div class="mwm-field">
						<label for="<?php echo esc_attr( $uid ); ?>-service"><?php echo esc_html( $label( 'label_service', __( 'الخدمة المطلوبة', 'mwm' ) ) ); ?></label>
						<select id="<?php echo esc_attr( $uid ); ?>-service" name="service">
							<option value=""><?php esc_html_e( '— اختر الخدمة —', 'mwm' ); ?></option>
							<?php foreach ( $services as $service ) : ?>
								<option value="<?php echo esc_attr( $service->post_name ); ?>" <?php selected( $preset_service, $service->post_name ); ?>><?php echo esc_html( get_the_title( $service ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>
				<?php if ( $types ) : ?>
					<fieldset class="mwm-field mwm-field--full mwm-types">
						<legend><?php echo esc_html( $label( 'label_type', __( 'نوع الطلب', 'mwm' ) ) ); ?></legend>
						<div class="mwm-types__list">
							<?php
							$first = true;
							foreach ( $types as $type_id => $type_label ) :
								$checked = '' !== $preset_type ? $preset_type === $type_id : $first;
								$first   = false;
								?>
								<label class="mwm-type">
									<input type="radio" name="type" value="<?php echo esc_attr( $type_id ); ?>" <?php checked( $checked ); ?>>
									<span><?php echo esc_html( $type_label ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</fieldset>
				<?php endif; ?>
				<div class="mwm-field mwm-field--full">
					<label for="<?php echo esc_attr( $uid ); ?>-message"><?php echo esc_html( $label( 'label_message', __( 'تفاصيل الطلب', 'mwm' ) ) ); ?></label>
					<textarea id="<?php echo esc_attr( $uid ); ?>-message" name="message" rows="4" maxlength="3000" placeholder="<?php echo esc_attr( $label( 'placeholder_message', '' ) ); ?>"></textarea>
				</div>
				<div class="mwm-field mwm-field--full">
					<?php if ( '' !== $notice ) : ?>
						<p class="mwm-field__hint">
							<?php echo esc_html( $notice ); ?>
							<?php if ( $privacy ) : ?>
								<a href="<?php echo esc_url( $privacy ); ?>"><?php echo esc_html( get_the_title( mwm_page_id( 'privacy' ) ) ); ?></a>
							<?php endif; ?>
						</p>
					<?php endif; ?>
					<label class="mwm-check">
						<input type="checkbox" name="consent" value="1" required aria-describedby="<?php echo esc_attr( $uid ); ?>-consent-e">
						<span><?php echo esc_html( $consent ); ?></span>
					</label>
					<span class="mwm-field__error" id="<?php echo esc_attr( $uid ); ?>-consent-e"></span>
				</div>
			</div>
			<button type="submit" class="mwm-btn mwm-btn--lg mwm-form__submit">
				<span><?php echo esc_html( $label( 'submit_label', __( 'إرسال الطلب', 'mwm' ) ) ); ?></span>
				<?php echo mwm_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</form>
	</div>
	<?php
}

/**
 * Message for an error code.
 *
 * @param string $code Code.
 * @return string
 */
function mwm_lead_error_message( $code ) {
	$messages = array(
		'invalid' => __( 'تأكد من تعبئة الحقول المطلوبة بشكل صحيح.', 'mwm' ),
		'spam'    => __( 'تعذّر التحقق من الطلب. أعد تحميل الصفحة وحاول مرة أخرى.', 'mwm' ),
		'rate'    => __( 'أرسلت عدة طلبات خلال وقت قصير. حاول بعد قليل أو تواصل معنا عبر واتساب.', 'mwm' ),
		'server'  => __( 'حدث خطأ غير متوقع. حاول مرة أخرى أو تواصل معنا عبر واتساب.', 'mwm' ),
	);
	return $messages[ $code ] ?? $messages['server'];
}

/**
 * Visitor IP hash (salted; raw IP never stored).
 *
 * @return string
 */
function mwm_lead_ip_hash() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return wp_hash( 'mwm_ip|' . $ip );
}

/**
 * Handle a submission (AJAX or classic POST).
 */
function mwm_handle_quote() {
	$is_ajax = ! empty( $_POST['mwm_ajax'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified below.
	$back    = isset( $_POST['mwm_source'] ) ? esc_url_raw( wp_unslash( $_POST['mwm_source'] ) ) : home_url( '/' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$back    = wp_validate_redirect( $back, home_url( '/' ) );

	$fail = static function ( $code, array $fields = array() ) use ( $is_ajax, $back ) {
		if ( $is_ajax ) {
			wp_send_json_error(
				array(
					'code'    => $code,
					'message' => mwm_lead_error_message( $code ),
					'fields'  => $fields,
				),
				'rate' === $code ? 429 : 400
			);
		}
		wp_safe_redirect( add_query_arg( 'mwm_error', $code, remove_query_arg( array( 'mwm_sent', 'mwm_error' ), $back ) ) . '#quote' );
		exit;
	};

	if ( ! isset( $_POST['mwm_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mwm_nonce'] ) ), 'mwm_quote' ) ) {
		$fail( 'spam' );
	}
	if ( ! empty( $_POST['website'] ) ) {
		$fail( 'spam' );
	}
	$token = isset( $_POST['mwm_token'] ) ? sanitize_text_field( wp_unslash( $_POST['mwm_token'] ) ) : '';
	$parts = explode( '.', $token );
	if ( 2 !== count( $parts ) || ! hash_equals( substr( wp_hash( 'mwm_lead|' . $parts[0], 'nonce' ), 0, 20 ), $parts[1] ) ) {
		$fail( 'spam' );
	}
	$age = time() - (int) $parts[0];
	if ( $age < MWM_LEAD_MIN_SECONDS || $age > DAY_IN_SECONDS ) {
		$fail( 'spam' );
	}

	$rate_key = 'mwm_rl_' . substr( mwm_lead_ip_hash(), 0, 32 );
	$count    = (int) get_transient( $rate_key );
	if ( $count >= MWM_LEAD_RATE_LIMIT ) {
		$fail( 'rate' );
	}

	$data = array(
		'name'    => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
		'phone'   => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
		'email'   => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'company' => isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '',
		'city'    => isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '',
		'service' => isset( $_POST['service'] ) ? sanitize_title( wp_unslash( $_POST['service'] ) ) : '',
		'type'    => isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '',
		'message' => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
		'utm'     => isset( $_POST['mwm_utm'] ) ? sanitize_text_field( wp_unslash( $_POST['mwm_utm'] ) ) : '',
		'ref'     => isset( $_POST['mwm_ref'] ) ? esc_url_raw( wp_unslash( $_POST['mwm_ref'] ) ) : '',
		'source'  => $back,
		'consent' => ! empty( $_POST['consent'] ),
	);

	$errors = array();
	if ( '' === $data['name'] ) {
		$errors['name'] = 'required';
	}
	if ( ! mwm_is_saudi_mobile( $data['phone'] ) ) {
		$errors['phone'] = '' === $data['phone'] ? 'required' : 'phone';
	}
	if ( isset( $_POST['email'] ) && '' !== trim( (string) wp_unslash( $_POST['email'] ) ) && ! is_email( $data['email'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$errors['email'] = 'email';
	}
	if ( ! $data['consent'] ) {
		$errors['consent'] = 'consent';
	}
	if ( $errors ) {
		$fail( 'invalid', $errors );
	}

	set_transient( $rate_key, $count + 1, MWM_LEAD_RATE_WINDOW );

	$service_post = $data['service'] ? get_page_by_path( $data['service'], OBJECT, 'mwm_service' ) : null;
	$types        = mwm_request_types();
	$type_label   = $types[ $data['type'] ] ?? '';
	$title        = $data['name'] . ( $service_post ? ' — ' . get_the_title( $service_post ) : '' );

	$lead_id = wp_insert_post(
		array(
			'post_type'   => 'mwm_request',
			'post_status' => 'publish',
			'post_title'  => $title,
		),
		true
	);
	if ( is_wp_error( $lead_id ) ) {
		$fail( 'server' );
	}

	$ua     = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	$device = preg_match( '/Mobi|Android|iPhone/i', $ua ) ? 'mobile' : ( preg_match( '/iPad|Tablet/i', $ua ) ? 'tablet' : 'desktop' );
	$meta   = array(
		'phone'      => mwm_phone_e164( $data['phone'] ),
		'email'      => $data['email'],
		'company'    => $data['company'],
		'city'       => $data['city'],
		'service_id' => $service_post ? $service_post->ID : 0,
		'type'       => $data['type'],
		'type_label' => $type_label,
		'message'    => $data['message'],
		'source_url' => $data['source'],
		'referrer'   => $data['ref'],
		'utm'        => $data['utm'],
		'device'     => $device,
		'status'     => 'new',
		'consent_at' => current_time( 'mysql' ),
	);
	foreach ( $meta as $key => $value ) {
		update_post_meta( $lead_id, '_mwm_' . $key, $value );
	}

	$sent = mwm_lead_notify( $lead_id, $data, $service_post, $type_label );
	update_post_meta( $lead_id, '_mwm_mail', $sent ? 'sent' : 'failed' );

	if ( $is_ajax ) {
		wp_send_json_success( array( 'id' => $lead_id ) );
	}
	wp_safe_redirect( add_query_arg( 'mwm_sent', '1', remove_query_arg( array( 'mwm_sent', 'mwm_error' ), $back ) ) . '#quote' );
	exit;
}
add_action( 'admin_post_mwm_quote', 'mwm_handle_quote' );
add_action( 'admin_post_nopriv_mwm_quote', 'mwm_handle_quote' );

/**
 * Email the team. From = the site domain (DMARC-safe), Reply-To = the visitor.
 *
 * @param int          $lead_id    Lead.
 * @param array        $data       Data.
 * @param WP_Post|null $service    Service.
 * @param string       $type_label Type label.
 * @return bool
 */
function mwm_lead_notify( $lead_id, array $data, $service, $type_label ) {
	$to = array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', (string) mwm_setting( 'notify_emails', (string) mwm_setting( 'email', get_option( 'admin_email' ) ) ) ) ) ) );
	if ( ! $to ) {
		return false;
	}
	$host    = wp_parse_url( home_url(), PHP_URL_HOST );
	$host    = $host && str_contains( $host, '.' ) ? preg_replace( '/^www\./', '', $host ) : 'localhost.localdomain';
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'From: ' . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . ' <no-reply@' . $host . '>',
	);
	if ( $data['email'] ) {
		$headers[] = 'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>';
	}

	/* translators: 1: request type, 2: visitor name. */
	$subject = sprintf( __( 'طلب جديد: %1$s — %2$s', 'mwm' ), $type_label ? $type_label : __( 'استفسار', 'mwm' ), $data['name'] );
	$lines   = array(
		__( 'الاسم', 'mwm' )        => $data['name'],
		__( 'الجوال', 'mwm' )       => mwm_phone_display( $data['phone'] ),
		__( 'البريد', 'mwm' )       => $data['email'],
		__( 'المنشأة', 'mwm' )      => $data['company'],
		__( 'المدينة', 'mwm' )      => $data['city'],
		__( 'الخدمة', 'mwm' )       => $service ? get_the_title( $service ) : '',
		__( 'نوع الطلب', 'mwm' )    => $type_label,
		__( 'التفاصيل', 'mwm' )     => $data['message'],
		__( 'صفحة الإرسال', 'mwm' ) => $data['source'],
	);
	$body = '';
	foreach ( $lines as $label => $value ) {
		if ( '' !== (string) $value ) {
			$body .= $label . ': ' . $value . "\n";
		}
	}
	$body .= "\n" . __( 'واتساب:', 'mwm' ) . ' ' . mwm_wa_url( $data['phone'] ) . "\n";
	$body .= __( 'عرض الطلب في لوحة التحكم:', 'mwm' ) . ' ' . admin_url( 'post.php?post=' . (int) $lead_id . '&action=edit' ) . "\n";

	return (bool) wp_mail( $to, $subject, $body, $headers );
}

/* ---------------------------------------------------------------------------
 * Inbox (admin)
 * ------------------------------------------------------------------------- */

/**
 * List columns.
 *
 * @return array
 */
function mwm_request_columns() {
	return array(
		'cb'         => '<input type="checkbox">',
		'title'      => __( 'الطلب', 'mwm' ),
		'mwm_phone'  => __( 'الجوال', 'mwm' ),
		'mwm_type'   => __( 'النوع', 'mwm' ),
		'mwm_status' => __( 'الحالة', 'mwm' ),
		'date'       => __( 'التاريخ', 'mwm' ),
	);
}
add_filter( 'manage_mwm_request_posts_columns', 'mwm_request_columns' );

/**
 * Column values.
 *
 * @param string $col     Column.
 * @param int    $post_id Post.
 */
function mwm_request_column_value( $col, $post_id ) {
	$statuses = mwm_lead_statuses();
	switch ( $col ) {
		case 'mwm_phone':
			$phone = (string) get_post_meta( $post_id, '_mwm_phone', true );
			if ( $phone ) {
				printf(
					'<span dir="ltr">%1$s</span><div class="row-actions visible"><a href="%2$s" target="_blank" rel="noopener">%3$s</a> | <a href="%4$s">%5$s</a></div>',
					esc_html( mwm_phone_display( $phone ) ),
					esc_url( mwm_wa_url( $phone ) ),
					esc_html__( 'واتساب', 'mwm' ),
					esc_url( mwm_tel_url( $phone ) ),
					esc_html__( 'اتصال', 'mwm' )
				);
			}
			break;
		case 'mwm_type':
			echo esc_html( (string) get_post_meta( $post_id, '_mwm_type_label', true ) );
			break;
		case 'mwm_status':
			$status = (string) get_post_meta( $post_id, '_mwm_status', true );
			printf( '<span class="mwm-status mwm-status--%1$s">%2$s</span>', esc_attr( $status ), esc_html( $statuses[ $status ] ?? $status ) );
			if ( 'failed' === get_post_meta( $post_id, '_mwm_mail', true ) ) {
				printf( ' <span class="mwm-status mwm-status--failed">%s</span>', esc_html__( 'فشل إرسال البريد', 'mwm' ) );
			}
			break;
	}
}
add_action( 'manage_mwm_request_posts_custom_column', 'mwm_request_column_value', 10, 2 );

/**
 * Status views.
 *
 * @param array $views Views.
 * @return array
 */
function mwm_request_views( $views ) {
	$current = isset( $_GET['mwm_status'] ) ? sanitize_key( wp_unslash( $_GET['mwm_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$out     = array( 'all' => $views['all'] ?? '' );
	foreach ( mwm_lead_statuses() as $key => $label ) {
		$count       = mwm_request_count( $key );
		$out[ $key ] = sprintf(
			'<a href="%1$s"%2$s>%3$s <span class="count">(%4$d)</span></a>',
			esc_url( admin_url( 'edit.php?post_type=mwm_request&mwm_status=' . $key ) ),
			$current === $key ? ' class="current"' : '',
			esc_html( $label ),
			$count
		);
	}
	if ( isset( $views['trash'] ) ) {
		$out['trash'] = $views['trash'];
	}
	return $out;
}
add_filter( 'views_edit-mwm_request', 'mwm_request_views' );

/**
 * Requests in a status.
 *
 * @param string $status Status.
 * @return int
 */
function mwm_request_count( $status ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'mwm_request',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => '_mwm_status',
					'value' => $status,
				),
			),
		)
	);
	return (int) $q->found_posts;
}

/**
 * Filter the list by status.
 *
 * @param WP_Query $query Query.
 */
function mwm_request_filter( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'mwm_request' !== $query->get( 'post_type' ) ) {
		return;
	}
	$status = isset( $_GET['mwm_status'] ) ? sanitize_key( wp_unslash( $_GET['mwm_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $status && isset( mwm_lead_statuses()[ $status ] ) ) {
		$query->set(
			'meta_query',
			array(
				array(
					'key'   => '_mwm_status',
					'value' => $status,
				),
			)
		);
	}
}
add_action( 'pre_get_posts', 'mwm_request_filter' );

/**
 * Unread bubble on the menu.
 */
function mwm_request_menu_bubble() {
	global $menu;
	$count = mwm_request_count( 'new' );
	if ( ! $count || ! is_array( $menu ) ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=mwm_request' === $item[2] ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $count . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}
}
add_action( 'admin_menu', 'mwm_request_menu_bubble', 99 );

/**
 * Details metabox.
 */
function mwm_request_metaboxes() {
	add_meta_box( 'mwm-request', __( 'تفاصيل الطلب', 'mwm' ), 'mwm_request_metabox', 'mwm_request', 'normal', 'high' );
	add_meta_box( 'mwm-request-status', __( 'الحالة', 'mwm' ), 'mwm_request_status_box', 'mwm_request', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'mwm_request_metaboxes' );

/**
 * Details.
 *
 * @param WP_Post $post Post.
 */
function mwm_request_metabox( $post ) {
	$get     = static fn( $k ) => (string) get_post_meta( $post->ID, '_mwm_' . $k, true );
	$service = (int) $get( 'service_id' );
	$phone   = $get( 'phone' );
	$rows    = array(
		__( 'الجوال', 'mwm' )        => $phone ? '<span dir="ltr">' . esc_html( mwm_phone_display( $phone ) ) . '</span>' : '',
		__( 'البريد', 'mwm' )        => $get( 'email' ) ? '<a href="' . esc_url( 'mailto:' . $get( 'email' ) ) . '" dir="ltr">' . esc_html( $get( 'email' ) ) . '</a>' : '',
		__( 'المنشأة', 'mwm' )       => esc_html( $get( 'company' ) ),
		__( 'المدينة', 'mwm' )       => esc_html( $get( 'city' ) ),
		__( 'الخدمة', 'mwm' )        => $service ? esc_html( get_the_title( $service ) ) : '',
		__( 'نوع الطلب', 'mwm' )     => esc_html( $get( 'type_label' ) ),
		__( 'التفاصيل', 'mwm' )      => nl2br( esc_html( $get( 'message' ) ) ),
		__( 'صفحة الإرسال', 'mwm' )  => $get( 'source_url' ) ? '<a href="' . esc_url( $get( 'source_url' ) ) . '" target="_blank" dir="ltr">' . esc_html( $get( 'source_url' ) ) . '</a>' : '',
		__( 'المصدر (Referrer)', 'mwm' ) => esc_html( $get( 'referrer' ) ),
		__( 'UTM', 'mwm' )           => esc_html( $get( 'utm' ) ),
		__( 'الجهاز', 'mwm' )        => esc_html( $get( 'device' ) ),
		__( 'وقت الموافقة', 'mwm' )  => esc_html( $get( 'consent_at' ) ),
		__( 'البريد الإلكتروني', 'mwm' ) => 'failed' === $get( 'mail' ) ? '<span class="mwm-status mwm-status--failed">' . esc_html__( 'فشل إرسال الإشعار — راجع إعدادات SMTP', 'mwm' ) . '</span>' : esc_html__( 'تم الإرسال', 'mwm' ),
	);
	echo '<table class="mwm-request-meta">';
	foreach ( $rows as $label => $value ) {
		if ( '' === $value ) {
			continue;
		}
		echo '<tr><th>' . esc_html( $label ) . '</th><td>' . wp_kses_post( $value ) . '</td></tr>';
	}
	echo '</table>';
	if ( $phone ) {
		echo '<div class="mwm-request-actions">';
		printf( '<a class="button button-primary" href="%1$s" target="_blank" rel="noopener">%2$s</a>', esc_url( mwm_wa_url( $phone ) ), esc_html__( 'رد عبر واتساب', 'mwm' ) );
		printf( '<a class="button" href="%1$s">%2$s</a>', esc_url( mwm_tel_url( $phone ) ), esc_html__( 'اتصال', 'mwm' ) );
		if ( $get( 'email' ) ) {
			printf( '<a class="button" href="%1$s">%2$s</a>', esc_url( 'mailto:' . $get( 'email' ) ), esc_html__( 'رد بالبريد', 'mwm' ) );
		}
		echo '</div>';
	}
}

/**
 * Status box.
 *
 * @param WP_Post $post Post.
 */
function mwm_request_status_box( $post ) {
	wp_nonce_field( 'mwm_request_status', 'mwm_request_nonce' );
	$current = (string) get_post_meta( $post->ID, '_mwm_status', true );
	echo '<select name="mwm_status" style="width:100%">';
	foreach ( mwm_lead_statuses() as $key => $label ) {
		printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $key ), selected( $current, $key, false ), esc_html( $label ) );
	}
	echo '</select>';
}

/**
 * Save status; opening a new lead marks it as seen only when the admin changes it.
 *
 * @param int $post_id Post.
 */
function mwm_request_save( $post_id ) {
	if ( ! isset( $_POST['mwm_request_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mwm_request_nonce'] ) ), 'mwm_request_status' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$status = isset( $_POST['mwm_status'] ) ? sanitize_key( wp_unslash( $_POST['mwm_status'] ) ) : '';
	if ( isset( mwm_lead_statuses()[ $status ] ) ) {
		update_post_meta( $post_id, '_mwm_status', $status );
	}
}
add_action( 'save_post_mwm_request', 'mwm_request_save' );

/**
 * Admin CSS on the inbox.
 *
 * @param string $hook Hook.
 */
function mwm_request_admin_css( $hook ) {
	if ( 'mwm_request' === get_post_type() || ( isset( $_GET['post_type'] ) && 'mwm_request' === $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_enqueue_style( 'mwm-admin', MWM_URI . '/assets/css/admin.css', array(), MWM_VERSION );
	}
}
add_action( 'admin_enqueue_scripts', 'mwm_request_admin_css' );

/**
 * CSV export button.
 *
 * @param string $which top|bottom.
 */
function mwm_request_export_button( $which ) {
	if ( 'top' !== $which || 'mwm_request' !== get_current_screen()->post_type ) {
		return;
	}
	printf(
		'<a class="button" href="%1$s" style="margin-inline-start:6px">%2$s</a>',
		esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=mwm_export_requests' ), 'mwm_export' ) ),
		esc_html__( 'تصدير CSV', 'mwm' )
	);
}
add_action( 'manage_posts_extra_tablenav', 'mwm_request_export_button' );

/**
 * Stream all requests as CSV (Excel-friendly UTF-8 BOM).
 */
function mwm_request_export() {
	if ( ! current_user_can( 'edit_posts' ) || ! check_admin_referer( 'mwm_export' ) ) {
		wp_die( esc_html__( 'غير مسموح.', 'mwm' ) );
	}
	$posts = get_posts(
		array(
			'post_type'      => 'mwm_request',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		)
	);
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=mwm-requests-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
	$cols = array( 'date', 'name', 'phone', 'email', 'company', 'city', 'service', 'type_label', 'message', 'status', 'source_url', 'utm', 'device' );
	fputcsv( $out, $cols );
	foreach ( $posts as $post ) {
		$get     = static fn( $k ) => (string) get_post_meta( $post->ID, '_mwm_' . $k, true );
		$service = (int) $get( 'service_id' );
		fputcsv(
			$out,
			array(
				get_the_date( 'Y-m-d H:i', $post ),
				explode( ' — ', $post->post_title )[0],
				mwm_phone_display( $get( 'phone' ) ),
				$get( 'email' ),
				$get( 'company' ),
				$get( 'city' ),
				$service ? get_the_title( $service ) : '',
				$get( 'type_label' ),
				$get( 'message' ),
				$get( 'status' ),
				$get( 'source_url' ),
				$get( 'utm' ),
				$get( 'device' ),
			)
		);
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_post_mwm_export_requests', 'mwm_request_export' );

/* ---------------------------------------------------------------------------
 * Retention, privacy tools, license expiry notice
 * ------------------------------------------------------------------------- */

/**
 * Schedule the daily cleanup.
 */
function mwm_schedule_cleanup() {
	if ( ! wp_next_scheduled( 'mwm_daily_cleanup' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'mwm_daily_cleanup' );
	}
}
add_action( 'init', 'mwm_schedule_cleanup' );

/**
 * Delete requests older than the retention period.
 */
function mwm_cleanup_requests() {
	$days = (int) mwm_setting( 'retention_days', 0 );
	if ( $days <= 0 ) {
		return;
	}
	$old = get_posts(
		array(
			'post_type'      => 'mwm_request',
			'post_status'    => 'any',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'date_query'     => array( array( 'before' => $days . ' days ago' ) ),
		)
	);
	foreach ( $old as $id ) {
		wp_delete_post( $id, true );
	}
}
add_action( 'mwm_daily_cleanup', 'mwm_cleanup_requests' );

/**
 * Requests matching an email address (privacy tools).
 *
 * @param string $email Email.
 * @return int[]
 */
function mwm_requests_by_email( $email ) {
	return get_posts(
		array(
			'post_type'      => 'mwm_request',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_mwm_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
}

add_filter(
	'wp_privacy_personal_data_exporters',
	static function ( $exporters ) {
		$exporters['mwm-requests'] = array(
			'exporter_friendly_name' => __( 'طلبات العملاء', 'mwm' ),
			'callback'               => static function ( $email ) {
				$items = array();
				foreach ( mwm_requests_by_email( $email ) as $id ) {
					$data = array();
					foreach ( array( 'phone', 'email', 'company', 'city', 'type_label', 'message', 'source_url' ) as $k ) {
						$data[] = array(
							'name'  => $k,
							'value' => (string) get_post_meta( $id, '_mwm_' . $k, true ),
						);
					}
					$items[] = array(
						'group_id'    => 'mwm-requests',
						'group_label' => __( 'طلبات العملاء', 'mwm' ),
						'item_id'     => 'mwm-request-' . $id,
						'data'        => $data,
					);
				}
				return array(
					'data' => $items,
					'done' => true,
				);
			},
		);
		return $exporters;
	}
);

add_filter(
	'wp_privacy_personal_data_erasers',
	static function ( $erasers ) {
		$erasers['mwm-requests'] = array(
			'eraser_friendly_name' => __( 'طلبات العملاء', 'mwm' ),
			'callback'             => static function ( $email ) {
				$ids = mwm_requests_by_email( $email );
				foreach ( $ids as $id ) {
					wp_delete_post( $id, true );
				}
				return array(
					'items_removed'  => count( $ids ),
					'items_retained' => false,
					'messages'       => array(),
					'done'           => true,
				);
			},
		);
		return $erasers;
	}
);

/**
 * Dashboard notice when a license is close to expiry.
 */
function mwm_license_expiry_notice() {
	$days = (int) mwm_setting( 'license_alert_days', 0 );
	if ( $days <= 0 || ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$limit = (int) wp_date( 'Ymd', time() + $days * DAY_IN_SECONDS );
	foreach ( mwm_get_licenses() as $license ) {
		$expiry = (int) preg_replace( '/\D/', '', (string) get_post_meta( $license->ID, 'expiry_date', true ) );
		if ( $expiry && $expiry <= $limit ) {
			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: 1: license title, 2: expiry date. */
						__( 'تنبيه: الترخيص «%1$s» ينتهي بتاريخ %2$s. حدّث بياناته بعد التجديد.', 'mwm' ),
						get_the_title( $license ),
						substr( (string) $expiry, 0, 4 ) . '/' . substr( (string) $expiry, 4, 2 ) . '/' . substr( (string) $expiry, 6, 2 )
					)
				)
			);
		}
	}
}
add_action( 'admin_notices', 'mwm_license_expiry_notice' );

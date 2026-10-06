<?php
/**
 * Core helpers: template args, settings access, Saudi phone formatting, links, output.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Absolute URL of the current front-end request, minus the form state flags.
 * home_url( REQUEST_URI ) would repeat the path when WordPress lives in a subdirectory.
 *
 * @return string
 */
function mwm_current_url() {
	global $wp;
	$path  = isset( $wp->request ) ? (string) $wp->request : '';
	$url   = home_url( '' === $path ? '/' : user_trailingslashit( $path ) );
	$query = array_diff_key(
		map_deep( wp_unslash( $_GET ), 'sanitize_text_field' ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		array_flip( array( 'mwm_sent', 'mwm_error' ) )
	);
	return $query ? add_query_arg( urlencode_deep( $query ), $url ) : $url;
}

/**
 * Template arg semantics: an absent key falls back to $default (live data),
 * a present key is used as-is — so a present-but-empty value hides the element.
 *
 * @param array  $args    Template args.
 * @param string $key     Key.
 * @param mixed  $default Fallback when the key is absent.
 * @return mixed
 */
function mwm_arg( array $args, $key, $default = '' ) {
	return array_key_exists( $key, $args ) ? $args[ $key ] : $default;
}

/**
 * All theme settings (one option).
 *
 * @return array
 */
function mwm_settings() {
	$saved = get_option( 'mwm_settings', array() );
	return is_array( $saved ) ? $saved : array();
}

/**
 * One setting value.
 *
 * @param string $key     Key.
 * @param mixed  $default Default.
 * @return mixed
 */
function mwm_setting( $key, $default = '' ) {
	$settings = mwm_settings();
	if ( ! isset( $settings[ $key ] ) || '' === $settings[ $key ] ) {
		return $default;
	}
	return $settings[ $key ];
}

/**
 * Normalize a Saudi phone number to E.164 (+9665XXXXXXXX). Non-Saudi numbers
 * keep their digits with a leading +.
 *
 * @param string $raw Number as typed.
 * @return string Empty when no digits.
 */
function mwm_phone_e164( $raw ) {
	$raw    = strtr( (string) $raw, array_combine( array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ), array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ) ) );
	$digits = preg_replace( '/\D+/', '', $raw );
	if ( '' === $digits ) {
		return '';
	}
	if ( str_starts_with( $digits, '00' ) ) {
		$digits = substr( $digits, 2 );
	}
	if ( preg_match( '/^0(5\d{8})$/', $digits, $m ) || preg_match( '/^(5\d{8})$/', $digits, $m ) ) {
		return '+966' . $m[1];
	}
	if ( preg_match( '/^0(1\d{7,8})$/', $digits, $m ) ) {
		return '+966' . $m[1];
	}
	if ( preg_match( '/^(8001\d{6}|9200\d{5})$/', $digits ) ) {
		return $digits;
	}
	return '+' . $digits;
}

/**
 * Local display format: 05X XXX XXXX for Saudi mobiles, otherwise as typed.
 *
 * @param string $raw Number as typed.
 * @return string
 */
function mwm_phone_display( $raw ) {
	$e164 = mwm_phone_e164( $raw );
	if ( preg_match( '/^\+966(5\d)(\d{3})(\d{4})$/', $e164, $m ) ) {
		return '0' . $m[1] . ' ' . $m[2] . ' ' . $m[3];
	}
	return trim( (string) $raw );
}

/**
 * tel: link.
 *
 * @param string $raw Number.
 * @return string
 */
function mwm_tel_url( $raw ) {
	$e164 = mwm_phone_e164( $raw );
	return '' === $e164 ? '' : 'tel:' . $e164;
}

/**
 * wa.me link with optional prefilled text.
 *
 * @param string $raw  Number.
 * @param string $text Message.
 * @return string
 */
function mwm_wa_url( $raw, $text = '' ) {
	$digits = ltrim( mwm_phone_e164( $raw ), '+' );
	if ( '' === $digits ) {
		return '';
	}
	$url = 'https://wa.me/' . $digits;
	if ( '' !== $text ) {
		$url .= '?text=' . rawurlencode( $text );
	}
	return $url;
}

/**
 * Configured phone slots that have a number.
 *
 * @return array<int,array{slot:int,label:string,number:string,display:string,whatsapp:bool,call:bool}>
 */
function mwm_phones() {
	$out = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$number = trim( (string) mwm_setting( "phone_{$i}_number" ) );
		if ( '' === $number ) {
			continue;
		}
		$out[] = array(
			'slot'     => $i,
			'label'    => (string) mwm_setting( "phone_{$i}_label" ),
			'number'   => $number,
			'display'  => mwm_phone_display( $number ),
			'whatsapp' => '1' === (string) mwm_setting( "phone_{$i}_whatsapp", '0' ),
			'call'     => '1' === (string) mwm_setting( "phone_{$i}_call", '0' ),
		);
	}
	return $out;
}

/**
 * Phones flagged for WhatsApp / calls.
 *
 * @param string $kind whatsapp|call.
 * @return array
 */
function mwm_phones_for( $kind ) {
	return array_values( array_filter( mwm_phones(), static fn( $p ) => ! empty( $p[ $kind ] ) ) );
}

/**
 * Prefilled WhatsApp message for the current context.
 *
 * @param string $context Page or service name.
 * @return string
 */
function mwm_wa_message( $context = '' ) {
	$template = (string) mwm_setting( 'wa_message' );
	if ( '' === $template ) {
		return '';
	}
	if ( '' === $context ) {
		$context = is_front_page() ? get_bloginfo( 'name' ) : wp_strip_all_tags( (string) get_the_title( get_queried_object_id() ) );
	}
	return trim( str_replace( array( '{page}', '{service}' ), $context, $template ) );
}

/**
 * Wrap LTR runs (phones, emails, Latin terms) for correct order inside Arabic text.
 *
 * @param string $text Plain text.
 * @return string Escaped HTML.
 */
function mwm_bdi( $text ) {
	return '<bdi dir="ltr">' . esc_html( $text ) . '</bdi>';
}

/**
 * ACF field with post-meta fallback, so the site never fatals without ACF.
 *
 * @param string   $key     Field name.
 * @param int|null $post_id Post ID.
 * @return mixed
 */
function mwm_field( $key, $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( function_exists( 'get_field' ) ) {
		return get_field( $key, $post_id );
	}
	return get_post_meta( $post_id, $key, true );
}

/**
 * Page roles managed on the settings screen.
 *
 * @return array<string,string> role => admin label.
 */
function mwm_page_roles() {
	return array(
		'home'     => __( 'الرئيسية', 'mwm' ),
		'about'    => __( 'من نحن', 'mwm' ),
		'services' => __( 'خدماتنا', 'mwm' ),
		'gallery'  => __( 'معرض الأعمال', 'mwm' ),
		'quote'    => __( 'طلب عرض سعر / معاينة', 'mwm' ),
		'contact'  => __( 'تواصل معنا', 'mwm' ),
		'privacy'  => __( 'سياسة الخصوصية', 'mwm' ),
	);
}

/**
 * Page ID assigned to a role.
 *
 * @param string $role Role.
 * @return int
 */
function mwm_page_id( $role ) {
	if ( 'home' === $role && 'page' === get_option( 'show_on_front' ) ) {
		$front = (int) get_option( 'page_on_front' );
		if ( $front ) {
			return $front;
		}
	}
	if ( 'privacy' === $role && ! mwm_setting( 'role_privacy' ) ) {
		return (int) get_option( 'wp_page_for_privacy_policy' );
	}
	$id = (int) mwm_setting( 'role_' . $role, 0 );
	return ( $id && 'publish' === get_post_status( $id ) ) ? $id : 0;
}

/**
 * URL of a role page ('' when unassigned).
 *
 * @param string $role Role.
 * @return string
 */
function mwm_page_url( $role ) {
	if ( 'home' === $role ) {
		return home_url( '/' );
	}
	$id = mwm_page_id( $role );
	return $id ? (string) get_permalink( $id ) : '';
}

/**
 * Resolve a link source (from widgets / settings) to href + attributes.
 *
 * @param string $source  custom|role:{role}|wa:{slot}|tel:{slot}|email.
 * @param string $custom  Custom URL when source is custom.
 * @param string $context WhatsApp message context.
 * @return array{href:string,external:bool}
 */
function mwm_resolve_link( $source, $custom = '', $context = '' ) {
	$source = (string) $source;
	$href   = '';
	$ext    = false;

	if ( 'role:quote' === $source && is_singular( 'mwm_service' ) && function_exists( 'mwm_quote_url' ) ) {
		$service = get_queried_object_id();
		$href    = mwm_quote_url( $service, (string) get_post_meta( $service, 'request_type', true ) );
	} elseif ( str_starts_with( $source, 'role:' ) ) {
		$href = mwm_page_url( substr( $source, 5 ) );
	} elseif ( str_starts_with( $source, 'wa:' ) ) {
		$slot = (int) substr( $source, 3 );
		$href = mwm_wa_url( (string) mwm_setting( "phone_{$slot}_number" ), mwm_wa_message( $context ) );
		$ext  = true;
	} elseif ( str_starts_with( $source, 'tel:' ) ) {
		$href = mwm_tel_url( (string) mwm_setting( 'phone_' . (int) substr( $source, 4 ) . '_number' ) );
	} elseif ( 'email' === $source ) {
		$email = sanitize_email( (string) mwm_setting( 'email' ) );
		$href  = $email ? 'mailto:' . $email : '';
	} elseif ( 'profile' === $source ) {
		$pdf  = (int) mwm_setting( 'profile_pdf', 0 );
		$href = $pdf ? (string) wp_get_attachment_url( $pdf ) : '';
		$ext  = true;
	} else {
		$href = trim( (string) $custom );
		$ext  = '' !== $href && ! str_starts_with( $href, home_url() ) && preg_match( '#^https?://#', $href );
	}

	return array(
		'href'     => $href,
		'external' => (bool) $ext,
	);
}

/**
 * Link source choices for selects.
 *
 * @return array<string,string>
 */
function mwm_link_source_choices() {
	$choices = array( 'custom' => __( 'رابط مخصص', 'mwm' ) );
	foreach ( mwm_page_roles() as $role => $label ) {
		/* translators: %s: page role label. */
		$choices[ 'role:' . $role ] = sprintf( __( 'صفحة: %s', 'mwm' ), $label );
	}
	for ( $i = 1; $i <= 3; $i++ ) {
		/* translators: %d: phone slot number. */
		$choices[ 'wa:' . $i ] = sprintf( __( 'واتساب – الرقم %d', 'mwm' ), $i );
		/* translators: %d: phone slot number. */
		$choices[ 'tel:' . $i ] = sprintf( __( 'اتصال – الرقم %d', 'mwm' ), $i );
	}
	$choices['email']   = __( 'البريد الإلكتروني', 'mwm' );
	$choices['profile'] = __( 'ملف تعريف الشركة (PDF)', 'mwm' );
	return $choices;
}

/**
 * Render a button/link from a resolved link.
 *
 * @param array  $link  From mwm_resolve_link().
 * @param string $label Visible label.
 * @param string $class CSS classes.
 * @param string $icon  Optional icon name.
 * @return string HTML ('' when label or href missing).
 */
function mwm_button( array $link, $label, $class = 'mwm-btn', $icon = '' ) {
	if ( '' === trim( (string) $label ) || empty( $link['href'] ) ) {
		return '';
	}
	$attrs = ! empty( $link['external'] ) ? ' target="_blank" rel="noopener"' : '';
	$icon  = $icon ? mwm_icon( $icon ) : '';
	return sprintf(
		'<a class="%1$s" href="%2$s"%3$s>%4$s<span>%5$s</span></a>',
		esc_attr( $class ),
		esc_url( $link['href'] ),
		$attrs,
		$icon,
		esc_html( $label )
	);
}

/**
 * Render a list of buttons.
 *
 * @param array  $buttons Each: label, link (resolved), style, icon.
 * @param string $extra   Extra class on each button (e.g. size).
 * @return string
 */
function mwm_buttons( array $buttons, $extra = '' ) {
	$html = '';
	foreach ( $buttons as $button ) {
		$style = ! empty( $button['style'] ) ? $button['style'] : 'primary';
		$class = trim( 'mwm-btn' . ( 'primary' !== $style ? ' mwm-btn--' . sanitize_html_class( $style ) : '' ) . ' ' . $extra );
		$html .= mwm_button( (array) ( $button['link'] ?? array() ), (string) ( $button['label'] ?? '' ), $class, (string) ( $button['icon'] ?? '' ) );
	}
	return '' === $html ? '' : '<div class="mwm-actions">' . $html . '</div>';
}

/**
 * Section heading block (kicker, title, lead).
 *
 * @param array  $args  Template args (kicker, title, text, title_tag).
 * @param string $class Modifier classes.
 * @param string $after Extra HTML after the text (escaped by caller).
 * @return string
 */
function mwm_section_head( array $args, $class = '', $after = '' ) {
	$kicker = (string) mwm_arg( $args, 'kicker' );
	$title  = (string) mwm_arg( $args, 'title' );
	$text   = (string) mwm_arg( $args, 'text' );
	$tag    = mwm_heading_tag( (string) mwm_arg( $args, 'title_tag', 'h2' ) );
	if ( '' === $kicker && '' === $title && '' === $text && '' === $after ) {
		return '';
	}
	$html = '<div class="' . esc_attr( trim( 'mwm-head ' . $class ) ) . '"><div>';
	if ( '' !== $kicker ) {
		$html .= '<p class="mwm-kicker">' . esc_html( $kicker ) . '</p>';
	}
	if ( '' !== $title ) {
		$html .= '<' . $tag . ' class="mwm-title">' . mwm_inline( $title ) . '</' . $tag . '>';
	}
	if ( '' !== $text ) {
		$html .= '<p class="mwm-lead">' . mwm_inline( $text ) . '</p>';
	}
	$html .= '</div>' . $after . '</div>';
	return $html;
}

/**
 * Whitelisted heading tag.
 *
 * @param string $tag Tag.
 * @return string
 */
function mwm_heading_tag( $tag ) {
	return in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'p', 'div' ), true ) ? $tag : 'h2';
}

/**
 * Allowed inline HTML in editable headings/text.
 *
 * @return array
 */
function mwm_inline_tags() {
	return array(
		'br'     => array(),
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'span'   => array( 'class' => true ),
		'mark'   => array(),
		'bdi'    => array( 'dir' => true ),
		'a'      => array(
			'href'   => true,
			'target' => true,
			'rel'    => true,
		),
	);
}

/**
 * Escape editable inline HTML; newlines become <br>.
 *
 * @param string $text Text.
 * @return string
 */
function mwm_inline( $text ) {
	return wp_kses( nl2br( (string) $text, false ), mwm_inline_tags() );
}

/**
 * Responsive image tag for an attachment. Lazy unless the caller says otherwise: core and Elementor
 * otherwise treat the first few images as in-viewport, and on phones section images rarely are.
 *
 * @param int    $id    Attachment ID.
 * @param string $size  Size.
 * @param array  $attrs Attributes.
 * @return string
 */
function mwm_image( $id, $size = 'large', array $attrs = array() ) {
	$id = (int) $id;
	if ( ! $id ) {
		return '';
	}
	return (string) wp_get_attachment_image( $id, $size, false, $attrs + array( 'loading' => 'lazy' ) );
}

/**
 * Is the current request an Elementor editor/preview request?
 *
 * @return bool
 */
function mwm_is_editor() {
	if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return true;
	}
	return class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor && \Elementor\Plugin::$instance->editor->is_edit_mode();
}

/**
 * Editor-only empty state (nothing on the public site).
 *
 * @param string $message Message.
 */
function mwm_editor_empty( $message ) {
	if ( mwm_is_editor() ) {
		printf( '<div class="mwm-editor-empty">%s</div>', esc_html( $message ) );
	}
}

/**
 * Does a post use an Elementor layout?
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function mwm_uses_elementor( $post_id ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return false;
	}
	$document = \Elementor\Plugin::$instance->documents->get( (int) $post_id );
	return $document && $document->is_built_with_elementor();
}

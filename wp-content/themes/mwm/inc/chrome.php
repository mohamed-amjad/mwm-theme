<?php
/**
 * Global chrome: header/footer resolution, floating WhatsApp, mobile action bar.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print the site header/footer: chosen Elementor template → theme template part.
 *
 * @param string $slot header|footer.
 */
function mwm_render_site_part( $slot ) {
	if ( is_singular( 'elementor_library' ) ) {
		return;
	}
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( $slot ) ) {
		return;
	}
	$template_id = (int) mwm_setting( $slot . '_template', 0 );
	if ( $template_id && did_action( 'elementor/loaded' ) && 'publish' === get_post_status( $template_id ) ) {
		$html = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id, false );
		if ( '' !== trim( (string) $html ) ) {
			// The wrapper is display:contents so the sticky header still sticks to the page, not to Elementor's boxes.
			echo '<div class="mwm-site-part mwm-site-part--' . esc_attr( $slot ) . '">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor-rendered markup.
			return;
		}
	}
	get_template_part( 'template-parts/' . $slot . '/site-' . $slot );
}

/**
 * Head-time CSS for the chosen header/footer templates (avoids a flash of unstyled chrome).
 */
function mwm_enqueue_template_css() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	foreach ( array( 'header', 'footer' ) as $slot ) {
		$id = (int) mwm_setting( $slot . '_template', 0 );
		if ( $id && 'publish' === get_post_status( $id ) ) {
			\Elementor\Plugin::instance()->frontend->enqueue_styles();
			\Elementor\Core\Files\CSS\Post::create( $id )->enqueue();
		}
	}
}
add_action( 'wp_enqueue_scripts', 'mwm_enqueue_template_css', 20 );

/**
 * Is the mobile bar shown?
 *
 * @return bool
 */
function mwm_bar_enabled() {
	return '1' === (string) mwm_setting( 'bar_enabled', '0' ) && ( mwm_phones() || mwm_page_url( 'quote' ) );
}

/**
 * Body class for the bar (bottom padding on phones).
 *
 * @param array $classes Classes.
 * @return array
 */
function mwm_bar_body_class( $classes ) {
	if ( mwm_bar_enabled() ) {
		$classes[] = 'mwm-has-bar';
	}
	return $classes;
}
add_filter( 'body_class', 'mwm_bar_body_class' );

/**
 * Floating WhatsApp picker (Popover API — works without JS) + mobile action bar.
 */
function mwm_render_floating_contact() {
	if ( is_singular( 'elementor_library' ) ) {
		return;
	}
	$wa      = mwm_phones_for( 'whatsapp' );
	$call    = mwm_phones_for( 'call' );
	$message = mwm_wa_message();
	$title   = (string) mwm_setting( 'float_title', __( 'تواصل معنا عبر واتساب', 'mwm' ) );

	if ( $wa && '1' === (string) mwm_setting( 'float_enabled', '0' ) ) :
		?>
		<div class="mwm-float">
			<?php if ( 1 === count( $wa ) ) : ?>
				<a class="mwm-float__btn" href="<?php echo esc_url( mwm_wa_url( $wa[0]['number'], $message ) ); ?>" target="_blank" rel="noopener" data-mwm-track="whatsapp">
					<?php echo mwm_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="mwm-sr"><?php echo esc_html( $title ); ?></span>
				</a>
			<?php else : ?>
				<button type="button" class="mwm-float__btn" popovertarget="mwm-wa-menu" aria-haspopup="true">
					<?php echo mwm_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="mwm-sr"><?php echo esc_html( $title ); ?></span>
				</button>
			<?php endif; ?>
		</div>
		<?php
	endif;

	if ( count( $wa ) > 1 ) :
		?>
		<div id="mwm-wa-menu" class="mwm-float__menu" popover>
			<p class="mwm-float__title"><?php echo esc_html( $title ); ?></p>
			<?php foreach ( $wa as $phone ) : ?>
				<a class="mwm-float__item" href="<?php echo esc_url( mwm_wa_url( $phone['number'], $message ) ); ?>" target="_blank" rel="noopener" data-mwm-track="whatsapp">
					<?php echo mwm_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span>
						<strong><?php echo esc_html( $phone['label'] ? $phone['label'] : __( 'واتساب', 'mwm' ) ); ?></strong>
						<small><?php echo mwm_bdi( $phone['display'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></small>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	endif;

	if ( ! mwm_bar_enabled() ) {
		return;
	}
	$quote = mwm_page_url( 'quote' );
	$cols  = ( $wa ? 1 : 0 ) + ( $call ? 1 : 0 ) + ( $quote ? 1 : 0 );
	?>
	<nav class="mwm-bar" style="--bar-cols:<?php echo (int) max( 1, $cols ); ?>" aria-label="<?php esc_attr_e( 'تواصل سريع', 'mwm' ); ?>" data-mwm-bar>
		<?php if ( $wa ) : ?>
			<?php if ( count( $wa ) > 1 ) : ?>
				<button type="button" popovertarget="mwm-wa-menu" data-mwm-track="whatsapp"><?php echo mwm_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'واتساب', 'mwm' ); ?></span></button>
			<?php else : ?>
				<a href="<?php echo esc_url( mwm_wa_url( $wa[0]['number'], $message ) ); ?>" target="_blank" rel="noopener" data-mwm-track="whatsapp"><?php echo mwm_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'واتساب', 'mwm' ); ?></span></a>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( $call ) : ?>
			<a href="<?php echo esc_url( mwm_tel_url( $call[0]['number'] ) ); ?>" data-mwm-track="call"><?php echo mwm_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'اتصال', 'mwm' ); ?></span></a>
		<?php endif; ?>
		<?php if ( $quote ) : ?>
			<a class="mwm-bar__primary" href="<?php echo esc_url( $quote ); ?>" data-mwm-track="quote"><?php echo mwm_icon( 'clipboard' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( (string) mwm_setting( 'bar_quote_label', __( 'عرض سعر', 'mwm' ) ) ); ?></span></a>
		<?php endif; ?>
	</nav>
	<?php
}
add_action( 'wp_footer', 'mwm_render_floating_contact', 5 );

/**
 * Analytics only when an ID is set.
 */
function mwm_render_analytics() {
	$id = trim( (string) mwm_setting( 'analytics_id' ) );
	if ( '' === $id || is_user_logged_in() ) {
		return;
	}
	if ( preg_match( '/^GTM-[A-Z0-9]+$/', $id ) ) {
		printf(
			"<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s);j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','%s');</script>\n",
			esc_js( $id )
		);
	} elseif ( preg_match( '/^G-[A-Z0-9]+$/', $id ) ) {
		printf( '<script async src="%s"></script>' . "\n", esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . $id ) );
		printf( "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','%s');</script>\n", esc_js( $id ) );
	}
}
add_action( 'wp_head', 'mwm_render_analytics', 20 );

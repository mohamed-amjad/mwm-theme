<?php
/**
 * Theme footer (settings, menus and records; the MWM footer widget passes overrides as args).
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args           = isset( $args ) && is_array( $args ) ? $args : array();
$mwm_about      = (string) mwm_arg( $args, 'about', mwm_setting( 'footer_about' ) );
$mwm_coverage   = mwm_arg( $args, 'show_coverage', true ) ? (string) mwm_setting( 'coverage' ) : '';
$mwm_email      = mwm_arg( $args, 'show_email', true ) ? sanitize_email( (string) mwm_setting( 'email' ) ) : '';
$mwm_phones     = mwm_arg( $args, 'show_phones', true ) ? mwm_phones() : array();
$mwm_services   = mwm_arg( $args, 'show_services', true ) ? mwm_get_services() : array();
$mwm_socials    = mwm_arg( $args, 'show_socials', true ) ? mwm_socials() : array();
$mwm_show_menu  = mwm_arg( $args, 'show_menu', true ) && has_nav_menu( 'footer' );
$mwm_menu_title = (string) mwm_arg( $args, 'menu_title', '' );
$mwm_menu_title = '' !== $mwm_menu_title ? $mwm_menu_title : (string) wp_get_nav_menu_name( 'footer' );
$mwm_svc_title  = (string) mwm_arg( $args, 'services_title', mwm_setting( 'footer_services_title', get_post_type_object( 'mwm_service' )->labels->name ) );
$mwm_ct_title   = (string) mwm_arg( $args, 'contact_title', mwm_setting( 'footer_contact_title', __( 'تواصل معنا', 'mwm' ) ) );
$mwm_privacy    = mwm_arg( $args, 'show_privacy', true ) ? mwm_page_url( 'privacy' ) : '';
$mwm_copy       = str_replace( '{year}', wp_date( 'Y' ), (string) mwm_arg( $args, 'copyright', mwm_setting( 'copyright' ) ) );
$mwm_ids        = ! mwm_arg( $args, 'show_ids', true ) ? array() : array_filter(
	array(
		__( 'السجل التجاري', 'mwm' )    => (string) mwm_setting( 'cr_number' ),
		__( 'الرقم الموحد', 'mwm' )     => (string) mwm_setting( 'unified_number' ),
		__( 'الرقم الضريبي', 'mwm' )    => (string) mwm_setting( 'vat_number' ),
	)
);
$mwm_has_contact = $mwm_phones || $mwm_email || '' !== $mwm_coverage;
?>
<footer class="mwm-footer" aria-labelledby="mwm-footer-title">
	<h2 id="mwm-footer-title" class="mwm-sr"><?php esc_html_e( 'تذييل الموقع', 'mwm' ); ?></h2>
	<div class="mwm-container mwm-footer__main">
		<div>
			<?php get_template_part( 'template-parts/header/brand', null, array( 'lazy' => true ) ); ?>
			<?php if ( '' !== $mwm_about ) : ?>
				<p class="mwm-footer__about"><?php echo esc_html( $mwm_about ); ?></p>
			<?php endif; ?>
			<?php if ( $mwm_socials ) : ?>
				<div class="mwm-socials">
					<?php foreach ( $mwm_socials as $mwm_net => $mwm_url ) : ?>
						<a href="<?php echo esc_url( $mwm_url ); ?>" target="_blank" rel="noopener">
							<?php echo mwm_icon( $mwm_net ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span class="mwm-sr"><?php echo esc_html( ucfirst( $mwm_net ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $mwm_show_menu ) : ?>
			<nav aria-label="<?php esc_attr_e( 'روابط سريعة', 'mwm' ); ?>">
				<?php if ( '' !== $mwm_menu_title ) : ?>
					<h3 class="mwm-footer__title"><?php echo esc_html( $mwm_menu_title ); ?></h3>
				<?php endif; ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<?php if ( $mwm_services ) : ?>
			<nav aria-label="<?php esc_attr_e( 'الخدمات', 'mwm' ); ?>">
				<?php if ( '' !== $mwm_svc_title ) : ?>
					<h3 class="mwm-footer__title"><?php echo esc_html( $mwm_svc_title ); ?></h3>
				<?php endif; ?>
				<ul>
					<?php foreach ( $mwm_services as $mwm_service ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $mwm_service ) ); ?>"><?php echo esc_html( get_the_title( $mwm_service ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

		<?php if ( $mwm_has_contact ) : ?>
		<div>
			<?php if ( '' !== $mwm_ct_title ) : ?>
				<h3 class="mwm-footer__title"><?php echo esc_html( $mwm_ct_title ); ?></h3>
			<?php endif; ?>
			<ul class="mwm-footer__contact">
				<?php foreach ( $mwm_phones as $mwm_p ) : ?>
					<li>
						<?php echo mwm_icon( $mwm_p['call'] ? 'phone' : 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span>
							<a href="<?php echo esc_url( $mwm_p['call'] ? mwm_tel_url( $mwm_p['number'] ) : mwm_wa_url( $mwm_p['number'] ) ); ?>"><?php echo mwm_bdi( $mwm_p['display'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
							<?php if ( $mwm_p['label'] ) : ?>
								<small>— <?php echo esc_html( $mwm_p['label'] ); ?></small>
							<?php endif; ?>
						</span>
					</li>
				<?php endforeach; ?>
				<?php if ( $mwm_email ) : ?>
					<li><?php echo mwm_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a href="<?php echo esc_url( 'mailto:' . $mwm_email ); ?>"><?php echo mwm_bdi( $mwm_email ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
				<?php endif; ?>
				<?php if ( '' !== $mwm_coverage ) : ?>
					<li><?php echo mwm_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $mwm_coverage ); ?></span></li>
				<?php endif; ?>
			</ul>
		</div>
		<?php endif; ?>
	</div>

	<div class="mwm-footer__bottom">
		<div class="mwm-container">
			<?php if ( '' !== trim( $mwm_copy ) ) : ?>
				<p><?php echo esc_html( $mwm_copy ); ?></p>
			<?php endif; ?>
			<?php if ( $mwm_ids || $mwm_privacy ) : ?>
				<ul class="mwm-footer__ids">
					<?php foreach ( $mwm_ids as $mwm_label => $mwm_value ) : ?>
						<li><?php echo esc_html( $mwm_label ); ?>: <?php echo mwm_bdi( $mwm_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
					<?php endforeach; ?>
					<?php if ( $mwm_privacy ) : ?>
						<li><a href="<?php echo esc_url( $mwm_privacy ); ?>"><?php echo esc_html( get_the_title( mwm_page_id( 'privacy' ) ) ); ?></a></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</footer>

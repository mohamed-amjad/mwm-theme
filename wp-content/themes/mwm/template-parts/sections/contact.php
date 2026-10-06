<?php
/**
 * Contact cards fed by the site settings: one card for all phone numbers, then email, then address/coverage.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args      = isset( $args ) && is_array( $args ) ? $args : array();
$show_tel  = (bool) mwm_arg( $args, 'show_phones', true );
$show_mail = (bool) mwm_arg( $args, 'show_email', true );
$show_cov  = (bool) mwm_arg( $args, 'show_coverage', true );
$labels    = array(
	'tel'  => (string) mwm_arg( $args, 'phones_label' ),
	'mail' => (string) mwm_arg( $args, 'email_label' ),
	'cov'  => (string) mwm_arg( $args, 'coverage_label' ),
);
$email     = sanitize_email( (string) mwm_setting( 'email' ) );
$coverage  = (string) mwm_setting( 'coverage' );
$section   = (string) mwm_arg( $args, 'section_class', '' );
$phones    = $show_tel ? mwm_phones() : array();
$has_any   = $phones || ( $show_mail && $email ) || ( $show_cov && '' !== $coverage );
?>
<section class="<?php echo esc_attr( trim( 'mwm-section mwm-contact ' . $section ) ); ?>">
	<div class="mwm-container">
		<?php echo mwm_section_head( $args, 'mwm-head--center' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( ! $has_any ) : ?>
			<?php mwm_editor_empty( __( 'أضف أرقام التواصل والبريد من إعدادات الموقع.', 'mwm' ) ); ?>
		<?php else : ?>
			<ul class="mwm-contact__grid" role="list">
				<?php if ( $phones ) : ?>
					<li data-mwm-reveal>
						<div class="mwm-contact__card">
							<span class="mwm-contact__icon"><?php echo mwm_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php if ( '' !== $labels['tel'] ) : ?>
								<span class="mwm-contact__label"><?php echo esc_html( $labels['tel'] ); ?></span>
							<?php endif; ?>
							<ul class="mwm-contact__numbers" role="list">
								<?php foreach ( $phones as $p ) : ?>
									<li>
										<a class="mwm-contact__value" href="<?php echo esc_url( mwm_tel_url( $p['number'] ) ); ?>" data-mwm-track="phone"><?php echo mwm_bdi( $p['display'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
										<?php if ( '' !== $p['label'] ) : ?>
											<span class="mwm-contact__note"><?php echo esc_html( $p['label'] ); ?></span>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</li>
				<?php endif; ?>
				<?php if ( $show_mail && $email ) : ?>
					<li data-mwm-reveal>
						<a class="mwm-contact__card" href="<?php echo esc_url( 'mailto:' . $email ); ?>" data-mwm-track="mail">
							<span class="mwm-contact__icon"><?php echo mwm_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php if ( '' !== $labels['mail'] ) : ?>
								<span class="mwm-contact__label"><?php echo esc_html( $labels['mail'] ); ?></span>
							<?php endif; ?>
							<span class="mwm-contact__value"><?php echo mwm_bdi( $email ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( $show_cov && '' !== $coverage ) : ?>
					<li data-mwm-reveal>
						<div class="mwm-contact__card">
							<span class="mwm-contact__icon"><?php echo mwm_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php if ( '' !== $labels['cov'] ) : ?>
								<span class="mwm-contact__label"><?php echo esc_html( $labels['cov'] ); ?></span>
							<?php endif; ?>
							<span class="mwm-contact__value"><?php echo esc_html( $coverage ); ?></span>
						</div>
					</li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>

<?php
/**
 * Quote / site-visit request section: form + contact aside.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args       = isset( $args ) && is_array( $args ) ? $args : array();
$aside_t    = (string) mwm_arg( $args, 'aside_title' );
$aside_x    = (string) mwm_arg( $args, 'aside_text' );
$aside_pts  = array_filter( (array) mwm_arg( $args, 'aside_points', array() ) );
$show_aside = (bool) mwm_arg( $args, 'show_aside', true );
$section    = (string) mwm_arg( $args, 'section_class', '' );
$message    = mwm_wa_message();
?>
<section id="quote" class="<?php echo esc_attr( trim( 'mwm-section mwm-quote ' . $section ) ); ?>">
	<div class="mwm-container">
		<?php echo mwm_section_head( $args, 'mwm-head--center' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="mwm-quote__grid<?php echo $show_aside ? '' : ' mwm-quote__grid--solo'; ?>">
			<div class="mwm-quote__form">
				<?php mwm_render_quote_form( $args ); ?>
			</div>
			<?php if ( $show_aside ) : ?>
				<aside class="mwm-quote__aside mwm-dark">
					<?php if ( '' !== $aside_t ) : ?>
						<h3 class="mwm-quote__aside-title"><?php echo esc_html( $aside_t ); ?></h3>
					<?php endif; ?>
					<?php if ( '' !== $aside_x ) : ?>
						<p><?php echo mwm_inline( $aside_x ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
					<?php endif; ?>
					<?php if ( $aside_pts ) : ?>
						<ul class="mwm-ticks mwm-ticks--light">
							<?php foreach ( $aside_pts as $point ) : ?>
								<li><?php echo mwm_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $point ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<div class="mwm-quote__direct">
						<?php foreach ( mwm_phones_for( 'whatsapp' ) as $p ) : ?>
							<a class="mwm-quote__line" href="<?php echo esc_url( mwm_wa_url( $p['number'], $message ) ); ?>" target="_blank" rel="noopener" data-mwm-track="whatsapp">
								<?php echo mwm_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><small><?php echo esc_html( $p['label'] ); ?></small><?php echo mwm_bdi( $p['display'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</a>
						<?php endforeach; ?>
						<?php
						$email = sanitize_email( (string) mwm_setting( 'email' ) );
						if ( $email ) :
							?>
							<a class="mwm-quote__line" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
								<?php echo mwm_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php echo mwm_bdi( $email ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</a>
						<?php endif; ?>
					</div>
				</aside>
			<?php endif; ?>
		</div>
	</div>
</section>

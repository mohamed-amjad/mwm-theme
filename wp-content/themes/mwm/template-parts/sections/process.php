<?php
/**
 * Numbered process steps.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args    = isset( $args ) && is_array( $args ) ? $args : array();
$steps   = array_values( array_filter( (array) mwm_arg( $args, 'steps', array() ), static fn( $s ) => '' !== trim( (string) ( $s['title'] ?? '' ) ) ) );
$tone    = (string) mwm_arg( $args, 'tone', '' );
$section = (string) mwm_arg( $args, 'section_class', '' );
?>
<section class="<?php echo esc_attr( trim( 'mwm-section mwm-process ' . ( $tone ? 'mwm-section--' . sanitize_html_class( $tone ) : '' ) . ' ' . $section ) ); ?>">
	<div class="mwm-container">
		<?php echo mwm_section_head( $args, 'mwm-head--center' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( ! $steps ) : ?>
			<?php mwm_editor_empty( __( 'أضف خطوات من لوحة الأداة.', 'mwm' ) ); ?>
		<?php else : ?>
			<ol class="mwm-steps" style="--cols:<?php echo (int) min( 5, count( $steps ) ); ?>">
				<?php foreach ( $steps as $index => $step ) : ?>
					<li class="mwm-step" data-mwm-reveal>
						<span class="mwm-step__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<?php if ( ! empty( $step['icon'] ) ) : ?>
							<span class="mwm-step__icon"><?php echo mwm_icon( (string) $step['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
						<h3 class="mwm-step__title"><?php echo esc_html( (string) $step['title'] ); ?></h3>
						<?php if ( ! empty( $step['text'] ) ) : ?>
							<p class="mwm-step__text"><?php echo mwm_inline( (string) $step['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>
</section>

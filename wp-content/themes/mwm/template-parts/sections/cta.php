<?php
/**
 * Call-to-action band.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args    = isset( $args ) && is_array( $args ) ? $args : array();
$kicker  = (string) mwm_arg( $args, 'kicker' );
$title   = (string) mwm_arg( $args, 'title' );
$text    = (string) mwm_arg( $args, 'text' );
$tag     = mwm_heading_tag( (string) mwm_arg( $args, 'title_tag', 'h2' ) );
$buttons = (array) mwm_arg( $args, 'buttons', array() );
$image   = (int) mwm_arg( $args, 'image', 0 );
$style   = (string) mwm_arg( $args, 'style', 'deep' );
$section = (string) mwm_arg( $args, 'section_class', '' );
?>
<section class="<?php echo esc_attr( trim( 'mwm-section mwm-section--tight mwm-cta mwm-cta--' . sanitize_html_class( $style ) . ' ' . $section ) ); ?>">
	<div class="mwm-container">
		<div class="mwm-cta__box mwm-dark" data-mwm-reveal>
			<?php if ( $image ) : ?>
				<div class="mwm-cta__image" aria-hidden="true"><?php echo mwm_image( $image, 'mwm-wide', array( 'alt' => '', 'sizes' => '(max-width: 1024px) 100vw, 1240px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
			<div class="mwm-cta__content">
				<?php if ( '' !== $kicker ) : ?>
					<p class="mwm-kicker"><?php echo esc_html( $kicker ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $title ) : ?>
					<<?php echo esc_html( $tag ); ?> class="mwm-cta__title"><?php echo mwm_inline( $title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $tag ); ?>>
				<?php endif; ?>
				<?php if ( '' !== $text ) : ?>
					<p class="mwm-cta__text"><?php echo mwm_inline( $text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php endif; ?>
			</div>
			<?php echo mwm_buttons( $buttons, 'mwm-btn--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
</section>

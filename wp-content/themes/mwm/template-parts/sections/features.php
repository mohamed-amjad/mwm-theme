<?php
/**
 * Feature list: why MWM, trust strip, industries — layouts grid | strip | tiles | split.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args    = isset( $args ) && is_array( $args ) ? $args : array();
$items   = array_values( array_filter( (array) mwm_arg( $args, 'items', array() ), static fn( $i ) => '' !== trim( (string) ( $i['title'] ?? '' ) ) ) );
$layout  = (string) mwm_arg( $args, 'layout', 'grid' );
$cols    = max( 1, min( 6, (int) mwm_arg( $args, 'columns', 3 ) ) );
$image   = (int) mwm_arg( $args, 'image', 0 );
$buttons = (array) mwm_arg( $args, 'buttons', array() );
$tone    = (string) mwm_arg( $args, 'tone', '' );
$section = (string) mwm_arg( $args, 'section_class', '' );

$classes = array( 'mwm-section', 'mwm-features', 'mwm-features--' . sanitize_html_class( $layout ) );
if ( $tone ) {
	$classes[] = 'mwm-section--' . sanitize_html_class( $tone );
}
if ( $section ) {
	$classes[] = $section;
}
$head_class = 'mwm-head--center';
// Without a section heading, h3 items would skip a level after the page H1.
$item_tag = ( 'strip' === $layout || '' === trim( (string) mwm_arg( $args, 'title', '' ) ) ) ? 'p' : 'h3';
?>
<section class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<div class="mwm-container<?php echo 'split' === $layout ? ' mwm-features__split' : ''; ?>">
		<?php if ( 'split' === $layout ) : ?>
			<div class="mwm-features__intro">
				<?php echo mwm_section_head( $args, '', mwm_buttons( $buttons ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php if ( $image ) : ?>
					<div class="mwm-features__image" data-mwm-reveal><?php echo mwm_image( $image, 'mwm-card', array( 'sizes' => '(max-width: 1024px) 92vw, 520px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
			</div>
		<?php elseif ( 'strip' !== $layout ) : ?>
			<?php echo mwm_section_head( $args, $head_class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>

		<?php if ( ! $items ) : ?>
			<?php mwm_editor_empty( __( 'أضف عناصر من لوحة الأداة.', 'mwm' ) ); ?>
		<?php else : ?>
			<ul class="mwm-features__list" style="--cols:<?php echo (int) $cols; ?>" role="list">
				<?php foreach ( $items as $item ) : ?>
					<li class="mwm-feature" data-mwm-reveal>
						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<span class="mwm-feature__icon"><?php echo mwm_icon( (string) $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
						<div class="mwm-feature__body">
							<<?php echo esc_html( $item_tag ); ?> class="mwm-feature__title"><?php echo esc_html( (string) $item['title'] ); ?></<?php echo esc_html( $item_tag ); ?>>
							<?php if ( ! empty( $item['text'] ) ) : ?>
								<p class="mwm-feature__text"><?php echo mwm_inline( (string) $item['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( 'split' !== $layout && $buttons ) : ?>
			<div class="mwm-features__actions"><?php echo mwm_buttons( $buttons ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php endif; ?>
	</div>
</section>

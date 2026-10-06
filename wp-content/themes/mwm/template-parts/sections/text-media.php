<?php
/**
 * Text + image split (about intro, service intro).
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args     = isset( $args ) && is_array( $args ) ? $args : array();
$kicker   = (string) mwm_arg( $args, 'kicker' );
$title    = (string) mwm_arg( $args, 'title' );
$tag      = mwm_heading_tag( (string) mwm_arg( $args, 'title_tag', 'h2' ) );
$content  = (string) mwm_arg( $args, 'content' );
$points   = array_filter( (array) mwm_arg( $args, 'points', array() ) );
$image    = (int) mwm_arg( $args, 'image', 0 );
$image_b  = (int) mwm_arg( $args, 'image_secondary', 0 );
$position = 'start' === mwm_arg( $args, 'image_position', 'end' ) ? 'start' : 'end';
$buttons  = (array) mwm_arg( $args, 'buttons', array() );
$note_t   = (string) mwm_arg( $args, 'note_title' );
$note_x   = (string) mwm_arg( $args, 'note_text' );
$tone     = (string) mwm_arg( $args, 'tone', '' );
$section  = (string) mwm_arg( $args, 'section_class', '' );
?>
<section class="<?php echo esc_attr( trim( 'mwm-section mwm-split mwm-split--media-' . $position . ( $tone ? ' mwm-section--' . sanitize_html_class( $tone ) : '' ) . ' ' . $section ) ); ?>">
	<div class="mwm-container mwm-split__grid<?php echo $image ? '' : ' mwm-split__grid--solo'; ?>">
		<div class="mwm-split__content">
			<?php if ( '' !== $kicker ) : ?>
				<p class="mwm-kicker"><?php echo esc_html( $kicker ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $title ) : ?>
				<<?php echo esc_html( $tag ); ?> class="mwm-title"><?php echo mwm_inline( $title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $tag ); ?>>
			<?php endif; ?>
			<?php if ( '' !== trim( wp_strip_all_tags( $content ) ) ) : ?>
				<div class="mwm-split__text mwm-prose"><?php echo wp_kses_post( wpautop( $content ) ); ?></div>
			<?php endif; ?>
			<?php if ( $points ) : ?>
				<ul class="mwm-ticks mwm-ticks--lg">
					<?php foreach ( $points as $point ) : ?>
						<li><?php echo mwm_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $point ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php echo mwm_buttons( $buttons ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php if ( $image ) : ?>
			<div class="mwm-split__media" data-mwm-reveal>
				<div class="mwm-split__frame"><?php echo mwm_image( $image, 'mwm-wide', array( 'sizes' => '(max-width: 1024px) 92vw, 600px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php if ( $image_b ) : ?>
					<div class="mwm-split__frame mwm-split__frame--sub"><?php echo mwm_image( $image_b, 'mwm-card', array( 'sizes' => '260px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
				<?php if ( '' !== $note_t || '' !== $note_x ) : ?>
					<div class="mwm-split__note">
						<?php echo mwm_icon( 'leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<div>
							<?php if ( '' !== $note_t ) : ?>
								<strong><?php echo esc_html( $note_t ); ?></strong>
							<?php endif; ?>
							<?php if ( '' !== $note_x ) : ?>
								<span><?php echo esc_html( $note_x ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

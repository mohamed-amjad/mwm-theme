<?php
/**
 * Home hero: split layout, crossfading photo slider (first slide is the LCP candidate), slogan, CTAs, trust points.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args     = isset( $args ) && is_array( $args ) ? $args : array();
$kicker   = (string) mwm_arg( $args, 'kicker' );
$title    = (string) mwm_arg( $args, 'title' );
$text     = (string) mwm_arg( $args, 'text' );
$tag      = mwm_heading_tag( (string) mwm_arg( $args, 'title_tag', 'h1' ) );
$slides   = array_values( array_filter( array_map( 'absint', (array) mwm_arg( $args, 'slides', array() ) ) ) );
$buttons  = (array) mwm_arg( $args, 'buttons', array() );
$points   = array_filter( (array) mwm_arg( $args, 'points', array() ) );
$chip     = (string) mwm_arg( $args, 'chip' );
$interval = max( 3, min( 15, (int) mwm_arg( $args, 'interval', 6 ) ) );

if ( ! $slides && (int) mwm_arg( $args, 'image', 0 ) ) {
	$slides = array( (int) mwm_arg( $args, 'image', 0 ) );
}
$total = count( $slides );
?>
<section class="mwm-hero mwm-dark<?php echo $total ? '' : ' mwm-hero--no-media'; ?>">
	<div class="mwm-hero__bg" aria-hidden="true"></div>
	<div class="mwm-container mwm-hero__grid">
		<div class="mwm-hero__content">
			<?php if ( '' !== $kicker ) : ?>
				<p class="mwm-kicker mwm-hero__kicker"><?php echo esc_html( $kicker ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $title ) : ?>
				<<?php echo esc_html( $tag ); ?> class="mwm-hero__title"><?php echo mwm_inline( $title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $tag ); ?>>
			<?php endif; ?>
			<?php if ( '' !== $text ) : ?>
				<p class="mwm-hero__text"><?php echo mwm_inline( $text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php endif; ?>
			<?php echo mwm_buttons( $buttons, 'mwm-btn--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $points ) : ?>
				<ul class="mwm-hero__points">
					<?php foreach ( $points as $point ) : ?>
						<li><?php echo mwm_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $point ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php if ( $total ) : ?>
			<div class="mwm-hero__media">
				<span class="mwm-hero__outline" aria-hidden="true"></span>
				<div
					class="mwm-hero__frame"
					<?php if ( $total > 1 ) : ?>
						data-mwm-slider
						style="--mwm-slide-ms: <?php echo esc_attr( $interval * 1000 ); ?>ms"
						role="region"
						aria-roledescription="<?php esc_attr_e( 'عرض صور', 'mwm' ); ?>"
						aria-label="<?php esc_attr_e( 'صور من أعمالنا الميدانية', 'mwm' ); ?>"
					<?php endif; ?>
				>
					<div class="mwm-hero__slides">
						<?php foreach ( $slides as $i => $slide_id ) : ?>
							<figure class="mwm-hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>"<?php echo 0 === $i ? '' : ' aria-hidden="true"'; ?>>
								<?php
								echo mwm_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									$slide_id,
									'mwm-wide',
									array(
										// The first slide is above the fold at every width (it leads the hero on phones), so it is the LCP candidate.
										'loading'       => 0 === $i ? 'eager' : 'lazy',
										'fetchpriority' => 0 === $i ? 'high' : 'auto',
										'decoding'      => 'async',
										'sizes'         => '(max-width: 1024px) 92vw, 600px',
									)
								);
								?>
							</figure>
						<?php endforeach; ?>
					</div>

					<?php if ( $total > 1 ) : ?>
						<div class="mwm-hero__controls">
							<span class="mwm-hero__count" aria-hidden="true"><b data-mwm-current>01</b> / <?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span>
							<div class="mwm-hero__dots">
								<?php foreach ( $slides as $i => $slide_id ) : ?>
									<?php /* translators: %d: slide number. */ ?>
									<button type="button" class="mwm-hero__dot" data-mwm-dot="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'الصورة %d', 'mwm' ), $i + 1 ) ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>><span></span></button>
								<?php endforeach; ?>
							</div>
							<div class="mwm-hero__navs">
								<button type="button" class="mwm-hero__nav mwm-hero__nav--prev" data-mwm-prev aria-label="<?php esc_attr_e( 'الصورة السابقة', 'mwm' ); ?>"><?php echo mwm_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
								<button type="button" class="mwm-hero__nav mwm-hero__nav--next" data-mwm-next aria-label="<?php esc_attr_e( 'الصورة التالية', 'mwm' ); ?>"><?php echo mwm_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( '' !== $chip ) : ?>
					<span class="mwm-hero__chip"><?php echo mwm_icon( 'wind' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $chip ); ?></span>
				<?php endif; ?>
				<span class="mwm-hero__leaf mwm-hero__leaf--a" aria-hidden="true"></span>
				<span class="mwm-hero__leaf mwm-hero__leaf--b" aria-hidden="true"></span>
			</div>
		<?php endif; ?>
	</div>
</section>

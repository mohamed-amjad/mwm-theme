<?php
/**
 * Service cards from the Services records.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args       = isset( $args ) && is_array( $args ) ? $args : array();
$ids        = array_filter( array_map( 'intval', (array) mwm_arg( $args, 'ids', array() ) ) );
$services   = $ids ? array_values( array_filter( array_map( 'get_post', $ids ), static fn( $p ) => $p && 'publish' === $p->post_status ) ) : mwm_get_services();
$show_img   = (bool) mwm_arg( $args, 'show_image', true );
$show_pts   = (bool) mwm_arg( $args, 'show_highlights', true );
$max_pts    = (int) mwm_arg( $args, 'max_highlights', 4 );
$link_label = (string) mwm_arg( $args, 'link_label' );
$cols       = max( 1, min( 4, (int) mwm_arg( $args, 'columns', 3 ) ) );
$exclude    = (int) mwm_arg( $args, 'exclude', 0 );
$section    = (string) mwm_arg( $args, 'section_class', '' );

if ( $exclude ) {
	$services = array_values( array_filter( $services, static fn( $p ) => $p->ID !== $exclude ) );
}
?>
<section class="<?php echo esc_attr( trim( 'mwm-section mwm-services ' . $section ) ); ?>">
	<div class="mwm-container">
		<?php echo mwm_section_head( $args, 'mwm-head--center' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( ! $services ) : ?>
			<?php mwm_editor_empty( __( 'لا توجد خدمات بعد — أضفها من لوحة التحكم ← الخدمات.', 'mwm' ) ); ?>
		<?php else : ?>
			<div class="mwm-cards<?php echo count( $services ) < $cols ? ' mwm-cards--short' : ''; ?>" style="--cols:<?php echo (int) $cols; ?>;--count:<?php echo (int) count( $services ); ?>">
				<?php
				foreach ( $services as $service ) :
					$card = mwm_service_card( $service );
					?>
					<article class="mwm-service-card" data-mwm-reveal>
						<?php if ( $show_img && $card['image'] ) : ?>
							<a class="mwm-service-card__media" href="<?php echo esc_url( $card['url'] ); ?>" tabindex="-1" aria-hidden="true">
								<?php echo mwm_image( $card['image'], 'mwm-card', array( 'sizes' => '(max-width: 767px) 92vw, (max-width: 1024px) 46vw, 400px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php endif; ?>
						<div class="mwm-service-card__body">
							<div class="mwm-service-card__top">
								<span class="mwm-chip-icon"><?php echo mwm_icon( $card['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</div>
							<?php if ( '' !== $card['kicker'] ) : ?>
								<p class="mwm-service-card__kicker"><?php echo esc_html( $card['kicker'] ); ?></p>
							<?php endif; ?>
							<h3 class="mwm-service-card__title"><a href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['title'] ); ?></a></h3>
							<?php if ( '' !== $card['excerpt'] ) : ?>
								<p class="mwm-service-card__text"><?php echo esc_html( $card['excerpt'] ); ?></p>
							<?php endif; ?>
							<?php if ( $show_pts && $card['highlights'] ) : ?>
								<ul class="mwm-ticks">
									<?php foreach ( array_slice( $card['highlights'], 0, max( 1, $max_pts ) ) as $point ) : ?>
										<li><?php echo mwm_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $point ); ?></span></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<?php if ( '' !== $link_label ) : ?>
								<a class="mwm-link mwm-service-card__link" href="<?php echo esc_url( $card['url'] ); ?>">
									<span><?php echo esc_html( $link_label ); ?><span class="mwm-sr"> — <?php echo esc_html( $card['title'] ); ?></span></span>
									<?php echo mwm_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

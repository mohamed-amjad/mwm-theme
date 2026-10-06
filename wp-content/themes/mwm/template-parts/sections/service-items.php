<?php
/**
 * Sub-service items of one service.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args    = isset( $args ) && is_array( $args ) ? $args : array();
$service = (int) mwm_arg( $args, 'service', is_singular( 'mwm_service' ) ? get_queried_object_id() : 0 );
$items   = mwm_get_service_items( $service );
$cols    = max( 1, min( 4, (int) mwm_arg( $args, 'columns', 3 ) ) );
$layout  = (string) mwm_arg( $args, 'layout', 'cards' );
$section = (string) mwm_arg( $args, 'section_class', '' );
$images  = (bool) mwm_arg( $args, 'show_image', true );
?>
<section class="<?php echo esc_attr( trim( 'mwm-section mwm-items ' . $section ) ); ?>">
	<div class="mwm-container">
		<?php echo mwm_section_head( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( ! $items ) : ?>
			<?php mwm_editor_empty( __( 'لا توجد بنود لهذه الخدمة — أضفها من الخدمات ← بنود الخدمات.', 'mwm' ) ); ?>
		<?php else : ?>
			<div class="mwm-items__grid mwm-items__grid--<?php echo esc_attr( sanitize_html_class( $layout ) ); ?>" style="--cols:<?php echo (int) $cols; ?>">
				<?php
				foreach ( $items as $index => $item ) :
					$icon  = (string) ( get_post_meta( $item->ID, 'icon', true ) ?: 'check' );
					$thumb = $images ? (int) get_post_thumbnail_id( $item ) : 0;
					$body  = trim( (string) $item->post_content );
					?>
					<article class="mwm-item<?php echo $thumb ? ' mwm-item--media' : ''; ?>" data-mwm-reveal>
						<?php if ( $thumb ) : ?>
							<div class="mwm-item__media"><?php echo mwm_image( $thumb, 'mwm-card', array( 'sizes' => '(max-width: 767px) 92vw, 400px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<?php endif; ?>
						<div class="mwm-item__body">
							<span class="mwm-item__icon"><?php echo mwm_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<h3 class="mwm-item__title"><?php echo esc_html( get_the_title( $item ) ); ?></h3>
							<?php if ( '' !== $body ) : ?>
								<div class="mwm-item__text"><?php echo wp_kses_post( wpautop( $body ) ); ?></div>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

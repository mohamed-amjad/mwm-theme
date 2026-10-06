<?php
/**
 * Works gallery grouped by service line, with filter tabs and a <dialog> lightbox.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args      = isset( $args ) && is_array( $args ) ? $args : array();
$source    = (int) mwm_arg( $args, 'service', 0 );
$show_tabs = (bool) mwm_arg( $args, 'tabs', true );
$all_label = (string) mwm_arg( $args, 'all_label' );
$limit     = (int) mwm_arg( $args, 'limit', 0 );
$cols      = max( 2, min( 5, (int) mwm_arg( $args, 'columns', 4 ) ) );
$buttons   = (array) mwm_arg( $args, 'buttons', array() );
$section   = (string) mwm_arg( $args, 'section_class', '' );
$groups    = (array) mwm_arg( $args, 'groups', mwm_gallery_groups() );

if ( $source && ! array_key_exists( 'groups', $args ) ) {
	$groups    = isset( $groups[ $source ] ) ? array( $source => $groups[ $source ] ) : array();
	$show_tabs = false;
}

$photos = array();
foreach ( $groups as $group ) {
	foreach ( $group['ids'] as $id ) {
		$photos[] = array(
			'id'    => $id,
			'group' => $group['slug'],
			'title' => $group['title'],
		);
	}
}

if ( $limit > 0 && ! $show_tabs ) {
	$photos = array_slice( $photos, 0, $limit );
} elseif ( $limit > 0 ) {
	// Round-robin across groups so every tab has photos and the "all" grid stays at exactly $limit.
	$queues = array();
	foreach ( $photos as $p ) {
		$queues[ $p['group'] ][] = $p;
	}
	$queues = array_values( $queues );
	$photos = array();
	for ( $round = 0; count( $photos ) < $limit; $round++ ) {
		$added = false;
		foreach ( $queues as $queue ) {
			if ( isset( $queue[ $round ] ) && count( $photos ) < $limit ) {
				$photos[] = $queue[ $round ];
				$added    = true;
			}
		}
		if ( ! $added ) {
			break;
		}
	}
}
$uid = 'mwm-gal-' . wp_unique_id();
?>
<section class="<?php echo esc_attr( trim( 'mwm-section mwm-gallery ' . $section ) ); ?>" data-mwm-gallery>
	<div class="mwm-container">
		<?php echo mwm_section_head( $args, 'mwm-head--center' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( ! $photos ) : ?>
			<?php mwm_editor_empty( array_key_exists( 'groups', $args ) ? __( 'لا توجد صور بعد — أضف تبويباً واختر صوره من لوحة الإعدادات.', 'mwm' ) : __( 'لا توجد صور بعد — أضفها من صفحة كل خدمة ← صور الخدمة.', 'mwm' ) ); ?>
		<?php else : ?>
			<?php if ( $show_tabs && count( $groups ) > 1 ) : ?>
				<div class="mwm-tabs" role="group" aria-label="<?php esc_attr_e( 'تصفية الصور حسب الخدمة', 'mwm' ); ?>" aria-controls="<?php echo esc_attr( $uid ); ?>">
					<?php if ( '' !== $all_label ) : ?>
						<button type="button" class="mwm-tab" aria-pressed="true" data-filter="*"><?php echo esc_html( $all_label ); ?></button>
					<?php endif; ?>
					<?php
					$first = '' === $all_label;
					foreach ( $groups as $group ) :
						?>
						<button type="button" class="mwm-tab" aria-pressed="<?php echo $first ? 'true' : 'false'; ?>" data-filter="<?php echo esc_attr( $group['slug'] ); ?>"><?php echo esc_html( $group['title'] ); ?></button>
						<?php
						$first = false;
					endforeach;
					?>
				</div>
			<?php endif; ?>
			<ul id="<?php echo esc_attr( $uid ); ?>" class="mwm-gallery__grid" style="--cols:<?php echo (int) $cols; ?>" role="list" aria-live="polite">
				<?php
				foreach ( $photos as $photo ) :
					$full = wp_get_attachment_image_url( $photo['id'], 'mwm-wide' );
					$alt  = trim( (string) get_post_meta( $photo['id'], '_wp_attachment_image_alt', true ) );
					?>
					<li class="mwm-gallery__item" data-group="<?php echo esc_attr( $photo['group'] ); ?>">
						<button type="button" class="mwm-gallery__btn" data-full="<?php echo esc_url( (string) $full ); ?>" data-caption="<?php echo esc_attr( '' !== $alt ? $alt : $photo['title'] ); ?>">
							<?php echo mwm_image( $photo['id'], 'mwm-card', array( 'sizes' => '(max-width: 767px) 46vw, (max-width: 1024px) 31vw, 300px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span class="mwm-gallery__zoom" aria-hidden="true"><?php echo mwm_icon( 'expand' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mwm-sr"><?php esc_html_e( 'تكبير الصورة', 'mwm' ); ?></span>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $buttons ) : ?>
				<div class="mwm-gallery__actions"><?php echo mwm_buttons( $buttons ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>

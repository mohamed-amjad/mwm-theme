<?php
/**
 * Branches + Kingdom-wide coverage card. Maps load on click only, and only when coordinates exist.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args       = isset( $args ) && is_array( $args ) ? $args : array();
$branches   = mwm_get_branches();
$cov_title  = (string) mwm_arg( $args, 'coverage_title' );
$cov_text   = (string) mwm_arg( $args, 'coverage_text', (string) mwm_setting( 'coverage' ) );
$map_label  = (string) mwm_arg( $args, 'map_label' );
$call_label = (string) mwm_arg( $args, 'call_label' );
$section    = (string) mwm_arg( $args, 'section_class', '' );
?>
<section class="<?php echo esc_attr( trim( 'mwm-section mwm-branches ' . $section ) ); ?>">
	<div class="mwm-container">
		<?php echo mwm_section_head( $args, 'mwm-head--center' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( ! $branches && '' === $cov_title ) : ?>
			<?php mwm_editor_empty( __( 'لا توجد فروع بعد — أضفها من لوحة التحكم ← الفروع.', 'mwm' ) ); ?>
		<?php else : ?>
			<div class="mwm-branches__grid">
				<?php
				foreach ( $branches as $branch ) :
					$region  = (string) get_post_meta( $branch->ID, 'region', true );
					$address = (string) get_post_meta( $branch->ID, 'address', true );
					$phone   = (string) get_post_meta( $branch->ID, 'phone', true );
					$email   = sanitize_email( (string) get_post_meta( $branch->ID, 'email', true ) );
					$hours   = (string) get_post_meta( $branch->ID, 'hours', true );
					$map     = (string) get_post_meta( $branch->ID, 'map_url', true );
					$lat     = (float) get_post_meta( $branch->ID, 'latitude', true );
					$lng     = (float) get_post_meta( $branch->ID, 'longitude', true );
					$embed   = ( $lat && $lng ) ? 'https://maps.google.com/maps?q=' . rawurlencode( $lat . ',' . $lng ) . '&z=14&output=embed' : '';
					?>
					<article class="mwm-branch" data-mwm-reveal>
						<span class="mwm-branch__icon"><?php echo mwm_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<h3 class="mwm-branch__title"><?php echo esc_html( get_the_title( $branch ) ); ?></h3>
						<?php if ( '' !== $region ) : ?>
							<p class="mwm-branch__region"><?php echo esc_html( $region ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $address || '' !== $phone || $email || '' !== $hours ) : ?>
							<ul class="mwm-branch__list">
								<?php if ( '' !== $address ) : ?>
									<li><?php echo mwm_icon( 'building' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $address ); ?></span></li>
								<?php endif; ?>
								<?php if ( '' !== $phone ) : ?>
									<li><?php echo mwm_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a href="<?php echo esc_url( mwm_tel_url( $phone ) ); ?>"><?php echo mwm_bdi( mwm_phone_display( $phone ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
								<?php endif; ?>
								<?php if ( $email ) : ?>
									<li><?php echo mwm_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo mwm_bdi( $email ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
								<?php endif; ?>
								<?php if ( '' !== $hours ) : ?>
									<li><?php echo mwm_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $hours ); ?></span></li>
								<?php endif; ?>
							</ul>
						<?php endif; ?>
						<?php if ( '' !== $embed && '' !== $map_label ) : ?>
							<div class="mwm-map" data-mwm-map="<?php echo esc_url( $embed ); ?>" data-title="<?php echo esc_attr( get_the_title( $branch ) ); ?>">
								<button type="button" class="mwm-btn mwm-btn--sm mwm-btn--outline"><?php echo mwm_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $map_label ); ?></span></button>
							</div>
						<?php elseif ( '' !== $map && '' !== $map_label ) : ?>
							<a class="mwm-btn mwm-btn--sm mwm-btn--outline" href="<?php echo esc_url( $map ); ?>" target="_blank" rel="noopener"><?php echo mwm_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $map_label ); ?></span></a>
						<?php endif; ?>
						<?php if ( '' !== $phone && '' !== $call_label ) : ?>
							<a class="mwm-link" href="<?php echo esc_url( mwm_tel_url( $phone ) ); ?>"><?php echo esc_html( $call_label ); ?> <?php echo mwm_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>

				<?php if ( '' !== $cov_title ) : ?>
					<article class="mwm-branch mwm-branch--coverage mwm-dark" data-mwm-reveal>
						<span class="mwm-branch__icon"><?php echo mwm_icon( 'globe' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<h3 class="mwm-branch__title"><?php echo esc_html( $cov_title ); ?></h3>
						<?php if ( '' !== $cov_text ) : ?>
							<p class="mwm-branch__region"><?php echo esc_html( $cov_text ); ?></p>
						<?php endif; ?>
					</article>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

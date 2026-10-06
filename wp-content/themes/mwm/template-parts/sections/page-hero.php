<?php
/**
 * Inner page hero: breadcrumbs, title, intro, optional image and badge.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args    = isset( $args ) && is_array( $args ) ? $args : array();
$post_id = (int) mwm_arg( $args, 'post_id', get_queried_object_id() );
$kicker  = (string) mwm_arg( $args, 'kicker' );
$title   = (string) mwm_arg( $args, 'title', $post_id ? get_the_title( $post_id ) : '' );
$text    = (string) mwm_arg( $args, 'text' );
$image   = (int) mwm_arg( $args, 'image', 0 );
$crumbs  = (bool) mwm_arg( $args, 'crumbs', true );
$buttons = (array) mwm_arg( $args, 'buttons', array() );
$badge   = (string) mwm_arg( $args, 'badge' );
$tag     = mwm_heading_tag( (string) mwm_arg( $args, 'title_tag', 'h1' ) );
?>
<section class="mwm-page-hero mwm-dark<?php echo $image ? ' mwm-page-hero--media' : ''; ?>">
	<div class="mwm-page-hero__bg" aria-hidden="true"></div>
	<div class="mwm-container mwm-page-hero__grid">
		<div class="mwm-page-hero__content">
			<?php
			if ( $crumbs ) {
				mwm_breadcrumbs( $post_id, $title );
			}
			?>
			<?php if ( '' !== $kicker || '' !== $badge ) : ?>
				<div class="mwm-page-hero__meta">
					<?php if ( '' !== $kicker ) : ?>
						<p class="mwm-kicker"><?php echo esc_html( $kicker ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $badge ) : ?>
						<span class="mwm-badge mwm-badge--light"><?php echo mwm_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $badge ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( '' !== $title ) : ?>
				<<?php echo esc_html( $tag ); ?> class="mwm-page-hero__title"><?php echo mwm_inline( $title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $tag ); ?>>
			<?php endif; ?>
			<?php if ( '' !== $text ) : ?>
				<p class="mwm-page-hero__text"><?php echo mwm_inline( $text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php endif; ?>
			<?php echo mwm_buttons( $buttons ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php if ( $image ) : ?>
			<div class="mwm-page-hero__media">
				<?php
				echo mwm_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$image,
					'mwm-card',
					array(
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'sizes'         => '(max-width: 1024px) 92vw, 520px',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>

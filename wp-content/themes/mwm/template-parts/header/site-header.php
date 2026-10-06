<?php
/**
 * Theme header (menus + settings; the MWM header widget passes overrides as args).
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args           = isset( $args ) && is_array( $args ) ? $args : array();
$mwm_phone_slot = (int) mwm_arg( $args, 'phone_slot', mwm_setting( 'header_phone_slot', 0 ) );
$mwm_phone      = $mwm_phone_slot ? (string) mwm_setting( "phone_{$mwm_phone_slot}_number" ) : '';
$mwm_cta        = mwm_resolve_link( (string) mwm_arg( $args, 'cta_link', mwm_setting( 'header_cta_link', 'role:quote' ) ), (string) mwm_arg( $args, 'cta_url' ) );
$mwm_cta_label  = (string) mwm_arg( $args, 'cta_label', mwm_setting( 'header_cta_label' ) );
$mwm_drawer_wa  = (bool) mwm_arg( $args, 'drawer_whatsapp', true );
$mwm_has_menu   = has_nav_menu( 'primary' );
?>
<header class="mwm-header" data-mwm-header>
	<div class="mwm-container mwm-header__inner">
		<?php get_template_part( 'template-parts/header/brand' ); ?>

		<?php if ( $mwm_has_menu ) : ?>
			<nav class="mwm-nav" aria-label="<?php esc_attr_e( 'القائمة الرئيسية', 'mwm' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<div class="mwm-header__actions">
			<?php if ( '' !== $mwm_phone ) : ?>
				<a class="mwm-header__phone" href="<?php echo esc_url( mwm_tel_url( $mwm_phone ) ); ?>">
					<?php echo mwm_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo mwm_bdi( mwm_phone_display( $mwm_phone ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="mwm-sr"><?php esc_html_e( 'اتصل بنا', 'mwm' ); ?></span>
				</a>
			<?php endif; ?>
			<?php echo mwm_button( $mwm_cta, $mwm_cta_label, 'mwm-btn mwm-btn--sm mwm-header__cta' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $mwm_has_menu ) : ?>
				<button type="button" class="mwm-burger" aria-controls="mwm-drawer" aria-expanded="false" data-mwm-drawer-open>
					<?php echo mwm_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="mwm-sr"><?php esc_html_e( 'فتح القائمة', 'mwm' ); ?></span>
				</button>
			<?php endif; ?>
		</div>
	</div>
</header>

<?php if ( $mwm_has_menu ) : ?>
	<dialog id="mwm-drawer" class="mwm-drawer" aria-label="<?php esc_attr_e( 'القائمة', 'mwm' ); ?>">
		<div class="mwm-drawer__head">
			<?php get_template_part( 'template-parts/header/brand', null, array( 'lazy' => true ) ); ?>
			<button type="button" class="mwm-drawer__close" data-mwm-drawer-close>
				<?php echo mwm_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="mwm-sr"><?php esc_html_e( 'إغلاق القائمة', 'mwm' ); ?></span>
			</button>
		</div>
		<nav class="mwm-drawer__nav" aria-label="<?php esc_attr_e( 'القائمة الرئيسية', 'mwm' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'depth'          => 2,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
		<div class="mwm-drawer__foot">
			<?php echo mwm_button( $mwm_cta, $mwm_cta_label, 'mwm-btn mwm-btn--block' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php foreach ( $mwm_drawer_wa ? mwm_phones_for( 'whatsapp' ) : array() as $mwm_p ) : ?>
				<a class="mwm-btn mwm-btn--outline mwm-btn--block" href="<?php echo esc_url( mwm_wa_url( $mwm_p['number'], mwm_wa_message() ) ); ?>" target="_blank" rel="noopener">
					<?php echo mwm_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( $mwm_p['label'] ? $mwm_p['label'] : __( 'واتساب', 'mwm' ) ); ?> — <?php echo mwm_bdi( $mwm_p['display'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</dialog>
<?php endif; ?>

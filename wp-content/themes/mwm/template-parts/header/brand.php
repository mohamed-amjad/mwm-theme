<?php
/**
 * Logo mark + company name lines.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$mwm_logo = (int) get_theme_mod( 'custom_logo' );
$mwm_name = (string) mwm_setting( 'company_name', get_bloginfo( 'name' ) );
$mwm_sub  = (string) mwm_setting( 'company_subname' );
$mwm_lazy = ! empty( $args['lazy'] );
?>
<a class="mwm-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
	<?php if ( $mwm_logo ) : ?>
		<span class="mwm-brand__mark">
			<?php
			echo wp_get_attachment_image(
				$mwm_logo,
				'mwm-logo',
				false,
				array(
					'alt'     => $mwm_name,
					'loading' => $mwm_lazy ? 'lazy' : 'eager',
					'sizes'   => '54px',
				)
			);
			?>
		</span>
	<?php endif; ?>
	<span class="mwm-brand__text">
		<span class="mwm-brand__name"><?php echo esc_html( $mwm_name ); ?></span>
		<?php if ( '' !== $mwm_sub ) : ?>
			<span class="mwm-brand__sub"><?php echo esc_html( $mwm_sub ); ?></span>
		<?php endif; ?>
	</span>
</a>

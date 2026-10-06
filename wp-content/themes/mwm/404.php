<?php
/**
 * 404.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

get_header();

$buttons = array(
	array(
		'label' => __( 'العودة للرئيسية', 'mwm' ),
		'link'  => mwm_resolve_link( 'role:home' ),
		'icon'  => 'arrow',
	),
);
$services = mwm_page_url( 'services' );
if ( $services ) {
	$buttons[] = array(
		'label' => get_the_title( mwm_page_id( 'services' ) ),
		'link'  => array(
			'href'     => $services,
			'external' => false,
		),
		'style' => 'ghost-light',
	);
}
?>
<section class="mwm-404 mwm-dark">
	<div class="mwm-container mwm-container--narrow">
		<span class="mwm-404__code" aria-hidden="true">404</span>
		<h1 class="mwm-page-hero__title" style="margin-inline: auto"><?php esc_html_e( 'الصفحة غير موجودة', 'mwm' ); ?></h1>
		<p class="mwm-page-hero__text" style="margin-inline: auto"><?php esc_html_e( 'ربما تم نقل الصفحة أو أن الرابط غير صحيح.', 'mwm' ); ?></p>
		<?php echo mwm_buttons( $buttons, 'mwm-btn--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</section>
<?php
get_footer();

<?php
/**
 * Free rich text (page body, service description, policies).
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args    = isset( $args ) && is_array( $args ) ? $args : array();
$content = trim( (string) mwm_arg( $args, 'content' ) );
$classes = trim( 'mwm-page-content ' . (string) mwm_arg( $args, 'section_class' ) );
$wide    = (bool) mwm_arg( $args, 'wide', false );

if ( '' === $content ) {
	mwm_editor_empty( __( 'اكتب النص من لوحة الإعدادات.', 'mwm' ) );
	return;
}
?>
<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="mwm-container">
		<div class="mwm-prose<?php echo $wide ? ' mwm-prose--wide' : ''; ?>"><?php echo do_shortcode( wpautop( wp_kses_post( $content ) ) ); ?></div>
	</div>
</section>

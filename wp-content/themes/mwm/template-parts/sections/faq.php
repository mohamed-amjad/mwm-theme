<?php
/**
 * FAQ accordion (native <details>).
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args    = isset( $args ) && is_array( $args ) ? $args : array();
$service = (int) mwm_arg( $args, 'service', 0 );
$faqs    = mwm_get_faqs( $service );
$limit   = (int) mwm_arg( $args, 'limit', 0 );
$open    = (bool) mwm_arg( $args, 'open_first', true );
$buttons = (array) mwm_arg( $args, 'buttons', array() );
$section = (string) mwm_arg( $args, 'section_class', '' );

if ( $limit > 0 ) {
	$faqs = array_slice( $faqs, 0, $limit );
}
if ( ! $faqs && ! mwm_is_editor() ) {
	return;
}
?>
<section class="<?php echo esc_attr( trim( 'mwm-section mwm-faq ' . $section ) ); ?>">
	<div class="mwm-container mwm-faq__grid">
		<div class="mwm-faq__intro">
			<?php echo mwm_section_head( $args, '', mwm_buttons( $buttons ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<div class="mwm-faq__list">
			<?php if ( ! $faqs ) : ?>
				<?php mwm_editor_empty( __( 'لا توجد أسئلة بعد — أضفها من لوحة التحكم ← الأسئلة الشائعة.', 'mwm' ) ); ?>
			<?php endif; ?>
			<?php foreach ( $faqs as $index => $faq ) : ?>
				<details class="mwm-faq__item" name="mwm-faq"<?php echo ( $open && 0 === $index ) ? ' open' : ''; ?>>
					<summary>
						<span><?php echo esc_html( get_the_title( $faq ) ); ?></span>
						<span class="mwm-faq__toggle" aria-hidden="true"><?php echo mwm_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</summary>
					<div class="mwm-faq__answer"><?php echo wp_kses_post( wpautop( $faq->post_content ) ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

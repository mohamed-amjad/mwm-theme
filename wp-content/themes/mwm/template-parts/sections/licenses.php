<?php
/**
 * License records: issuer, field, number, dates, verify link — each shown only when filled.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

$args         = isset( $args ) && is_array( $args ) ? $args : array();
$licenses     = mwm_get_licenses();
$verify_label = (string) mwm_arg( $args, 'verify_label' );
$doc_label    = (string) mwm_arg( $args, 'document_label' );
$show_svc     = (bool) mwm_arg( $args, 'show_services', true );
$layout       = (string) mwm_arg( $args, 'layout', 'cards' );
$section      = (string) mwm_arg( $args, 'section_class', '' );
$labels       = array(
	'number' => (string) mwm_arg( $args, 'number_label' ),
	'issue'  => (string) mwm_arg( $args, 'issue_label' ),
	'expiry' => (string) mwm_arg( $args, 'expiry_label' ),
);

$format_date = static function ( $ymd ) {
	$ymd = preg_replace( '/\D/', '', (string) $ymd );
	if ( 8 !== strlen( $ymd ) ) {
		return '';
	}
	return substr( $ymd, 0, 4 ) . '/' . substr( $ymd, 4, 2 ) . '/' . substr( $ymd, 6, 2 );
};
?>
<section class="<?php echo esc_attr( trim( 'mwm-section mwm-licenses mwm-licenses--' . sanitize_html_class( $layout ) . ' ' . $section ) ); ?>">
	<div class="mwm-container">
		<?php echo mwm_section_head( $args, 'mwm-head--center' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( ! $licenses ) : ?>
			<?php mwm_editor_empty( __( 'لا توجد تراخيص بعد — أضفها من لوحة التحكم ← التراخيص.', 'mwm' ) ); ?>
		<?php else : ?>
			<div class="mwm-licenses__grid">
				<?php
				foreach ( $licenses as $license ) :
					$issuer   = (string) get_post_meta( $license->ID, 'issuer', true );
					$field    = (string) get_post_meta( $license->ID, 'field', true );
					$number   = (string) get_post_meta( $license->ID, 'number', true );
					$issue    = $format_date( get_post_meta( $license->ID, 'issue_date', true ) );
					$expiry   = $format_date( get_post_meta( $license->ID, 'expiry_date', true ) );
					$verify   = (string) get_post_meta( $license->ID, 'verify_url', true );
					$document = (int) get_post_meta( $license->ID, 'document', true );
					$thumb    = (int) get_post_thumbnail_id( $license );
					$services = $show_svc ? array_filter( array_map( 'get_post', mwm_relation_ids( $license->ID, 'services' ) ) ) : array();
					$meta     = array_filter(
						array(
							$labels['number'] => $number,
							$labels['issue']  => $issue,
							$labels['expiry'] => $expiry,
						),
						static fn( $v, $k ) => '' !== $v && '' !== $k,
						ARRAY_FILTER_USE_BOTH
					);
					?>
					<article class="mwm-license" data-mwm-reveal>
						<div class="mwm-license__head">
							<span class="mwm-license__seal"><?php echo mwm_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<div>
								<?php if ( '' !== $issuer ) : ?>
									<p class="mwm-license__issuer"><?php echo esc_html( $issuer ); ?></p>
								<?php endif; ?>
								<h3 class="mwm-license__title"><?php echo esc_html( get_the_title( $license ) ); ?></h3>
							</div>
						</div>
						<?php if ( '' !== $field ) : ?>
							<p class="mwm-license__field"><?php echo esc_html( $field ); ?></p>
						<?php endif; ?>
						<?php if ( $meta ) : ?>
							<dl class="mwm-license__meta">
								<?php foreach ( $meta as $label => $value ) : ?>
									<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo mwm_bdi( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
								<?php endforeach; ?>
							</dl>
						<?php endif; ?>
						<?php if ( $services ) : ?>
							<ul class="mwm-license__services">
								<?php foreach ( $services as $svc ) : ?>
									<li><a href="<?php echo esc_url( get_permalink( $svc ) ); ?>"><?php echo esc_html( get_the_title( $svc ) ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<?php if ( $thumb ) : ?>
							<div class="mwm-license__image"><?php echo mwm_image( $thumb, 'medium_large', array( 'sizes' => '(max-width: 767px) 92vw, 520px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<?php endif; ?>
						<?php if ( ( '' !== $verify && '' !== $verify_label ) || ( $document && '' !== $doc_label ) ) : ?>
							<div class="mwm-actions">
								<?php if ( '' !== $verify && '' !== $verify_label ) : ?>
									<a class="mwm-btn mwm-btn--sm" href="<?php echo esc_url( $verify ); ?>" target="_blank" rel="noopener"><?php echo mwm_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $verify_label ); ?></span></a>
								<?php endif; ?>
								<?php if ( $document && '' !== $doc_label ) : ?>
									<a class="mwm-btn mwm-btn--sm mwm-btn--outline" href="<?php echo esc_url( (string) wp_get_attachment_url( $document ) ); ?>" target="_blank" rel="noopener"><?php echo mwm_icon( 'document' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $doc_label ); ?></span></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

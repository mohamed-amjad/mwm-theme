<?php
/**
 * Single service: Elementor layout when built, otherwise an automatic composition
 * from the service record (hero, body, items, photos, FAQ, other services).
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$service_id = get_the_ID();

	if ( mwm_uses_elementor( $service_id ) ) {
		the_content();
		continue;
	}

	$card     = mwm_service_card( get_post() );
	$licenses = mwm_service_licenses( $service_id );
	$buttons  = array();
	if ( '' !== $card['cta_label'] ) {
		$buttons[] = array(
			'label' => $card['cta_label'],
			'link'  => mwm_resolve_link( 'role:quote' ),
			'icon'  => 'arrow',
		);
	}
	$wa = mwm_phones_for( 'whatsapp' );
	if ( $wa ) {
		$buttons[] = array(
			'label' => __( 'تواصل عبر واتساب', 'mwm' ),
			'link'  => mwm_resolve_link( 'wa:' . $wa[0]['slot'], '', get_the_title() ),
			'style' => 'ghost-light',
			'icon'  => 'whatsapp',
		);
	}

	get_template_part(
		'template-parts/sections/page-hero',
		null,
		array(
			'post_id' => $service_id,
			'kicker'  => $card['kicker'],
			'text'    => $card['excerpt'],
			'image'   => $card['image'],
			'badge'   => $licenses ? get_the_title( $licenses[0] ) : '',
			'buttons' => $buttons,
		)
	);

	if ( '' !== trim( (string) get_the_content() ) ) :
		?>
		<div class="mwm-page-content">
			<div class="mwm-container">
				<div class="mwm-prose"><?php the_content(); ?></div>
			</div>
		</div>
		<?php
	endif;

	get_template_part(
		'template-parts/sections/service-items',
		null,
		array(
			'service' => $service_id,
			'title'   => (string) get_post_meta( $service_id, 'items_title', true ),
		)
	);

	get_template_part(
		'template-parts/sections/gallery',
		null,
		array(
			'service'       => $service_id,
			'title'         => (string) get_post_meta( $service_id, 'gallery_title', true ),
			'section_class' => 'mwm-section--tint',
		)
	);

	get_template_part(
		'template-parts/sections/faq',
		null,
		array(
			'service' => $service_id,
			'title'   => __( 'الأسئلة الشائعة', 'mwm' ),
		)
	);

	get_template_part(
		'template-parts/sections/services',
		null,
		array(
			'exclude'         => $service_id,
			'title'           => __( 'خدمات أخرى', 'mwm' ),
			'show_highlights' => false,
			'link_label'      => __( 'تفاصيل الخدمة', 'mwm' ),
			'section_class'   => 'mwm-section--green',
		)
	);
endwhile;

get_footer();

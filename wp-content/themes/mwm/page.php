<?php
/**
 * Pages: Elementor layout when built, otherwise page hero + prose.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	if ( mwm_uses_elementor( get_the_ID() ) ) :
		the_content();
	else :
		get_template_part(
			'template-parts/sections/page-hero',
			null,
			array(
				'post_id' => get_the_ID(),
				'text'    => has_excerpt() ? get_the_excerpt() : '',
				'image'   => (int) get_post_thumbnail_id(),
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
	endif;
endwhile;

get_footer();

<?php
/**
 * Elementor saved templates (header/footer): preview and editor canvas.
 * The site header/footer are skipped here, so the template is shown on its own.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	the_content();
endwhile;

get_footer();

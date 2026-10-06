<?php
/**
 * Document head + site header.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#194223">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="mwm-skip" href="#content"><?php esc_html_e( 'تخطي إلى المحتوى', 'mwm' ); ?></a>
<?php mwm_render_site_part( 'header' ); ?>
<main id="content" class="mwm-main" tabindex="-1">

<?php
/**
 * Generic fallback (archives, search, blog index).
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

get_header();

$title = is_search()
	/* translators: %s: search query. */
	? sprintf( __( 'نتائج البحث عن: %s', 'mwm' ), get_search_query() )
	: wp_strip_all_tags( get_the_archive_title() );

if ( is_home() && get_option( 'page_for_posts' ) ) {
	$title = get_the_title( (int) get_option( 'page_for_posts' ) );
}

get_template_part(
	'template-parts/sections/page-hero',
	null,
	array(
		'post_id' => 0,
		'title'   => $title,
		'crumbs'  => false,
	)
);
?>
<div class="mwm-section">
	<div class="mwm-container mwm-container--narrow">
		<?php if ( have_posts() ) : ?>
			<div class="mwm-faq__list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article class="mwm-faq__item" style="padding: var(--mwm-s-6)">
						<h2 class="mwm-item__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php if ( has_excerpt() || get_the_content() ) : ?>
							<p class="mwm-item__text"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
						<?php endif; ?>
					</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p class="mwm-lead"><?php esc_html_e( 'لا توجد نتائج.', 'mwm' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();

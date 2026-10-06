<?php
/**
 * Ordered photo gallery per service (ACF Free has no Gallery field).
 * Stored as an attachment-ID array in meta `mwm_gallery`.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gallery IDs of a post.
 *
 * @param int $post_id Post ID.
 * @return int[]
 */
function mwm_gallery_ids( $post_id ) {
	$ids = get_post_meta( (int) $post_id, 'mwm_gallery', true );
	if ( ! is_array( $ids ) ) {
		return array();
	}
	return array_values( array_filter( array_map( 'absint', $ids ), static fn( $id ) => $id && wp_attachment_is_image( $id ) ) );
}

/**
 * Add the metabox.
 */
function mwm_gallery_metabox() {
	add_meta_box( 'mwm-gallery', __( 'صور الخدمة (معرض الأعمال)', 'mwm' ), 'mwm_gallery_metabox_render', 'mwm_service', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'mwm_gallery_metabox' );

/**
 * Render the metabox.
 *
 * @param WP_Post $post Post.
 */
function mwm_gallery_metabox_render( $post ) {
	wp_nonce_field( 'mwm_gallery_save', 'mwm_gallery_nonce' );
	$ids = mwm_gallery_ids( $post->ID );
	?>
	<div class="mwm-gallery-box">
		<p class="description"><?php esc_html_e( 'هذه الصور هي الصور المبدئية لمعرض الخدمة عند تجهيز صفحتها أول مرة، وتظهر في أي معرض مصدره «تلقائي» أو «كل الخدمات». الصفحات التي تُعدّل بـ Elementor تُدار صورها وتبويباتها من ودجت المعرض نفسه. اسحب الصور لترتيبها.', 'mwm' ); ?></p>
		<ul class="mwm-gallery-box__list">
			<?php foreach ( $ids as $id ) : ?>
				<li data-id="<?php echo esc_attr( (string) $id ); ?>"><?php echo wp_get_attachment_image( $id, 'thumbnail' ); ?><button type="button" aria-label="<?php esc_attr_e( 'إزالة', 'mwm' ); ?>">&times;</button></li>
			<?php endforeach; ?>
		</ul>
		<input type="hidden" name="mwm_gallery" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
		<button type="button" class="button mwm-gallery-box__add"><?php esc_html_e( 'إضافة صور', 'mwm' ); ?></button>
	</div>
	<?php
}

/**
 * Save the gallery.
 *
 * @param int $post_id Post ID.
 */
function mwm_gallery_save( $post_id ) {
	if ( ! isset( $_POST['mwm_gallery_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mwm_gallery_nonce'] ) ), 'mwm_gallery_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$raw = isset( $_POST['mwm_gallery'] ) ? sanitize_text_field( wp_unslash( $_POST['mwm_gallery'] ) ) : '';
	$ids = array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
	update_post_meta( $post_id, 'mwm_gallery', $ids );
}
add_action( 'save_post_mwm_service', 'mwm_gallery_save' );

/**
 * Metabox assets.
 *
 * @param string $hook Hook.
 */
function mwm_gallery_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'mwm_service' !== get_post_type() ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'mwm-admin', MWM_URI . '/assets/css/admin.css', array(), MWM_VERSION );
	wp_enqueue_script( 'mwm-gallery-box', MWM_URI . '/assets/js/gallery-box.js', array( 'jquery-ui-sortable' ), MWM_VERSION, true );
	wp_localize_script( 'mwm-gallery-box', 'mwmGallery', array( 'title' => __( 'اختر صور الخدمة', 'mwm' ) ) );
}
add_action( 'admin_enqueue_scripts', 'mwm_gallery_assets' );

<?php
/**
 * MWM section widgets. Each maps its controls to the args of one template part.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Home hero.
 */
class Mwm_Widget_Hero extends Mwm_Widget {
	protected $template       = 'hero';
	protected $title_selector = '.mwm-hero__title';
	protected $text_selector  = '.mwm-hero__text';

	public function get_title() {
		return __( 'MWM — الواجهة الرئيسية', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-banner';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls( array( 'tag' => 'h1' ) );
		$this->buttons_control();
		$this->add_control(
			'points',
			array(
				'label'       => __( 'نقاط الثقة (سطر لكل نقطة)', 'mwm' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'placeholder' => __( "مرخّصون من المركز الوطني للرقابة على الالتزام البيئي\nنخدم جميع مناطق المملكة", 'mwm' ),
			)
		);
		$this->end_controls_section();

		$this->open( 'media', __( 'صور العرض', 'mwm' ) );
		$this->add_control(
			'slides',
			array(
				'label'       => __( 'الصور', 'mwm' ),
				'type'        => Controls_Manager::GALLERY,
				'description' => __( 'صور ميدانية حقيقية تتبدّل تلقائياً. الصورة الأولى تُحمّل أولاً لسرعة الظهور.', 'mwm' ),
			)
		);
		$this->add_control(
			'interval',
			array(
				'label'   => __( 'مدة كل صورة (ثوانٍ)', 'mwm' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 3,
				'max'     => 15,
				'step'    => 1,
				'default' => 6,
			)
		);
		$this->text( 'chip', __( 'وسم صغير على الصورة', 'mwm' ), __( 'خبراء جودة الهواء', 'mwm' ) );
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		$slides = array();
		foreach ( (array) ( $s['slides'] ?? array() ) as $item ) {
			$slides[] = $this->media_id( $item );
		}
		return $this->head_args( $s ) + array(
			'buttons'  => $this->buttons_args( $s['buttons'] ?? array() ),
			'points'   => $this->lines( $s['points'] ?? '' ),
			'slides'   => array_filter( $slides ),
			'image'    => $this->media_id( $s['image'] ?? null ),
			'interval' => (int) ( $s['interval'] ?? 6 ),
			'chip'     => (string) ( $s['chip'] ?? '' ),
		);
	}

	protected function style_controls() {
		parent::style_controls();
		$this->open( 'hero_style', __( 'خلفية الواجهة', 'mwm' ), Controls_Manager::TAB_STYLE );
		$this->add_responsive_control(
			'min_height',
			array(
				'label'      => __( 'أقل ارتفاع', 'mwm' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'svh' ),
				'range'      => array(
					'px' => array(
						'min' => 400,
						'max' => 1100,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .mwm-hero' => 'min-block-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'hero_bg',
			array(
				'label'     => __( 'لون الخلفية', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .mwm-hero' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}
}

/**
 * Inner page hero.
 */
class Mwm_Widget_Page_Hero extends Mwm_Widget {
	protected $template       = 'page-hero';
	protected $title_selector = '.mwm-page-hero__title';
	protected $text_selector  = '.mwm-page-hero__text';

	public function get_title() {
		return __( 'MWM — رأس الصفحة', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-header';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls( array( 'tag' => 'h1' ) );
		$this->update_control( 'title', array( 'description' => __( 'اتركه فارغاً لاستخدام عنوان الصفحة تلقائياً.', 'mwm' ) ) );
		$this->switcher( 'crumbs', __( 'إظهار مسار التنقل', 'mwm' ) );
		$this->text( 'badge', __( 'شارة', 'mwm' ), __( 'مرخّص من NCEC', 'mwm' ), array( 'default' => '' ) );
		$this->buttons_control();
		$this->add_control(
			'image',
			array(
				'label' => __( 'صورة جانبية', 'mwm' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		$args = $this->head_args( $s ) + array(
			'crumbs'  => $this->on( $s, 'crumbs' ),
			'badge'   => (string) ( $s['badge'] ?? '' ),
			'buttons' => $this->buttons_args( $s['buttons'] ?? array() ),
			'image'   => $this->media_id( $s['image'] ?? null ),
		);
		if ( '' === trim( $args['title'] ) ) {
			unset( $args['title'] );
		}
		return $args;
	}
}

/**
 * Text + image split.
 */
class Mwm_Widget_Text_Media extends Mwm_Widget {
	protected $template      = 'text-media';
	protected $text_selector = '.mwm-split__text';

	public function get_title() {
		return __( 'MWM — نص وصورة', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-image-box';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls( array( 'text' => false ) );
		$this->add_control(
			'content',
			array(
				'label' => __( 'النص', 'mwm' ),
				'type'  => Controls_Manager::WYSIWYG,
			)
		);
		$this->add_control(
			'points',
			array(
				'label' => __( 'نقاط (سطر لكل نقطة)', 'mwm' ),
				'type'  => Controls_Manager::TEXTAREA,
				'rows'  => 4,
			)
		);
		$this->buttons_control();
		$this->end_controls_section();

		$this->open( 'media', __( 'الصورة', 'mwm' ) );
		$this->add_control(
			'image',
			array(
				'label' => __( 'الصورة الرئيسية', 'mwm' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$this->add_control(
			'image_secondary',
			array(
				'label' => __( 'صورة صغيرة إضافية', 'mwm' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$this->add_control(
			'image_position',
			array(
				'label'   => __( 'موضع الصورة', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'end',
				'options' => array(
					'end'   => __( 'بعد النص', 'mwm' ),
					'start' => __( 'قبل النص', 'mwm' ),
				),
			)
		);
		$this->text( 'note_title', __( 'عنوان البطاقة على الصورة', 'mwm' ) );
		$this->text( 'note_text', __( 'نص البطاقة على الصورة', 'mwm' ) );
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		return $this->head_args( $s ) + array(
			'content'         => (string) ( $s['content'] ?? '' ),
			'points'          => $this->lines( $s['points'] ?? '' ),
			'buttons'         => $this->buttons_args( $s['buttons'] ?? array() ),
			'image'           => $this->media_id( $s['image'] ?? null ),
			'image_secondary' => $this->media_id( $s['image_secondary'] ?? null ),
			'image_position'  => (string) ( $s['image_position'] ?? 'end' ),
			'note_title'      => (string) ( $s['note_title'] ?? '' ),
			'note_text'       => (string) ( $s['note_text'] ?? '' ),
			'section_class'   => $this->tone_class( $s ),
		);
	}
}

/**
 * Service cards.
 */
class Mwm_Widget_Services extends Mwm_Widget {
	protected $template      = 'services';
	protected $card_selector = '.mwm-service-card';

	public function get_title() {
		return __( 'MWM — بطاقات الخدمات', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls();
		$this->end_controls_section();

		$this->open( 'source', __( 'الخدمات المعروضة', 'mwm' ) );
		$this->add_control(
			'ids',
			array(
				'label'       => __( 'اختر خدمات محددة', 'mwm' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->service_options(),
				'description' => __( 'اتركه فارغاً لعرض كل الخدمات بترتيبها في لوحة التحكم.', 'mwm' ),
			)
		);
		$this->switcher( 'exclude_current', __( 'إخفاء الخدمة الحالية (في صفحات الخدمات)', 'mwm' ) );
		$this->columns_control( 3, 4 );
		$this->switcher( 'show_image', __( 'إظهار الصورة', 'mwm' ) );
		$this->switcher( 'show_highlights', __( 'إظهار أبرز النقاط', 'mwm' ) );
		$this->add_control(
			'max_highlights',
			array(
				'label'     => __( 'أقصى عدد للنقاط', 'mwm' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 10,
				'default'   => 4,
				'condition' => array( 'show_highlights' => 'yes' ),
			)
		);
		$this->text( 'link_label', __( 'نص رابط التفاصيل', 'mwm' ), __( 'تفاصيل الخدمة', 'mwm' ) );
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		return $this->head_args( $s ) + array(
			'ids'             => array_map( 'intval', (array) ( $s['ids'] ?? array() ) ),
			'exclude'         => ( $this->on( $s, 'exclude_current' ) && is_singular( 'mwm_service' ) ) ? get_queried_object_id() : 0,
			'columns'         => (int) ( $s['columns'] ?? 3 ),
			'show_image'      => $this->on( $s, 'show_image' ),
			'show_highlights' => $this->on( $s, 'show_highlights' ),
			'max_highlights'  => (int) ( $s['max_highlights'] ?? 4 ),
			'link_label'      => (string) ( $s['link_label'] ?? '' ),
			'section_class'   => $this->tone_class( $s ),
		);
	}
}

/**
 * Sub-service items.
 */
class Mwm_Widget_Service_Items extends Mwm_Widget {
	protected $template      = 'service-items';
	protected $card_selector = '.mwm-item';

	public function get_title() {
		return __( 'MWM — بنود الخدمة', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls();
		$this->add_control(
			'service',
			array(
				'label'   => __( 'الخدمة', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->service_options( array( '' => __( 'تلقائي — الخدمة الحالية', 'mwm' ) ) ),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'الشكل', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cards',
				'options' => array(
					'cards' => __( 'بطاقات', 'mwm' ),
					'list'  => __( 'قائمة مدمجة', 'mwm' ),
				),
			)
		);
		$this->columns_control( 3, 4 );
		$this->switcher( 'show_image', __( 'إظهار صور البنود', 'mwm' ) );
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		return $this->head_args( $s ) + array(
			'service'       => $this->service_from( $s['service'] ?? '' ),
			'layout'        => (string) ( $s['layout'] ?? 'cards' ),
			'columns'       => (int) ( $s['columns'] ?? 3 ),
			'show_image'    => $this->on( $s, 'show_image' ),
			'section_class' => $this->tone_class( $s ),
		);
	}
}

/**
 * Feature list (why us / industries / trust strip).
 */
class Mwm_Widget_Features extends Mwm_Widget {
	protected $template      = 'features';
	protected $card_selector = '.mwm-feature';

	public function get_title() {
		return __( 'MWM — مميزات / قطاعات', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-icon-box';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls();
		$rep = new Repeater();
		$this->icon_control( $rep, 'icon', 'leaf' );
		$rep->add_control(
			'title',
			array(
				'label'       => __( 'العنوان', 'mwm' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);
		$rep->add_control(
			'text',
			array(
				'label' => __( 'الوصف', 'mwm' ),
				'type'  => Controls_Manager::TEXTAREA,
				'rows'  => 3,
			)
		);
		$this->add_control(
			'items',
			array(
				'label'         => __( 'العناصر', 'mwm' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $rep->get_controls(),
				'title_field'   => '{{{ title }}}',
				'prevent_empty' => false,
				'default'       => array(),
			)
		);
		$this->buttons_control();
		$this->end_controls_section();

		$this->open( 'layout_section', __( 'التنسيق', 'mwm' ) );
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'الشكل', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid'  => __( 'شبكة بطاقات', 'mwm' ),
					'tiles' => __( 'مربعات بأيقونة كبيرة', 'mwm' ),
					'strip' => __( 'شريط أفقي مختصر', 'mwm' ),
					'split' => __( 'عنوان جانبي + قائمة', 'mwm' ),
				),
			)
		);
		$this->columns_control( 3, 6 );
		$this->add_control(
			'image',
			array(
				'label'     => __( 'صورة (للشكل الجانبي)', 'mwm' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'layout' => 'split' ),
			)
		);
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		$items = array();
		foreach ( (array) ( $s['items'] ?? array() ) as $row ) {
			$items[] = array(
				'icon'  => (string) ( $row['icon'] ?? '' ),
				'title' => (string) ( $row['title'] ?? '' ),
				'text'  => (string) ( $row['text'] ?? '' ),
			);
		}
		return $this->head_args( $s ) + array(
			'items'         => $items,
			'buttons'       => $this->buttons_args( $s['buttons'] ?? array() ),
			'layout'        => (string) ( $s['layout'] ?? 'grid' ),
			'columns'       => (int) ( $s['columns'] ?? 3 ),
			'image'         => $this->media_id( $s['image'] ?? null ),
			'section_class' => $this->tone_class( $s ),
		);
	}
}

/**
 * Process steps.
 */
class Mwm_Widget_Process extends Mwm_Widget {
	protected $template      = 'process';
	protected $card_selector = '.mwm-step';

	public function get_title() {
		return __( 'MWM — خطوات العمل', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-number-field';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls();
		$rep = new Repeater();
		$this->icon_control( $rep, 'icon', '' );
		$rep->add_control(
			'title',
			array(
				'label'       => __( 'الخطوة', 'mwm' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);
		$rep->add_control(
			'text',
			array(
				'label' => __( 'الوصف', 'mwm' ),
				'type'  => Controls_Manager::TEXTAREA,
				'rows'  => 3,
			)
		);
		$this->add_control(
			'steps',
			array(
				'label'         => __( 'الخطوات', 'mwm' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $rep->get_controls(),
				'title_field'   => '{{{ title }}}',
				'prevent_empty' => false,
				'default'       => array(),
			)
		);
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		$steps = array();
		foreach ( (array) ( $s['steps'] ?? array() ) as $row ) {
			$steps[] = array(
				'icon'  => (string) ( $row['icon'] ?? '' ),
				'title' => (string) ( $row['title'] ?? '' ),
				'text'  => (string) ( $row['text'] ?? '' ),
			);
		}
		return $this->head_args( $s ) + array(
			'steps'         => $steps,
			'section_class' => $this->tone_class( $s ),
		);
	}
}

/**
 * Works gallery.
 */
class Mwm_Widget_Gallery extends Mwm_Widget {
	protected $template = 'gallery';

	public function get_title() {
		return __( 'MWM — معرض الأعمال', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls();
		$this->add_control(
			'service',
			array(
				'label'       => __( 'مصدر الصور', 'mwm' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->service_options(
					array(
						'custom' => __( 'تبويبات وصور أختارها هنا', 'mwm' ),
						''       => __( 'تلقائي — صور الخدمة الحالية أو كل الخدمات', 'mwm' ),
						'all'    => __( 'كل الخدمات', 'mwm' ),
					)
				),
				'description' => __( 'الخيارات التلقائية تأخذ الصور من صفحة كل خدمة ← صور الخدمة.', 'mwm' ),
			)
		);
		$group = new Repeater();
		$group->add_control(
			'title',
			array(
				'label'       => __( 'اسم التبويب', 'mwm' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => __( 'تبويب جديد', 'mwm' ),
			)
		);
		$group->add_control(
			'images',
			array(
				'label'   => __( 'الصور', 'mwm' ),
				'type'    => Controls_Manager::GALLERY,
				'default' => array(),
			)
		);
		$this->add_control(
			'groups',
			array(
				'label'         => __( 'التبويبات وصورها', 'mwm' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $group->get_controls(),
				'title_field'   => '{{{ title }}}',
				'prevent_empty' => false,
				'default'       => array(),
				'condition'     => array( 'service' => 'custom' ),
			)
		);
		$this->switcher( 'tabs', __( 'إظهار التبويبات', 'mwm' ) );
		$this->text( 'all_label', __( 'نص تبويب «الكل»', 'mwm' ), __( 'الكل', 'mwm' ), array( 'condition' => array( 'tabs' => 'yes' ) ) );
		$this->add_control(
			'limit',
			array(
				'label'       => __( 'أقصى عدد للصور', 'mwm' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'default'     => 0,
				'description' => __( '0 = كل الصور', 'mwm' ),
			)
		);
		$this->columns_control( 4, 5 );
		$this->buttons_control();
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		$custom = 'custom' === ( $s['service'] ?? '' );
		$groups = array();
		if ( $custom ) {
			foreach ( (array) ( $s['groups'] ?? array() ) as $n => $row ) {
				$ids = array_values( array_filter( array_map( fn( $m ) => $this->media_id( $m ), (array) ( $row['images'] ?? array() ) ) ) );
				if ( $ids ) {
					$groups[] = array(
						'title' => (string) ( $row['title'] ?? '' ),
						'slug'  => 'g-' . sanitize_html_class( (string) ( $row['_id'] ?? $n ) ),
						'ids'   => $ids,
					);
				}
			}
		}
		return $this->head_args( $s ) + ( $custom ? array( 'groups' => $groups ) : array() ) + array(
			'service'       => $custom ? 0 : $this->service_from( $s['service'] ?? '' ),
			'tabs'          => $this->on( $s, 'tabs' ),
			'all_label'     => (string) ( $s['all_label'] ?? '' ),
			'limit'         => (int) ( $s['limit'] ?? 0 ),
			'columns'       => (int) ( $s['columns'] ?? 4 ),
			'buttons'       => $this->buttons_args( $s['buttons'] ?? array() ),
			'section_class' => $this->tone_class( $s ),
		);
	}
}

/**
 * Licenses.
 */
class Mwm_Widget_Licenses extends Mwm_Widget {
	protected $template      = 'licenses';
	protected $card_selector = '.mwm-license';

	public function get_title() {
		return __( 'MWM — التراخيص', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-lock-user';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls();
		$this->add_control(
			'note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'بيانات التراخيص تُدار من لوحة التحكم ← التراخيص. أي حقل فارغ لا يظهر.', 'mwm' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'الشكل', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cards',
				'options' => array(
					'cards' => __( 'بطاقات', 'mwm' ),
					'wide'  => __( 'صفوف عريضة', 'mwm' ),
				),
			)
		);
		$this->switcher( 'show_services', __( 'إظهار الخدمات المرخّصة', 'mwm' ) );
		$this->text( 'number_label', __( 'عنوان «رقم الترخيص»', 'mwm' ), __( 'رقم الترخيص', 'mwm' ) );
		$this->text( 'issue_label', __( 'عنوان «تاريخ الإصدار»', 'mwm' ), __( 'تاريخ الإصدار', 'mwm' ) );
		$this->text( 'expiry_label', __( 'عنوان «تاريخ الانتهاء»', 'mwm' ), __( 'سارٍ حتى', 'mwm' ) );
		$this->text( 'verify_label', __( 'نص زر التحقق', 'mwm' ), __( 'تحقق من الترخيص', 'mwm' ) );
		$this->text( 'document_label', __( 'نص زر عرض الملف', 'mwm' ), __( 'عرض الترخيص', 'mwm' ) );
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		return $this->head_args( $s ) + array(
			'layout'         => (string) ( $s['layout'] ?? 'cards' ),
			'show_services'  => $this->on( $s, 'show_services' ),
			'number_label'   => (string) ( $s['number_label'] ?? '' ),
			'issue_label'    => (string) ( $s['issue_label'] ?? '' ),
			'expiry_label'   => (string) ( $s['expiry_label'] ?? '' ),
			'verify_label'   => (string) ( $s['verify_label'] ?? '' ),
			'document_label' => (string) ( $s['document_label'] ?? '' ),
			'section_class'  => $this->tone_class( $s ),
		);
	}
}

/**
 * Branches + coverage.
 */
class Mwm_Widget_Branches extends Mwm_Widget {
	protected $template      = 'branches';
	protected $card_selector = '.mwm-branch:not(.mwm-branch--coverage)';

	public function get_title() {
		return __( 'MWM — الفروع والتغطية', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-map-pin';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls();
		$this->text( 'coverage_title', __( 'عنوان بطاقة التغطية', 'mwm' ), __( 'نخدم جميع مناطق المملكة', 'mwm' ), array( 'description' => __( 'اتركه فارغاً لإخفاء البطاقة.', 'mwm' ) ) );
		$this->add_control(
			'coverage_text',
			array(
				'label'       => __( 'نص بطاقة التغطية', 'mwm' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'description' => __( 'اتركه فارغاً لاستخدام «نطاق التغطية» من إعدادات الموقع.', 'mwm' ),
			)
		);
		$this->text( 'map_label', __( 'نص زر الخريطة', 'mwm' ), __( 'عرض على الخريطة', 'mwm' ) );
		$this->text( 'call_label', __( 'نص رابط الاتصال بالفرع', 'mwm' ), __( 'اتصل بالفرع', 'mwm' ) );
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		$args = $this->head_args( $s ) + array(
			'coverage_title' => (string) ( $s['coverage_title'] ?? '' ),
			'coverage_text'  => (string) ( $s['coverage_text'] ?? '' ),
			'map_label'      => (string) ( $s['map_label'] ?? '' ),
			'call_label'     => (string) ( $s['call_label'] ?? '' ),
			'section_class'  => $this->tone_class( $s ),
		);
		if ( '' === trim( $args['coverage_text'] ) ) {
			unset( $args['coverage_text'] );
		}
		return $args;
	}
}

/**
 * FAQ.
 */
class Mwm_Widget_Faq extends Mwm_Widget {
	protected $template      = 'faq';
	protected $card_selector = '.mwm-faq__item';

	public function get_title() {
		return __( 'MWM — الأسئلة الشائعة', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-accordion';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls();
		$this->add_control(
			'service',
			array(
				'label'   => __( 'الأسئلة المعروضة', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->service_options(
					array(
						''    => __( 'تلقائي — أسئلة الخدمة الحالية أو الكل', 'mwm' ),
						'all' => __( 'كل الأسئلة', 'mwm' ),
					)
				),
			)
		);
		$this->add_control(
			'limit',
			array(
				'label'   => __( 'أقصى عدد', 'mwm' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 0,
				'default' => 0,
			)
		);
		$this->switcher( 'open_first', __( 'فتح السؤال الأول', 'mwm' ) );
		$this->buttons_control();
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		return $this->head_args( $s ) + array(
			'service'       => $this->service_from( $s['service'] ?? '' ),
			'limit'         => (int) ( $s['limit'] ?? 0 ),
			'open_first'    => $this->on( $s, 'open_first' ),
			'buttons'       => $this->buttons_args( $s['buttons'] ?? array() ),
			'section_class' => $this->tone_class( $s ),
		);
	}
}

/**
 * CTA band.
 */
class Mwm_Widget_Cta extends Mwm_Widget {
	protected $template       = 'cta';
	protected $title_selector = '.mwm-cta__title';
	protected $text_selector  = '.mwm-cta__text';

	public function get_title() {
		return __( 'MWM — دعوة للتواصل', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls();
		$this->buttons_control();
		$this->add_control(
			'image',
			array(
				'label' => __( 'صورة خلفية (اختياري)', 'mwm' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$this->add_control(
			'style',
			array(
				'label'   => __( 'اللون', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'deep',
				'options' => array(
					'deep'  => __( 'أخضر داكن', 'mwm' ),
					'green' => __( 'أخضر', 'mwm' ),
				),
			)
		);
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		return $this->head_args( $s ) + array(
			'buttons' => $this->buttons_args( $s['buttons'] ?? array() ),
			'image'   => $this->media_id( $s['image'] ?? null ),
			'style'   => (string) ( $s['style'] ?? 'deep' ),
		);
	}

	protected function style_controls() {
		parent::style_controls();
		$this->open( 'cta_box', __( 'الصندوق', 'mwm' ), Controls_Manager::TAB_STYLE );
		$this->add_control(
			'box_bg',
			array(
				'label'     => __( 'لون الخلفية', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .mwm-cta__box' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}
}

/**
 * Contact cards.
 */
class Mwm_Widget_Contact extends Mwm_Widget {
	protected $template      = 'contact';
	protected $card_selector = '.mwm-contact__card';

	public function get_title() {
		return __( 'MWM — بيانات التواصل', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-envelope';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls();
		$this->add_control(
			'note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'الأرقام والبريد تُدار من «إعدادات الموقع» وتتحدث هنا تلقائياً.', 'mwm' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);
		$this->switcher( 'show_phones', __( 'أرقام التواصل', 'mwm' ) );
		$this->text( 'phones_label', __( 'عنوان بطاقة الأرقام', 'mwm' ), __( 'أرقام التواصل', 'mwm' ), array( 'condition' => array( 'show_phones' => 'yes' ) ) );
		$this->switcher( 'show_email', __( 'البريد الإلكتروني', 'mwm' ) );
		$this->text( 'email_label', __( 'عنوان بطاقة البريد', 'mwm' ), __( 'البريد الإلكتروني', 'mwm' ), array( 'condition' => array( 'show_email' => 'yes' ) ) );
		$this->switcher( 'show_coverage', __( 'العنوان / نطاق التغطية', 'mwm' ) );
		$this->text( 'coverage_label', __( 'عنوان بطاقة العنوان', 'mwm' ), __( 'العنوان', 'mwm' ), array( 'condition' => array( 'show_coverage' => 'yes' ) ) );
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		return $this->head_args( $s ) + array(
			'show_phones'    => $this->on( $s, 'show_phones' ),
			'show_email'     => $this->on( $s, 'show_email' ),
			'show_coverage'  => $this->on( $s, 'show_coverage' ),
			'phones_label'   => (string) ( $s['phones_label'] ?? __( 'أرقام التواصل', 'mwm' ) ),
			'email_label'    => (string) ( $s['email_label'] ?? '' ),
			'coverage_label' => (string) ( $s['coverage_label'] ?? '' ),
			'section_class'  => $this->tone_class( $s ),
		);
	}
}

/**
 * Quote / site-visit form.
 */
class Mwm_Widget_Quote_Form extends Mwm_Widget {
	protected $template = 'quote-form';

	public function get_title() {
		return __( 'MWM — نموذج طلب عرض سعر', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Overridable field labels.
	 *
	 * @return array<string,string>
	 */
	private function field_labels() {
		return array(
			'label_name'    => __( 'الاسم', 'mwm' ),
			'label_phone'   => __( 'رقم الجوال', 'mwm' ),
			'label_company' => __( 'اسم المنشأة', 'mwm' ),
			'label_city'    => __( 'المدينة / موقع المشروع', 'mwm' ),
			'label_email'   => __( 'البريد الإلكتروني', 'mwm' ),
			'label_service' => __( 'الخدمة المطلوبة', 'mwm' ),
			'label_type'    => __( 'نوع الطلب', 'mwm' ),
			'label_message' => __( 'تفاصيل الطلب', 'mwm' ),
		);
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->head_controls();
		$this->add_control(
			'note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'أنواع الطلب، البريد المستلم، نص الموافقة ورسالة النجاح تُدار من «إعدادات الموقع ← الطلبات». الطلبات تُحفظ في لوحة التحكم ← طلبات العملاء.', 'mwm' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);
		$this->text( 'submit_label', __( 'نص زر الإرسال', 'mwm' ), __( 'إرسال الطلب', 'mwm' ) );
		$this->text( 'success_title', __( 'عنوان رسالة النجاح', 'mwm' ), __( 'شكراً لتواصلك', 'mwm' ) );
		$this->text( 'success_wa', __( 'نص زر واتساب بعد الإرسال', 'mwm' ), __( 'تابع معنا عبر واتساب', 'mwm' ) );
		$this->text( 'placeholder_message', __( 'نص مساعد في خانة التفاصيل', 'mwm' ) );
		$this->end_controls_section();

		$this->open( 'labels', __( 'عناوين الحقول', 'mwm' ) );
		foreach ( $this->field_labels() as $key => $label ) {
			$this->text( $key, $label, $label );
		}
		$this->end_controls_section();

		$this->open( 'aside', __( 'العمود الجانبي', 'mwm' ) );
		$this->switcher( 'show_aside', __( 'إظهار العمود الجانبي', 'mwm' ) );
		$this->text( 'aside_title', __( 'العنوان', 'mwm' ), __( 'تفضّل بالتواصل المباشر', 'mwm' ), array( 'condition' => array( 'show_aside' => 'yes' ) ) );
		$this->add_control(
			'aside_text',
			array(
				'label'     => __( 'النص', 'mwm' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'condition' => array( 'show_aside' => 'yes' ),
			)
		);
		$this->add_control(
			'aside_points',
			array(
				'label'     => __( 'نقاط (سطر لكل نقطة)', 'mwm' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 4,
				'condition' => array( 'show_aside' => 'yes' ),
			)
		);
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		$args = $this->head_args( $s ) + array(
			'submit_label'        => (string) ( $s['submit_label'] ?? '' ),
			'success_title'       => (string) ( $s['success_title'] ?? '' ),
			'success_wa'          => (string) ( $s['success_wa'] ?? '' ),
			'placeholder_message' => (string) ( $s['placeholder_message'] ?? '' ),
			'show_aside'          => $this->on( $s, 'show_aside' ),
			'aside_title'         => (string) ( $s['aside_title'] ?? '' ),
			'aside_text'          => (string) ( $s['aside_text'] ?? '' ),
			'aside_points'        => $this->lines( $s['aside_points'] ?? '' ),
			'section_class'       => $this->tone_class( $s ),
		);
		foreach ( array_keys( $this->field_labels() ) as $key ) {
			$args[ $key ] = (string) ( $s[ $key ] ?? '' );
		}
		return $args;
	}
}

/**
 * Free rich text.
 */
class Mwm_Widget_Prose extends Mwm_Widget {
	protected $template      = 'prose';
	protected $title_selector = '.mwm-prose :is(h2, h3, h4)';
	protected $text_selector  = '.mwm-prose';

	public function get_title() {
		return __( 'MWM — نص حر', 'mwm' );
	}

	public function get_icon() {
		return 'eicon-text';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'المحتوى', 'mwm' ) );
		$this->add_control(
			'content',
			array(
				'label'   => __( 'النص', 'mwm' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => '',
			)
		);
		$this->switcher( 'wide', __( 'عرض كامل (بدلاً من عمود قراءة ضيق)', 'mwm' ), false );
		$this->tone_control();
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		return array(
			'content'       => (string) ( $s['content'] ?? '' ),
			'wide'          => $this->on( $s, 'wide' ),
			'section_class' => $this->tone_class( $s ),
		);
	}
}

/**
 * Site header (used inside the header template).
 */
class Mwm_Widget_Site_Header extends Mwm_Widget {
	protected $template = 'site-header';

	public function get_title() {
		return __( 'MWM — هيدر الموقع', 'mwm' );
	}

	public function show_in_panel() {
		return mwm_editing_template();
	}

	public function get_icon() {
		return 'eicon-header';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'الهيدر', 'mwm' ) );
		$this->add_control(
			'note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'روابط القائمة تُدار من المظهر ← القوائم. الشعار من المظهر ← تخصيص ← هوية الموقع. الأرقام من «إعدادات الموقع».', 'mwm' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);
		$this->add_control(
			'phone_slot',
			array(
				'label'   => __( 'رقم الاتصال الظاهر', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '1',
				'options' => array(
					'0' => __( 'بدون', 'mwm' ),
					'1' => __( 'الرقم 1', 'mwm' ),
					'2' => __( 'الرقم 2', 'mwm' ),
					'3' => __( 'الرقم 3', 'mwm' ),
				),
			)
		);
		$this->text( 'cta_label', __( 'نص الزر', 'mwm' ), __( 'اطلب عرض سعر', 'mwm' ), array( 'description' => __( 'اتركه فارغاً لإخفاء الزر.', 'mwm' ) ) );
		$this->link_controls( null, 'cta_' );
		$this->switcher( 'drawer_whatsapp', __( 'أزرار واتساب في قائمة الجوال', 'mwm' ) );
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		return array(
			'phone_slot'      => (int) ( $s['phone_slot'] ?? 0 ),
			'cta_label'       => (string) ( $s['cta_label'] ?? '' ),
			'cta_link'        => (string) ( $s['cta_link'] ?? 'role:quote' ),
			'cta_url'         => (string) ( $s['cta_url']['url'] ?? '' ),
			'drawer_whatsapp' => $this->on( $s, 'drawer_whatsapp' ),
		);
	}

	protected function render() {
		get_template_part( 'template-parts/header/site-header', null, $this->template_args( $this->get_settings_for_display() ) );
	}

	protected function style_controls() {
		$this->open( 'header_nav', __( 'القائمة', 'mwm' ), Controls_Manager::TAB_STYLE );
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'nav_typo',
				'selector' => '{{WRAPPER}} .mwm-nav > ul > li > a',
			)
		);
		$this->end_controls_section();

		$this->open( 'header_cta', __( 'زر الهيدر', 'mwm' ), Controls_Manager::TAB_STYLE );
		$this->add_control(
			'cta_bg',
			array(
				'label'     => __( 'لون الزر', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .mwm-header__cta' => '--btn-bg: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'cta_fg',
			array(
				'label'     => __( 'لون نص الزر', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .mwm-header__cta' => '--btn-fg: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'cta_radius',
			array(
				'label'      => __( 'استدارة الزوايا', 'mwm' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .mwm-header__cta' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}
}

/**
 * Site footer (used inside the footer template).
 */
class Mwm_Widget_Site_Footer extends Mwm_Widget {
	protected $template = 'site-footer';

	public function get_title() {
		return __( 'MWM — فوتر الموقع', 'mwm' );
	}

	public function show_in_panel() {
		return mwm_editing_template();
	}

	public function get_icon() {
		return 'eicon-footer';
	}

	protected function content_controls() {
		$this->open( 'content', __( 'العمود الأول', 'mwm' ) );
		$this->add_control(
			'note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'الأرقام والبريد والعنوان والسجل التجاري وروابط التواصل الاجتماعي تُدار من «إعدادات الموقع». روابط عمود «روابط سريعة» من المظهر ← القوائم، وعمود الخدمات يتحدث تلقائياً من الخدمات.', 'mwm' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);
		$this->add_control(
			'about',
			array(
				'label'   => __( 'نبذة', 'mwm' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => '',
			)
		);
		$this->switcher( 'show_socials', __( 'أيقونات التواصل الاجتماعي', 'mwm' ) );
		$this->end_controls_section();

		$this->open( 'columns', __( 'الأعمدة', 'mwm' ) );
		$this->switcher( 'show_menu', __( 'عمود روابط سريعة', 'mwm' ) );
		$this->add_control(
			'menu_title',
			array(
				'label'       => __( 'عنوان عمود الروابط', 'mwm' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
				'placeholder' => __( 'اسم القائمة تلقائياً', 'mwm' ),
				'condition'   => array( 'show_menu' => 'yes' ),
			)
		);
		$this->switcher( 'show_services', __( 'عمود الخدمات', 'mwm' ) );
		$this->text( 'services_title', __( 'عنوان عمود الخدمات', 'mwm' ), __( 'خدماتنا', 'mwm' ), array( 'condition' => array( 'show_services' => 'yes' ) ) );
		$this->text( 'contact_title', __( 'عنوان عمود التواصل', 'mwm' ), __( 'تواصل معنا', 'mwm' ) );
		$this->switcher( 'show_phones', __( 'الأرقام', 'mwm' ) );
		$this->switcher( 'show_email', __( 'البريد الإلكتروني', 'mwm' ) );
		$this->switcher( 'show_coverage', __( 'نطاق التغطية', 'mwm' ) );
		$this->end_controls_section();

		$this->open( 'bottom', __( 'الشريط السفلي', 'mwm' ) );
		$this->text( 'copyright', __( 'نص حقوق النشر', 'mwm' ), '', array( 'description' => __( 'استخدم {year} لإظهار السنة الحالية.', 'mwm' ) ) );
		$this->switcher( 'show_ids', __( 'السجل التجاري والأرقام الرسمية', 'mwm' ) );
		$this->switcher( 'show_privacy', __( 'رابط سياسة الخصوصية', 'mwm' ) );
		$this->end_controls_section();
	}

	protected function template_args( array $s ) {
		return array(
			'about'          => (string) ( $s['about'] ?? '' ),
			'show_socials'   => $this->on( $s, 'show_socials' ),
			'show_menu'      => $this->on( $s, 'show_menu' ),
			'menu_title'     => (string) ( $s['menu_title'] ?? '' ),
			'show_services'  => $this->on( $s, 'show_services' ),
			'services_title' => (string) ( $s['services_title'] ?? '' ),
			'contact_title'  => (string) ( $s['contact_title'] ?? '' ),
			'show_phones'    => $this->on( $s, 'show_phones' ),
			'show_email'     => $this->on( $s, 'show_email' ),
			'show_coverage'  => $this->on( $s, 'show_coverage' ),
			'copyright'      => (string) ( $s['copyright'] ?? '' ),
			'show_ids'       => $this->on( $s, 'show_ids' ),
			'show_privacy'   => $this->on( $s, 'show_privacy' ),
		);
	}

	protected function render() {
		get_template_part( 'template-parts/footer/site-footer', null, $this->template_args( $this->get_settings_for_display() ) );
	}

	protected function style_controls() {
		$this->open( 'footer_colors', __( 'الألوان', 'mwm' ), Controls_Manager::TAB_STYLE );
		$this->add_control(
			'footer_bg',
			array(
				'label'     => __( 'لون الخلفية', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .mwm-footer' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'footer_text',
			array(
				'label'     => __( 'لون النصوص والروابط', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .mwm-footer, {{WRAPPER}} .mwm-footer a, {{WRAPPER}} .mwm-footer .mwm-brand__sub' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'footer_titles',
			array(
				'label'     => __( 'لون العناوين', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .mwm-footer__title' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'footer_typo',
				'label'    => __( 'خط النصوص', 'mwm' ),
				'selector' => '{{WRAPPER}} .mwm-footer__main',
			)
		);
		$this->end_controls_section();
	}
}

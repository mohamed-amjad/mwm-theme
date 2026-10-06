<?php
/**
 * Base class for MWM widgets: shared controls, setting readers, template rendering.
 *
 * @package MWM
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

/**
 * MWM widget base.
 */
abstract class Mwm_Widget extends \Elementor\Widget_Base {

	/**
	 * Template part slug in template-parts/sections/.
	 *
	 * @var string
	 */
	protected $template = '';

	/**
	 * Selector of the main heading for style controls.
	 *
	 * @var string
	 */
	protected $title_selector = '.mwm-title';

	/**
	 * Selector of the lead text for style controls.
	 *
	 * @var string
	 */
	protected $text_selector = '.mwm-lead';

	/**
	 * Selector of repeated cards ('' = no card style section).
	 *
	 * @var string
	 */
	protected $card_selector = '';

	/**
	 * Build template args from display settings.
	 *
	 * @param array $s Settings.
	 * @return array
	 */
	abstract protected function template_args( array $s );

	/**
	 * Content controls.
	 */
	abstract protected function content_controls();

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'mwm-' . $this->template;
	}

	/**
	 * Category.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'mwm' );
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'mwm', $this->template );
	}

	/**
	 * Optimized markup: no inner wrapper.
	 *
	 * @return bool
	 */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	/**
	 * Reads live records/settings, so Element Caching must not serve stale output.
	 *
	 * @return bool
	 */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->content_controls();
		$this->layout_controls();
		$this->style_controls();
	}

	/**
	 * Render the shared template part.
	 */
	protected function render() {
		$args = $this->template_args( $this->get_settings_for_display() );
		get_template_part( 'template-parts/sections/' . $this->template, null, $args );
	}

	/* ------------------------------------------------------------------
	 * Control helpers
	 * ---------------------------------------------------------------- */

	/**
	 * Open a content section.
	 *
	 * @param string $id    ID.
	 * @param string $label Label.
	 * @param string $tab   Tab.
	 */
	protected function open( $id, $label, $tab = Controls_Manager::TAB_CONTENT ) {
		// Sections share the control namespace; prefix so a section never shadows a field of the same name.
		$this->start_controls_section(
			'section_' . $id,
			array(
				'label' => $label,
				'tab'   => $tab,
			)
		);
	}

	/**
	 * Section heading controls (kicker, title, tag, lead).
	 *
	 * @param array $opts Options: tag default, text (bool).
	 */
	protected function head_controls( array $opts = array() ) {
		$opts = array_merge(
			array(
				'tag'  => 'h2',
				'text' => true,
			),
			$opts
		);
		$this->add_control(
			'kicker',
			array(
				'label'       => __( 'العنوان الصغير', 'mwm' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'مثال: خدماتنا', 'mwm' ),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'       => __( 'العنوان', 'mwm' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'placeholder' => __( 'اكتب العنوان هنا', 'mwm' ),
				'description' => __( 'يمكن تمييز كلمة بوضعها بين <mark>…</mark>', 'mwm' ),
			)
		);
		$this->add_control(
			'title_tag',
			array(
				'label'   => __( 'وسم العنوان', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $opts['tag'],
				'options' => array(
					'h1'  => 'H1',
					'h2'  => 'H2',
					'h3'  => 'H3',
					'p'   => 'P',
					'div' => 'DIV',
				),
			)
		);
		if ( $opts['text'] ) {
			$this->add_control(
				'text',
				array(
					'label'       => __( 'النص التمهيدي', 'mwm' ),
					'type'        => Controls_Manager::TEXTAREA,
					'rows'        => 3,
					'placeholder' => __( 'نص قصير يشرح القسم', 'mwm' ),
				)
			);
		}
	}

	/**
	 * Head args.
	 *
	 * @param array $s Settings.
	 * @return array
	 */
	protected function head_args( array $s ) {
		return array(
			'kicker'    => (string) ( $s['kicker'] ?? '' ),
			'title'     => (string) ( $s['title'] ?? '' ),
			'title_tag' => (string) ( $s['title_tag'] ?? 'h2' ),
			'text'      => (string) ( $s['text'] ?? '' ),
		);
	}

	/**
	 * Link source select + custom URL.
	 *
	 * @param Repeater|null $target  Repeater or null for the widget.
	 * @param string        $prefix  Control name prefix.
	 * @param string        $default Default source.
	 */
	protected function link_controls( $target, $prefix, $default = 'role:quote' ) {
		$target = $target ? $target : $this;
		$target->add_control(
			$prefix . 'link',
			array(
				'label'   => __( 'الرابط', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $default,
				'options' => mwm_link_source_choices(),
			)
		);
		$target->add_control(
			$prefix . 'url',
			array(
				'label'       => __( 'الرابط المخصص', 'mwm' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://',
				'condition'   => array( $prefix . 'link' => 'custom' ),
				'dynamic'     => array( 'active' => true ),
			)
		);
	}

	/**
	 * Buttons repeater.
	 *
	 * @param string $name    Control name.
	 * @param array  $default Default rows.
	 */
	protected function buttons_control( $name = 'buttons', array $default = array() ) {
		$rep = new Repeater();
		$rep->add_control(
			'label',
			array(
				'label'       => __( 'نص الزر', 'mwm' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'اطلب عرض سعر', 'mwm' ),
			)
		);
		$this->link_controls( $rep, '' );
		$rep->add_control(
			'style',
			array(
				'label'   => __( 'شكل الزر', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'primary',
				'options' => array(
					'primary'     => __( 'أخضر أساسي', 'mwm' ),
					'deep'        => __( 'أخضر داكن', 'mwm' ),
					'leaf'        => __( 'أخضر فاتح', 'mwm' ),
					'light'       => __( 'أبيض', 'mwm' ),
					'outline'     => __( 'بإطار', 'mwm' ),
					'ghost-light' => __( 'شفاف (على خلفية داكنة)', 'mwm' ),
				),
			)
		);
		$rep->add_control(
			'icon',
			array(
				'label'   => __( 'الأيقونة', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''      => __( 'بدون', 'mwm' ),
					'arrow' => __( 'سهم', 'mwm' ),
				) + mwm_icon_choices(),
			)
		);
		$this->add_control(
			$name,
			array(
				'label'       => __( 'الأزرار', 'mwm' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'default'     => $default,
				'title_field' => '{{{ label }}}',
				'prevent_empty' => false,
			)
		);
	}

	/**
	 * Resolve button rows.
	 *
	 * @param mixed $rows Rows.
	 * @return array
	 */
	protected function buttons_args( $rows ) {
		$out = array();
		foreach ( (array) $rows as $row ) {
			$label = trim( (string) ( $row['label'] ?? '' ) );
			if ( '' === $label ) {
				continue;
			}
			$out[] = array(
				'label' => $label,
				'link'  => mwm_resolve_link( (string) ( $row['link'] ?? 'custom' ), (string) ( $row['url']['url'] ?? '' ) ),
				'style' => (string) ( $row['style'] ?? 'primary' ),
				'icon'  => (string) ( $row['icon'] ?? '' ),
			);
		}
		return $out;
	}

	/**
	 * Icon select.
	 *
	 * @param Repeater|null $target  Target.
	 * @param string        $name    Name.
	 * @param string        $default Default.
	 * @param bool          $none    Allow none.
	 */
	protected function icon_control( $target, $name = 'icon', $default = 'leaf', $none = true ) {
		$target = $target ? $target : $this;
		$target->add_control(
			$name,
			array(
				'label'   => __( 'الأيقونة', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $default,
				'options' => ( $none ? array( '' => __( 'بدون', 'mwm' ) ) : array() ) + mwm_icon_choices(),
			)
		);
	}

	/**
	 * Lines textarea → array.
	 *
	 * @param mixed $text Text.
	 * @return string[]
	 */
	protected function lines( $text ) {
		return mwm_lines( (string) $text );
	}

	/**
	 * Media control value → attachment ID.
	 *
	 * @param mixed $media Media.
	 * @return int
	 */
	protected function media_id( $media ) {
		return is_array( $media ) ? (int) ( $media['id'] ?? 0 ) : 0;
	}

	/**
	 * Service picker options.
	 *
	 * @param array $lead Leading options.
	 * @return array
	 */
	protected function service_options( array $lead = array() ) {
		$options = $lead;
		foreach ( mwm_get_services() as $service ) {
			$options[ (string) $service->ID ] = get_the_title( $service );
		}
		return $options;
	}

	/**
	 * Resolve a service picker value ('' = current service page or all).
	 *
	 * @param mixed $value Value.
	 * @return int
	 */
	protected function service_from( $value ) {
		if ( is_numeric( $value ) && (int) $value > 0 ) {
			return (int) $value;
		}
		if ( 'all' === $value ) {
			return 0;
		}
		return is_singular( 'mwm_service' ) ? get_queried_object_id() : 0;
	}

	/**
	 * Background tone select.
	 */
	protected function tone_control() {
		$this->add_control(
			'tone',
			array(
				'label'   => __( 'خلفية القسم', 'mwm' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''      => __( 'أبيض', 'mwm' ),
					'tint'  => __( 'رمادي فاتح جداً', 'mwm' ),
					'green' => __( 'أخضر فاتح', 'mwm' ),
					'sky'   => __( 'سماوي فاتح', 'mwm' ),
					'deep'  => __( 'أخضر داكن', 'mwm' ),
				),
			)
		);
	}

	/**
	 * Section classes from the tone select.
	 *
	 * @param array $s Settings.
	 * @return string
	 */
	protected function tone_class( array $s ) {
		$tone = (string) ( $s['tone'] ?? '' );
		if ( '' === $tone ) {
			return '';
		}
		return 'mwm-section--' . sanitize_html_class( $tone ) . ( 'deep' === $tone ? ' mwm-dark' : '' );
	}

	/**
	 * Optional per-widget layout controls (overridden).
	 */
	protected function layout_controls() {}

	/**
	 * Shared style controls.
	 */
	protected function style_controls() {
		$this->open( 'mwm_style_section', __( 'القسم', 'mwm' ), Controls_Manager::TAB_STYLE );
		$this->add_responsive_control(
			'section_padding',
			array(
				'label'      => __( 'المسافة الداخلية (أعلى/أسفل)', 'mwm' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 240,
					),
				),
				'selectors'  => array( '{{WRAPPER}} > section' => 'padding-block: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'section_bg',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} > section',
			)
		);
		$this->end_controls_section();

		$this->open( 'mwm_style_head', __( 'العناوين والنصوص', 'mwm' ), Controls_Manager::TAB_STYLE );
		$this->add_control(
			'kicker_color',
			array(
				'label'     => __( 'لون العنوان الصغير', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .mwm-kicker' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'لون العنوان', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} ' . $this->title_selector => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typo',
				'label'    => __( 'خط العنوان', 'mwm' ),
				'selector' => '{{WRAPPER}} ' . $this->title_selector,
			)
		);
		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'لون النص', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} ' . $this->text_selector => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'text_typo',
				'label'    => __( 'خط النص', 'mwm' ),
				'selector' => '{{WRAPPER}} ' . $this->text_selector,
			)
		);
		$this->add_responsive_control(
			'head_align',
			array(
				'label'     => __( 'محاذاة رأس القسم', 'mwm' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'start'  => array(
						'title' => __( 'بداية', 'mwm' ),
						'icon'  => 'eicon-text-align-right',
					),
					'center' => array(
						'title' => __( 'وسط', 'mwm' ),
						'icon'  => 'eicon-text-align-center',
					),
					'end'    => array(
						'title' => __( 'نهاية', 'mwm' ),
						'icon'  => 'eicon-text-align-left',
					),
				),
				'selectors' => array( '{{WRAPPER}} .mwm-head' => 'text-align: {{VALUE}};' ),
				'selectors_dictionary' => array(
					'start'  => 'start; margin-inline: 0',
					'center' => 'center; margin-inline: auto',
					'end'    => 'end; margin-inline: auto 0',
				),
			)
		);
		$this->end_controls_section();

		if ( '' !== $this->card_selector ) {
			$this->open( 'mwm_style_cards', __( 'البطاقات', 'mwm' ), Controls_Manager::TAB_STYLE );
			$this->add_control(
				'card_bg',
				array(
					'label'     => __( 'خلفية البطاقة', 'mwm' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( '{{WRAPPER}} ' . $this->card_selector => 'background-color: {{VALUE}};' ),
				)
			);
			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => 'card_border',
					'selector' => '{{WRAPPER}} ' . $this->card_selector,
				)
			);
			$this->add_responsive_control(
				'card_radius',
				array(
					'label'      => __( 'استدارة الزوايا', 'mwm' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 48,
						),
					),
					'selectors'  => array( '{{WRAPPER}} ' . $this->card_selector => 'border-radius: {{SIZE}}{{UNIT}};' ),
				)
			);
			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => 'card_shadow',
					'selector' => '{{WRAPPER}} ' . $this->card_selector,
				)
			);
			$this->add_responsive_control(
				'grid_gap',
				array(
					'label'      => __( 'المسافة بين البطاقات', 'mwm' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 80,
						),
					),
					'selectors'  => array( '{{WRAPPER}}' => '--mwm-grid-gap: {{SIZE}}{{UNIT}};' ),
				)
			);
			$this->end_controls_section();
		}

		$this->open( 'mwm_style_buttons', __( 'الأزرار', 'mwm' ), Controls_Manager::TAB_STYLE );
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'btn_typo',
				'selector' => '{{WRAPPER}} .mwm-btn',
			)
		);
		$this->add_control(
			'btn_radius',
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
				'selectors'  => array( '{{WRAPPER}} .mwm-btn' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'btn_primary_bg',
			array(
				'label'     => __( 'لون الزر الأساسي', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .mwm-btn:not([class*="mwm-btn--deep"],[class*="mwm-btn--leaf"],[class*="mwm-btn--light"],[class*="mwm-btn--outline"],[class*="mwm-btn--ghost"])' => '--btn-bg: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'btn_primary_fg',
			array(
				'label'     => __( 'لون نص الزر الأساسي', 'mwm' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .mwm-btn:not([class*="mwm-btn--deep"],[class*="mwm-btn--leaf"],[class*="mwm-btn--light"],[class*="mwm-btn--outline"],[class*="mwm-btn--ghost"])' => '--btn-fg: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Columns select.
	 *
	 * @param int $default Default.
	 * @param int $max     Max.
	 */
	protected function columns_control( $default = 3, $max = 4 ) {
		$options = array();
		for ( $i = 2; $i <= $max; $i++ ) {
			$options[ (string) $i ] = (string) $i;
		}
		$this->add_control(
			'columns',
			array(
				'label'       => __( 'عدد الأعمدة (شاشات كبيرة)', 'mwm' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => (string) $default,
				'options'     => $options,
				'description' => __( 'يقل العدد تلقائياً على الأجهزة اللوحية والجوال.', 'mwm' ),
			)
		);
	}

	/**
	 * Switcher shortcut.
	 *
	 * @param string $name    Name.
	 * @param string $label   Label.
	 * @param bool   $default Default on.
	 */
	protected function switcher( $name, $label, $default = true ) {
		$this->add_control(
			$name,
			array(
				'label'        => $label,
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => $default ? 'yes' : '',
			)
		);
	}

	/**
	 * Text shortcut. The placeholder doubles as the default value, because an
	 * empty saved value hides the element on the front end.
	 *
	 * @param string $name        Name.
	 * @param string $label       Label.
	 * @param string $placeholder Placeholder / default.
	 * @param array  $extra       Extra args.
	 */
	protected function text( $name, $label, $placeholder = '', array $extra = array() ) {
		$this->add_control(
			$name,
			array_merge(
				array(
					'label'       => $label,
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'placeholder' => $placeholder,
					'default'     => $placeholder,
				),
				$extra
			)
		);
	}

	/**
	 * Is a switcher on?
	 *
	 * @param array  $s    Settings.
	 * @param string $name Name.
	 * @return bool
	 */
	protected function on( array $s, $name ) {
		return 'yes' === ( $s[ $name ] ?? '' );
	}
}

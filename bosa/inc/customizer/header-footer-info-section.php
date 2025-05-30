<?php
/**
 * Header Footer Info Customizer Section.
 *
 * @package Bosa
 */

if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return null;
}

/**
 * Header Footer Info Customizer Section class.
 *
 * @since  1.0.0
 * @access public
 */
class Bosa_Header_Footer_Info_Section extends WP_Customize_Section {

	/**
	 * The type of customize section being rendered.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $type = 'header_footer';

	/**
	 * Custom pro button URL.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $url = '';

	/**
	 * Custom pro button TEXT.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $text = '';

	/**
	 * Custom pro button LINK.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $link = '';

	/**
	 * Custom pro button NOTE.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $note = '';

	/**
	 * Custom pro button URL_TEXT.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $url_text = '';

	/**
	 * Add custom parameters to pass to the JS via JSON.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function json() {
		$json = parent::json();

		$json['title'] = $this->title;
		$json['text'] = $this->text;
		$json['note'] = $this->note;
		$json['link'] = $this->link;
		$json['url_text'] = $this->url_text;
		$json['url']  = esc_url( $this->url );

		return $json;
	}

	/**
	 * Outputs the Underscore.js template.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	protected function render_template() { ?>

		<li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }} cannot-expand">

			<div class="header-footer-info-wrapper">
				<h4 class="info-title">{{ data.title }}</h4>
				<p class="info-desc">{{ data.text }}</p>
				<p class="link-txt">
					<span>{{ data.link }}</span>
					<a href="{{ data.url }}" class="" target="_blank">{{ data.url_text }}</a>					
				</p>
				<h4 class="info-note">{{ data.note }}</h4>
			</div>
		</li>
	<?php }
}
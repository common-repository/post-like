<?php
/**
 * Class Post_Like_Display
 */
class PL_SVG_ICONS
{

	/**
	 * PL_SVG_ICONS constructor.
	 */
	public function __construct()
	{
		// inlude svg definitions to the frontend footer.
		add_action( 'wp_footer', array( $this, 'pl_include_svg_icons' ), 999 );

		// check if current user can manage options and is admin page
		if ( is_admin() &&  current_user_can('manage_options') ) {
			// include svg definitions to the admin footer.
			add_action( 'admin_footer', array( $this, 'pl_include_svg_icons' ), 999 );
		}
	}

	/**
	 * Add SVG definitions to the footer.
	 */
	function pl_include_svg_icons() {
		// Define SVG sprite file.
		$svg_icons = PL_BASE_DIR . '/assets/images/post-like.svg';
		// If it exists, include it.
		if ( file_exists( $svg_icons ) ) {
			require_once( $svg_icons );
		}
	}

	/**
	 * Return SVG markup.
	 *
	 * @param array $args {
	 *     Parameters needed to display an SVG.
	 *
	 *     @type string $icon  Required SVG icon filename.
	 *     @type string $title Optional SVG title.
	 *     @type string $desc  Optional SVG description.
	 * }
	 * @return string SVG markup.
	 * <svg class="pl-icon pl-icon-heart" aria-hidden="true" role="img">
	 *  <use xlink:href="#pl-icon-heart"></use>
	 * </svg>
	 */
	public function pl_get_svg( $args = array() ) {
		// Make sure $args are an array.
		if ( empty( $args ) ) {
			return __( 'Please define default parameters in the form of an array.', 'post-like' );
		}

		// Define an icon.
		if ( false === array_key_exists( 'icon', $args ) ) {
			return __( 'Please define an SVG icon filename.', 'post-like' );
		}

		// Set defaults.
		$defaults = array(
			'icon'        => '',
			'title'       => '',
			'desc'        => '',
			'aria_hidden' => true,
			'fallback'    => false,
		);

		// Parse args.
		$args = wp_parse_args( $args, $defaults );

		// Set aria hidden.
		$aria_hidden = '';
		if ( true === $args['aria_hidden'] ) {
			$aria_hidden = ' aria-hidden="true"';
		}

		// Set ARIA.
		$aria_labelledby = '';
		if ( $args['title'] && $args['desc'] ) {
			$aria_labelledby = ' aria-labelledby="title desc"';
		}
		// Begin SVG markup.
		$svg = '<svg class="pl-icon pl-icon-' . esc_attr( $args['icon'] ) . '"' . $aria_hidden . $aria_labelledby . ' role="img">';

		// If there is a title, display it.
		if ( $args['title'] ) {
			$svg .= '<title>' . esc_html( $args['title'] ) . '</title>';
		}

		// If there is a description, display it.
		if ( $args['desc'] ) {
			$svg .= '<desc>' . esc_html( $args['desc'] ) . '</desc>';
		}

		// Use absolute path in the Customizer so that icons show up in there.
		if ( is_customize_preview() ) {
			$svg .= '<use xlink:href="' . PL_BASE_URI . 'assets/images/post-like.svg#pl-icon-' . esc_html( $args['icon'] ) . '"></use>';
		} else {
			$svg .= '<use xlink:href="#pl-icon-' . esc_html( $args['icon'] ) . '"></use>';
		}

		// Add some markup to use as a fallback for browsers that do not support SVGs.
		if ( $args['fallback'] ) {
			$svg .= '<span class="svg-fallback pl-icon-' . esc_attr( $args['icon'] ) . '"></span>';
		}
		$svg .= '</svg>';
		return $svg;
	}
}
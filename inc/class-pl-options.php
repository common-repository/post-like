<?php
/**
 * Post like plugin options
 */
if ( ! class_exists( 'PL_Options' ) ) :
	class PL_Options {

		/**
		 * @var $name plugin name
		 * @var $version plugin version
		 */
		public $name;
		public $version;

		/**
		 * @var $settings_api settings api object
		 */
		private $settings_api;

		/**
		 * @var $pl_svg_icons class object
		 */
		public $pl_svg_icons;

		/**
		 * PL_Options constructor.
		 */
		function __construct() {
			// set plugin name & version
			$this->name = PL_NAME;
			$this->version = PL_VERSION;

			$this->settings_api = new PL_Settings_API;
			$this->pl_svg_icons = new PL_SVG_ICONS;

			add_action( 'admin_init', array( $this, 'pl_admin_init' ) );
			add_action( 'admin_menu', array( $this, 'pl_admin_menu' ) );

			// display admin form sidebar
			add_action( 'pl_form_sidebar', array( $this, 'pl_form_sidebar_container' ) );
		}

		/**
		 * admin menu initialization
		 */
		function pl_admin_init() {
			//set the settings
			$this->settings_api->set_sections( $this->pl_get_sections() );
			$this->settings_api->set_fields( $this->pl_get_section_fields() );

			//initialize settings
			$this->settings_api->admin_init();
		}

		/**
		 * create admin menu options page
		 */
		function pl_admin_menu() {
			add_options_page(
				'Post Like Settings',
				'Post Like Settings',
				'manage_options',
				'post_like_options',
				array( $this, 'pl_plugin_page' )
			);
		}

		/**
		 * create tabbed sections
		 * @return array setting sections
		 */
		function pl_get_sections() {
			$sections = array(
				// basic settings tab
				array(
					'id'    => 'pl_basic',
					'title' => __( 'Basic Options', 'post-like' )
				),
				// style options
				array(
					'id'    => 'pl_style',
					'title' => __( 'Style Options', 'post-like' )
				)
			);

			return $sections;
		}

		/**
		 * Returns all the settings fields
		 * @return array settings fields
		 */
		function pl_get_section_fields() {
			$settings_fields = array(
				'pl_basic' => array(
					// like text input
					array(
						'name'              => 'pl_like_text',
						'label'             => __( 'Like Text', 'post-like' ),
						'desc'              => __( 'Like link text, e.g Like, Love, Favorite, Wishlist etc.', 'post-like' ),
						'placeholder'       => __( 'Like', 'post-like' ),
						'type'              => 'text',
						'default'           => 'Like',
						'sanitize_callback' => 'sanitize_text_field'
					),
					// liked text input
					array(
						'name'              => 'pl_liked_text',
						'label'             => __( 'Liked Text', 'post-like' ),
						'desc'              => __( 'Liked link text, e.g Liked, Loved, Add to Favorite etc.', 'post-like' ),
						'placeholder'       => __( 'Liked', 'post-like' ),
						'type'              => 'text',
						'default'           => 'Liked',
						'sanitize_callback' => 'sanitize_text_field'
					),
					// like icon
					array(
						'name'    => 'pl_like_icon',
						'label'   => __( 'Like Icon', 'post-like' ),
						'type'    => 'image_radio',
						'options' => array(
							'heart'    => $this->pl_svg_icons->pl_get_svg( array( 'icon' => 'heart' ) ),
							'thumb-up' => $this->pl_svg_icons->pl_get_svg( array( 'icon' => 'thumb-up' ) ),
							'star'     => $this->pl_svg_icons->pl_get_svg( array( 'icon' => 'star' ) ),
						)
					),
				),
				'pl_style' => array(
					// font size
					array(
						'name'              => 'pl_font_size',
						'label'             => __( 'Font Size', 'post-like' ),
						'desc'              => __( 'Set Like text, icon and counter font size in `px`', 'post-like' ),
						'placeholder'       => __( '14', 'post-like' ),
						'min'               => 8,
						'max'               => 40,
						'step'              => '1',
						'type'              => 'number',
						'default'           => 'Title',
						'sanitize_callback' => 'floatval'
					),
					// text color
					array(
						'name'    => 'pl_txt_color',
						'label'   => __( 'Text, Icon, Count Color', 'post-like' ),
						'desc'    => __( 'Change text, icon and count color', 'post-like' ),
						'type'    => 'color',
						'default' => ''
					),
					// post like alignment
					array(
						'name'    => 'pl_alignment',
						'label'   => __( 'Post Like Link Alignment', 'post-like' ),
						'type'    => 'select',
						'default' => 'left',
						'options' => array(
							'left'   => 'Align Left',
							'right'  => 'Align Right',
							'center' => 'Align Center'
						)
					),
				)
			);

			return $settings_fields;
		}

		/**
		 * create plugin settings page
		 * with tabbed navigation and form fields
		 */
		function pl_plugin_page() {
			echo '<div class="wrap">';
				echo '<div class="pl-options-wrap">';
					echo '<div class="pl-wrap ">';
						echo '<div class="pl-options-header">';
							echo '<h3 class="pl-header-title">';
							echo '<img class="pl-logo" src="' . PL_BASE_URI . 'assets/images/pl-logo.svg" alt="Post Like Options" />';
							echo '<span class="pl-name">' . $this->name . '</span>';
							echo '<small class="pl-version">' . $this->version . '</small>';
							echo '</h3>';
						echo '</div>';
						$this->settings_api->show_navigation();
						$this->settings_api->show_forms();
					echo '</div>';
				echo '</div>';

				do_action('pl_form_sidebar');
			echo '</div>';
		}

		/**
		 * add sidebar on plugin settings page.
		 */
		function pl_form_sidebar_container() {
			$html = '<div class="pl-sidebar-wrap">';
				$html .= '<div class="pl-sidebar">';
				$html .= '<h3>Thank you for using Post Like</h3>';
				$html .= '<p><a href="https://wordpress.org/support/plugin/post-like/reviews/?filter=5#new-post" target="_blank"><i class="dashicons dashicons-star-filled"></i><i class="dashicons dashicons-star-filled"></i><i class="dashicons dashicons-star-filled"></i><i class="dashicons dashicons-star-filled"></i><i class="dashicons dashicons-star-filled"></i></a></p>';
				$html .= '<p><strong>Thank you!</strong> for using <strong>Post Like</strong> plugin. If you have any probilem please read the documentation or post post in the support forum. We will get back to you quickly.</p>';
				$html .= '<p>If you are happy with our plugin, please leave a <a href="https://wordpress.org/support/plugin/post-like/reviews/?filter=5#new-post">5 star review</a>. This appreciation will help us create more free plugins.</p>';
				$html .= '<p><a href="http://wpthemecraft.com/post-like-free-plugin-documentation/" target="_blank"><i class="dashicons dashicons-external"></i> Plugin Documentation</a></p>';
				$html .= '<p><a href="http://wpthemecraft.com/forums/forum/post-like-free/" target="_blank"><i class="dashicons dashicons-external"></i> Plugin Support Forum</a></p>';
				$html .= '<p><a href="http://wpthemecraft.com/category/post-like-releases/" target="_blank"><i class="dashicons dashicons-external"></i> Plugin Release Notes</a></p>';
				$html .= '</div>';

				//$html .= '<div class="pl-sidebar">';
				//$html .= '<h3>Post Like Pro</h3>';
				//$html .= '</div>';
			$html .= '</div>';

			echo $html;
		}

		/**
		 * Get all the pages
		 * @return array page names with key value pairs
		 */
		function get_pages() {
			$pages         = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ( $pages as $page ) {
					$pages_options[ $page->ID ] = $page->post_title;
				}
			}

			return $pages_options;
		}
	}
endif;
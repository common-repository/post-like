<?php
/**
 * Plugin Name:     Post Like
 * Plugin URI:      http://wpthemecraft.com/plugins/post-like-plugin-for-wordpress/
 * Description:     Post like is a simple ajax based post like/unlike plugin that help your visitors to like posts. Counter shows number of post likes.
 * Version:         1.0.3
 * Author:          Aamer Shahzad
 * Author URI:      http://wpthemecraft.com/
 *
 * @package post-like
 */

/**
 *  block direct access to plugin files
 *  ===================================
 */
defined( 'ABSPATH' ) or die( 'No direct access' );

/**
 *  plugin base paths & URI
 *  ====================================
 */
if ( ! defined( 'PL_BASE_DIR' ) ) {
	define( 'PL_BASE_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'PL_BASE_URI' ) ) {
	define( 'PL_BASE_URI', plugin_dir_url( __FILE__ ) );
}
// include directory path
define( 'PL_INC_DIR', trailingslashit( PL_BASE_DIR . 'inc' ) );
// assets directory uri
define( 'PL_ASSETS_URI', trailingslashit( PL_BASE_URI . 'assets' ) );

/**
 *  define dynamic plugin name & version
 *  ====================================
 */
$pl_data = get_file_data( __FILE__, array( 'Name' => 'Plugin Name', 'Version' => 'Version' ), false );
define ( 'PL_NAME', $pl_data['Name'] );
define ( 'PL_VERSION', $pl_data['Version'] );


/**
 *  plugin translation & texdomain loading
 *  ======================================
 */
function pl_load_text_domain() {
	load_plugin_textdomain( 'post-like', false, PL_BASE_DIR . 'languages' );
}
add_action( 'init', 'pl_load_text_domain' );

/**
 *  run the plugine when loaded
 *  ===========================
 */
function pl_plugin_init() {
	$pl_options = new PL_Options();
	$plugin = new Post_Like();
}
add_action( 'plugins_loaded', 'pl_plugin_init' );

/**
 *  register plugin activation
 *  ==========================
 */
register_activation_hook( __FILE__, array( 'Post_Like', 'pl_install' ) );

/**
 *  include plugin classes & functions
 *  ==================================
 */
include( PL_INC_DIR . 'class-svg-icons.php' );
include( PL_INC_DIR . 'class-post-like.php' );
// plugin options
include( PL_INC_DIR . 'class-pl-settings-api.php' );
include( PL_INC_DIR . 'class-pl-options.php' );
// TODO : most liked posts widget
<?php
/**
 * This magic file is run automatically
 * when the users deletes the plugin.
 */

/**
 *  block direct access to uninstall
 *  ================================
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die( 'No direct access' );
}

/**
 * available plugin options
 * ========================
 */
$options_basic = 'pl_basic';
$options_style = 'pl_style';

/**
 * Removes plugin options
 * ======================
 */
delete_option( $options_basic );
delete_option( $options_style );

/**
 * Removes multisite plugin option
 * ===============================
 */
delete_site_option( $options_basic );
delete_site_option( $options_style );
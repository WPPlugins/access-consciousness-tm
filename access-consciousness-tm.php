<?php
/*
 * Plugin Name: JSM's Access Consciousness Trademarks
 * Text Domain: access-consciousness-tm
 * Domain Path: /languages
 * Plugin URI: https://surniaulula.com/extend/plugins/access-consciousness-tm/
 * Assets URI: https://jsmoriss.github.io/access-consciousness-tm/assets/
 * Author: JS Morisset
 * Author URI: https://surniaulula.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Description: Searches for Access Consciousness&reg; registered trademarks in content, excerpt, titles, etc. and appends the &reg; suffix.
 * Requires At Least: 3.7
 * Tested Up To: 4.8
 * Version: 2.1.3
 * 
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *	{major}		Major structural code changes / re-writes or incompatible API changes.
 *	{minor}		New functionality was added or improved in a backwards-compatible manner.
 *	{bugfix}	Backwards-compatible bug fixes or small improvements.
 *	{stage}.{level}	Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 *
 * Copyright 2012-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

add_action( 'admin_init', 'actm_check_wp_version' );
add_action( 'init', 'actm_add_filters' );

function actm_add_filters() {
	if ( ! is_admin() ) {
		add_filter( 'the_title', 'actm_filter_html' );
		add_filter( 'the_excerpt', 'actm_filter_html' );
		add_filter( 'the_content', 'actm_filter_html' );
		add_filter( 'link_name', 'actm_filter_text' );
		add_filter( 'link_description', 'actm_filter_text' );
	}
}

function actm_check_wp_version() {
	global $wp_version;
	$wp_min_version = 3.7;

	if ( version_compare( $wp_version, $wp_min_version, '<' ) ) {
		$plugin = plugin_basename( __FILE__ );
		if ( is_plugin_active( $plugin ) ) {
			load_plugin_textdomain( 'access-consciousness-tm', false, 'access-consciousness-tm/languages/' );
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				require_once trailingslashit( ABSPATH ).'wp-admin/includes/plugin.php';
			}
			$plugin_data = get_plugin_data( __FILE__, false );	// $markup = false
			deactivate_plugins( $plugin, true );	// $silent = true
			wp_die( 
				'<p>'.sprintf( __( '%1$s requires %2$s version %3$s or higher and has been deactivated.',
					'access-consciousness-tm' ), $plugin_data['Name'], 'WordPress', $wp_min_version ).'</p>'.
				'<p>'.sprintf( __( 'Please upgrade %1$s before trying to re-activate the %2$s plugin.',
					'access-consciousness-tm' ), 'WordPress', $plugin_data['Name'] ).'</p>'
			);
		}
	}
}

function actm_filter_html( $html ) { 
	return actm_replace( $html, '<span class="acreg">&reg;</span>' );
}

function actm_filter_text( $text ) { 
	return actm_replace( $text, '&reg;' );
}

function actm_replace( $text, $char ) {
	$preg_begin = '(^|[^"\'])';
	$preg_reg = '(&reg;|<span class="acreg">&reg;<\/span>)?';
	$preg_end = '([^"\']|$)';

	$pattern = array(
		'/'.$preg_begin.'(Access Consciousness(<\/[aA]>)?)'.$preg_reg.$preg_end.'/',				// english
		'/'.$preg_begin.'((Access B|The B)[aA][rR][sS](<\/[aA]>)?)'.$preg_reg.$preg_end.'/',			// english
		'/'.$preg_begin.'(Barres d(\'|\’|&#039;|&#8217;|&rsquo;)[Aa]ccès(<\/[aA]>)?)'.$preg_reg.$preg_end.'/',	// french
		'/'.$preg_begin.'(Les Barres(<\/[aA]>)?)'.$preg_reg.'('.$preg_end.'?( [^d]))/',				// french
	);

	$replace = array(
		'$1$2'.$char.'$5',
		'$1${3}ars$4'.$char.'$6',	// Bars
		'$1$2'.$char.'$6',
		'$1$2'.$char.'$5',
	);

	ksort( $pattern );
	ksort( $replace );

	$text = preg_replace( $pattern, $replace, $text );

	return $text;
}

?>

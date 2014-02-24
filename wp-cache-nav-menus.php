<?php
/**
 * Plugin Name:       WP Cache Nav Menus
 * Plugin URI:        @TODO
 * Description:       Optimize the speed of WordPress Menus
 * Version:           1.0.0
 * Author:            mharis
 * Author URI:        @TODO
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wp_nav_menu_args', function( $args ) {
	
	$post_id = get_queried_object_id();
	$key = 'menu-cache-' . $args['theme_location'] . '-' . $post_id;
	
	$cache = get_transient( $key );
	if( $cache ) {
		
		$last_updated = get_transient('menu-cache-' . $args['theme_location'] . '-last-updated');
		
		$cache = get_transient( $key );
		if ( !isset($cache['time']) || empty($last_updated) || $last_updated > $cache['time'] ) {
			return $args;
		}
		
		$args = array_merge( $args, array(
			'fallback_cb' => 'this_pro_function',
			'the_cache' => $cache['data']
		) );
	}
	
	return $args;

}, 10, 1 );

function this_pro_function( $args ) { 

        if( $args['echo'] ) {
        	echo $args['the_cache'];
        }
       
        return $args['the_cache'];

}

add_filter( 'wp_nav_menu', function( $nav, $args ) {

	$post_id = get_queried_object_id();

	if( $post_id ) {
		$key = 'menu-cache-' . $args->theme_location;
		$data = array('time' => time(), 'data' => $nav);
		set_transient( $key, $data );
	}
	
	return $nav;

}, 10, 2 );


add_action( 'wp_update_nav_menu', function() {
	
	set_transient('menu-cache-' . $args['theme_location'] . '-last-updated', time());
	
}, 10, 0);

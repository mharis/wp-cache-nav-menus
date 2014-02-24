<?php
/**
 * Plugin Name:       WP Cache Nav Menus
 * Plugin URI:        https://github.com/mharis/wp-cache-nav-menus
 * Description:       Optimize the speed of WordPress Menus
 * Version:           1.0.0
 * Author:            Asad Khan, Muhammad Haris
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/mharis/wp-cache-nav-menus
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
		
		if ( !isset($cache['time']) || empty($last_updated) || $last_updated > $cache['time'] ) {
			return $args;
		}
		
		$args = array_merge( $args, array(
			'fallback_cb' => 'this_pro_function',
			'theme_location' => 'something-unexpected-for-a-menu-name',
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
	$last_updated = get_transient('menu-cache-' . $args->theme_location . '-last-updated');

	if( ! $last_updated ) {
		set_transient('menu-cache-' . $args->theme_location . '-last-updated', time());
	}

	if( $post_id ) {
		$key = 'menu-cache-' . $args->theme_location . '-' . $post_id;
		$data = array('time' => time(), 'data' => $nav);
		set_transient( $key, $data );
	}
	
	return $nav;

}, 10, 2 );


add_action( 'wp_update_nav_menu', function($menu_id) {
	$locations = array_flip(get_nav_menu_locations());
	
	if( isset($locations[$menu_id]) ) {
	
		set_transient('menu-cache-' . $locations[$menu_id] . '-last-updated', time());
		
	}
	
}, 10, 1);

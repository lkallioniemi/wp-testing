<?php
/*
Plugin name: Batcache Manager
Plugin URI: http://wordpress.org/extend/plugins/batcache/
Description: This optional plugin improves Batcache. Modified by Frantic to support SSL admin.
Author: Andy Skelton
Author URI: http://andyskelton.com/
Version: 1.2.1
*/

// Do not load if our advanced-cache.php isn't loaded
if ( ! is_object($batcache) || ! method_exists( $wp_object_cache, 'incr' ) )
	return;

$batcache->configure_groups();

// Regen home and permalink on posts and pages
add_action('clean_post_cache', 'batcache_post');

// Regen permalink on comments (TODO)
//add_action('comment_post',          'batcache_comment');
//add_action('wp_set_comment_status', 'batcache_comment');
//add_action('edit_comment',          'batcache_comment');

function batcache_post($post_id) {
	global $batcache;

	$post = get_post($post_id);
	if ( $post->post_type == 'revision' || get_post_status($post_id) != 'publish' )
		return;

	$home_url = get_option('home');
	$home_url_trailing_slash = trailingslashit( get_option('home') );
	$permalink = get_permalink($post_id);

	batcache_clear_url( $home_url );
	batcache_clear_url( $home_url_trailing_slash );
	batcache_clear_url( $permalink );

	if ($batcache->is_ssl()) {
		// If the site uses SSL, for some reason the pages get stored to MemCachier as http.
		// This means that the we need to replace SSL URL's with normal http in order for the
		// caches to clear properly.
		batcache_clear_url( str_replace('https://', 'http://', $home_url) );
		batcache_clear_url( str_replace('https://', 'http://', $home_url_trailing_slash) );
		batcache_clear_url( str_replace('https://', 'http://', $permalink) );
	}
}

function batcache_clear_url($url) {
	global $batcache;
	if ( empty($url) )
		return false;
	$url_key = md5($url);
	wp_cache_add("{$url_key}_version", 0, $batcache->group);
	return wp_cache_incr("{$url_key}_version", 1, $batcache->group);
}


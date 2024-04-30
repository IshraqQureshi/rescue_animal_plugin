<?php
/**
 * Plugin Name: Rescue Animals Custom Plugin
 * Description: A plugin to create a custom post type for Rescue Animals with a custom taxonomy and custom meta box.
 * Version: 1.0
 * Author: Ishraq Qureshi
 */

// Ensure this file is not accessed directly
if (!defined('ABSPATH')) {
	exit;
}

require_once('classes/RescueAnimalsPostType.php'); 
require_once('classes/EnqueueAssets.php');

// Instantiate the class to trigger the hooks
new RescueAnimalsPostType();

// Instantiate the class to enqueue frontend and admin assets
new EnqueueAssets();

function set_archive_template($archive_template) {
	global $post;   
	if ( is_post_type_archive ( 'rescue_animals' ) ) {
		$archive_template = dirname( __FILE__ ) . '/templates/archives-rescue_animals.php';
	}
	return $archive_template;
}

function set_single_template( $single_template ) {    
	global $post;
	if ( 'rescue_animals' === $post->post_type ) {
			$single_template = dirname( __FILE__ ) . '/templates/single-rescue_animals.php';
	}
	return $single_template;
}

add_filter( 'archive_template', 'set_archive_template' ) ;
// add_filter( 'single_template', 'set_single_template' ) ;

?>

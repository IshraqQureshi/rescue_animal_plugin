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
add_filter( 'single_template', 'set_single_template' ) ;


define('STRIPE_PUBLISHABLE_KEY', 'pk_test_BFzpn9CIWXtPGBkY6TUvMTGP002jKKf99Z');
define('STRIPE_SECRET_KEY', 'sk_test_wnIUPYx0o6a1q4d0SUadZxzN00Hdafg2pI');

require_once 'vendor/autoload.php';
require_once 'vendor/stripe/stripe-php/init.php';

function create_stripe_checkout_session() {
	// if (!current_user_can('edit_posts')) {
	// 		wp_send_json_error('Not authorized');
	// 		return;
	// }		
	// Require Stripe libraries	


	\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
	
	// Get the donation amount from the AJAX request
	$data = json_decode(file_get_contents('php://input'), true);
	$donationAmount = $data['amount'];

	// Create a new Stripe Checkout session
	$session = \Stripe\Checkout\Session::create([
			'payment_method_types' => ['card'],
			'line_items' => [
					[
							'price_data' => [
									'currency' => 'usd', // Change as needed
									'product_data' => [
											'name' => 'Donation',
									],
									'unit_amount' => $donationAmount * 100, // Stripe expects amounts in cents
							],
							'quantity' => 1,
					],
			],
			'mode' => 'payment',
			'success_url' => home_url('/donation-success/'), // Adjust success and failure URLs
			'cancel_url' => home_url('/donation-failure/'),
	]);

	wp_send_json_success(['id' => $session->id]); // Return the session ID
}

add_action('wp_ajax_create_stripe_checkout_session', 'create_stripe_checkout_session');
add_action('wp_ajax_nopriv_create_stripe_checkout_session', 'create_stripe_checkout_session');

function create_stripe_subscription_session() {
	// if (!current_user_can('edit_posts')) {
	// 		wp_send_json_error('Not authorized');
	// 		return;
	// }

	\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

	$data = json_decode(file_get_contents('php://input'), true);
	$donationAmount = $data['amount'];

	// Create a recurring Stripe Checkout session (Subscription)
	// Replace 'your-price-id' with the Price ID from Stripe for your recurring donation
	$session = \Stripe\Checkout\Session::create([
			'payment_method_types' => ['card'],
			'line_items' => [
					[
							'price' => 'price_1PBkiMFFQETLxGYXGEH4W4DG', // This should be the Stripe Price ID for your subscription
							'quantity' => 1,
					],
			],
			'mode' => 'subscription',
			'success_url' => home_url('/subscription-success/'),
			'cancel_url' => home_url('/subscription-failure/'),
	]);

	// var_dump($session->id);die;

	wp_send_json_success(['id' => $session->id]);
}

add_action('wp_ajax_create_stripe_subscription_session', 'create_stripe_subscription_session');
add_action('wp_ajax_nopriv_create_stripe_subscription_session', 'create_stripe_subscription_session');

?>

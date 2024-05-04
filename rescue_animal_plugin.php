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
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $amount = $_POST['amount'];

    try {
        $charge = \Stripe\Charge::create([
            'amount' => $amount * 100, // Stripe expects the amount in cents
            'currency' => 'usd',
            'source' => $token,
            'description' => 'Custom Payment',
        ]);

				$amount = $charge->amount / 100;
				$donation_date = current_time('mysql');
				$user_id = get_current_user_id();
				$status = 'completed';
				$stripe_response = json_encode($charge);

				 // Insert data into the donation table
				 global $wpdb;
				 $table_name = $wpdb->prefix . 'rescue_animal_donation';
 
				 $wpdb->insert(
						 $table_name,
						 array(
								 'post_id' => $_GET['post_id'],
								 'amount' => $amount,
								 'donation_date' => $donation_date,
								 'user_id' => $user_id,
								 'status' => $status,
								 'stripe_response' => $stripe_response,
						 ),
						 array(
								 '%d',  // post_id
								 '%f',  // amount
								 '%s',  // donation_date
								 '%d',  // user_id
								 '%s',  // status
								 '%s'   // stripe_response
						 )
				 );
        http_response_code(200);
        wp_send_json_success([
					"status" => true,
					"message" => "Payment Successfully"
				]);
    } catch (\Stripe\Exception\CardException $e) {
        http_response_code(400);
				wp_send_json_success([
					"status" => false,
					"message" => "Payment failed: " . $e->getError()->message
				]);
    } catch (Exception $e) {
        http_response_code(500);
        wp_send_json_success([
					"status" => false,
					"message" => 'An error occurred. Please try again later.'
				]);
    }
	}
}

add_action('wp_ajax_create_stripe_checkout_session', 'create_stripe_checkout_session');
add_action('wp_ajax_nopriv_create_stripe_checkout_session', 'create_stripe_checkout_session');

// Hook to activate the plugin
register_activation_hook(__FILE__, 'create_rescue_animal_donation_table');

// Function to create the table
function create_rescue_animal_donation_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rescue_animal_donation';

    // SQL query to create the table
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        post_id INT(11),
        amount DECIMAL(10, 2),
        donation_date DATETIME,
        user_id INT(11),
        status VARCHAR(255),
        stripe_response TEXT,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);  // Execute the table creation
    }
}


?>

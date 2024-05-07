
<?php
/**
 * Plugin Name: Rescue Animals Custom Plugin
 * Description: A plugin to create a custom post type for Rescue Animals with a custom taxonomy and custom meta box.
 * Version: 1.0
 * Author: Webs Aura
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

				$exclusive_content = get_post_meta($_GET['post_id'], '_exclusive_content', true); // Adjust custom field name
				 $html = "";
				if (is_array($exclusive_content) && count($exclusive_content) > 0) {
					$table_name = $wpdb->prefix . 'rescue_animal_donation';
					$user_id = get_current_user_id();
					$post_id = $_GET['post_id'];
					$status = "completed";

					$results = $wpdb->get_results($wpdb->prepare(
						"SELECT * FROM $table_name WHERE user_id = %d AND post_id = %d AND status = %s LIMIT 1",
						$user_id,
						$post_id,
						$status
					), ARRAY_A);  // ARRAY_A for associative array results

					if (count($results) > 0){
						$html .= '<div class="parent_paid_box">';
						foreach ($exclusive_content as $key => $content){
							$date1 = new DateTime($content['date']);
							$date2 = new DateTime($results[0]['donation_date']);
							if ($date1 <= $date2){
								$post_permalink = get_permalink($post_id);
								$query_args = array(
									'social_share' => true,
									'user_id' => $user_id,
									'exclusive_content' => $key
								);
								$url_with_query = add_query_arg($query_args, $post_permalink);
								$html .= '<div class="paid_cart">';
									$html .= '<div class="paidImage">';
										$html .= wp_get_attachment_image($content["image_id"]);
									$html .= '</div>';
									$html .= '<div class="paidContent">';
										$html .= '<p>'. $content["content"] .'</p>';
										$html .= '<div style="position=relative">';
											$html .= '<a href="https://www.facebook.com/sharer/sharer.php?u=' . $url_with_query .'" target="_blank">';
												$html .= 'Share on Facebook';
											$html .= '</a>';
											$html .= '<a href="https://twitter.com/intent/tweet?url=' . $url_with_query .'" target="_blank">';
												$html .= 'Share on Twitter';
											$html .= '</a>';
											$html .= '<div class="tooltip" style="display: none;position: absolute;background: #333;color: #fff;padding: 5px;border-radius: 3px;font-size: 12px;">Link Copied</div>';
										$html .= '</div>';
									$html .= '</div>';
								$html .= '</div>';
							}
						}
						$html .= '</div>';
					}
				}

        wp_send_json_success([
					"status" => true,
					"message" => "Payment Successfully",
					"html" => $html
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

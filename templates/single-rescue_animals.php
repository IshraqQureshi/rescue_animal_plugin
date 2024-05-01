<?php

get_header(); // Load the WordPress header


if ( have_posts() ) :
    while ( have_posts() ) : the_post(); // Start the loop
        // Output the page title
        echo '<h1>' . get_the_title() . '</h1>';

        // Output the page content
        the_content();

        // Get and output the custom field value for "resident_type"
        $resident_type = get_post_meta( get_the_ID(), '_resident_type', true ); // Adjust custom field name
        if ( !empty($resident_type) ) {
            echo '<p><strong>Resident Type:</strong> ' . esc_html($resident_type) . '</p>';
        }
        ?>
        <form id="donation-form" action="" method="post">
            <label for="donation-amount">Donation Amount (USD)</label>
            <input type="number" name="donation_amount" id="donation-amount" min="1" required>

            <label for="donation-type">Donation Type</label>
            <select name="donation_type" id="donation-type">
                <option value="one-time">One-time</option>
                <option value="recurring">Recurring</option>
            </select>
            
            <button type="button" id="checkout-button">Donate</button>
        </form>
        
        <!-- Include Stripe JS -->
        <script src="https://js.stripe.com/v3/"></script>
        <script>
        (function() {
            // Initialize Stripe
            var stripe = Stripe("pk_test_BFzpn9CIWXtPGBkY6TUvMTGP002jKKf99Z");
            
            jQuery('#checkout-button').click(function(e){
              
              e.preventDefault();
              var donationAmount = document.getElementById('donation-amount').value;
                var donationType = document.getElementById('donation-type').value;

                // Determine which Stripe session to create (one-time or recurring)
                var ajaxAction = donationType === 'one-time' 
                    ? 'create_stripe_checkout_session' 
                    : 'create_stripe_subscription_session';

                fetch('/wp-admin/admin-ajax.php?action=' + ajaxAction, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ amount: donationAmount })
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(session) {
                  console.log(session);
                    stripe.redirectToCheckout({ sessionId: session.data.id });
                })
                .catch(function(error) {
                    console.error('Error:', error);
                });
            })
        })();
        </script>
    <?php
    endwhile;
else:
    echo '<p>No content found.</p>';
endif;

get_footer(); // Load the WordPress footer
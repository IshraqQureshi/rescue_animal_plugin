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


        $exclusive_content = get_post_meta( get_the_ID(), '_exclusive_content', true ); // Adjust custom field name
        
        if(is_array($exclusive_content) && count($exclusive_content) > 0) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rescue_animal_donation';

            $user_id = get_current_user_id();
            $post_id = get_the_ID();
            $status = "completed";
            // Fetch records where user_id is 1, post_id is 11, and status is "completed"
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND post_id = %d AND status = %s LIMIT 1",
                $user_id, $post_id, $status
            ), ARRAY_A);  // ARRAY_A for associative array results

    if(count($results) > 0):            
            foreach($exclusive_content as $content) {                
                $date1 = new DateTime($content['date']);
                $date2 = new DateTime($results[0]['donation_date']);

                if($date1 <= $date2): ?>
                    <?php echo wp_get_attachment_image($content['image_id']) ?>
                    <p><?php echo $content['content'] ?></p>
                <?php endif;
            }            
        
    endif;
}

        ?>
        <?php if(is_user_logged_in()): ?>

            <form id="payment-form">
                <label for="amount">Amount (USD):</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0" required>

                <div id="card-element"></div>
                <div id="card-errors" role="alert"></div>

                <button type="submit">Pay</button>
            </form>
        
        <?php endif; ?>
        
        <!-- Include Stripe JS -->
        <script src="https://js.stripe.com/v3/"></script>


            <script>
                var stripe = Stripe('pk_test_BFzpn9CIWXtPGBkY6TUvMTGP002jKKf99Z');
                var elements = stripe.elements();

                var card = elements.create('card');
                card.mount('#card-element');

                card.on('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
                });

                var form = document.getElementById('payment-form');
                form.addEventListener('submit', function(event) {
                event.preventDefault();

                const currentPostID = "<?php echo get_the_ID(); ?>";

                stripe.createToken(card).then(function(result) {
                    if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                    } else {
                    // Send the token and other form data to the server for processing
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", `/wp-admin/admin-ajax.php?action=create_stripe_checkout_session&post_id=${currentPostID}`, true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                    xhr.onreadystatechange = function () {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                        console.log(xhr.responseText);
                        // Handle successful payment
                        alert("Payment successful!");
                        } else if (xhr.readyState == 4) {
                        console.error(xhr.responseText);
                        alert("Payment failed. Please try again.");
                        }
                    };

                    var amount = document.getElementById("amount").value;

                    xhr.send("token=" + encodeURIComponent(result.token.id) + "&amount=" + encodeURIComponent(amount));
                    }
                });
                });
            </script>        
    <?php
    endwhile;
else:
    echo '<p>No content found.</p>';
endif;

get_footer(); // Load the WordPress footer
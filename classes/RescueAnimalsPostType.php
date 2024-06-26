<?php

class RescueAnimalsPostType {
  // Constructor to hook into WordPress actions
  public function __construct() {
      global $post;
      // Register custom post type and taxonomy
      add_action('init', array($this, 'register_rescue_animals_cpt'));
      add_action('init', array($this, 'register_animal_types_taxonomy'));

      // Add meta boxes
      add_action('add_meta_boxes', array($this, 'add_post_type_meta_box'));
      add_action('save_post', array($this, 'save_post_type_meta_box'));      
  }

  // Register the custom post type
  public function register_rescue_animals_cpt() {
      $args = array(
          'label' => 'Rescue Animals',
          'public' => true,
          'has_archive' => true,
          'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
          'show_in_rest' => true, // for Gutenberg editor compatibility
      );

      register_post_type('rescue_animals', $args);
  }

  // Register the custom taxonomy
  public function register_animal_types_taxonomy() {
      $args = array(
          'labels' => array(
              'name' => 'Animal Types',
              'singular_name' => 'Animal Type',
          ),
          'public' => true,
          'hierarchical' => true, // Enables category-style taxonomy
      );

      register_taxonomy('animal_types', 'rescue_animals', $args);
  }

  // Add a custom meta box for Custom Post Type
  public function add_post_type_meta_box() {
      add_meta_box(
          'resident_type_meta_box', // ID
          'Resident Type', // Title
          array($this, 'resident_type_meta_box_callback'), // Callback
          'rescue_animals', // Post type
          'side', // Context
          'high' // Priority
      );

      add_meta_box(
        'exclusive_content_meta_box', // ID
        'Exclusive Content', // Title
        array($this, 'exclusive_content_meta_box_callback'), // Callback
        'rescue_animals', // Post type
        'normal', // Context
        'high' // Priority
    );
  }

  // Callback to render the meta box
  public function resident_type_meta_box_callback($post) {
      // Get the current value of the meta field
      $resident_type = get_post_meta($post->ID, '_resident_type', true);

      // Use a nonce for security
      wp_nonce_field('save_post_type_meta_box', 'resident_type_nonce');

      echo '<label for="resident_type">Resident Type: </label>';
      echo '<select name="resident_type"><option value="permanent"'. selected($resident_type, 'permanent', false)  .'>Permanent</option><option value="adoption" '. selected($resident_type, 'adoption', false) .'>Available for adoption</option></select>';
  }

  public function exclusive_content_meta_box_callback($post) {
    $exclusive_content = get_post_meta($post->ID, '_exclusive_content', true);

    // Use a nonce for security
    wp_nonce_field('save_post_type_meta_box', 'exclusive_content_nonce');

    echo '<div id="repeater-container">';
      if ($exclusive_content && is_array($exclusive_content)) {
          foreach ($exclusive_content as $key => $data) {
              $this->render_repeater_item($key, $data);
          }
      } else {
          // Render one empty repeater item initially
          $this->render_repeater_item(0, null);
      }
      echo '</div>';

      // Button to add more items
      echo '<button type="button" id="add-repeater-item" class="button">Add More</button>';

      // JavaScript to handle repeater functionality
      $this->enqueue_repeater_script();      
  }

  // Enqueue the JavaScript for the repeater functionality
  private function enqueue_repeater_script() {
    // Inline script to add repeater functionality
    echo '<script>
        (function($){
            $("#add-repeater-item").on("click", function(){

                const newItem = $(".repeater-item:first").clone();
                const newIndex = index = $("#repeater-container > .repeater-item").length;
                
                newItem.attr("data-index", newIndex);

                inputs = newItem[0].querySelectorAll("input, textarea, select");
                newItem.find("input, textarea").val("");
                newItem.find(".remove-repeater-item").show();
                newItem.find("textarea").html("");

                inputs.forEach(input => {
                    const name = input.getAttribute("name");
                    if (name) {
                        const updatedName = name.replace(/\[\d+\]/, `[${newIndex}]`);
                        input.setAttribute("name", updatedName);
                    }
                });

                $("#repeater-container").append(newItem);            

            });

            $(document).on("click", ".remove-repeater-item", function(){
                if ($("#repeater-container > .repeater-item").length > 1) {
                    $(this).closest(".repeater-item").remove(); // Remove item
                }
            });

            $(".upload-image-button").on("click", function(e) {
                e.preventDefault();
        
                // Store a reference to the current button
                var button = $(this);
        
                // Create a new media frame for the image uploader
                var frame = wp.media({
                    title: "Select or Upload an Image",
                    button: {
                        text: "Use this image"
                    },
                    multiple: false // Single image upload
                });
        
                // When an image is selected in the media uploader
                frame.on("select", function() {
                    var attachment = frame.state().get("selection").first().toJSON();
        
                    // Example: Store the attachment ID in a hidden field
                    button.siblings("input[type=hidden]").val(attachment.id);
        
                    // Optionally display a preview of the image
                    var imagePreview = button.siblings(".image-preview");
                    imagePreview.html(`<img src="${attachment.url}" style="max-width: 100px;">`);
                });
        
                // Open the media frame
                frame.open();
            });

        })(jQuery);
    </script>';
  }

  // Render a single repeater item
  private function render_repeater_item($index, $data) {
    $image_id = isset($data['image_id']) ? $data['image_id'] : '';
    $content = isset($data['content']) ? $data['content'] : '';
    $date = isset($data['date']) ? $data['date'] : '';

    echo '<div class="repeater-item" data-index="' . esc_attr($index) . '">';
    echo '<div style="margin-bottom: 10px;">';
        echo '<label>Image: </label>';
        echo '<button class="button upload-image-button">Upload Image</button>';
        echo '<input type="hidden" name="exclusive_content[' . $index . '][image_id]" value="' . esc_attr($image_id) . '" />';
        echo '<div class="image-preview" style="margin-top: 10px;">' . ($image_id ? wp_get_attachment_image($image_id) : '') . '</div>';
    echo '</div>';

    echo '<div>';
        echo '<label>Content: </label>';
        echo '<textarea name="exclusive_content[' . $index . '][content]" class="large-text">' . esc_textarea($content) . '</textarea>';
    echo '</div>';

    echo '<div>';
        echo '<label>Date: </label>';
        echo '<input name="exclusive_content[' . $index . '][date]" type="date" value="'. esc_textarea($date) .'">';
    echo '</div>';

    echo '<button type="button" class="button remove-repeater-item" style="margin-top: 10px;">Remove</button>';
    echo '</div>';
  }

  // Save the meta box data
  public function save_post_type_meta_box($post_id) {
      // Check if the nonce is valid
      if (
        (!isset($_POST['resident_type_nonce']) ||
        !wp_verify_nonce($_POST['resident_type_nonce'], 'save_post_type_meta_box')) &&
        !isset($_POST['exclusive_content_nonce']) ||
        !wp_verify_nonce($_POST['exclusive_content_nonce'], 'save_post_type_meta_box') 
      ) {
          return;
      }

      // Check if the current user has permission to edit the post
      if (!current_user_can('edit_post', $post_id)) {
          return;
      }

      // Update the meta field
      if (isset($_POST['resident_type'])) {
          update_post_meta($post_id, '_resident_type', sanitize_text_field($_POST['resident_type']));
      }

      // Update the meta field with sanitized data
      if (isset($_POST['exclusive_content']) && is_array($_POST['exclusive_content'])) {
        $sanitized_data = array();
        foreach ($_POST['exclusive_content'] as $index => $data) {
            $sanitized_data[$index] = array(
                'image_id' => isset($data['image_id']) ? sanitize_text_field($data['image_id']) : '',
                'content' => isset($data['content']) ? sanitize_textarea_field($data['content']) : '',
                'date' => isset($data['date']) ? sanitize_textarea_field($data['date']) : '',
            );
        }

        update_post_meta($post_id, '_exclusive_content', $sanitized_data);
      }
  }  
}
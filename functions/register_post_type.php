<?php

/*
* Creating a function to create our CPT
*/
  
function register_custom_post_type() {
  
  // Set UI labels for Custom Post Type
      $labels = array(
          'name'                => _x( 'Rescue Animals', 'Post Type General Name', 'twentytwentyfour' ),
          'singular_name'       => _x( 'Rescue Animal', 'Post Type Singular Name', 'twentytwentyfour' ),
          'menu_name'           => __( 'Rescue Animals', 'twentytwentyfour' ),
          'parent_item_colon'   => __( 'Parent Rescue Animal', 'twentytwentyfour' ),
          'all_items'           => __( 'All Rescue Animals', 'twentytwentyfour' ),
          'view_item'           => __( 'View Rescue Animal', 'twentytwentyfour' ),
          'add_new_item'        => __( 'Add New Rescue Animal', 'twentytwentyfour' ),
          'add_new'             => __( 'Add New', 'twentytwentyfour' ),
          'edit_item'           => __( 'Edit Rescue Animal', 'twentytwentyfour' ),
          'update_item'         => __( 'Update Rescue Animal', 'twentytwentyfour' ),
          'search_items'        => __( 'Search Rescue Animal', 'twentytwentyfour' ),
          'not_found'           => __( 'Not Found', 'twentytwentyfour' ),
          'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwentyfour' ),
      );
        
  // Set other options for Custom Post Type
        
      $args = array(
          'label'               => __( 'rescue-animal', 'twentytwentyfour' ),
          'description'         => __( 'Register your permanent and rescued animals', 'twentytwentyfour' ),
          'labels'              => $labels,
          // Features this CPT supports in Post Editor
          'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail' ),
          /* A hierarchical CPT is like Pages and can have
          * Parent and child items. A non-hierarchical CPT
          * is like Posts.
          */
          'hierarchical'        => false,
          'public'              => true,
          'show_ui'             => true,
          'show_in_menu'        => true,
          'show_in_nav_menus'   => true,
          'show_in_admin_bar'   => true,
          'menu_position'       => 5,
          'can_export'          => true,
          'has_archive'         => true,
          'exclude_from_search' => false,
          'publicly_queryable'  => true,
          'capability_type'     => 'post',
          'show_in_rest' => true,
    
      );
        
      // Registering your Custom Post Type
      register_post_type( 'rescue-animal', $args );
    
  }
    
  /* Hook into the 'init' action so that the function
  * Containing our post type registration is not 
  * unnecessarily executed. 
  */
    
  add_action( 'init', 'register_custom_post_type', 0 );
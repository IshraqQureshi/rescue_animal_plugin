<?php
get_header();

// Pagination setup
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Get the selected filter values from the query parameters
$resident_type_filter = isset($_GET['resident_type']) ? sanitize_text_field($_GET['resident_type']) : '';
$order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : '';

// Query arguments for custom post type
$args = array(
    'post_type' => 'rescue_animals',
    'paged' => $paged,
    'meta_query' => array(),
    'orderby' => 'date',
    'order' => $order_by,
);

// Add meta query for resident_type if filter is applied
if ($resident_type_filter) {
    $args['meta_query'][] = array(
        'key' => '_resident_type',
        'value' => $resident_type_filter,
        'compare' => '=',
    );
}

// WP_Query to fetch rescue animals
$query = new WP_Query($args);

if ($query->have_posts()) :
    echo '<div class="rescue-animals">';

    // Display a filter form
    echo '<form method="get" class="filter-form">';
    echo 'Filter by Resident Type: ';
    echo '<input name="post_type" value="rescue_animals" type="hidden">';
    echo '<select name="resident_type">';
    echo '<option value="">All</option>';
    echo '<option value="permanent"' . selected($resident_type_filter, 'permanent', false) . '>Permanent</option>';
    echo '<option value="adoption"' . selected($resident_type_filter, 'adoption', false) . '>Available for adoption</option>';
    echo '</select>';
    echo ' Order by: ';
    echo '<select name="order_by">';
    echo '<option value="DESC"' . selected($order_by, 'DESC', false) . '>Recently Added</option>';
    echo '<option value="ASC"' . selected($order_by, 'ASC', false) . '>Older</option>';
    echo '</select>';
    echo '<button type="submit">Apply</button>';
    echo '</form>';

    // Loop through the posts and display them
    while ($query->have_posts()) :
        $query->the_post();
        // Display post content
        the_title('<h2>', '</h2>');
        the_content();
    endwhile;

    // Pagination
    echo paginate_links(array(
        'total' => $query->max_num_pages,
        'current' => $paged,
    ));

    echo '</div>'; // End of rescue-animals div
else :
    echo '<p>No rescue animals found.</p>';
endif;

// Reset post data
wp_reset_postdata();

get_footer();

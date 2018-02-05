<?php
/**
 * Admin Custom Post Type.
 *
 * @link       	http://hdplus.es
 * @since      	1.0.0
 *
 * @package    	CSL
 * @subpackage 	csl/ACPT
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Register Queries Post Type.
 *
 * @since	1.0.0
 */
function csl_acpt_register_post_type() {

	$ptlabels = array(
		'name'                => __( 'Queries', CSL_TEXT_DOMAIN_PREFIX ),
		'singular_name'       => __( 'Query', CSL_TEXT_DOMAIN_PREFIX ),
		'menu_name'           => __( 'Queries', CSL_TEXT_DOMAIN_PREFIX ),
		'name_admin_bar'      => __( 'Query', CSL_TEXT_DOMAIN_PREFIX ),
		'parent_item_colon'   => __( 'Parent query', CSL_TEXT_DOMAIN_PREFIX ),
		'all_items'           => __( 'All queries', CSL_TEXT_DOMAIN_PREFIX ),
		'add_new_item'        => __( 'Add new query', CSL_TEXT_DOMAIN_PREFIX ),
		'add_new'             => __( 'Add new query', CSL_TEXT_DOMAIN_PREFIX ),
		'new_item'            => __( 'New query', CSL_TEXT_DOMAIN_PREFIX ),
		'edit_item'           => __( 'Edit query', CSL_TEXT_DOMAIN_PREFIX ),
		'update_item'         => __( 'Update query', CSL_TEXT_DOMAIN_PREFIX ),
		'view_item'           => __( 'View query', CSL_TEXT_DOMAIN_PREFIX ),
		'search_items'        => __( 'Search queries', CSL_TEXT_DOMAIN_PREFIX ),
		'not_found'           => __( 'No queries found', CSL_TEXT_DOMAIN_PREFIX ),
		'not_found_in_trash'  => __( 'No queriest found in trash', CSL_TEXT_DOMAIN_PREFIX ),
	);
	$ptargs = array(
		'label'               => __( 'csl_acpt_query', CSL_TEXT_DOMAIN_PREFIX ),
		'description'         => __( 'Queries', CSL_TEXT_DOMAIN_PREFIX ),
		'labels'              => $ptlabels,
		'supports'            => array( 'title', 'editor', 'excerpt' ),
		'taxonomies'          => array( 'csl_acpt_query_type' ),
		'public'              => true,
		'hierarchical'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-chart-area',
		'has_archive'         => 'queries',
	    'rewrite' 			  => array( 'slug' =>'query', 'with_front' => false ),
        'menu_position'       => 25,
        'capabilities' => array(
            'edit_post'          => 'update_core',
            'read_post'          => 'update_core',
            'delete_post'        => 'update_core',
            'edit_posts'         => 'update_core',
            'edit_others_posts'  => 'update_core',
            'delete_posts'       => 'update_core',
            'publish_posts'      => 'update_core',
            'read_private_posts' => 'update_core',
        ),
	);
	register_post_type( 'csl_acpt_query', $ptargs );

}
add_action( 'init', 'csl_acpt_register_post_type' );


/**
 * Register Queries Taxonomies.
 *
 * @since	1.0.0
 */
function csl_acpt_register_taxonomies() {

	$args = array(
		'hierarchical'               => true,
		'show_admin_column'          => true,
		'show_tagcloud'              => false,
	    'rewrite' 			         => array( 'slug' =>'queries', 'with_front' => true, 'hierarchical' => true ),
	);

	// Register categories for dictionary
	$catlabels = array(
		'name'                       => __( 'Query types', CSL_TEXT_DOMAIN_PREFIX ),
		'singular_name'              => __( 'Query type', CSL_TEXT_DOMAIN_PREFIX ),
		'menu_name'                  => __( 'Type', CSL_TEXT_DOMAIN_PREFIX ),
	);
	$args['labels'] = $catlabels;

	register_taxonomy( 'csl_acpt_query_type', array( 'csl_acpt_query' ), $args );

}
add_action( 'init', 'csl_acpt_register_taxonomies' );

function csl_acpt_title_like_posts_where( $where, &$wp_query ) {
    global $wpdb;
    
    if ( $post_title_like = $wp_query->get( 'post_title_like' ) ) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'' . esc_sql( $wpdb->esc_like( $post_title_like ) ) . '%\'';
    }
    return $where;
}
add_filter( 'posts_where', 'csl_acpt_title_like_posts_where', 10, 2 );

/**
 * Register Dictionary Meta Box.
 *
 * @since	1.0.0
 */
function csl_acpt_post_meta_box_setup() {
    add_action( 'add_meta_boxes', 'csl_acpt_add_post_meta_box' );
    add_action( 'save_post', 'csl_acpt_save_metadata', 10, 2 );
}
add_action( 'load-post.php', 'csl_acpt_post_meta_box_setup' );
add_action( 'load-post-new.php', 'csl_acpt_post_meta_box_setup' );

function csl_acpt_add_post_meta_box() {
    add_meta_box(
        'csl_acpt_query_metadata',                          // Unique ID
        esc_html__( 'Metadata', CSL_TEXT_DOMAIN_PREFIX ),   // Title
        'csl_acpt_query_metadata_meta_box',                 // Callback function
        'csl_acpt_query',                                   // Admin page (or post type)
        'advanced',                                         // Context
        'default'                                           // Priority
    );
}

function csl_acpt_query_metadata_meta_box( $object, $box ) {
    wp_nonce_field( basename( __FILE__ ), 'csl_acpt_metadata_nonce' );
    echo '<p>' . PHP_EOL;
    echo '<label for="csl_acpt_column_labels">' . __( 'Enter the custom query column labels.', CSL_TEXT_DOMAIN_PREFIX ) . '</label>' . PHP_EOL;
    echo '<br />' . PHP_EOL;
    echo '<input class="widefat" type="text" name="csl_acpt_column_labels" id="csl_acpt_column_labels" value="' . esc_attr( get_post_meta( $object->ID, 'csl_acpt_column_labels', true ) ) . '" />' . PHP_EOL;
    echo '</p>' . PHP_EOL;
    echo '<p class="description">' . PHP_EOL;
    echo __( 'Separate column labels using commas (,).', CSL_TEXT_DOMAIN_PREFIX ) . PHP_EOL;    
    echo '</p>' . PHP_EOL;
    
    echo '<p>' . PHP_EOL;
    echo '<label for="csl_acpt_column_ids">' . __( 'Enter the custom query column id\'s.', CSL_TEXT_DOMAIN_PREFIX ) . '</label>' . PHP_EOL;
    echo '<br />' . PHP_EOL;
    echo '<input class="widefat" type="text" name="csl_acpt_column_ids" id="csl_acpt_column_ids" value="' . esc_attr( get_post_meta( $object->ID, 'csl_acpt_column_ids', true ) ) . '" />' . PHP_EOL;
    echo '</p>' . PHP_EOL;
    echo '<p class="description">' . PHP_EOL;
    echo __( 'Separate column id\'s using commas (,).', CSL_TEXT_DOMAIN_PREFIX ) . PHP_EOL;    
    echo '</p>' . PHP_EOL;

    echo '<p>' . PHP_EOL;
    echo '<label for="csl_acpt_column_chart_descriptions">' . __( 'Enter the custom query columns used as description for chart.', CSL_TEXT_DOMAIN_PREFIX ) . '</label>' . PHP_EOL;
    echo '<br />' . PHP_EOL;
    echo '<input class="widefat" type="text" name="csl_acpt_column_chart_descriptions" id="csl_acpt_column_chart_descriptions" value="' . esc_attr( get_post_meta( $object->ID, 'csl_acpt_column_chart_descriptions', true ) ) . '" />' . PHP_EOL;
    echo '</p>' . PHP_EOL;
    echo '<p class="description">' . PHP_EOL;
    echo __( 'Separate column id\'s using commas (,).', CSL_TEXT_DOMAIN_PREFIX ) . PHP_EOL;    
    echo '</p>' . PHP_EOL;

    echo '<p>' . PHP_EOL;
    echo '<label for="csl_acpt_column_chart_values">' . __( 'Enter the custom query columns used as values for chart.', CSL_TEXT_DOMAIN_PREFIX ) . '</label>' . PHP_EOL;
    echo '<br />' . PHP_EOL;
    echo '<input class="widefat" type="text" name="csl_acpt_column_chart_values" id="csl_acpt_column_chart_values" value="' . esc_attr( get_post_meta( $object->ID, 'csl_acpt_column_chart_values', true ) ) . '" />' . PHP_EOL;
    echo '</p>' . PHP_EOL;
    echo '<p class="description">' . PHP_EOL;
    echo __( 'Separate column id\'s using commas (,).', CSL_TEXT_DOMAIN_PREFIX ) . PHP_EOL;    
    echo '</p>' . PHP_EOL;

    echo '<p>' . PHP_EOL;
    echo '<label for="csl_acpt_column_chart_type">' . __( 'Enter the type of chart.', CSL_TEXT_DOMAIN_PREFIX ) . '</label>' . PHP_EOL;
    echo '<br />' . PHP_EOL;
    echo '<input class="widefat" type="text" name="csl_acpt_column_chart_type" id="csl_acpt_column_chart_type" value="' . esc_attr( get_post_meta( $object->ID, 'csl_acpt_column_chart_type', true ) ) . '" />' . PHP_EOL;
    echo '</p>' . PHP_EOL;
    echo '<p class="description">' . PHP_EOL;
    echo __( 'Accepted types: line, spline, step, area, area-spline, area-step, bar, scatter, pie, donut and gauge.', CSL_TEXT_DOMAIN_PREFIX ) . PHP_EOL;    
    echo '</p>' . PHP_EOL;
}

function csl_acpt_save_metadata( $post_id, $post ) {
    /* Verify the nonce before proceeding. */
    if ( !isset( $_POST['csl_acpt_metadata_nonce'] ) || !wp_verify_nonce( $_POST['csl_acpt_metadata_nonce'], basename( __FILE__ ) ) )
        return $post_id;
    
    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );
    
    /* Check if the current user has permission to edit the post. */
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
        return $post_id;
    
    // Column labels routines
    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = ( isset( $_POST['csl_acpt_column_labels'] ) ? sanitize_text_field( $_POST['csl_acpt_column_labels'] ) : '' );
    
    /* Get the meta key. */
    $meta_key = 'csl_acpt_column_labels';
    
    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta( $post_id, $meta_key, true );
    
    /* If a new meta value was added and there was no previous value, add it. */
    if ( $new_meta_value && '' == $meta_value )
        add_post_meta( $post_id, $meta_key, $new_meta_value, true );
    
    /* If the new meta value does not match the old value, update it. */
    elseif ( $new_meta_value && $new_meta_value != $meta_value )
        update_post_meta( $post_id, $meta_key, $new_meta_value );
    
    /* If there is no new meta value but an old value exists, delete it. */
    elseif ( '' == $new_meta_value && $meta_value )
        delete_post_meta( $post_id, $meta_key, $meta_value );

     // Column ids routines
    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = ( isset( $_POST['csl_acpt_column_ids'] ) ? sanitize_text_field( $_POST['csl_acpt_column_ids'] ) : '' );
    
    /* Get the meta key. */
    $meta_key = 'csl_acpt_column_ids';
    
    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta( $post_id, $meta_key, true );
    
    /* If a new meta value was added and there was no previous value, add it. */
    if ( $new_meta_value && '' == $meta_value )
        add_post_meta( $post_id, $meta_key, $new_meta_value, true );
    
    /* If the new meta value does not match the old value, update it. */
    elseif ( $new_meta_value && $new_meta_value != $meta_value )
        update_post_meta( $post_id, $meta_key, $new_meta_value );
    
    /* If there is no new meta value but an old value exists, delete it. */
    elseif ( '' == $new_meta_value && $meta_value )
        delete_post_meta( $post_id, $meta_key, $meta_value );

   // Column chart descriptions routines
    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = ( isset( $_POST['csl_acpt_column_chart_descriptions'] ) ? sanitize_text_field( $_POST['csl_acpt_column_chart_descriptions'] ) : '' );
    
    /* Get the meta key. */
    $meta_key = 'csl_acpt_column_chart_descriptions';
    
    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta( $post_id, $meta_key, true );
    
    /* If a new meta value was added and there was no previous value, add it. */
    if ( $new_meta_value && '' == $meta_value )
        add_post_meta( $post_id, $meta_key, $new_meta_value, true );
    
    /* If the new meta value does not match the old value, update it. */
    elseif ( $new_meta_value && $new_meta_value != $meta_value )
        update_post_meta( $post_id, $meta_key, $new_meta_value );
    
    /* If there is no new meta value but an old value exists, delete it. */
    elseif ( '' == $new_meta_value && $meta_value )
        delete_post_meta( $post_id, $meta_key, $meta_value );

    // Column chart values routines
    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = ( isset( $_POST['csl_acpt_column_chart_values'] ) ? sanitize_text_field( $_POST['csl_acpt_column_chart_values'] ) : '' );
    
    /* Get the meta key. */
    $meta_key = 'csl_acpt_column_chart_values';
    
    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta( $post_id, $meta_key, true );
    
    /* If a new meta value was added and there was no previous value, add it. */
    if ( $new_meta_value && '' == $meta_value )
        add_post_meta( $post_id, $meta_key, $new_meta_value, true );
    
    /* If the new meta value does not match the old value, update it. */
    elseif ( $new_meta_value && $new_meta_value != $meta_value )
        update_post_meta( $post_id, $meta_key, $new_meta_value );
    
    /* If there is no new meta value but an old value exists, delete it. */
    elseif ( '' == $new_meta_value && $meta_value )
        delete_post_meta( $post_id, $meta_key, $meta_value );

    // Column chart type routines
    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = ( isset( $_POST['csl_acpt_column_chart_type'] ) ? sanitize_text_field( $_POST['csl_acpt_column_chart_type'] ) : '' );
    
    /* Get the meta key. */
    $meta_key = 'csl_acpt_column_chart_type';
    
    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta( $post_id, $meta_key, true );
    
    /* If a new meta value was added and there was no previous value, add it. */
    if ( $new_meta_value && '' == $meta_value )
        add_post_meta( $post_id, $meta_key, $new_meta_value, true );
    
    /* If the new meta value does not match the old value, update it. */
    elseif ( $new_meta_value && $new_meta_value != $meta_value )
        update_post_meta( $post_id, $meta_key, $new_meta_value );
    
    /* If there is no new meta value but an old value exists, delete it. */
    elseif ( '' == $new_meta_value && $meta_value )
        delete_post_meta( $post_id, $meta_key, $meta_value );
}

// Customized "enter title here"message for any post type
function csl_acpt_generic_custom_default_title( $title ) {
    $screen = get_current_screen();
    $txtnam = get_post_type_object($screen->post_type)->labels->singular_name;
    if  ( 'add' == $screen->action ) {
        $title = sprintf(__('Enter the public name for %s', CSL_TEXT_DOMAIN_PREFIX), strtolower($txtnam));
    } else {
        $title = "";
    }
    return $title;
}
add_filter( 'enter_title_here', 'csl_acpt_generic_custom_default_title');

?>
<?php

/**
 * Custom surveys functions
 * Code based in Multiple Votes in one page v1.0.4 (https://github.com/lequanghuylc/multiple-votes-in-one-page),
 * Base plugin by Huy Le (http://lequanghuy.xyz), license: GPLv2 or later
 */

// Single call function
function csl_survey_single_call( ) {
    global $csl_global_nonce;
    $cuser = wp_get_current_user()->ID;
    $cpage = get_post()->ID;
    $ctitl = get_post()->post_title;
    $hasev = csl_survey_current_page_has_been_evaluated( $cpage, $cuser );
    $hasms = $hasev 
        ? 
        '
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="' . __( 'Close', CSL_TEXT_DOMAIN_PREFIX ) . '"><span aria-hidden="true">&times;</span></button>
            ' . __( '<strong>Warning!</strong>. There is already an evaluation made by you on this page. You can do it again, if you wish. The new values you submit will replace those previously saved.', CSL_TEXT_DOMAIN_PREFIX ) . '
        </div>                
        '
        :
        '';
    $args = array(
    	'posts_per_page'   => -1,
    	'offset'           => 0,
    	'orderby'          => 'title',
    	'order'            => 'ASC',
    	'post_type'        => 'evalcsl',
    	'post_status'      => 'publish',
    	'suppress_filters' => true
    ); 
    $posts_array = get_posts( $args );
    $content = '
        <div class="row">
        <div class="col-md-12">
        <h4>
        ' . sprintf( __( 'Quality Evaluation. Page ID: %s, %s', CSL_TEXT_DOMAIN_PREFIX ), $cpage, $ctitl ) . '
        </h4>
        ' . $hasms . '
        <p>
        ' . __( 'Use this form to evaluate this page quality. You can select the appropriate values by sliding the selectors. Higher values denote better opinion about assessed aspect. Keep in mind that your evaluation NOT is done anonymously.', CSL_TEXT_DOMAIN_PREFIX ) . '
        </p>
        <form class="form-horizontal" id="evalfrm" method="POST" target="">
    ';   
    foreach( $posts_array as $spost ) {
        $postid      = $spost->ID;
        $posttitle   = $spost->post_title;
        $postexcerpt = $spost->post_excerpt;
    	$countnumber = number_format_i18n( (int)get_post_meta( $postid, '_csl_survey_vote', true ), 0 );
        $content    .= '
            <div class="form-group">
                <label for="cs' . $postid . '" class="col-sm-3 control-label">' . $posttitle . '</label>
                <div class="col-sm-1" class="center-block" style="font-size: larger;"><output class="label label-danger" id="rval' . $postid . '" style="text-align: center; font-weight: bold;">0</output></div>
                <div class="col-sm-8">
                    <p class="form-control-static">
                    <input class="range-slider" id="rate-' . $postid . '" type="range" name="rate-' . $postid . '" min="0" max="9" value="0" step="1" />
                    </p>
                    <p class="form-control-static">' . $postexcerpt . '</p>
                </div>
            </div>        
        ';
    }
    $content .= '
        <div class="form-group">
        <label for="evalsub" class="col-sm-3 control-label">' . __( 'Total average', CSL_TEXT_DOMAIN_PREFIX ) . '</label>
        <div class="col-sm-1" class="center-block" style="font-size: larger;">
            <span id="evaltotal"><strong class="label label-danger">' . number_format_i18n( 0, 0 ) . '</strong></span>
        </div>
        <div class="col-sm-8">
            <a href="#" id="evalsub" class="btn btn-default">' . __( 'Submit evaluation', CSL_TEXT_DOMAIN_PREFIX ) . '</a>
            <span id="evalactivity"></span>
        </div>
        </div>
        </form>
        ';
    $content .= '
        <script type="text/javascript">
        jQuery( document ).ready(function($) {
            var classrng = {
                0: "danger",
                1: "danger",
                2: "warning",
                3: "warning",
                4: "info",
                5: "info",
                6: "primary",
                7: "primary",
                8: "success",
                9: "success",
            }
            $(".range-slider").on( "change", function( index ) {
                var valfield = $(this).attr("id").replace("rate-", "rval");
                var valvalue = $(this).val();
                var valclass = "label label-" + classrng[valvalue];
                $("#" + valfield).val(valvalue); 
                $("#" + valfield).removeClass();
                $("#" + valfield).addClass(valclass);
            });
            $("#evallink").on( "click", function( e ){
                e.preventDefault();
                /*
                $("[id^=rval]").each(function(index) {
                    $(this).val(0);
                });
                */
                $("[id^=rate]").each(function(index) {
                    $(this).val(0);
                    $(this).trigger("change");
                });
                $("#evaltotal").html("<strong class=\"label label-" + classrng[0] + "\">0</strong>");              
                $("#evalactivity").html("");
            });
            $("#evalsub").on( "click", function( ){
                var ntotal = 0;
                $("[id^=rval]").each(function(index) {
                    $("#evalactivity").html("<span class=\"text-danger\">' .  __( 'Sending data&hellip;', CSL_TEXT_DOMAIN_PREFIX ) . '</span>");
                    ntotal += parseInt($(this).val());
        			$.ajax({
        				type:"POST",
        				url: "'.admin_url("admin-ajax.php", null).'",
        				data: "data=" + $(this).attr("id") + "|" + $(this).val() + "|" + "' . get_post()->ID . '" + "&action=csl_survey_single_ajax&security=' . $csl_global_nonce . '",
        				success:function(data){
        					$("#evalactivity").html("<span class=\"text-success\">' .  __( 'Evaluation data sent', CSL_TEXT_DOMAIN_PREFIX ) . '</span>");
        				}
        			});
                });
                $( document ).ajaxStop(function() {
                    $("#evalactivity").html("<strong class=\"text-success\">' .  __( 'Complete evaluation sent', CSL_TEXT_DOMAIN_PREFIX ) . '</strong>");
                    $("#evaltotal").html("<strong class=\"label label-" + classrng[Math.floor(ntotal/$("[id^=rval]").length)] + "\">" + Math.floor(ntotal/$("[id^=rval]").length) + "</strong>");
                });                
            });
        });
        </script>
    ';
    $content .= '
        </div></div>
    ';
    return $content;
}

// AJAX Handling Form from single function
function csl_survey_single_ajax(){
    if( !isset( $_POST['data'] ) ) {
    	die();
    }
    $csl_post_ref = explode( '|', sanitize_text_field( $_POST['data'] ) );
    if( count( $csl_post_ref ) != 3 || !isset( $_POST['security'] ) || !wp_verify_nonce( $_POST['security'], NONCE_KEY ) ) {
    	die();
    }
    $csl_post_id  = absint( intval( str_replace( 'rval', '', $csl_post_ref[0] ) ) );
    $csl_post_num = absint( intval( $csl_post_ref[1] ) );
    $csl_page_id  = absint( intval( $csl_post_ref[2] ) );
    update_post_meta( $csl_post_id, '_csl_survey_vote_' . $csl_page_id . '_' . wp_get_current_user()->ID , $csl_post_num );
	die();
}
add_action('wp_ajax_csl_survey_single_ajax', 'csl_survey_single_ajax');
add_action('wp_ajax_nopriv_csl_survey_single_ajax', 'csl_survey_single_ajax');

// AJAX Handling for clear all evaluations
function csl_survey_clear_evals(){
    global $wpdb;
    if( !isset( $_POST['data'] ) || !isset( $_POST['security'] ) || !wp_verify_nonce( $_POST['security'], NONCE_KEY ) ) {
    	die();
    }
    $csl_post_ref = sanitize_text_field( $_POST['data'] );
    $wpdb->get_results( "
    	DELETE 
    	FROM $wpdb->postmeta
    	WHERE meta_key LIKE '_csl_survey_vote_%' AND post_id = $csl_post_ref
    ");
	die();
}
add_action('wp_ajax_csl_survey_clear_evals', 'csl_survey_clear_evals');
add_action('wp_ajax_nopriv_csl_survey_clear_evals', 'csl_survey_clear_evals');

// Add custom post type Evaluation and its taxonomy
function csl_survey_create_eval_post_type() {
	
	$labels = array(
			'name'               => __( 'Evaluations', CSL_TEXT_DOMAIN_PREFIX ),
			'singular_name'      => __( 'Evaluation', CSL_TEXT_DOMAIN_PREFIX ),
			'add_new'            => __( 'Add evaluation', CSL_TEXT_DOMAIN_PREFIX ),
			'add_new_item'       => __( 'Add evaluation', CSL_TEXT_DOMAIN_PREFIX ),
			'edit_item'          => __( 'Edit evaluation', CSL_TEXT_DOMAIN_PREFIX ),
			'new_item'           => __( 'New evaluation', CSL_TEXT_DOMAIN_PREFIX ),
			'view_item'          => __( 'View evaluation', CSL_TEXT_DOMAIN_PREFIX ),
			'search_items'       => __( 'Search evaluations', CSL_TEXT_DOMAIN_PREFIX ),
			'not_found'          => __( 'No evaluations found', CSL_TEXT_DOMAIN_PREFIX ),
			'not_found_in_trash' => __( 'No evaluations in the trash', CSL_TEXT_DOMAIN_PREFIX ),
		);
		$supports = array(
			'title',
            'excerpt',
		);
		$args = array(
			'labels'          => $labels,
			'supports'        => $supports,
			'public'          => true,
			'capability_type' => 'post',
			'rewrite'         => array( 'slug' => 'eval-csl', ),
			'menu_position'   => 30,
			'menu_icon'       => 'dashicons-awards',
			'register_meta_box_cb' => 'csl_survey_add_metaboxes'
		);
	
	
	register_post_type( 'evalcsl', $args );
	
	$labels2 = array(
			'name'                       => __( 'Evaluation Categories', CSL_TEXT_DOMAIN_PREFIX ),
			'singular_name'              => __( 'Evaluation Category', CSL_TEXT_DOMAIN_PREFIX ),
			'menu_name'                  => __( 'Evaluation Categories', CSL_TEXT_DOMAIN_PREFIX ),
			'edit_item'                  => __( 'Edit Evaluation Category', CSL_TEXT_DOMAIN_PREFIX ),
			'update_item'                => __( 'Update Evaluation Category', CSL_TEXT_DOMAIN_PREFIX ),
			'add_new_item'               => __( 'Add New Evaluation Category', CSL_TEXT_DOMAIN_PREFIX ),
			'new_item_name'              => __( 'New Evaluation Category Name', CSL_TEXT_DOMAIN_PREFIX ),
			'parent_item'                => __( 'Parent Evaluation Category', CSL_TEXT_DOMAIN_PREFIX ),
			'parent_item_colon'          => __( 'Parent Evaluation Category:', CSL_TEXT_DOMAIN_PREFIX ),
			'all_items'                  => __( 'All Evaluation Categories', CSL_TEXT_DOMAIN_PREFIX ),
			'search_items'               => __( 'Search Evaluation Categories', CSL_TEXT_DOMAIN_PREFIX ),
			'popular_items'              => __( 'Popular Evaluation Categories', CSL_TEXT_DOMAIN_PREFIX ),
			'separate_items_with_commas' => __( 'Separate Evaluation categories with commas', CSL_TEXT_DOMAIN_PREFIX ),
			'add_or_remove_items'        => __( 'Add or remove Evaluation categories', CSL_TEXT_DOMAIN_PREFIX ),
			'choose_from_most_used'      => __( 'Choose from the most used Evaluation categories', CSL_TEXT_DOMAIN_PREFIX ),
			'not_found'                  => __( 'No Evaluation categories found.', CSL_TEXT_DOMAIN_PREFIX ),
		);
		$args2 = array(
			'labels'            => $labels2,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => 'eval-csl-category' ),
			'show_admin_column' => true,
			'query_var'         => true,
		);
		$args2 = apply_filters( 'eval_post_type_category_args', $args2 );
	
	register_taxonomy( 'eval-csl-categories', 'evalcsl', $args2 );
	
	// Add the Evaluation Count Meta Boxes and show Shortcode
	function csl_survey_add_metaboxes() {
		add_meta_box( 
            'csl-eval-count', 
            __( 'Evaluation records deletion', CSL_TEXT_DOMAIN_PREFIX ), 
            'csl_survey_add_metabox_count', 
            'evalcsl', 
            'normal', 
            'low'
        );
	}
    
	function csl_survey_add_metabox_count() {
		global $post;
        global $csl_global_nonce;
        
        $countrecords = csl_survey_get_stat_values( '_csl_survey_vote_', $post->ID, 'COUNT' );
        $smessage     = sprintf( 
            __( 'There are %s evaluation record(s) for %s. Be warned about the erase operation is COMPLETELY IRREVERSIBLE, and none of them can be recovered.', CSL_TEXT_DOMAIN_PREFIX ), 
            '<strong><span id="numrec">' . number_format_i18n( $countrecords, 0 ) . '</span></strong>',
            '<strong>' . $post->post_title . '</strong>' 
        );
        $sbutton      = '<a href="#" id="deletemeta" class="button">' . __( 'Delete ALL evaluation records', CSL_TEXT_DOMAIN_PREFIX ) . '</a>';

		echo '<input type="hidden" name="evalmeta_noncename" id="evalmeta_noncename" value="' . 
		wp_create_nonce( NONCE_KEY . 'eval' ) . '" />';
	
		// Echo out the field
		echo '
            <p>' . $smessage . '</p>
            <p>' . $sbutton . '&nbsp;<span id="evalactivity"></span></p>
        ';
        echo '
            <script type="text/javascript">
            jQuery( document ).ready(function($) {
                $("#deletemeta").on( "click", function( ){
                    $("#evalactivity").html("' .  __( 'Deleting evaluation records', CSL_TEXT_DOMAIN_PREFIX ) . '&hellip;");
        			$.ajax({
        				type:"POST",
        				url: "'.admin_url("admin-ajax.php", null).'",
        				data: { data: "' . get_post()->ID . '", action: "csl_survey_clear_evals", security: "' . $csl_global_nonce . '" },
        				success:function(data){
        					$("#evalactivity").html("' . __( 'Deleted rows', CSL_TEXT_DOMAIN_PREFIX ) . ': " + "' . number_format_i18n( $countrecords, 0 ) . '");
        					$("#numrec").html("0");
                            $("#deletemeta").addClass("disabled");
                            $("#deletemeta").off("click"); 
                            $("#evalres").hide();                           
        				}
        			});
                });
            });
            </script>
        ';
        $evaluations = array();
        $evaluators  = get_users( array( 'fields' => array( 'ID', 'display_name' ), 'role' => 'subscriber' ) );
        $args = array(
        	'sort_order' => 'asc',
        	'sort_column' => 'post_title',
        	'post_type' => 'page',
        	'post_status' => 'publish'
        ); 
        $evalpages  = get_pages( $args );
        foreach( $evaluators as $evaluator ) {
            $evaluated = array();
            $notevaluated = array();
            foreach( $evalpages as $evalpage ) {
                if( get_post_meta( $post->ID, '_csl_survey_vote_' . $evalpage->ID . '_' . $evaluator->ID ) ) {
                    $evaluated []= $evalpage->post_title;    
                } else {
                    $notevaluated []= $evalpage->post_title;    
                }
            }                 
            $evaluations []= array(
                'sevaluator' => $evaluator->display_name,
                'sevaluated' => implode( ', ', $evaluated ),
                'snotevaluated' => implode( ', ', $notevaluated ),
                'sevalpercent' => number_format_i18n( ( count( $evaluated ) / count( $evalpages ) ) * 100, 0 ) . '%'
            );            
        }
        $theaders = array(
            __( 'Evaluator', CSL_TEXT_DOMAIN_PREFIX ),
            __( 'Evaluated pages', CSL_TEXT_DOMAIN_PREFIX ),
            __( 'Not evaluated pages', CSL_TEXT_DOMAIN_PREFIX ),
            __( 'Evaluation percent', CSL_TEXT_DOMAIN_PREFIX ),
        );
        echo csl_build_table( 'evalres', $theaders, $evaluations );
	}
	
	// Save the Metabox Data
	function csl_survey_save_meta_content($post_id, $post) {
        if ( isset($_POST['evalmeta_noncename']) && !wp_verify_nonce( $_POST['evalmeta_noncename'], NONCE_KEY . 'eval' )) {
            return $post->ID;
        }
        
		if ( !current_user_can( 'edit_post', $post->ID ))
			return $post->ID;

		if( isset( $_POST['_csl_survey_vote'] ) ){
			$evalcsl_meta['_csl_survey_vote_' . wp_get_current_user()->ID ] = $_POST['_csl_survey_vote'];
			foreach ($evalcsl_meta as $key => $value) { // Cycle through the $multiplevotesiopcsl_meta array!
				if( $post->post_type == 'revision' ) return; // Don't store custom data twice
				$value = implode( ',', (array)$value ); // If $value is an array, make it a CSV (unlikely)
				if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
					update_post_meta($post->ID, $key, $value);
				} else { // If the custom field doesn't have a value
					add_post_meta($post->ID, $key, $value);
				}
				
			}	
		}
		
	
	}

    add_action( 'save_post', 'csl_survey_save_meta_content', 1, 2 ); // save the custom fields
}
add_action( 'init', 'csl_survey_create_eval_post_type' );

// add column to show count content in admin page
add_filter( 'manage_edit-evalcsl_columns', 'csl_survey_add_columns_admin' ) ;

function csl_survey_add_columns_admin( $columns ) {
	$columns = array_slice($columns, 0, 2, true) +
        array( 
            'totalscore' => __( 'Total score', CSL_TEXT_DOMAIN_PREFIX ), 
            'averagescore' => __( 'Average score', CSL_TEXT_DOMAIN_PREFIX ), 
            'averagestars' => __( 'Average stars', CSL_TEXT_DOMAIN_PREFIX ), 
            'pagesnumber' => __( 'Scored pages', CSL_TEXT_DOMAIN_PREFIX ), 
            'usersnumber' => __( 'Evaluators', CSL_TEXT_DOMAIN_PREFIX ), 
            'evalpercent' => __( 'Evaluation %', CSL_TEXT_DOMAIN_PREFIX ), 
        ) +
        array_slice($columns, 6, count($columns)-6, true);
	return $columns;
}

add_action( 'manage_evalcsl_posts_custom_column', 'csl_survey_manage_columns_admin', 10, 2 );

function csl_survey_manage_columns_admin( $column, $post_id ) {
	global $post;
    global $wpdb;
    
    $sumnumber   = csl_survey_get_stat_values( '_csl_survey_vote_', $post_id, 'SUM' );
    $avgnumber   = csl_survey_get_stat_values( '_csl_survey_vote_', $post_id, 'AVG' );
    $pagesnumber = csl_survey_get_stat_values( '_csl_survey_vote_', $post_id, 'COUNT', 'DISTINCT CONCAT("_csl_survey_vote_", SUBSTRING_INDEX(REPLACE(meta_key, "_csl_survey_vote_", ""),"_",1))' );
    $countpages  = (int)wp_count_posts( 'page' )->publish;
    $usersnumber = csl_survey_get_stat_values( '_csl_survey_vote_', $post_id, 'COUNT', 'DISTINCT SUBSTRING_INDEX(REPLACE(meta_key, "_csl_survey_vote_", ""),"_",-1)' );
    
    $result      = count_users();
    $countusers  = $result['total_users'];
    $evaluators  = $result['avail_roles']['subscriber'];
    $avgtofive   = ceil( ( $avgnumber / 2 ) / 0.5 ) * 0.5;
    $avgcolor    = $avgtofive < 1.5 ? '#d9534f' : ( $avgtofive > 1 && $avgtofive < 4.5 ? '#f0ad4e' : '#5cb85c' );
    $avgfilledst = str_repeat( '<span class="dashicons dashicons-star-filled" style="color: ' . $avgcolor . ';"></span>', floor( $avgtofive ) );
    $avghalfst   = floor( $avgtofive ) == $avgtofive ? '' : '<span class="dashicons dashicons-star-half" style="color: ' . $avgcolor . ';"></span>';
    $avgemptyst  = str_repeat( '<span class="dashicons dashicons-star-empty" style="color: #ccc;"></span>', 5 - ceil( $avgtofive ) );
    $avgstars    = $avgfilledst . $avghalfst . $avgemptyst;
    
    $targeteval  = $countpages * $evaluators;
    $leveleval   = round( ( ( $pagesnumber * $usersnumber ) / $targeteval ) * 100, 0 );
    $levelcolor  = $leveleval < 25 ? "#d9534f" : ( $leveleval > 75 ? "#5cb85c" : "#f0ad4e" );
    $bareval     = '
    <div style="background: #ddd; width: 100%; height: 20px;">
    	<div style="width: ' . $leveleval . '%; background: ' . $levelcolor . '; height: 20px;"></div>        
    </div>
    ' . $leveleval . '% [' . sprintf( __( '%s of %s evaluations', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n( $leveleval, 0 ), number_format_i18n( $targeteval, 0 ) ) . ']  
    ';

	switch( $column ) {
		case 'totalscore' :
			printf( number_format_i18n( $sumnumber, 0 ) );
			break;
		case 'averagescore' :
			printf( number_format_i18n( $avgnumber, 1 ) );
			break;
		case 'averagestars' :
			printf( $avgstars );
			break;
		case 'pagesnumber' :
			printf( 
                __( '%s of %s', CSL_TEXT_DOMAIN_PREFIX ), 
                number_format_i18n( $pagesnumber, 0 ),
                number_format_i18n( $countpages, 0 )
            );
			break;
		case 'usersnumber' :
			printf( 
                __( '%s of %s total users, %s valid evaluators', CSL_TEXT_DOMAIN_PREFIX ), 
                number_format_i18n( $usersnumber, 0 ), 
                number_format_i18n( $countusers, 0 ), 
                number_format_i18n( $evaluators, 0 ) 
            );
			break;
		case 'evalpercent' :
            echo $bareval;
			break;
		default :
			break;
	}
}

add_filter('parse_query', 'csl_survey_handle_filtering');

function csl_survey_handle_filtering($query) {
	global $pagenow;
	$post_type = 'evalcsl'; // change to your post type
	$taxonomy  = 'eval-csl-categories'; // change to your taxonomy
	$q_vars    = &$query->query_vars;
	if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
		$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
		$q_vars[$taxonomy] = $term->slug;
	}
}

// Auxiliary functions
function csl_survey_get_stat_values( $keystart, $postid, $type = "SUM", $field = "meta_value" ) {
    // Allowwed types: SUM, COUNT, AVG, MAX, MIN, STDEV, VARIANCE
    global $wpdb;
    $wholekey = $keystart . "%";
    $sumvotes = $wpdb->get_var( $wpdb->prepare( 
    	"
    		SELECT $type($field) 
    		FROM $wpdb->postmeta 
    		WHERE meta_key LIKE %s
            AND post_id = %d
    	", 
    	$wholekey,
        $postid
    ) );
    return $sumvotes;
}

function csl_survey_current_page_has_been_evaluated( $pid = null, $uid = null ) {
    if( $pid == null || $uid == null ) {
        return false;
    }
    global $wpdb;
    return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = \"_csl_survey_vote_{$pid}_{$uid}\";" ) > 0;
}

function csl_survey_evaluations_by_user( $uid = null ) {
    if( $uid == null ) {
        return false;
    }
    global $wpdb;
    return array(
        'eval_pages' => $wpdb->get_var( 
            "SELECT COUNT(DISTINCT SUBSTRING_INDEX(REPLACE(meta_key, \"_csl_survey_vote_\", \"\"),\"_\",1)) FROM {$wpdb->postmeta} WHERE meta_key LIKE \"_csl_survey_vote_%_{$uid}\";" 
        ),
        'total_pages' => wp_count_posts( 'page' )->publish
    );
}


?>
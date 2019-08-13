<?php

/**
 * Remove Dashboard access for not allowed users (subscribers and test subscribers)
 * Based in Remove Dashboard Access v1.1.3, a plugin by Drew Jaynes (DrewAPicture) (http://www.werdswords.com)
 * License: GPLv2
 */

if ( ! class_exists( 'csl_RDA_Remove_Access' ) ) {
    class csl_RDA_Remove_Access {
    	var $capability;
    	var $settings = array();
    
    	function __construct( $capability, $settings ) {
    		if ( empty( $capability ) ) {
    			return; // Bail
    		} else {
    			$this->capability = $capability;
    		}
    		$this->settings = $settings;
    		add_action( 'init', array( $this, 'is_user_allowed' ) );
    	}
    
    	function is_user_allowed() {
    		if ( $this->capability && ! current_user_can( $this->capability ) && ! defined( 'DOING_AJAX' ) ) {
    			$this->lock_it_up();
    		} else {
    			return; // Bail
    		}
    	}
    
    	function lock_it_up() {
    		add_action( 'admin_init',     array( $this, 'dashboard_redirect' ) );
    		add_action( 'admin_head',     array( $this, 'hide_menus' ) );
    		add_action( 'admin_bar_menu', array( $this, 'hide_toolbar_items' ), 999 );
    	}
    
    	public function hide_menus() {
    		/** @global array $menu */
    		global $menu;
    
    		$menu_ids = array();
    		// Gather menu IDs (minus profile.php).
    		foreach ( $menu as $index => $values ) {
    			if ( isset( $values[2] ) ) {
    				if ( 'profile.php' == $values[2] ) {
    					continue;
    				}
    				remove_menu_page( $values[2] );
    			}
    		}
    	}
    
    	function dashboard_redirect() {
    		/** @global string $pagenow */
    		global $pagenow;
    
    		if ( 'profile.php' != $pagenow || ! $this->settings['enable_profile'] ) {
    			wp_redirect( $this->settings['redirect_url'] );
    			exit;
    		}
    	}
    
    	function hide_toolbar_items( $wp_admin_bar ) {
    		$edit_profile = ! $this->settings['enable_profile'] ? 'edit-profile' : '';
    		if ( is_admin() ) {
    			$ids = array( 'about', 'comments', 'new-content', $edit_profile );
    			$nodes = apply_filters( 'rda_toolbar_nodes', $ids );
    		} else {
    			$ids = array( 'about', 'dashboard', 'comments', 'new-content', 'edit', $edit_profile );
    			$nodes = apply_filters( 'rda_frontend_toolbar_nodes', $ids );
    		}
    		foreach ( $nodes as $id ) {
    			$wp_admin_bar->remove_menu( $id );
    		}
    	}	
    } // csl_RDA_Remove_Access
} // class_exists

$access = new csl_RDA_Remove_Access( 'edit_posts', array( 'enable_profile' => true, 'redirect_url' => get_home_url() ) );

/**
 * Adds Post Counts by Post Type per User in the User List withing WordPress' Admin console (URL path => /wp-admin/users.php)
 * Written for: http://wordpress.stackexchange.com/questions/3233/showing-users-post-counts-by-custom-post-type-in-the-admins-user-list
 * By: Mike Schinkel (http://mikeschinkel.com)
 * Date: 24 October 2010
 */
 
/**
 * csl_manage_users_columns function.
 * 
 * @access public
 * @param mixed $column_headers
 * @return void
 */
function csl_manage_users_columns($column_headers) {
	unset($column_headers['posts']);
	$column_headers['custom_posts'] = __( 'Records', CSL_TEXT_DOMAIN_PREFIX );
	return $column_headers;
}
add_action('manage_users_columns','csl_manage_users_columns');

/**
 * csl_manage_users_custom_column function.
 * 
 * @access public
 * @param mixed $custom_column
 * @param mixed $column_name
 * @param mixed $user_id
 * @return void
 */
function csl_manage_users_custom_column($custom_column,$column_name,$user_id) {
	if ($column_name=='custom_posts') {
		$counts = _csl_get_author_post_type_counts();
		$custom_column = array();
		if (isset($counts[$user_id]) && is_array($counts[$user_id]))
			foreach($counts[$user_id] as $count)
				$custom_column[] = "{$count['label']}: <strong>" . number_format_i18n( $count['count'], 0 ) . "</strong>";
		$custom_column = implode(", ",$custom_column);
		if (empty($custom_column))
			$custom_column = __( 'No records', CSL_TEXT_DOMAIN_PREFIX );
	}
	return $custom_column;
}
add_action('manage_users_custom_column','csl_manage_users_custom_column',10,3);

/**
 * _csl_get_author_post_type_counts function.
 * 
 * @access private
 * @return void
 */
function _csl_get_author_post_type_counts() {
	static $counts;
	if (!isset($counts)) {
		global $wpdb;
		global $wp_post_types;
		$sql = "
			SELECT
			  post_type,
			  post_author,
			  COUNT(*) AS post_count
			FROM
			  {$wpdb->posts}
			WHERE 1=1
			  AND post_type NOT IN ('revision','nav_menu_item')
			  AND post_status IN ('publish','pending')
			GROUP BY
			  post_type,
			  post_author
			";
		$posts = $wpdb->get_results($sql);
		foreach($posts as $post) {
			$post_type_object = $wp_post_types[$post_type = $post->post_type];
			if (!empty($post_type_object->label))
				$label = $post_type_object->label;
			else if (!empty($post_type_object->labels->name))
					$label = $post_type_object->labels->name;
			else
				$label = ucfirst(str_replace(array('-','_'),' ',$post_type));
			if (!isset($counts[$post_author = $post->post_author]))
				$counts[$post_author] = array();
			$counts[$post_author][] = array(
				'label' => $label,
				'count' => $post->post_count,
			);
		}
	}
	return $counts;
}

/**
 *  CSL. Notices management
 *  From https: // make . wordpress.  org / core/2015/04/23/spinners-and-dismissible-admin-notices-in-4-2/: 	
    Any notice can now be made dismissible by ensuring the it has the classes .notice and .is-dismissible (recognize that naming convention?). 
    Core handles adding the close button and removing the notice for you. However, for the best possible user experience, you should ensure that those notices 
    will not come back on a page refresh or when navigating to another page. There are two different paths for this. The first applies to notices 
    that are added when a query arg is present in the URL, such as message=6. Core will now remove certain query args and use JS to replace 
    the URL in the browser with that “cleaned up” version. By default, core handles 'message', 'settings-updated', 'saved', 'update', 'updated',
    'activated', 'activate', 'deactivate', 'locked', 'deleted', 'trashed', 'untrashed', 'enabled', 'disabled', 'skipped', 'spammed', and 'unspammed'. 
    To add (or remove) items to this array to accommodate your needs, use the removable_query_args filter.
    
    Valid classes: [mandatory] notice is-dismissible [optional] notice-info notice-warning notice-success notice-error
 
 */
 
add_action('admin_notices', 'csl_new_chat_activity_notice' );
add_action('admin_notices', 'csl_custom_menu_spp_msg_connected_managers');

function csl_custom_menu_spp_msg_connected_managers() {
    global $pagenow;

    if ('index.php' === $pagenow) {
	    $aOUT = array();
	    $aMNG = array_intersect( array_map( 'intval', array_merge( (array) get_users( 'fields=ID&role=editor' ), (array) get_users( 'fields=ID&role=administrator' ) ) ), csl_get_connected_users() );
		foreach( $aMNG as $user) {
			$aOUT []= sprintf(
				__('%s %s ago', CSL_TEXT_DOMAIN_PREFIX),
				get_userdata($user)->display_name,
				human_time_diff( get_transient( CSL_DATA_PREFIX . 'user_' . $user ), current_time('timestamp'))
			);	
		}
		$authors_text = count( csl_get_connected_users() ) == 0 ? '' : ' ' . sprintf(
				__('%s user(s)', CSL_TEXT_DOMAIN_PREFIX),
				number_format_i18n( count( csl_get_connected_users() ) - count( $aMNG ), 0 )
			);
		echo csl_format_admin_notice( $text = ( count( $aOUT ) ? implode(', ', $aOUT) . '. ' : '' ) . $authors_text, $icon = 'eye', $class = 'info' );
	} else {
		echo false;
	}
}

// Change footer messages and version info (left/right)
function csl_change_footer_admin() {
    return CSL_ORGANIZATION_SHORT . ' ' . date('Y') . ' ' . CSL_AUTHOR_WEB;
}
function csl_change_footer_version() {
	return CSL_NAME . ' v' . CSL_VERSION . '. <a href="https://es.wordpress.org/" target="_blank">WPAF</a> Engine v' . get_bloginfo('version') . ' ' . get_bloginfo('language');
}
add_filter('admin_footer_text', 'csl_change_footer_admin', 9999);
add_filter('update_footer', 'csl_change_footer_version', 9999);	  

// Change "Howdy" message in admin bar
function csl_replace_howdy ( $wp_admin_bar ) {
    $my_account = $wp_admin_bar->get_node( 'my-account' );
    $newtitle   = __( 'Logged in as', CSL_TEXT_DOMAIN_PREFIX ) . ' ' . wp_get_current_user()->display_name;//str_replace ( __('Howdy'), __( 'Logged in as', CSL_TEXT_DOMAIN_PREFIX ), $my_account->title );
    $wp_admin_bar->add_node( array(
        'id'    => 'my-account',
        'title' => $newtitle,
    ) );
}
add_filter ( 'admin_bar_menu', 'csl_replace_howdy', 25 );

// Disable comments
function csl_remove_comment_support() {
    $atypes = array_merge( CSL_CUSTOM_POST_TYPE_ARRAY, array( 'page', 'post' ) );
    foreach( $atypes as $atype ) {
        remove_post_type_support( $atype, 'comments' );
    }
}
add_action( 'admin_menu', 'csl_remove_comment_support' );

// CUSTOM USER PROFILE FIELDS
function csl_custom_users_fields( $contactmethods ) {
	// Social network fields
	$contactmethods['twitter']					= __( 'Twitter user', CSL_TEXT_DOMAIN_PREFIX );
	$contactmethods['skype']					= __( 'Skype user', CSL_TEXT_DOMAIN_PREFIX );
	$contactmethods['facebook']					= __( 'Facebook user', CSL_TEXT_DOMAIN_PREFIX );
	// Phone fields
    $contactmethods['contact_phone_office']     = __( 'Phone', CSL_TEXT_DOMAIN_PREFIX );
    $contactmethods['contact_phone_mobile']     = __( 'Mobile phone', CSL_TEXT_DOMAIN_PREFIX );
	// Address fields
	$contactmethods['address_line_1']			= __( 'Address', CSL_TEXT_DOMAIN_PREFIX );
	$contactmethods['address_city']				= __( 'City', CSL_TEXT_DOMAIN_PREFIX );
	$contactmethods['address_state']			= __( 'State/Province', CSL_TEXT_DOMAIN_PREFIX );
	$contactmethods['address_zipcode']			= __( 'ZIP', CSL_TEXT_DOMAIN_PREFIX );

	return $contactmethods;
}

function csl_extra_profile_fields( $user ) { 
	echo '<h3>' . __( 'Positions in the workforce and personal skills', CSL_TEXT_DOMAIN_PREFIX ) . '</h3>' . PHP_EOL;
    echo '<table class="form-table">' . PHP_EOL;
    // User password resend
    echo '<tr>' . PHP_EOL;
	echo '<th><label for="password_resend">' . __( 'Password resend', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
    echo '<td>' . PHP_EOL;
    echo '<input name="password_resend" type="radio" value="0" checked />&nbsp;' . __( 'No', CSL_TEXT_DOMAIN_PREFIX ) . '&nbsp;' . PHP_EOL;
    echo '<input name="password_resend" type="radio" value="1" />&nbsp;' . __( 'Yes', CSL_TEXT_DOMAIN_PREFIX ) . '<br />' . PHP_EOL;
	echo '<span class="description">' . __( 'Select Yes if you want to resend password to user.', CSL_TEXT_DOMAIN_PREFIX ) . '</span>' . PHP_EOL;
	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;
    // User staff position
    echo '<tr>' . PHP_EOL;
	echo '<th><label for="position">' . __( 'Position', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
    echo '<td>' . PHP_EOL;
    echo '<input type="text" name="position" id="position" value="' . esc_attr(get_the_author_meta('position', $user->ID)) . '" class="regular-text" /><br />' . PHP_EOL;
	echo '<span class="description">' . __( 'Enter the position of the user in the workforce.', CSL_TEXT_DOMAIN_PREFIX ) . '</span>' . PHP_EOL;
	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;
    // User assignments
    echo '<tr>' . PHP_EOL;
	echo '<th><label for="assignments">' . __( 'Assignments', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
    echo '<td>' . PHP_EOL;
    echo '<input type="text" name="assignments" id="assignments" value="' . esc_attr(get_the_author_meta('assignments', $user->ID)) . '" class="regular-text" /><br />' . PHP_EOL;
	echo '<span class="description">' . __( 'Enter the assignments of the user in the workforce.', CSL_TEXT_DOMAIN_PREFIX ) . '</span>' . PHP_EOL;
	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;
    // User skills
    echo '<tr>' . PHP_EOL;
	echo '<th><label for="skills">' . __( 'Skills', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
    echo '<td>' . PHP_EOL;
    echo '<input type="text" name="skills" id="skills" value="' . esc_attr(get_the_author_meta('skills', $user->ID)) . '" class="large-text" /><br />' . PHP_EOL;
	echo '<span class="description">' . __( 'Enter the personal skills of the user.', CSL_TEXT_DOMAIN_PREFIX ) . '</span>' . PHP_EOL;
	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;
    // Is project staff member?
    echo '<tr>' . PHP_EOL;
    if ( ! in_array( csl_get_current_user_role( false ), array( 'test_subscribers', 'subscribers', 'author', 'test_authors' ) ) ) {
    	echo '<th><label for="is_project_staff_member">' . sprintf(__( 'Is a member of %s project team?', CSL_TEXT_DOMAIN_PREFIX ), CSL_PROJECT_NAME) . '</label></th>' . PHP_EOL;
        echo '<td>' . PHP_EOL;
        echo '<input type="radio" name="is_project_staff_member" id="is_project_staff_member1" value="1"' . 
        	(esc_attr(get_the_author_meta('is_project_staff_member', $user->ID)) == '1' ? ' checked' : '') . '>&nbsp;' . 
        	__( 'Yes', CSL_TEXT_DOMAIN_PREFIX ) . '&nbsp;' . 
        	'<input type="radio" name="is_project_staff_member" id="is_project_staff_member0" value="0"' . 
        	(esc_attr(get_the_author_meta('is_project_staff_member', $user->ID)) == '0' || empty(get_the_author_meta('is_project_staff_member', $user->ID)) ? ' checked' : '') . '>&nbsp;' . 
        	__( 'No', CSL_TEXT_DOMAIN_PREFIX ) . '<br />' . 
        	PHP_EOL;
    	echo '<span class="description">' . sprintf(__( 'Select if user is or is not a member of the %s project team.', CSL_TEXT_DOMAIN_PREFIX ), CSL_PROJECT_NAME) . '</span>' . PHP_EOL;
    	echo '</td>' . PHP_EOL;
    	echo '</tr>' . PHP_EOL;
    }
    echo '</table>' . PHP_EOL;
}

function csl_save_extra_profile_fields( $user_id )  {
    if ( !current_user_can('edit_user', $user_id) ) { 
        return false; 
    } else {
        if( isset( $_POST['password_resend'] ) ) {
            if( (int) $_POST['password_resend'] == 1 ) {
                csl_retrieve_password( get_userdata( $user_id )->user_email );
            }
        }
        if(isset($_POST['position']) && $_POST['position'] != '') {
            update_usermeta( $user_id, 'position', sanitize_text_field($_POST['position']) );
        } else {
            delete_usermeta($user_id, 'position');
        }
        if(isset($_POST['assignments']) && $_POST['assignments'] != '') {
            update_usermeta( $user_id, 'assignments', sanitize_text_field($_POST['assignments']) );
        } else {
            delete_usermeta($user_id, 'assignments');
        }
        if(isset($_POST['skills']) && $_POST['skills'] != '') {
            update_usermeta( $user_id, 'skills', sanitize_text_field($_POST['skills']) );
        } else {
            delete_usermeta($user_id, 'skills');
        }
        if(isset($_POST['is_project_staff_member']) && $_POST['is_project_staff_member'] != '') {
            update_usermeta( $user_id, 'is_project_staff_member', sanitize_text_field($_POST['is_project_staff_member']) );
        } else {
            delete_usermeta($user_id, 'is_project_staff_member');
        }
    }
}
/* Hook functions */
add_filter( 'user_contactmethods','csl_custom_users_fields', 10, 1);
add_action( 'show_user_profile', 'csl_extra_profile_fields', 10, 1 );
add_action( 'edit_user_profile', 'csl_extra_profile_fields', 10, 1 );
add_action( 'personal_options_update', 'csl_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'csl_save_extra_profile_fields' );

// CUSTOM ADMIN MENU LINK FOR ALL SETTINGS
function csl_all_settings_for_administrator_link() {
    add_options_page(__('All Settings'), __('All Settings'), 'administrator', 'options.php');
}
add_action('admin_menu', 'csl_all_settings_for_administrator_link');

// DASHBOARD MAIN PAGE
// FROM https: // mypluginlab. com /tutorials /core /breaking-down-wordpress-admin-ui /
// AND http: // premium.wpmudev. org /blog /tabbed-interface/?utm_expid=3606929-40.lszTaIEzTbifDhvhVdd39A.0&utm_referrer=https%3A%2F%2Fwww.google.es%2F
function csl_custom_menu_status_page() {
    add_submenu_page(
        'index.php',
        CSL_NAME . ': ' . __( 'Application status', CSL_TEXT_DOMAIN_PREFIX ),
        __( 'Application status', CSL_TEXT_DOMAIN_PREFIX ), 
        'edit_posts', 
        'csl_app_status', 
        'csl_custom_menu_status_page_paint', 
        'dashicons-info', 
        3
    ); 
}
add_action('admin_menu', 'csl_custom_menu_status_page');

function csl_admin_tabs(){
	$tabs = array(
		'the_app'				=> __( 'The app', CSL_TEXT_DOMAIN_PREFIX ),
		'help_and_messages'			=> __( 'BBS & messages', CSL_TEXT_DOMAIN_PREFIX ),
		'system_status'			=> __( 'Quality control', CSL_TEXT_DOMAIN_PREFIX ),
		'users_activity'		=> __( 'Activity', CSL_TEXT_DOMAIN_PREFIX ),
		'evolution'			=> __( 'Evolution', CSL_TEXT_DOMAIN_PREFIX ),
		'system_maintenance'    => __( 'Maintenance', CSL_TEXT_DOMAIN_PREFIX ),
	);
	if(!current_user_can('manage_options')) {
	    if(current_user_can('edit_others_posts')) {
			unset($tabs['system_maintenance']);
	    } else {
			unset($tabs['system_maintenance']);
	    }
	}
    
	return apply_filters('csl_admin_tabs', $tabs);
}

function csl_custom_menu_status_page_paint() {
    global $wpdb;
    
    $tabs = csl_admin_tabs();
    $current = sanitize_text_field(isset($_GET['tab']) ? $_GET['tab'] : 'the_app');

    echo '<div class="wrap">' . PHP_EOL;
    echo '<h2>' . get_admin_page_title() . '</h2>';

    echo '<h3 class="nav-tab-wrapper">';
    if(!empty($tabs)){
        foreach($tabs as $key => $value) {
            $class = ( $key == $current ) ? ' nav-tab-active' : ''; 
            echo '<a href="?page=csl_app_status&tab=' . $key . '" class="nav-tab' . $class . '">' . $value . '</a>';
        }
    }
    echo '</h3>';
    
    switch($current) {
        case 'the_app':
            csl_custom_menu_spp_the_app();
            break;
        case 'help_and_messages':
            csl_custom_menu_spp_help_and_messages();
            break;
        case 'system_status':
            csl_custom_menu_spp_system_status();
            break;
        case 'users_activity':
            csl_custom_menu_spp_users_activity();
            break;
        case 'evolution':
            csl_custom_menu_spp_evolution();
            break;
        case 'system_maintenance':
            csl_custom_menu_spp_system_maintenance();
            break;
        default:
            break;
    }
    
    echo '</div>';
}

function csl_custom_menu_spp_the_app( $file = 'the_app' ) {
    $text = file_get_contents(get_template_directory() . '/assets/docs/' . get_locale() . '/' . $file . '.html');
    echo $text ? $text : __('Not help file found.', CSL_TEXT_DOMAIN_PREFIX);

    csl_draw_project_status_gantt_chart($title = sprintf(__( '%s project current status', CSL_TEXT_DOMAIN_PREFIX ), CSL_PROJECT_NAME),  $title_level = '3');
    csl_write_project_team_table($title = sprintf(__( '%s & %s work team', CSL_TEXT_DOMAIN_PREFIX ), CSL_PROJECT_NAME, CSL_LOGO),  $title_level = '3');            
}

function csl_custom_menu_spp_help_and_messages() {
    global $csl_a_options;
    $secnonce = wp_create_nonce( NONCE_KEY );
	echo '<h3>' . __( 'Global administrative news', CSL_TEXT_DOMAIN_PREFIX ) . '</h3>' . PHP_EOL;
	echo '<p>' . PHP_EOL;
	echo sprintf(
		__( 'This bulletin board displays the %s most recent administrative notices.', CSL_TEXT_DOMAIN_PREFIX ),
		number_format_i18n( (int)$csl_a_options['news_to_show'], 0 )
		);
	echo '</p>' . PHP_EOL;
	$notices = csl_get_internal_notices($limit = $csl_a_options['news_to_show'], $orderby = 'post_modified', $order = 'DESC');
	$last_activity = csl_get_user_activity_status( get_current_user_id(), ' AND activity = "read_chat_messages"' )['last_activity'];
	
	echo '<table style="width: 100%">' . PHP_EOL;
	date_default_timezone_set( get_option('timezone_string') ); 
	foreach($notices as $notice) {
		$class_name = strtotime($notice['post_modified']) > strtotime($last_activity) ? ' class="settings-values"' : '';
		echo '<tr>';
		echo '<td style="width: 45px;"' . $class_name . '><p class="calendar">' . 
			date_i18n( 'j', strtotime($notice['post_modified']) ) . '<em>' . 
			date_i18n( 'M', strtotime($notice['post_modified']) ) . 
			 '</em></p></td>';
		echo '<td><p>' . sprintf(
			__( '%s ago', CSL_TEXT_DOMAIN_PREFIX ),
			human_time_diff( strtotime($notice['post_modified']), time() ) 
			) . 
			'<br />';
		echo $notice['display_name'] . '<br />';
		echo '<a href="' . admin_url( '/admin-ajax.php?action=csl_generic_ajax_call&q=bbnotice&x=' . $notice['post_id'] ) . 
        	'&s=' . $secnonce . '&TB_iframe=true&height=600&width=750' . 
        	'" class="thickbox">' . 
        	$notice['post_title'] . 
        	'</a></td>';	
		echo '</tr>';
	}
	echo '</table>' . PHP_EOL;

	echo '<h3>' . __( 'Dashboard chat', CSL_TEXT_DOMAIN_PREFIX ) . '</h3>' . PHP_EOL;
	echo '<div id="csl_dashboard_chat">' . PHP_EOL;
	$chat = new CSL_Dashboard_Chat;
	echo '</div>' . PHP_EOL;
	csl_insert_log(array('object_id' => -1, 'object_type' => 'chat_messages', 'activity' => 'read_chat_messages'));
}

function csl_custom_menu_spp_system_status() {
    global $csl_a_options;
	$secnonce = wp_create_nonce( NONCE_KEY );

	echo '<table class="form-table">' . PHP_EOL;
	
    // Status table 2 (errors table)
	$errorsRec = csl_global_quality_control(true, true);
	$errorsGlb = csl_global_quality_control(true, false);
    $glberr = 0;
    $glbrec = 0;
    $glbert = 0;
    $glbokr = 0;
	foreach($errorsRec as $key => $value) {
        $glberr += $value->n_duplicated_title + $value->n_mandatory_fields_lack + $value->n_geonames_error + $value->n_autolookup_error + $value->n_taxonomy_lack;
        $glbert += 
            (($value->n_duplicated_title / $value->n_total_records) +
            ($value->n_mandatory_fields_lack / $value->n_total_records) +
            ($value->n_geonames_error / $value->n_total_records) +
            ($value->n_autolookup_error / $value->n_total_records) +
            ($value->n_taxonomy_lack / $value->n_total_records)) / 5;
        $glbrec += $value->n_total_records;
    }
    $glbper = $glbert / count($errorsRec);
    $glbokr = $glbrec - $glberr;

	$aTBL = array(
		array(
			__( 'No error', CSL_TEXT_DOMAIN_PREFIX ),
			number_format_i18n($glbokr, 0)
		),
		array(
			__( 'At least one error', CSL_TEXT_DOMAIN_PREFIX ),
			number_format_i18n($glberr, 0)
		),
		array(
			__( 'Average error level', CSL_TEXT_DOMAIN_PREFIX ),
			__( 'Average', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . 
			'<span class="text-' . 
			human_value_diff_valuation($csl_a_options['error_rate'], $glbper, true) . 
			'"><strong>' . 
			number_format_i18n(($glbper) * 100, 1) . '%' . '</strong></span> / ' . 
			__( 'Admissible', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . 
			number_format_i18n(($csl_a_options['error_rate']) * 100, 1) . '%'
		),
	);
    
	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . __( 'Processing errors', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;
	echo csl_build_table(
		'statusTableR', 
		NULL, 
		$aTBL, 
		__( 'Global recording error status', CSL_TEXT_DOMAIN_PREFIX )
	);

    // Global errors by user
	$aHDR = array(
		 __( 'User', CSL_TEXT_DOMAIN_PREFIX ),	
		 __( 'Records', CSL_TEXT_DOMAIN_PREFIX ),
		 __( 'Errors', CSL_TEXT_DOMAIN_PREFIX ),
		 __( 'E-Rate', CSL_TEXT_DOMAIN_PREFIX ),
		 __( 'Valid', CSL_TEXT_DOMAIN_PREFIX )
	);
	$aTBL = array();
	foreach($errorsGlb as $key => $value) {
		$aTMP = array();
        $toterr = $value->n_duplicated_title + $value->n_mandatory_fields_lack + $value->n_geonames_error + $value->n_autolookup_error + $value->n_taxonomy_lack;
        $maxerr = max(array($value->n_duplicated_title, $value->n_mandatory_fields_lack, $value->n_geonames_error, $value->n_autolookup_error, $value->n_taxonomy_lack));
        $pererr = 
            (($value->n_duplicated_title / $value->n_total_records) +
            ($value->n_mandatory_fields_lack / $value->n_total_records) +
            ($value->n_geonames_error / $value->n_total_records) +
            ($value->n_autolookup_error / $value->n_total_records) +
            ($value->n_taxonomy_lack / $value->n_total_records)) / 5;
        $aTMP []= '<a href="' . admin_url('/admin-ajax.php?action=csl_generic_ajax_call&q=exporterrorsxls&x=' . $value->post_author) . 
        	'&s=' . $secnonce . 
        	'">' . 
        	$value->display_name . 
        	'</a>';
        /* $aTMP []= strtoupper( substr (get_post_type_object( $value->post_type )->labels->name, 0, 3 ) ); */
        $aTMP []= (int) $value->n_total_records;
        $aTMP []= (int) $toterr;
        $aTMP []= (int) round($pererr * 100, 0);
        $aTMP []= (int) $value->n_total_records - $maxerr;
        $aTBL []= $aTMP;
    }
    
	echo csl_build_table(
		'statusTableUE', 
		$aHDR, 
		$aTBL, 
		__( 'Summarized errors by user', CSL_TEXT_DOMAIN_PREFIX ),
		__( 'NOTICE: The value of E-Rate is not a direct percentage of total errors on the total records, since the same record can contain more than one error. It is the average of the single rates for each error type.', CSL_TEXT_DOMAIN_PREFIX )
	);
	
    $legend = array(
    	__( 'Duplicated', CSL_TEXT_DOMAIN_PREFIX ) => __( 'Possible duplicated title', CSL_TEXT_DOMAIN_PREFIX ),
        __( 'Mandatory', CSL_TEXT_DOMAIN_PREFIX ) => __( 'Lack of one or more mandatory fields', CSL_TEXT_DOMAIN_PREFIX ),
        __( 'Geoname', CSL_TEXT_DOMAIN_PREFIX ) => __( 'Bad Geonames local entity convention (semicolons rule) in one or more fields', CSL_TEXT_DOMAIN_PREFIX ),
        __( 'Autosearch', CSL_TEXT_DOMAIN_PREFIX ) => __( 'Bad self-reference convention (number + colon rule) in one or more fields', CSL_TEXT_DOMAIN_PREFIX ),
        __( 'Taxonomy', CSL_TEXT_DOMAIN_PREFIX ) => __( 'Lack of one or more mandatory taxonomies', CSL_TEXT_DOMAIN_PREFIX ),
    );
	
	echo '<h3>' . __( 'Error types', CSL_TEXT_DOMAIN_PREFIX ) . '</h3>' . PHP_EOL;
	echo csl_array_to_list( $legend, $type = 'ul' );
	
    // Detailed errors by record type and user
	$aHDR = array(
		 __( 'User', CSL_TEXT_DOMAIN_PREFIX ),	
		 __( 'Duplicated', CSL_TEXT_DOMAIN_PREFIX ),
		 __( 'Mandatory', CSL_TEXT_DOMAIN_PREFIX ),
		 __( 'Geoname', CSL_TEXT_DOMAIN_PREFIX ),
		 __( 'Autosearch', CSL_TEXT_DOMAIN_PREFIX ),
		 __( 'Taxonomy', CSL_TEXT_DOMAIN_PREFIX ),
	);
    foreach(CSL_CUSTOM_POST_TYPE_ARRAY as $pent) {
    	$aTBL = array();	
    	foreach($errorsRec as $key => $value) {
            if($value->post_type == $pent) {
        		$aTMP = array();
                $toterr = $value->n_duplicated_title + $value->n_mandatory_fields_lack + $value->n_geonames_error + $value->n_autolookup_error + $value->n_taxonomy_lack;
                $maxerr = max(array($value->n_duplicated_title, $value->n_mandatory_fields_lack, $value->n_geonames_error, $value->n_autolookup_error, $value->n_taxonomy_lack));
                $pererr = 
                    (($value->n_duplicated_title / $value->n_total_records) +
                    ($value->n_mandatory_fields_lack / $value->n_total_records) +
                    ($value->n_geonames_error / $value->n_total_records) +
                    ($value->n_autolookup_error / $value->n_total_records) +
                    ($value->n_taxonomy_lack / $value->n_total_records)) / 5;
                $aTMP []= $value->display_name;
                $aTMP []= number_format_i18n($value->n_duplicated_title, 0);
                $aTMP []= number_format_i18n($value->n_mandatory_fields_lack, 0);
                $aTMP []= number_format_i18n($value->n_geonames_error, 0);
                $aTMP []= number_format_i18n($value->n_autolookup_error, 0);
                $aTMP []= number_format_i18n($value->n_taxonomy_lack, 0);
                $aTBL []= $aTMP;
            }
        }
    	echo csl_build_table(
    		'statusTableDET' . $pent, 
    		$aHDR, 
    		$aTBL, 
    		sprintf(__( '%s recording error details', CSL_TEXT_DOMAIN_PREFIX ),  get_post_type_object( $pent )->labels->name)
    	);
    }

	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;

	echo '</table>' . PHP_EOL;
 
}

function csl_custom_menu_spp_users_activity() {
    global $wpdb;
    global $csl_a_human_custom_fields_names;
    global $cls_a_custom_fields_nomenclature;
    global $csl_a_options;
    global $csl_s_user_is_manager;
    
    $todays_date = current_time('mysql', 1);
    $valid_post_types = '\'' . implode('\',\'', CSL_CUSTOM_POST_TYPE_ARRAY) . '\'';
    $curtim = current_time('timestamp');
    $secnonce = wp_create_nonce( NONCE_KEY );

	echo '<table class="form-table">' . PHP_EOL;

	// Users list
 	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . sprintf(__( '%s staff', CSL_TEXT_DOMAIN_PREFIX ), CSL_LOGO) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;
	
    $aROL = array(
        /* 'editor' => __( 'Coordination', CSL_TEXT_DOMAIN_PREFIX ), 
        'administrator' => __( 'Site management', CSL_TEXT_DOMAIN_PREFIX ),  */
        'author' => __( 'Contribution', CSL_TEXT_DOMAIN_PREFIX )
    );
    foreach($aROL as $rol => $rolname) {
    	$aHDR = array(
    		__( 'User', CSL_TEXT_DOMAIN_PREFIX ),
    		__( 'Published records', CSL_TEXT_DOMAIN_PREFIX ),
    	);
    	$aTBL = array();
        $aARG = array(
            'role'     => $rol,
            'meta_key' => 'last_name',
            'orderby'  => 'meta_value',
        );
    	$systemusers = get_users($aARG);
    	foreach ( $systemusers as $user ) {
    		$aTMP = array();
    		$user_activity = csl_get_user_activity_status($user->ID);
    		if( ! empty($user_activity) ) {
	    		if ($user_activity['logged_since']) {
		    		$user_activity_text = 
		    			'<span class="connected-user">' . 
		    			sprintf (
			    			__( 'Logged %s ago. Last activity %s ago, at %s.', CSL_TEXT_DOMAIN_PREFIX ),
			    			human_time_diff( $user_activity['logged_since'], current_time( 'timestamp' ) ),
			    			human_time_diff( mysql2date( 'U', $user_activity['last_activity']) , current_time( 'timestamp' ) ),
			    			strtolower( date_i18n(get_option('date_format') . ', ' . get_option('time_format'), mysql2date( 'U', $user_activity['last_activity'] ) ) )
		    			) . '</span>';
	    		} else {
		    		$user_activity_text = 
		    			'<span class="disconnected-user">' . 
		    			sprintf (
			    			__( 'Not logged. Last activity %s ago, at %s.', CSL_TEXT_DOMAIN_PREFIX ),
			    			human_time_diff( mysql2date( 'U', $user_activity['last_activity']) , current_time( 'timestamp' ) ),
			    			strtolower( date_i18n(get_option('date_format') . ', ' . get_option('time_format'), mysql2date( 'U', $user_activity['last_activity'] ) ) )
		    			) . '</span>';
	    		}
    		} else {
	    		$user_activity_text = "";
    		}
    		$aTMP []= 
    			'<table style="padding: 0; margin: 0;"><tr><td style=" width: 64px;">' . get_csl_local_avatar($user->user_email, 64) . '</td><td>' .
    			'<a href="mailto:' . $user->user_email . '">' . $user->first_name .  ' ' . $user->last_name . '</a>' . 
    			'<br />' . get_user_meta( $user->ID, 'position', true )  . '</tr></td></table>';
    		$aTMX = array();
    		foreach(CSL_CUSTOM_POST_TYPE_ARRAY as $cpt) {
	    		$nPBU = count_user_posts($user->ID, $cpt);
	    		if($nPBU > 0) {
	    			$aTMX []= 
	    				'<a href="' . admin_url('edit.php?post_type=' . $cpt . '&author=' . $user->ID) . '">' . 
	    				get_post_type_object($cpt)->labels->name . '</a>' . 
	    				' (' . number_format_i18n($nPBU, 0) . ')';
	    		}
    		}
    		$aTMP []= implode(', ', $aTMX) . '<hr />' . $user_activity_text;
    		$aTBL []= $aTMP;
    	}
    
    	echo csl_build_table(
    		'staffList' . $rol, 
    		$aHDR, 
    		$aTBL, 
    		sprintf(__( '%s staff', CSL_TEXT_DOMAIN_PREFIX ), $rolname)
    	);	
    }

	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;
	
    // Most recently updated records
	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . __( 'Last users activity', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;

	$aHDR = array(
		__( 'Time', CSL_TEXT_DOMAIN_PREFIX ),
		__( 'User', CSL_TEXT_DOMAIN_PREFIX ),
		__( 'Type', CSL_TEXT_DOMAIN_PREFIX ),
		__( 'Record', CSL_TEXT_DOMAIN_PREFIX ),
			
	);
	$aTBL = array();
    if ( $recent_posts = $wpdb->get_results( 
        "SELECT 
        ID,
        post_author, 
        post_title,
        post_modified,
        post_type 
        FROM $wpdb->posts 
        WHERE post_status = 'publish' 
        AND 
        post_modified_gmt < '$todays_date' 
        AND 
        post_type IN ($valid_post_types) 
        ORDER BY 
        post_modified_gmt DESC 
        LIMIT " . $csl_a_options['recent_records'] . ";")):

        foreach ($recent_posts as $post) {
	        $aTMP = array();
            if ($post->post_title == '') $post->post_title = sprintf( __( 'Record #%s', CSL_TEXT_DOMAIN_PREFIX ), $post->ID );
            $aTMP []= sprintf(__( '%s ago', CSL_TEXT_DOMAIN_PREFIX ), human_time_diff(strtotime($post->post_modified), current_time('timestamp'))); 
            $aTMP []= get_userdata($post->post_author)->display_name;
            $aTMP []= get_post_type_object($post->post_type)->labels->singular_name;
            $aTMP []= "<a href='".get_permalink($post->ID)."'>". wp_trim_words( $post->post_title, 5, $more = '&hellip;' ) . '</a>';
            $aTBL []= $aTMP;
        }
    endif;

	echo csl_build_table(
		'userLastActivity', 
		$aHDR, 
		$aTBL, 
		sprintf( __( '%s most recently updated records', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n($csl_a_options['recent_records'], 0))
	);

	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;

    // Posts by user
	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . __( 'Users yield', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;

	$aHDR = array(
        __( 'User', CSL_TEXT_DOMAIN_PREFIX )
    );
	foreach(CSL_CUSTOM_POST_TYPE_ARRAY as $key => $value) {
        $aHDR []= get_post_type_object($value)->labels->name;
    }
    $aHDR []= $cls_a_custom_fields_nomenclature[CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri'];
    $aHDR []= $cls_a_custom_fields_nomenclature[CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri'];
    
	$aTBL = array();
    $blogusers = get_users( array( 'orderby' => array('display_name'), 'role' => 'author' ) );
    foreach ( $blogusers as $user ) {
    	$aTMP = array();
        $aTMP []= $user->display_name;
        foreach ( CSL_CUSTOM_POST_TYPE_ARRAY as $key => $value ) {            
            $aTMP []= number_format_i18n(count_user_posts_by_type( $user->ID, $value ), 0 );
        } 
        $aTMP []= number_format_i18n(count_user_posts_by_meta_key( $user->ID, CSL_CUSTOM_POST_ENTITY_TYPE_NAME, CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri' ), 0 );
        $aTMP []= number_format_i18n(count_user_posts_by_meta_key( $user->ID, CSL_CUSTOM_POST_ENTITY_TYPE_NAME, CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri' ), 0 );
        $aTBL []= $aTMP;
    }

	echo csl_build_table(
		'userActivityStats', 
		$aHDR, 
		$aTBL, 
		__( 'Recording ranking', CSL_TEXT_DOMAIN_PREFIX )
	);

	$aHDR = array(
        __( 'User', CSL_TEXT_DOMAIN_PREFIX ),
        __( 'Work days', CSL_TEXT_DOMAIN_PREFIX ),
        __( 'Minutes', CSL_TEXT_DOMAIN_PREFIX ),
        __( 'Time', CSL_TEXT_DOMAIN_PREFIX ),
        __( 'Activities', CSL_TEXT_DOMAIN_PREFIX ),
    );
   
	$aTBL = array();
    foreach ( csl_get_users_activities_stats(true) as $user ) {
	    if(csl_get_user_roles((int)$user['user_id']) == 'author') {
	    	$aTMP = array();
	        $aTMP []= $user['display_name'];
	        $aTMP []= number_format_i18n($user['num_dates'], 0 );
	        $aTMP []= number_format_i18n($user['time_diff'], 0 );
	        $aTMP []= $user['human_time_diff'];
	        $aTMP []= number_format_i18n($user['num_activities'], 0 );
	        $aTBL []= $aTMP;
	    }
    }

	echo csl_build_table(
		'userTimetable', 
		$aHDR, 
		$aTBL, 
		__( 'Summarized user timetable', CSL_TEXT_DOMAIN_PREFIX )
	);

	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;


    // Operations
	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . __( 'Operations', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;

    echo '<p>';
    echo '<a class="button button-secondary" href="' . admin_url('/admin-ajax.php?action=csl_generic_ajax_call&q=exporterrorsxls&x=' . get_current_user_id()) . '&s=' . $secnonce . '">' . 
        	__('Activity (errors included)', CSL_TEXT_DOMAIN_PREFIX) . 
        	'</a>';
        echo '&nbsp;';
    echo '<a class="button button-secondary" href="' . admin_url('/admin-ajax.php?action=csl_generic_ajax_call&q=exporterrurisxls&x=' . get_current_user_id()) . '&s=' . $secnonce . '">' . 
        	__( 'Invalid RSS URIs', CSL_TEXT_DOMAIN_PREFIX ) . 
        	'</a>&nbsp;';
    echo '<a class="button button-secondary" href="' . 
            admin_url('admin-ajax.php?action=csl_generic_ajax_call&amp;q=exportactivitiesxls') . 
            '&s=' . $secnonce .
            (!$csl_s_user_is_manager ? '&x=' . get_current_user_id() : '') . 
            '">' . 
            __('Low-level activity log', CSL_TEXT_DOMAIN_PREFIX) .  
            '</a>' . 
            '&nbsp;' . 
            '<a class="button button-secondary" href="' . 
            admin_url('admin-ajax.php?action=csl_generic_ajax_call&amp;q=exportsessionsxls') . 
            '&s=' . $secnonce .
            (!$csl_s_user_is_manager ? '&x=' . get_current_user_id() : '') . 
            '">' . 
            __('Sessions log', CSL_TEXT_DOMAIN_PREFIX) .  
            '</a>&nbsp;';
    if($csl_s_user_is_manager) {
	    echo '<a class="button button-primary" href="' . admin_url('/admin-ajax.php?action=csl_generic_ajax_call&q=exportuactxls&x=' . get_current_user_id()) . '&s=' . $secnonce . '">' . 
	        	__( 'Users timetable', CSL_TEXT_DOMAIN_PREFIX ) . 
	        	'</a>';	    
    }           
    echo '</p>';
    
	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;
	echo '</table>' . PHP_EOL;

}    

function csl_custom_menu_spp_evolution() {
    global $wpdb;
    global $csl_a_human_custom_fields_names;
    global $csl_a_options;
    
    $todays_date = current_time('mysql', 1);
    $valid_post_types = '\'' . implode('\',\'', CSL_CUSTOM_POST_TYPE_ARRAY) . '\'';
    $justnow = current_time('timestamp');
	$secnonce = wp_create_nonce( NONCE_KEY );

    $aStatus = csl_count_for_target_status();

    $expected_date = $justnow + $aStatus['current_status']['target_probably_end'];
    
    $totalposts = wp_count_posts('entity')->publish + wp_count_posts('exhibition')->publish;

	echo '<table class="form-table">' . PHP_EOL;

	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . __( 'Duration and time', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;
	
	csl_project_status_screen();
	
	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;


    // Gauge table
	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . __( 'Completion level', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;

	echo '<table class="widefat" cellspacing="0" style="margin-top: 10px;">' . PHP_EOL;
	echo '<tbody>' . PHP_EOL;
	echo '<tr><td =style="padding: 10px;" class="alternate">' . PHP_EOL;
	echo '<p class="meter-title">' . __( 'Completion degree', CSL_TEXT_DOMAIN_PREFIX ) . '</p>';

	echo csl_completion_degree_bar(
		__( 'Global completion level', CSL_TEXT_DOMAIN_PREFIX ),
		round($aStatus['current_status']['current_valued_ops_per'] * 100, 0),
		round($aStatus['current_status']['current_valued_ops_due_per'] * 100, 0)
	);

	echo csl_completion_degree_bar(
		__( 'Entities completion level', CSL_TEXT_DOMAIN_PREFIX ),
		round($aStatus['current_status']['0_per'] * 100, 0),
		round($aStatus['current_status']['0_due'] * 100, 0)
	);

	echo csl_completion_degree_bar(
		__( 'Exhibitions completion level', CSL_TEXT_DOMAIN_PREFIX ),
		round($aStatus['current_status']['1_per'] * 100, 0),
		round($aStatus['current_status']['1_due'] * 100, 0)
	);

	echo csl_completion_degree_bar(
		__( 'RSS URIs completion level', CSL_TEXT_DOMAIN_PREFIX ),
		round($aStatus['current_status']['2_per'] * 100, 0),
		round($aStatus['current_status']['2_due'] * 100, 0)
	);

	echo csl_completion_degree_bar(
		__( 'HTML URIs completion level', CSL_TEXT_DOMAIN_PREFIX ),
		round($aStatus['current_status']['3_per'] * 100, 0),
		round($aStatus['current_status']['3_due'] * 100, 0)
	);

	echo '</td></tr>' . PHP_EOL;
		
	$quarters_time_value = round($aStatus['target_dates']['project_duration'] / 5, 0);
	$quarters_nums_value = round($aStatus['current_status']['target_valued_ops_num'] / 4, 0);
	
	echo '<tr><td =style="padding: 10px;">' . PHP_EOL;
	echo '		
		<p class="meter-title">' . __( 'Project timeline', CSL_TEXT_DOMAIN_PREFIX ) . '</p>
		<div class="history-tl-container">
			<div class="overlaydue" style="height: ' . round($aStatus['current_status']['current_valued_ops_due_per'] * 100, 0) . '% !important;"></div>
			<div class="overlay" style="height: ' . round($aStatus['current_status']['current_valued_ops_per'] * 100, 0) . '% !important;"></div>
			<div class="timeline-text"> 
			<div class="legend-cur"></div>' .
			sprintf(__( '%s completion. %s records', CSL_TEXT_DOMAIN_PREFIX ),
				number_format_i18n($aStatus['current_status']['current_valued_ops_per'] * 100, 0) . '%',
				number_format_i18n($aStatus['current_status']['current_valued_ops_num'], 0)	
			) . 
			'&nbsp;
			<div class="legend-due"></div>' .
			sprintf(__( '%s completion. %s records', CSL_TEXT_DOMAIN_PREFIX ),
				number_format_i18n($aStatus['current_status']['current_valued_ops_due_per'] * 100, 0) . '%',
				number_format_i18n($aStatus['current_status']['current_valued_ops_due'], 0)	
			) . 
			'</div>
			<ul class="tl">
				<li class="tl-item start" ng-repeat="item in retailer_history">
					<div class="timestamp">' . 
						ucfirst(date_i18n(get_option( 'date_format' ), $aStatus['target_dates']['project_start'])) . 
						'</div>
					<div class="item-title">' . __( 'Project start', CSL_TEXT_DOMAIN_PREFIX ) . '</div>
					<div class="item-detail">' . 
						sprintf(__( 'Range: from %s to %s records', CSL_TEXT_DOMAIN_PREFIX ),
							number_format_i18n(0, 0),	
							number_format_i18n($quarters_nums_value, 0)	
						) . 
						'</div>
				</li>
				<li class="tl-item" ng-repeat="item in retailer_history">
					<div class="timestamp">' . 
						ucfirst(date_i18n(get_option( 'date_format' ), $aStatus['target_dates']['project_start'] + $quarters_time_value)) . 
						'</div>
					<div class="item-title">' . __( 'Project first quarter', CSL_TEXT_DOMAIN_PREFIX ) . '</div>
					<div class="item-detail">' . 
						sprintf(__( 'Range: from %s to %s records', CSL_TEXT_DOMAIN_PREFIX ),
							number_format_i18n($quarters_nums_value, 0),	
							number_format_i18n($quarters_nums_value * 2, 0)	
						) . 
						'</div>
				</li>
				<li class="tl-item" ng-repeat="item in retailer_history">
					<div class="timestamp">' . 
						ucfirst(date_i18n(get_option( 'date_format' ), $aStatus['target_dates']['project_start'] + ($quarters_time_value * 2))) . 
						'</div>
					<div class="item-title">' . __( 'Project second quarter', CSL_TEXT_DOMAIN_PREFIX ) . '</div>
					<div class="item-detail">' . 
						sprintf(__( 'Range: from %s to %s records', CSL_TEXT_DOMAIN_PREFIX ),
							number_format_i18n($quarters_nums_value * 2, 0),	
							number_format_i18n($quarters_nums_value * 3, 0)	
						) . 
						'</div>
				</li>
				<li class="tl-item" ng-repeat="item in retailer_history">
					<div class="timestamp">' . 
						ucfirst(date_i18n(get_option( 'date_format' ), $aStatus['target_dates']['project_start'] + ($quarters_time_value * 3))) . 
						'</div>
					<div class="item-title">' . __( 'Project third quarter', CSL_TEXT_DOMAIN_PREFIX ) . '</div>
					<div class="item-detail">' . 
						sprintf(__( 'Range: from %s to %s records', CSL_TEXT_DOMAIN_PREFIX ),
							number_format_i18n($quarters_nums_value * 3, 0),	
							number_format_i18n($quarters_nums_value * 4, 0)	
						) . 
						'</div>
				</li>
				<li class="tl-item end" ng-repeat="item in retailer_history">
					<div class="timestamp">' . 
						ucfirst(date_i18n(get_option( 'date_format' ), $aStatus['target_dates']['project_end'])) . 
						'</div>
					<div class="item-title">' . __( 'Project end', CSL_TEXT_DOMAIN_PREFIX ) . '</div>
					<div class="item-detail">' . 
						sprintf(__( 'Must be published %s records at the end', CSL_TEXT_DOMAIN_PREFIX ),
							number_format_i18n($aStatus['current_status']['target_valued_ops_num'], 0)	
						) . 
						'</div>
				</li>
			</ul>
		</div>
	';	
	echo '</td></tr></tbody></table>' . PHP_EOL;
	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;

    // Status table 1
	$aHDR = array(
		__( 'Type', CSL_TEXT_DOMAIN_PREFIX ),	
		__( 'Target', CSL_TEXT_DOMAIN_PREFIX ),	
		__( 'Current', CSL_TEXT_DOMAIN_PREFIX ),	
		__( 'Due', CSL_TEXT_DOMAIN_PREFIX ),	
		__( 'Diff', CSL_TEXT_DOMAIN_PREFIX ),	
		__( 'Trend', CSL_TEXT_DOMAIN_PREFIX ),	
	);

	$aTBL = array();
    foreach($aStatus['target_records'] as $key => $value) {
        if(strpos($key,'_') == false) {
    		$aTMP = array();
	        $aTMP []= get_post_type_object($key)->labels->name;
	        $aTMP []= number_format_i18n($value, 0);
        } else {
            switch(substr($key, -4)) {
                case '_num':
                case '_due':
                    $aTMP []= number_format_i18n($value, 0);
                    break;
                case '_dif':
                    $aTMP []= number_format_i18n($value, 0);
                    $aTMP []= $value >= 0 
                    	? 
                    	'<span class="dashicons dashicons-arrow-up statusMark-OK"></span>' 
                    	: 
                    	'<span class="dashicons dashicons-arrow-down statusMark-ERROR"></span>';
					$aTBL []= $aTMP;
                    break;
                default:
                    break;
            }
        }
    }
	foreach($aStatus['target_fields'] as $key => $value) {
        if(!array_key_exists(substr($key, -4), array('_num' => 0, '_due' => 0, '_dif' => 0, '_rng' => 0, '_sta' => 0))) {
    		$aTMP = array();
	        $aTMP []= $aStatus['target_fields_human_name'][$key];
	        $aTMP []= number_format_i18n($value, 0);
       } else {
            switch(substr($key, -4)) {
                case '_num':
                case '_due':
                    $aTMP []= number_format_i18n($value, 0);
                    break;
                case '_dif':
                    $aTMP []= number_format_i18n($value, 0);
                    $aTMP []= $value >= 0 
                    	? 
                    	'<span class="dashicons dashicons-arrow-up statusMark-OK"></span>' 
                    	: 
                    	'<span class="dashicons dashicons-arrow-down statusMark-ERROR"></span>';
					$aTBL []= $aTMP;
                    break;
                default:
                    break;
            }
        }
    }	
	
	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . __( 'Performance', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;
	echo csl_build_table(
		'statusTable1', 
		$aHDR, 
		$aTBL, 
		__( 'Recording & time performance', CSL_TEXT_DOMAIN_PREFIX )
	);
	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;

   	// Status table 4 (Beagle capture stats)
	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . __( 'Beagle automatic task', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;

	$aHDR = array(
		__( 'Date', CSL_TEXT_DOMAIN_PREFIX ),
		__( 'Valid URIs', CSL_TEXT_DOMAIN_PREFIX ),
		__( 'Checked entries', CSL_TEXT_DOMAIN_PREFIX ),
		__( 'Sapefull URIs', CSL_TEXT_DOMAIN_PREFIX ),
		__( 'Sapefull entries', CSL_TEXT_DOMAIN_PREFIX ),
		__( 'Added entries', CSL_TEXT_DOMAIN_PREFIX ),
		__( 'Discarded entries', CSL_TEXT_DOMAIN_PREFIX ),
			
	);
	$aTBL = array();
    $totalposts = wp_count_posts('entity')->publish;
    $args = array(
	    'fields' => array('log_date','valid_uris','checked_entries','sapless_uris','sapfull_entries','added_entries','discarded_entries'),
		'orderby' => 'datetime',
		'order' => 'desc',
		'number' => $csl_a_options['top_records']    
    );
    $terms = csl_get_beaglecr_logs($args);
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
        foreach ( $terms as $term ) {
            $aTMP = array();
            $aTMP []= sprintf(__( '%s ago', CSL_TEXT_DOMAIN_PREFIX ), human_time_diff(strtotime($term->log_date), current_time('timestamp')));
            
            //echo '<th scope="row" style="">' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'),  strtotime($term->log_date)) . '</th>';
            $aTMP []= number_format_i18n($term->valid_uris, 0);
            $aTMP []= number_format_i18n($term->checked_entries, 0);
            $aTMP []= number_format_i18n($term->valid_uris - $term->sapless_uris, 0);
            $aTMP []= number_format_i18n($term->sapfull_entries, 0);
            $aTMP []= number_format_i18n($term->added_entries, 0);
            $aTMP []= number_format_i18n($term->discarded_entries, 0);
            $aTBL []= $aTMP;
        }
    }
	
	echo csl_build_table(
		'statusTable4', 
		$aHDR, 
		$aTBL, 
		sprintf(__( '%s last logs of Beagle', CSL_TEXT_DOMAIN_PREFIX ),  number_format_i18n($csl_a_options['top_records'], 0))
	);

	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;

    // Global view
	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . __( 'Global view. Entities', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;
	csl_google_maps_clustered_map(  $title = '', $title_level = '3', $type = CSL_ENTITIES_DATA_PREFIX ); 
	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;

	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . __( 'Global view. Exhibitions', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;
	csl_google_maps_clustered_map(  $title = '', $title_level = '3', $type = CSL_EXHIBITIONS_DATA_PREFIX ); 
	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;

    
}
        
function csl_custom_menu_spp_system_maintenance() {
	global $wpdb;
	$secnonce = wp_create_nonce( NONCE_KEY );

	// Operations post $_REQUEST
	$resultmessage = '';
	if(isset($_REQUEST['o'])) {
		switch($_REQUEST['o']) {
			case 'c1':
				$resultnumber = 0;
				$atbfld = array(
					array('tb' => $wpdb->posts, 'fl' => 'post_title'),
					array('tb' => $wpdb->posts, 'fl' => 'post_excerpt'),
					array('tb' => $wpdb->posts, 'fl' => 'post_content'),
					array('tb' => $wpdb->postmeta, 'fl' => 'meta_value'),
					array('tb' => $wpdb->terms, 'fl' => 'name'),
				);
				$asrchr = array(
					array('se' => 'â€œ', 're' => '“'),
					array('se' => 'â€', 're' => '”'),
					array('se' => 'â€™', 're' => '’'),
					array('se' => 'â€˜', 're' => '‘'),
					array('se' => 'â€”', 're' => '–'),
					array('se' => 'â€“', 're' => '—'),
					array('se' => 'â€¢', 're' => '-'),
					array('se' => 'â€¦', 're' => '…'),
				);
				foreach($atbfld as $key => $value) {
					foreach($asrchr as $k => $v) {
						$resultnumber += $wpdb->query("
							UPDATE " . $value['tb'] . " SET " . $value['fl'] ." = REPLACE(" . $value['fl'] .", '" . $v['se'] ."', '" . $v['re'] ."');
						");	
					}
				}
				$resultmessage .= '<div class="updated below-h2">';	
				$resultmessage .= '<p>';	
				$resultmessage .= '<span class="dashicons dashicons-admin-generic"></span>&nbsp;';
				$resultmessage .= sprintf(
					__( '%s records updated.', CSL_TEXT_DOMAIN_PREFIX ),
					number_format_i18n($resultnumber, 0)
				);
				$resultmessage .= '</p>';	
				$resultmessage .= '</div>';
				break;
			case 'c2':
				$resultnumber = 0;
				$resultnumber += $wpdb->query("
					DELETE a
					FROM 
						{$wpdb->terms} a 
					WHERE 
						a.term_id IN (SELECT term_id FROM {$wpdb->term_taxonomy} tt WHERE count = 0 );
						
				");
				$resultnumber += $wpdb->query("
					DELETE a
					FROM 
						{$wpdb->term_taxonomy} a 
					WHERE 
						a.term_id NOT IN (SELECT term_id FROM {$wpdb->terms} t);
				");
				$resultnumber += $wpdb->query("
					DELETE a
					FROM 
						{$wpdb->term_relationships} a
					WHERE 
						a.term_taxonomy_id NOT IN (SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} tt);
				");
				$resultmessage .= '<div class="updated below-h2">';	
				$resultmessage .= '<p>';	
				$resultmessage .= '<span class="dashicons dashicons-admin-generic"></span>&nbsp;';
				$resultmessage .= sprintf(
					__( '%s records updated.', CSL_TEXT_DOMAIN_PREFIX ),
					number_format_i18n($resultnumber, 0)
				);
				$resultmessage .= '</p>';	
				$resultmessage .= '</div>';
				break;
			case 'c3':
				$resultnumber = 0;
				$atbfld = array(
					array('tb' => $wpdb->terms),
					array('tb' => $wpdb->term_taxonomy),
					array('tb' => $wpdb->term_relationships),
					array('tb' => $wpdb->posts),
					array('tb' => $wpdb->postmeta),
					array('tb' => $wpdb->users),
					array('tb' => $wpdb->usermeta),
					array('tb' => $wpdb->xtr_activity_log),
					array('tb' => $wpdb->xtr_beaglecr_log),
					array('tb' => $wpdb->xtr_urierror_log),
					array('tb' => $wpdb->options),
				);
				foreach($atbfld as $key => $value) {
					$resultnumber += $wpdb->query("
						OPTIMIZE TABLE " . $value['tb'] .";
					");	
				}
				$resultmessage .= '<div class="updated below-h2">';	
				$resultmessage .= '<p>';	
				$resultmessage .= '<span class="dashicons dashicons-admin-generic"></span>&nbsp;';
				$resultmessage .= sprintf(
					__( '%s table optimization operations executed.', CSL_TEXT_DOMAIN_PREFIX ),
					number_format_i18n($resultnumber, 0)
				);
				$resultmessage .= '</p>';	
				$resultmessage .= '</div>';
				break;
			case 'c4':
				set_time_limit ( 0 );
				$resultnumberss = 0;
				$resultnumbersh = 0;
				$sql = "
			        TRUNCATE {$wpdb->xtr_nbc_b8_wordlist}; 
			    ";
			    $results = $wpdb->get_results($sql);
				$sql = "
			        INSERT IGNORE INTO {$wpdb->xtr_nbc_b8_wordlist} (token, count_ham) VALUES ('b8*dbversion', '3');
			    ";
			    $results = $wpdb->get_results($sql);
				$sql = "
			        INSERT IGNORE INTO {$wpdb->xtr_nbc_b8_wordlist} (token, count_ham, count_spam) VALUES ('b8*texts', '0', '0');       
			    ";
			    $results = $wpdb->get_results($sql);
				$sql = "
			        SELECT 
			        	TRIM(CONCAT(p.post_title, ' ', p.post_excerpt, ' ', p.post_content)) AS text,
			        	'SH' AS label
			        FROM 
			        	{$wpdb->posts} p
			        WHERE
			        	p.post_status = 'publish'
			        	AND
			        	p.post_type = 'exhibition'
			        UNION ALL
			        SELECT 
			        	TRIM(CONCAT(p.post_title, ' ', p.post_excerpt, ' ', p.post_content)) AS text,
			        	'SS' AS label
			        FROM 
			        	{$wpdb->posts} p
			        WHERE
			        	p.post_status = 'trash'
			        	AND
			        	p.post_type = 'exhibition'
			        ;       
			    ";
			    foreach($wpdb->get_results($sql) as $key => $value) {
					$dump = csl_naive_bayesian_classification($value->text, $value->label);	
					$resultnumberss += $value->label == 'SS' ? 1 : 0;			    
					$resultnumbersh += $value->label == 'SH' ? 1 : 0;			    
			    }
				$resultmessage .= '<div class="updated below-h2">';	
				$resultmessage .= '<p>';	
				$resultmessage .= '<span class="dashicons dashicons-admin-generic"></span>&nbsp;';
				$resultmessage .= sprintf(
					__( 'NBC table initialized. %s elements learned as invalid, %s as valid.', CSL_TEXT_DOMAIN_PREFIX ),
					number_format_i18n($resultnumberss, 0),
					number_format_i18n($resultnumbersh, 0)
				);
				$resultmessage .= '</p>';	
				$resultmessage .= '</div>';
                break;
			case 'c5':
				$resultnumber = 0;
				$resultnumber += $wpdb->query("
					DELETE 
					FROM 
						{$wpdb->prefix}xtr_dashboard_chat_log 
				");
				$resultmessage .= '<div class="updated below-h2">';	
				$resultmessage .= '<p>';	
				$resultmessage .= '<span class="dashicons dashicons-admin-generic"></span>&nbsp;';
				$resultmessage .= sprintf(
					__( '%s records updated.', CSL_TEXT_DOMAIN_PREFIX ),
					number_format_i18n($resultnumber, 0)
				);
				$resultmessage .= '</p>';	
				$resultmessage .= '</div>';
				break;
			default:
				break;
		}
	}

	$aDBInfo = csl_database_size();
	$datasrv = shell_exec('uptime');
	$uptime = explode(' up ', $datasrv);
	$uptime = explode(',', $uptime[1]);
	$uptime = $uptime[0].', '.$uptime[1];
	$uptime = csl_normalize_uptime_dates($uptime);
	$loadav = sys_getloadavg();

	echo $resultmessage;
		
	echo '<table class="form-table">' . PHP_EOL;

    // System health
	echo '<tr valign="top">' . PHP_EOL;
	echo '<th scope="row"><label>' . __( 'System health', CSL_TEXT_DOMAIN_PREFIX ) . '</label></th>' . PHP_EOL;
	echo '<td>' . PHP_EOL;

	$aTBL = array(
		array(__( 'OS', CSL_TEXT_DOMAIN_PREFIX ), php_uname()),
		array(__( 'Time since last boot', CSL_TEXT_DOMAIN_PREFIX ), $uptime),
		array(__( 'PHP version', CSL_TEXT_DOMAIN_PREFIX ), phpversion()),
		array(__( 'DB space', CSL_TEXT_DOMAIN_PREFIX ), 
			__( 'In use', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . size_format((int) $aDBInfo[0]['db_size']) . ' / ' . 
			__( 'Free', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . 
			csl_colorize_up_down(
				size_format((int) $aDBInfo[0]['free_space']),
				(int) $aDBInfo[0]['free_space'],
				(int) $aDBInfo[0]['db_size'],
				true,
				false
			)),
		array(__( 'Disk space', CSL_TEXT_DOMAIN_PREFIX ), 
			__( 'Total', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . size_format(disk_total_space(ABSPATH)) . ' / ' . 
			__( 'Free', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . size_format(disk_free_space(ABSPATH))),
		array(__( 'Memory', CSL_TEXT_DOMAIN_PREFIX ), 
			__( 'In use', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . size_format(memory_get_usage()) . ' / ' . 
			__( 'Peak', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . size_format(memory_get_peak_usage())),
		array(__( 'Framework', CSL_TEXT_DOMAIN_PREFIX ), 
			'<a href="https://es.wordpress.org/" target="_blank">WPAF</a> v' . get_bloginfo('version')),
		array(__( 'Admin', CSL_TEXT_DOMAIN_PREFIX ), 
			'<a href="mailto:' . get_bloginfo('admin_email') . '">' . get_bloginfo('admin_email') . '</a>'),
		array(__( 'Application', CSL_TEXT_DOMAIN_PREFIX ), 
			CSL_LOGO . ' v' . CSL_VERSION),
		array(__( 'Language', CSL_TEXT_DOMAIN_PREFIX ), 
			get_bloginfo('language') . ' ' . get_bloginfo('charset')),
		array(__( 'Running processes', CSL_TEXT_DOMAIN_PREFIX ), 
			'<span id="txtPROC">' . number_format_i18n(csl_askapache_get_process_count(), 0) . '</span>',
		),
		array(__( 'Server load', CSL_TEXT_DOMAIN_PREFIX ), 
			__( 'Last minute', CSL_TEXT_DOMAIN_PREFIX ) . ': <span class="legend-sm-cpu1"></span><span id="txtCPU1">' . number_format_i18n($loadav[0] * 100, 0) . '%</span><br>' . 
			__( 'Last five minutes', CSL_TEXT_DOMAIN_PREFIX ) . ': <span class="legend-sm-cpu5"></span><span id="txtCPU5">' . number_format_i18n($loadav[1] * 100, 0) . '%</span><br>' . 
			__( 'Last fifteen minutes', CSL_TEXT_DOMAIN_PREFIX ) . ': <span class="legend-sm-cpu15"></span><span id="txtCPU15">' . number_format_i18n($loadav[2] * 100, 0) . '%</span>'),
        array(
			__( 'Load chart', CSL_TEXT_DOMAIN_PREFIX ),
            '<div><canvas id="rtServerChart" width="400" height="200"></canvas></div>',
        ),
		array(__( 'Beagle status', CSL_TEXT_DOMAIN_PREFIX ), 
			wp_next_scheduled( 'beagle_hook' ) ? 
				'<span class="text-green" style="font-weight: bolder;">' . __( 'ACTIVE', CSL_TEXT_DOMAIN_PREFIX ) . '</span>' : 
				'<span class="text-red" style="font-weight: bolder;">' . __( 'INACTIVE', CSL_TEXT_DOMAIN_PREFIX ) . '</span>'),
        array(
			__( 'Beagle next run', CSL_TEXT_DOMAIN_PREFIX ),
            date_i18n( get_option ('date_format') . ' H:i:s', wp_next_scheduled( 'beagle_hook' ))),
        array(
			__( 'Beagle schedule', CSL_TEXT_DOMAIN_PREFIX ),
            human_schedule_periods(wp_get_schedule( 'beagle_hook' ))),
        array(
			__( 'Beagle operations', CSL_TEXT_DOMAIN_PREFIX ),
            '<a class="button button-secondary" href="' . admin_url() . '/index.php?page=csl_settings">' . 
			__( 'Change settings', CSL_TEXT_DOMAIN_PREFIX ) . '</a>&nbsp;' . 
			'<a class="button button-secondary thickbox" href="' . get_template_directory_uri() . '/_csl_cron_beagle.php?TB_iframe=true&height=600&width=750" ' . 
			'title="' . __( 'Beagle task direct execution', CSL_TEXT_DOMAIN_PREFIX ) . '">' . 
			__( 'Launch task', CSL_TEXT_DOMAIN_PREFIX ) . '</a>'
		),
        array(
			__( 'Housekeeping', CSL_TEXT_DOMAIN_PREFIX ),
        	'<a class="button button-secondary" href="' . admin_url() . '/index.php?page=csl_app_status&tab=system_maintenance&o=c1">' . 
			__( 'Normalize chars', CSL_TEXT_DOMAIN_PREFIX ) . '</a>&nbsp;' .   
        	'<a class="button button-secondary" href="' . admin_url() . '/index.php?page=csl_app_status&tab=system_maintenance&o=c2">' . 
			__( 'Clean taxonomies', CSL_TEXT_DOMAIN_PREFIX ) . '</a>&nbsp;' .   
        	'<a class="button button-secondary" href="' . admin_url() . '/index.php?page=csl_app_status&tab=system_maintenance&o=c3">' . 
			__( 'Optimize tables', CSL_TEXT_DOMAIN_PREFIX ) . '</a>&nbsp;' .  
			'<p class="description">' . 
			__( 'Be sure to back up data before launch these tasks.', CSL_TEXT_DOMAIN_PREFIX ) . '</p>'	
        ),
         array(
			__( 'No regular duties', CSL_TEXT_DOMAIN_PREFIX ),
        	'<a class="button button-secondary" href="' . admin_url() . '/index.php?page=csl_app_status&tab=system_maintenance&o=c4">' . 
			__( 'Initialize NBC table', CSL_TEXT_DOMAIN_PREFIX ) . '</a>&nbsp;' .  
        	'<a class="button button-secondary" href="' . admin_url() . '/index.php?page=csl_app_status&tab=system_maintenance&o=c5">' . 
			__( 'Clean chat history', CSL_TEXT_DOMAIN_PREFIX ) . '</a>&nbsp;' .  
			'<p class="description">' . 
			__( 'Be sure to back up data before launch these tasks.', CSL_TEXT_DOMAIN_PREFIX ) . '</p>'	
        ),
       array(
			__( 'Data export', CSL_TEXT_DOMAIN_PREFIX ),
			__( 'Format', CSL_TEXT_DOMAIN_PREFIX ) . 
			':&nbsp;<select id="expFormat">' . 
			'<option value="xls" selected>Microsoft&trade; Excel&reg;</option>' . 
			'<option value="csv">CSV</option>' . 
			'<option value="tsv">TSV</option>' . 
			'<select>&nbsp;' . 
			'<a rel="nofollow" class="button button-secondary" onClick="cslExport(\'entities\');">' . 
			__( 'Entities', CSL_TEXT_DOMAIN_PREFIX ) . '</a>&nbsp;' . 
			'<a rel="nofollow" class="button button-secondary" onClick="cslExport(\'exhibitions\');">' . 
			__( 'Exhibitions', CSL_TEXT_DOMAIN_PREFIX ) . '</a>' . 
			'<script type="text/javascript">' . 
			'function cslExport(ptype) {' . 
			'var e=document.getElementById(\'expFormat\');' . 
			'var a=e.options[e.selectedIndex].value;' . 
			'window.location.href=\'' . admin_url( 'admin-ajax.php' ) . 
			'?action=csl_generic_ajax_call&q=export\' + ptype + a + \'&s=' . $secnonce . '\'' . 
			'}</script>'
        ),
	);

	echo csl_build_table(
		'systemHealth', 
		NULL, 
		$aTBL, 
		NULL
	);

	echo '</td>' . PHP_EOL;
	echo '</tr>' . PHP_EOL;
	echo '</table>' . PHP_EOL;

}

// Remove WP logo from Admin bar
function csl_no_wp_logo_admin_bar_remove() {        
    ?>
        <style type="text/css">
            #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
                content: url(<?php echo get_stylesheet_directory_uri(); ?>/assets/img/csl-ab-logo.png)   !important;
                top: 2px;
            }

            #wpadminbar #wp-admin-bar-wp-logo > a.ab-item {
                pointer-events: none;
                cursor: default;
            }
            img.alignright {float:right; margin:0 0 1em 1em}
            img.alignleft {float:left; margin:0 1em 1em 0}
            img.aligncenter {display: block; margin-left: auto; margin-right: auto}
            a img.alignright {float:right; margin:0 0 1em 1em}
            a img.alignleft {float:left; margin:0 1em 1em 0}
            a img.aligncenter {display: block; margin-left: auto; margin-right: auto}
        </style>
    <?php
}
add_action('wp_before_admin_bar_render', 'csl_no_wp_logo_admin_bar_remove', 0);

// CSL Settings
// Register settings
function csl_settings_register(){
    register_setting( 'csl_settings', 'csl_settings', 'csl_sanitize_settings' );
}
 
// Add settings page to menu
function csl_settings_add() {
    add_submenu_page(
        'index.php',
        CSL_NAME . ': ' . __( 'Settings', CSL_TEXT_DOMAIN_PREFIX ),
        __( 'Settings', CSL_TEXT_DOMAIN_PREFIX ), 
        'edit_others_posts', 
        'csl_settings', 
        'csl_custom_menu_settings_page_paint', 
        'dashicons-info', 
        3
    ); 
}

// Sanitize settings
function csl_sanitize_settings( $input ) {
    foreach($input as $key => &$value) {
        switch($key) {
            case 'project_start_date':
            case 'project_end_date':
                $value = strtotime($value);
                break;
            case 'error_rate':
                $value = $value / 100;
                break;
            case 'beagle_next_exec_date':
                $value = strtotime($value);
                break;
            default:
                break;
        }
    }
    return $input;
}

// Add actions
add_action( 'admin_init', 'csl_settings_register' );
add_action( 'admin_menu', 'csl_settings_add' );
 
// Settings page painting on screen
function csl_custom_menu_settings_page_paint() {
	global $color_scheme;
    global $csl_a_default_options;
    
    $curtim = current_time('timestamp');
	 
	if ( ! isset( $_REQUEST['updated'] ) )
		$_REQUEST['updated'] = false;
	 
    echo '<div class="wrap">' . PHP_EOL;
    echo '<h2>' . get_admin_page_title() . '</h2>';
	?>
	<div class="wrap"> 
		<form method="post" action="options.php" id="settingsForm">
			<?php settings_fields( 'csl_settings', $csl_a_default_options ); ?>
<?php 

$options  = get_option( 'csl_settings' );
$duration = $options['project_end_date'] - $options['project_start_date'];
$percent  = round((($curtim - $options['project_start_date']) / $duration)  * 100, 0);
$beagplan = isset($options['beagle_schedule_plan']) ? $options['beagle_schedule_plan'] : 'daily';

echo '<p>';
echo sprintf(
    __( 'From this settings screen you can configure the way %s performs calculations to assess the performance of the team and the progress of the project %s development.', CSL_TEXT_DOMAIN_PREFIX ),
    '<strong>' . CSL_LOGO . '</strong>',
    '<strong>' . CSL_PROJECT_NAME . '</strong>'
);
echo '</p>';
echo '<p>';
echo sprintf(
    __( 'According to the specified parameters, the total duration of the project is %s, and the midpoint for review is %s. The temporal evolution determines that %s is at %s of its full development.', CSL_TEXT_DOMAIN_PREFIX ),
    '<strong>' . human_time_diff($curtim, $curtim + $duration) . '</strong>',
    '<strong>' . strtolower(date_i18n(get_option( 'date_format' ), $options['project_start_date'] + round($duration / 2, 0))) . '</strong>',
    '<strong>' . CSL_PROJECT_NAME . '</strong>',
    '<strong>' . $percent . '%</strong>'
);
echo '</p>';
?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="csl_settings[project_name]"><?php _e( 'Project name', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-text">
						<p>
						<input type="text" placeholder="<?php _e( 'Project name', CSL_TEXT_DOMAIN_PREFIX ); ?>" class="regular-text" id="csl_settings[project_name]" name="csl_settings[project_name]" value="<?php esc_attr_e( $options['project_name'] ); ?>" size="30">
						</p>
						<p class="description"><?php echo __( 'Enter the name of the project.', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[project_start_date]"><?php _e( 'Project start and end dates', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-text">
						<p>
						<input type="text" placeholder="<?php _e( 'Start date', CSL_TEXT_DOMAIN_PREFIX ); ?>" class="datepicker" id="csl_settings[project_start_date]" name="csl_settings[project_start_date]" value="<?php echo date( 'Y-m-d', (int) $options['project_start_date'] ); ?>" size="30">
						<input type="text" placeholder="<?php _e( 'End date', CSL_TEXT_DOMAIN_PREFIX ); ?>" class="datepicker" id="csl_settings[project_end_date]" name="csl_settings[project_end_date]" value="<?php echo date( 'Y-m-d', (int) $options['project_end_date'] ); ?>" size="30">
						</p>
						<p class="description"><?php echo __( 'Enter the start and end dates of the project.', CSL_TEXT_DOMAIN_PREFIX ) . CSL_FIELD_MARK_DATE; ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[news_to_show]"><?php _e( 'News to show', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-values">
						<p>
						<input type="range" class="rangevalues" id="csl_settings[news_to_show]" name="csl_settings[news_to_show]" value="<?php esc_attr_e( $options['news_to_show'] ); ?>" step="5" min="0" max="100" style="width: 50%;" oninput="val_news_to_show.value=parseInt(this.value).toLocaleString()" />
						<output id="val_news_to_show"><?php esc_attr_e( $options['news_to_show'] ); ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the number of news to show in Dashboard (between 0 and 100).', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[recent_records]"><?php _e( 'Most recent records to show', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-values">
						<p>
						<input type="range" class="rangevalues" id="csl_settings[recent_records]" name="csl_settings[recent_records]" value="<?php esc_attr_e( $options['recent_records'] ); ?>" step="5" min="0" max="100" style="width: 50%;" oninput="val_recent_records.value=parseInt(this.value).toLocaleString()" />
						<output id="val_recent_records"><?php esc_attr_e( $options['recent_records'] ); ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the number of most recent records to show in Dashboard (between 0 and 100).', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[top_records]"><?php _e( 'Top N records to show', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-values">
						<p>
						<input type="range" class="rangevalues" id="csl_settings[top_records]" name="csl_settings[top_records]" value="<?php esc_attr_e( $options['top_records'] ); ?>" step="5" min="0" max="100" style="width: 50%;" oninput="val_top_records.value=parseInt(this.value).toLocaleString()" />
						<output id="val_top_records"><?php esc_attr_e( $options['top_records'] ); ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the number of top records to show in Dashboard (between 0 and 100).', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[error_rate]"><?php _e( 'Admissible error rate', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-values">
						<p>
						<input type="range" class="rangevalues" id="csl_settings[error_rate]" name="csl_settings[error_rate]" value="<?php echo (float) $options['error_rate'] * 100; ?>" step="5" min="0" max="100" style="width: 50%;" oninput="val_error_rate.value=parseInt(this.value).toLocaleString()" />
						<output id="val_error_rate"><?php echo (float) $options['error_rate'] * 100; ?></output>%
						</p>
						<p class="description"><?php echo __( 'Select the maximum admissible error rate in recording processes (between 0 and 100).', CSL_TEXT_DOMAIN_PREFIX );?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[beagle_global_threshold]"><?php _e( 'Beagle global threshold', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-beagle">
						<p>
						<input type="range" class="rangebeagle" id="csl_settings[beagle_global_threshold]" name="csl_settings[beagle_global_threshold]" value="<?php echo (int) $options['beagle_global_threshold']; ?>" step="5" min="0" max="50" style="width: 50%;" oninput="val_beagle_global_threshold.value=parseInt(this.value).toLocaleString()" />
						<output id="val_beagle_global_threshold"><?php echo (int) $options['beagle_global_threshold']; ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the maximum global threshold for Beagle (between 0 and 10).', CSL_TEXT_DOMAIN_PREFIX );?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[beagle_relative_threshold]"><?php _e( 'Beagle relative threshold', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-beagle">
						<p>
						<input type="range" class="rangebeagle" id="csl_settings[beagle_relative_threshold]" name="csl_settings[beagle_relative_threshold]" value="<?php echo (int) $options['beagle_relative_threshold']; ?>" step="1" min="0" max="10" style="width: 50%;" oninput="val_beagle_relative_threshold.value=parseInt(this.value).toLocaleString()" />
						<output id="val_beagle_relative_threshold"><?php echo (int) $options['beagle_relative_threshold']; ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the maximum relative threshold for Beagle (between 0 and 10).', CSL_TEXT_DOMAIN_PREFIX );?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[beagle_isactive]"><?php _e( 'Scheduled run of Beagle', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-beagle">
						<p>
						<input type="checkbox" id="csl_settings[beagle_isactive]" name="csl_settings[beagle_isactive]" value="1"<?php echo isset($options['beagle_isactive']) ? 'checked="checked"' : ''; ?> />
						&nbsp;
						<?php echo isset($options['beagle_isactive']) ? '<span class="text-green"><strong>' . __( 'Scheduled run: ACTIVE', CSL_TEXT_DOMAIN_PREFIX ) . '</strong></span>' : '<span class="text-red"><strong>' . __( 'Scheduled run: INACTIVE', CSL_TEXT_DOMAIN_PREFIX ) . '</strong></span>'; ?>
						</p>
						<p class="description"><?php echo __( 'Switch to automatically run or not run Beagle.', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[beagle_next_exec_date]"><?php _e( 'Beagle next scheduled run', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-beagle">
						<p>
						<input class="datepicker" id="csl_settings[beagle_next_exec_date]" name="csl_settings[beagle_next_exec_date]" type="text" value="<?php echo date('Y-m-d', $options['beagle_next_exec_date']); ?>"/>
						</p>
						<p class="description"><?php echo __( 'Select the next scheduled date for run Beagle (at 00:00:00).', CSL_TEXT_DOMAIN_PREFIX );?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[beagle_schedule_plan]"><?php _e( 'Beagle schedule plan', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-beagle">
						<p>
						<select id="csl_settings[beagle_schedule_plan]" name="csl_settings[beagle_schedule_plan]">
							<option value="hourly"<?php echo 'hourly' == $beagplan ? ' selected' : ''; ?>><?php _e( 'Hourly', CSL_TEXT_DOMAIN_PREFIX); ?></option>
							<option value="daily"<?php echo 'daily' == $beagplan ? ' selected' : ''; ?>><?php _e( 'Daily', CSL_TEXT_DOMAIN_PREFIX); ?></option>
							<option value="twicedaily"<?php echo 'twicedaily' == $beagplan ? ' selected' : ''; ?>><?php _e( 'Twice daily', CSL_TEXT_DOMAIN_PREFIX); ?></option>
							<option value="fourtimesdaily"<?php echo 'fourtimesdaily' == $beagplan ? ' selected' : ''; ?>><?php _e( 'Four times a day', CSL_TEXT_DOMAIN_PREFIX); ?></option>
							<option value="weekly"<?php echo 'weekly' == $beagplan ? ' selected' : ''; ?>><?php _e( 'Once weekly', CSL_TEXT_DOMAIN_PREFIX); ?></option>
							<option value="monthly"<?php echo 'monthly' == $beagplan ? ' selected' : ''; ?>><?php _e( 'Once a month', CSL_TEXT_DOMAIN_PREFIX); ?></option>
						</select> 
						</p>
						<p class="description"><?php echo __( 'Select the appropriate schedule plan to run Beagle.', CSL_TEXT_DOMAIN_PREFIX );?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[entities_target]"><?php _e( 'Entities target', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-target">
						<p>
						<input type="range" class="rangetarget" id="csl_settings[entities_target]" name="csl_settings[entities_target]" value="<?php esc_attr_e( $options['entities_target'] ); ?>" step="500" min="0" max="100000" style="width: 50%;" oninput="val_entities_target.value=parseInt(this.value).toLocaleString()" />
						<output id="val_entities_target"><?php echo number_format_i18n( $options['entities_target'], 0 ); ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the entities target to get at the end of the project (between 0 and 100,000).', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[persons_target]"><?php _e( 'People target', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-target">
						<p>
						<input type="range" class="rangetarget" id="csl_settings[persons_target]" name="csl_settings[persons_target]" value="<?php esc_attr_e( $options['persons_target'] ); ?>" step="500" min="0" max="100000" style="width: 50%;" oninput="val_persons_target.value=parseInt(this.value).toLocaleString()" />
						<output id="val_persons_target"><?php echo number_format_i18n( $options['persons_target'], 0 ); ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the people target to get at the end of the project (between 0 and 100,000).', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[papers_target]"><?php _e( 'Papers target', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-target">
						<p>
						<input type="range" class="rangetarget" id="csl_settings[papers_target]" name="csl_settings[papers_target]" value="<?php esc_attr_e( $options['papers_target'] ); ?>" step="100" min="0" max="10000" style="width: 50%;" oninput="val_papers_target.value=parseInt(this.value).toLocaleString()" />
						<output id="val_papers_target"><?php echo number_format_i18n( $options['papers_target'], 0 ); ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the papers target to get at the end of the project (between 0 and 10,000).', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[companies_target]"><?php _e( 'Companies target', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-target">
						<p>
						<input type="range" class="rangetarget" id="csl_settings[companies_target]" name="csl_settings[companies_target]" value="<?php esc_attr_e( $options['companies_target'] ); ?>" step="10" min="0" max="5000" style="width: 50%;" oninput="val_companies_target.value=parseInt(this.value).toLocaleString()" />
						<output id="val_companies_target"><?php echo number_format_i18n( $options['companies_target'], 0 ); ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the companies target to get at the end of the project (between 0 and 5,000).', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[exhibitions_target]"><?php _e( 'Exhibitions target', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-target">
						<p>
						<input type="range" class="rangetarget" id="csl_settings[exhibitions_target]" name="csl_settings[exhibitions_target]" value="<?php esc_attr_e( $options['exhibitions_target'] ); ?>" step="500" min="0" max="100000" style="width: 50%;" oninput="val_exhibitions_target.value=parseInt(this.value).toLocaleString()" />
						<output id="val_exhibitions_target"><?php echo number_format_i18n( $options['exhibitions_target'], 0 ); ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the exhibitions target to get at the end of the project (between 0 and 100,000).', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[rss_uris_target]"><?php _e( 'RSS URIs target', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-target">
						<p>
						<input type="range" class="rangetarget" id="csl_settings[rss_uris_target]" name="csl_settings[rss_uris_target]" value="<?php esc_attr_e( $options['rss_uris_target'] ); ?>" step="500" min="0" max="100000" style="width: 50%;" oninput="val_rss_uris_target.value=parseInt(this.value).toLocaleString()" />
						<output id="val_rss_uris_target"><?php echo number_format_i18n( $options['rss_uris_target'], 0 ); ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the RSS URIs target to get at the end of the project (between 0 and 100,000).', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="csl_settings[html_uris_target]"><?php _e( 'HTML URIs target', CSL_TEXT_DOMAIN_PREFIX ); ?></label></th>
					<td class="settings-target">
						<p>
						<input type="range" class="rangetarget" id="csl_settings[html_uris_target]" name="csl_settings[html_uris_target]" value="<?php esc_attr_e( $options['html_uris_target'] ); ?>" step="500" min="0" max="100000" style="width: 50%;" oninput="val_html_uris_target.value=parseInt(this.value).toLocaleString()" />
						<output id="val_html_uris_target"><?php echo number_format_i18n( $options['html_uris_target'], 0 ); ?></output>
						</p>
						<p class="description"><?php echo __( 'Select the HTML URIs target to get at the end of the project (between 0 and 100,000).', CSL_TEXT_DOMAIN_PREFIX ); ?></p>
					</td>
				</tr>

			</table>
			<p><?php submit_button(); ?></p>
		</form>
	 
	</div><!-- END wrap -->
	 
	<?php
}

if(CSL_ENABLE_LINKS_MENU) {	
    add_filter( 'pre_option_link_manager_enabled', '__return_true' );
}

//////////////////////////////////////////////
// Admin bar customization
function csl_admin_bar_customization() {
	global $wp_admin_bar;
	if (!current_user_can( 'edit_others_posts' )) {    
    	$wp_admin_bar->remove_node( 'new-post' );
	}
	if (!current_user_can( 'manage_options' )) {    
		// Remove toolbar nodes
		$wp_admin_bar->remove_menu('comments');
    	//$wp_admin_bar->remove_node( 'new-post' );
    	$wp_admin_bar->remove_node( 'new-media' );
    	$wp_admin_bar->remove_node( 'new-page' );
    	$wp_admin_bar->remove_node( 'new-user' );
		// Remove update notification
		add_filter('pre_site_transient_update_core', create_function('$a', "return null;"));
    }
	$wp_admin_bar->remove_node( 'about' );
	$wp_admin_bar->remove_node( 'wporg' );
	$wp_admin_bar->remove_node( 'documentation' );
	$wp_admin_bar->remove_node( 'support-forums' );
	$wp_admin_bar->remove_node( 'feedback' );
	$args = array(
        'parent'=> 'wp-logo',
		'id'    => 'iarthis_lab_page',
		'title' => __( 'iArtHis_LAB', CSL_TEXT_DOMAIN_PREFIX ),
		'href'  => __( 'http://iarthis.hdplus.es/', CSL_TEXT_DOMAIN_PREFIX ),
		'meta'  => array( 'target' => '_blank', 'title' => __( 'Go to iArtHis_LAB site', CSL_TEXT_DOMAIN_PREFIX ) )
	);
	$wp_admin_bar->add_node( $args );    
	$args = array(
        'parent'=> 'wp-logo',
		'id'    => 'project_management_page',
		'title' => __( 'Project management', CSL_TEXT_DOMAIN_PREFIX ),
		'href'  => __( 'http://projects-tracking.hdplus.es/', CSL_TEXT_DOMAIN_PREFIX ),
		'meta'  => array( 'target' => '_blank', 'title' => __( 'Go to project management site', CSL_TEXT_DOMAIN_PREFIX ) )
	);
	$wp_admin_bar->add_node( $args );    
}
add_action('wp_before_admin_bar_render', 'csl_admin_bar_customization');

function csl_redirect_dashboard() {
    if( is_admin() ) {
        $screen = get_current_screen();
        if( $screen->base == 'dashboard' ) {
            wp_redirect( admin_url( 'index.php?page=csl_app_status' ) );
        }
    }
}
add_action('load-index.php', 'csl_redirect_dashboard', 1, 0 );

function csl_change_dashboard_start_admin_menu() {
    global $submenu;
    
    if(!current_user_can('edit_others_posts')) {
        remove_menu_page( 'edit.php' );                   //Posts
	}    
    if(!current_user_can('manage_options')) {
        remove_submenu_page( 'index.php', 'index.php' );
        remove_menu_page( 'upload.php' );                 //Media
        remove_menu_page( 'edit.php?post_type=page' );    //Pages
        remove_menu_page( 'edit-comments.php' );          //Comments
        remove_menu_page( 'themes.php' );                 //Appearance
        remove_menu_page( 'plugins.php' );                //Plugins
        remove_menu_page( 'users.php' );                  //Users
        remove_menu_page( 'tools.php' );                  //Tools
        remove_menu_page( 'options-general.php' );        //Settings
    }
}  
add_action( 'admin_menu', 'csl_change_dashboard_start_admin_menu' );

// Remove "emojis" to avoid jQuery and Javascript errors
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

/*
Plugin Name: Chrome Admin Menu Fix
Description: Quick fix for the Chrome 45 admin menu display glitches
Author: Steve Jones for The Space Between / Samuel Wood / Otto42
Author URI: http: //the--space--between.com
Version: 2.1.0
*/

function chromefix_inline_css() { 
	wp_add_inline_style( 'wp-admin', '#adminmenu { transform: translateZ(0); }' );
}
add_action('admin_enqueue_scripts', 'chromefix_inline_css');

?>
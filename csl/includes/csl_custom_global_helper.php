<?php

/**
 * Class Name: wp_bootstrap_navwalker
 * GitHub URI: https://github.com/twittem/wp-bootstrap-navwalker
 * Description: A custom WordPress nav walker class to implement the Bootstrap 3 navigation style in a custom theme using the WordPress built in menu manager.
 * Version: 2.0.4
 * Author: Edward McIntyre - @twittem
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
class wp_bootstrap_navwalker extends Walker_Nav_Menu {
	/**
	 * @see Walker::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul role=\"menu\" class=\" dropdown-menu\">\n";
	}
	/**
	 * @see Walker::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		/**
		 * Dividers, Headers or Disabled
		 * =============================
		 * Determine whether the item is a Divider, Header, Disabled or regular
		 * menu item. To prevent errors we use the strcasecmp() function to so a
		 * comparison that is not case sensitive. The strcasecmp() function returns
		 * a 0 if the strings are equal.
		 */
		if ( strcasecmp( $item->attr_title, 'divider' ) == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="divider">';
		} else if ( strcasecmp( $item->title, 'divider') == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="divider">';
		} else if ( strcasecmp( $item->attr_title, 'dropdown-header') == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="dropdown-header">' . esc_attr( $item->title );
		} else if ( strcasecmp($item->attr_title, 'disabled' ) == 0 ) {
			$output .= $indent . '<li role="presentation" class="disabled"><a href="#">' . esc_attr( $item->title ) . '</a>';
		} else {
			$class_names = $value = '';
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes[] = 'menu-item-' . $item->ID;
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
			if ( $args->has_children )
				$class_names .= ' dropdown';
			if ( in_array( 'current-menu-item', $classes ) )
				$class_names .= ' active';
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
			$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';
			$output .= $indent . '<li' . $id . $value . $class_names .'>';
			$atts = array();
			$atts['title']  = ! empty( $item->title )	? $item->title	: '';
			$atts['target'] = ! empty( $item->target )	? $item->target	: '';
			$atts['rel']    = ! empty( $item->xfn )		? $item->xfn	: '';
			// If item has_children add atts to a.
			if ( $args->has_children && $depth === 0 ) {
				$atts['href']   		= '#';
				$atts['data-toggle']	= 'dropdown';
				$atts['class']			= 'dropdown-toggle';
				$atts['aria-haspopup']	= 'true';
			} else {
				$atts['href'] = ! empty( $item->url ) ? $item->url : '';
			}
			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );
			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}
			$item_output = $args->before;
			/*
			 * Glyphicons
			 * ===========
			 * Since the the menu item is NOT a Divider or Header we check the see
			 * if there is a value in the attr_title property. If the attr_title
			 * property is NOT null we apply it as the class name for the glyphicon.
			 */
			if ( ! empty( $item->attr_title ) )
				$item_output .= '<a'. $attributes .'><span class="glyphicon ' . esc_attr( $item->attr_title ) . '"></span>&nbsp;';
			else
				$item_output .= '<a'. $attributes .'>';
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			$item_output .= ( $args->has_children && 0 === $depth ) ? ' <span class="caret"></span></a>' : '</a>';
			$item_output .= $args->after;
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}
	/**
	 * Traverse elements to create list from elements.
	 *
	 * Display one element if the element doesn't have any children otherwise,
	 * display the element and its children. Will only traverse up to the max
	 * depth and no ignore elements under that depth.
	 *
	 * This method shouldn't be called directly, use the walk() method instead.
	 *
	 * @see Walker::start_el()
	 * @since 2.5.0
	 *
	 * @param object $element Data object
	 * @param array $children_elements List of elements to continue traversing.
	 * @param int $max_depth Max depth to traverse.
	 * @param int $depth Depth of current element.
	 * @param array $args
	 * @param string $output Passed by reference. Used to append additional content.
	 * @return null Null on failure with no changes to parameters.
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
        if ( ! $element )
            return;
        $id_field = $this->db_fields['id'];
        // Display this element.
        if ( is_object( $args[0] ) )
           $args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
	/**
	 * Menu Fallback
	 * =============
	 * If this function is assigned to the wp_nav_menu's fallback_cb variable
	 * and a manu has not been assigned to the theme location in the WordPress
	 * menu manager the function with display nothing to a non-logged in user,
	 * and will add a link to the WordPress menu manager if logged in as an admin.
	 *
	 * @param array $args passed from the wp_nav_menu function.
	 *
	 */
	public static function fallback( $args ) {
		if ( current_user_can( 'manage_options' ) ) {
			extract( $args );
			$fb_output = null;
			if ( $container ) {
				$fb_output = '<' . $container;
				if ( $container_id )
					$fb_output .= ' id="' . $container_id . '"';
				if ( $container_class )
					$fb_output .= ' class="' . $container_class . '"';
				$fb_output .= '>';
			}
			$fb_output .= '<ul';
			if ( $menu_id )
				$fb_output .= ' id="' . $menu_id . '"';
			if ( $menu_class )
				$fb_output .= ' class="' . $menu_class . '"';
			$fb_output .= '>';
			$fb_output .= '<li><a href="' . admin_url( 'nav-menus.php' ) . '">Add a menu</a></li>';
			$fb_output .= '</ul>';
			if ( $container )
				$fb_output .= '</' . $container . '>';
			echo $fb_output;
		}
	}
}


/**
 * WordPress Bootstrap Pagination
 */
function csl_bootstrap_pagination( $pages = '', $range = 4 ) {  
     $showitems = ($range * 2) + 1;  
     global $paged;
 
	 $lblFirst = __('First', CSL_TEXT_DOMAIN_PREFIX );
	 $lblLast = __('Last', CSL_TEXT_DOMAIN_PREFIX );
	 $lblPrevious = __('Previous', CSL_TEXT_DOMAIN_PREFIX );
	 $lblNext = __('Next', CSL_TEXT_DOMAIN_PREFIX );

     if(empty($paged)) $paged = 1;

     if($pages == '') {
         global $wp_query; 
		 $pages = $wp_query->max_num_pages;
         if(!$pages) {
             $pages = 1;
          }
     }   

     if(1 != $pages) {
        echo '<div class="text-center">'; 
        echo '<nav><ul class="pagination"><li class="disabled hidden-xs"><span><span aria-hidden="true">';
        echo sprintf( 
        	__('Page %s of %s', CSL_TEXT_DOMAIN_PREFIX ), 
        	number_format_i18n($paged, 0), 
        	number_format_i18n($pages, 0) 
        ); 

        echo '</span></span></li>';
		if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<li><a href='".get_pagenum_link(1)."' aria-label='$lblFirst'>&laquo;<span class='hidden-xs'> $lblFirst</span></a></li>";
		if($paged > 1 && $showitems < $pages) echo "<li><a href='".get_pagenum_link($paged - 1)."' aria-label='$lblPrevious'>&lsaquo;<span class='hidden-xs'> $lblPrevious</span></a></li>";
		for ($i=1; $i <= $pages; $i++) {
			if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )) {
				echo ($paged == $i)? "<li class=\"active\"><span>".$i." <span class=\"sr-only\">(current)</span></span></li>":"<li><a href='".get_pagenum_link($i)."'>".$i."</a></li>";
			}
		}
		if ($paged < $pages && $showitems < $pages) echo "<li><a href=\"".get_pagenum_link($paged + 1)."\"  aria-label='$lblNext'><span class='hidden-xs'>$lblNext </span>&rsaquo;</a></li>";  
		if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<li><a href='".get_pagenum_link($pages)."' aria-label='$lblLast'><span class='hidden-xs'>$lblLast </span>&raquo;</a></li>";
		echo "</ul></nav>";
		echo "</div>";
	}
}

/**
 * Load stats functions
 */
if ( ! function_exists( 'csl_load_stats_printing' ) ) :
	function csl_load_stats_printing( ) {
		$timer_stop 		= timer_stop(0);
		$query_count 		= get_num_queries();
		$memory_usage 		= round( size_format( memory_get_usage() ), 2 );
		$memory_peak_usage 	= round( size_format( memory_get_peak_usage() ), 2 );
		$memory_limit 		= round( size_format( csl_letter_to_number_size( WP_MEMORY_LIMIT ) ), 2 );
	
		$tplout  = '<span class="label label-default"><i class="fa fa-database"></i> %s</span> <span class="label label-default"><i class="fa fa-clock-o"></i> %s</span> <span class="label label-default"><i class="fa fa-area-chart"></i> %s/%s (%s)</span>';
	    $output  = '<span id="loadStats">';
	    $output .= sprintf( 
	    	__( $tplout, CSL_TEXT_DOMAIN_PREFIX ), 
	    	$query_count, 
	    	$timer_stop, 
	    	$memory_usage, 
	    	$memory_limit, 
	    	round( ( $memory_usage / $memory_limit ), 2 ) * 100 . '%' 
	    );
	    return $output;
	}
endif;


/**
 * Utility functions
 */

if ( ! function_exists( 'csl_get_season_prefix' ) ) :
	function csl_get_season_prefix() {
       // Locate the icons
       $icons = array(
               "spring" => "sp",
               "summer" => "su",
               "autumn" => "fa",
               "winter" => "wi"
       );

       // What is today's date - number
       $day = date("z");

       //  Days of spring
       $spring_starts = date("z", strtotime("March 21"));
       $spring_ends   = date("z", strtotime("June 20"));

       //  Days of summer
       $summer_starts = date("z", strtotime("June 21"));
       $summer_ends   = date("z", strtotime("September 22"));

       //  Days of autumn
       $autumn_starts = date("z", strtotime("September 23"));
       $autumn_ends   = date("z", strtotime("December 20"));

       //  If $day is between the days of spring, summer, autumn, and winter
       if( $day >= $spring_starts && $day <= $spring_ends ) :
               $season = "spring";
       elseif( $day >= $summer_starts && $day <= $summer_ends ) :
               $season = "summer";
       elseif( $day >= $autumn_starts && $day <= $autumn_ends ) :
               $season = "autumn";
       else :
               $season = "winter";
       endif;

       return $icons[$season];
	}
endif;

// Number between range
if( ! function_exists( 'csl_numbers_between' ) ) :
function csl_numbers_between( $n, $a, $b ) {
    return ! is_numeric( $n ) ? false : ( $n - $a ) * ( $n - $b ) <= 0;
}
endif;

// Letters size to numeric value translation
if ( ! function_exists( 'csl_letter_to_number_size' ) ) :
	function csl_letter_to_number_size( $size ) {
	    $l 		 = substr( $size, -1 );
	    $ret 	 = substr( $size, 0, -1 );
	    switch( strtoupper( $l ) ) {
		    case 'P':
		        $ret *= 1024;
		    case 'T':
		        $ret *= 1024;
		    case 'G':
		        $ret *= 1024;
		    case 'M':
		        $ret *= 1024;
		    case 'K':
		        $ret *= 1024;
	    }
	    return $ret;
	}
endif;

// Client IP functions
if ( ! function_exists( 'csl_get_client_ip' ) ) :
    function csl_get_client_ip() {
	    $ipaddress = '';
	    if (getenv('HTTP_CLIENT_IP'))
	        $ipaddress = getenv('HTTP_CLIENT_IP');
	    else if(getenv('HTTP_X_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	    else if(getenv('HTTP_X_FORWARDED'))
	        $ipaddress = getenv('HTTP_X_FORWARDED');
	    else if(getenv('HTTP_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_FORWARDED_FOR');
	    else if(getenv('HTTP_FORWARDED'))
	        $ipaddress = getenv('HTTP_FORWARDED');
	    else if(getenv('REMOTE_ADDR'))
	        $ipaddress = getenv('REMOTE_ADDR');
	    else
	        $ipaddress = 'UNKNOWN';
	 
	    return $ipaddress;
    }
endif;

// Flush buffer actions
if ( ! function_exists( 'csl_initialize_buffer' ) ) :
    /**
     * csl_initialize_buffer function.
     * 
     * @access public
     * @return void
     */
    function csl_initialize_buffer() {
    	ob_start();
    }
endif;

if ( ! function_exists( 'csl_flush_buffer' ) ) :
    /**
     * csl_flush_buffer function.
     * 
     * @access public
     * @return void
     */
    function csl_flush_buffer() {
    	if ( 0 < ob_get_level() )
    		ob_flush();
    }
endif;

add_action( 'init', 'csl_initialize_buffer', 0 );		
add_action( 'wp_footer', 'csl_flush_buffer', 100 );
add_action( 'admin_footer', 'csl_flush_buffer', 100 );	 						  

if ( !function_exists( 'csl_output_buffer_flush' ) ) :
	/**
	 * csl_output_buffer_flush function.
	 * 
	 * @access public
	 * @return void
	 */
	function csl_output_buffer_flush(){
	    echo str_pad('', 512);
	    echo ' ';
	    if(ob_get_length()){
	        @ob_flush();
	        @flush();
	        @ob_end_flush();
	    }
	    @ob_start();
	}
endif;


if ( ! function_exists( 'csl_microtime_float' ) ) :
	/**
	 * csl_microtime_float function.
	 * 
	 * @access public
	 * @return void
	 */
	function csl_microtime_float() {
		list($usec, $sec) = explode(' ', microtime());
		return((float) $usec + (float) $sec);
	}
endif;

if ( ! function_exists( 'csl_string_contains_array_element' ) ) :
    /**
     * csl_string_contains_array_element function.
     * 
     * @access public
     * @param mixed $str
     * @param mixed $arr
     * @return void
     */
    function csl_string_contains_array_element($str, $arr) {
        $ptn = '';
        foreach ($arr as $s) {
            if ($ptn != '') $ptn .= '|';
            $ptn .= preg_quote($s, '/');
        }
        return preg_match("/$ptn/i", $str);
    }
endif;

if ( ! function_exists( 'csl_sort_multiarray' ) ) :
	/**
	 * csl_sort_multiarray function.
	 * 
	 * @access public
	 * @param mixed $array
	 * @param mixed $key
	 * @return void
	 */
	function csl_sort_multiarray ($arr, $index) {
	    $b = array();
	    $c = array();
	    foreach ($arr as $key => $value) {
	        $b[$key] = $value[$index];
	    }
	    asort($b);
	    foreach ($b as $key => $value) {
	        $c[] = $arr[$key];
	    }
	    return $c;
	} 
endif;

/**
 * User functions
 **/

/**
 * Handles sending password retrieval email to user.
 * Based in a work by Clay in "Hungred Dot Com" (http://hungred.com/how-to/resend-username-password-user-wordpress-wpmail/).
 *
 * @uses $wpdb WordPress Database object
 *
 * @return bool|WP_Error True: when finish. WP_Error on error
 */
function csl_retrieve_password($user_email) {
	global $wpdb, $current_site;

	$errors = new WP_Error();

	// redefining user_login ensures we return the right case in the email
	$user_login = $user_email;

	do_action('retreive_password', $user_login);  // Misspelled and deprecated
	do_action('retrieve_password', $user_login);



	$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
	if ( empty($key) ) {
		// Generate something random for a key...
		$key = wp_generate_password(20, false);
		do_action('retrieve_password_key', $user_login, $key);
		// Now insert the new md5 key into the db
		$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
	}
	$message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
	$message .= network_site_url() . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
	$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
	$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

	if ( is_multisite() )
		$blogname = $GLOBALS['current_site']->site_name;
	else
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$title = sprintf( __('[%s] Password Reset'), $blogname );

	$title = apply_filters('retrieve_password_title', $title);
	$message = apply_filters('retrieve_password_message', $message, $key);

	if ( $message && !wp_mail($user_email, $title, $message) )
		wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );

	return true;
}

function csl_user_initials ( $user_id ) {
    if(!$user_id) return __('N/A', CSL_TEXT_DOMAIN_PREFIX);
	if(get_userdata( $user_id )->first_name) $first_name = explode( ' ', get_userdata( $user_id )->first_name );
	if(get_userdata( $user_id )->last_name) $last_name = explode( ' ', get_userdata( $user_id )->last_name );
	$initials = '';
    if(get_userdata( $user_id )->first_name) {
    	foreach( $first_name as $fn ) {
    		$initials .= substr( $fn, 0, 1 );	
    	}
    }
    if(get_userdata( $user_id )->first_name) {
    	foreach( $last_name as $ln ) {
    		$initials .= substr( $ln, 0, 1 );	
    	}
    }
	return strtoupper( remove_accents( $initials ) );	
} 

/**
 * Returns the translated role of the current user. If that user has
 * no role for the current blog, it returns false.
 *
 * @return string The name of the current role
 **/
function csl_get_current_user_role( $translated_name = true ) {
	global $wp_roles;
	$current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$role = array_shift($roles);
	return isset($wp_roles->role_names[$role]) ? ( $translated_name ? translate_user_role($wp_roles->role_names[$role] ) : $role ): false;
}


/**
 * Format functions
 **/
 
/**
 * csl_build_table function.
 * 
 * @access public
 * @param mixed $cID
 * @param mixed $aHeaders (default: NULL)
 * @param mixed $aData
 * @param mixed $title (default: NULL)
 * @param bool $footer_note (default: false)
 * @param bool $include_footer (default: false)
 * @return void
 */
function csl_build_table( $cID, $aHeaders = NULL, $aData, $title = NULL, $footer_note = false, $include_footer = false ){
	if(count($aData) == 0) {
		return false;
	}
    $aAlign = array();
    $i = 0;
    foreach($aData[0] as $key => $value) {        
        $aAlign[$i] = is_numeric(str_replace(':', '', str_replace('%', '', str_replace(',','',$value)))) 
        	? 
        	"style=\"text-align: right; padding: 10px;\"" 
        	: 
        	"style=\"text-align: left; padding: 10px;\"";
        if(strpos($value, 'class="dashicons') !== false) {
	    	$aAlign[$i] = str_replace(': left', ': center', $aAlign[$i]);
        }
        $i++;        
    }
    $html  = '';
    $html .= '<table id="' . $cID . '" class="table widefat" cellspacing="0">' . PHP_EOL;
    $html .= !is_null($title) ? '<caption class="cls-autotable"><h4>' . $title . '</h4></caption>' . PHP_EOL : '';
    if(!is_null($aHeaders)) {
	    $html .= '<thead>' . PHP_EOL;
	    $html .= '<tr>' . PHP_EOL;    
	    for($i = 0; $i < count($aHeaders); $i++) {
	        $html .= '<th ' .  ($i == 0 ? 'class="manage-column" ' : '') . $aAlign[$i] . '>' . $aHeaders[$i] .  '</th>' . PHP_EOL;         
	    }
	    $html .= '</tr>' . PHP_EOL;
	    $html .= '</thead>' . PHP_EOL;
        if($include_footer) {
    	    $html .= '<tfoot>' . PHP_EOL;
    	    $html .= '<tr>' . PHP_EOL;    
    	    for($i = 0; $i < count($aHeaders); $i++) {
    	        $html .= '<th ' .  ($i == 0 ? 'class="manage-column" ' : '') . $aAlign[$i] . '>' . $aHeaders[$i] .  '</th>' . PHP_EOL;         
    	    }
    	    $html .= '</tr>' . PHP_EOL;
    	    $html .= '</tfoot>' . PHP_EOL;
	        if($footer_note) {
	    	    $html .= '<tr>' . PHP_EOL;    
	    	    $html .= '<th></th><th class="tablefootnote" colspan="' .  (count($aHeaders) - 1) . '">' . $footer_note .  '</th>' . PHP_EOL;         
	    	    $html .= '</tr>' . PHP_EOL;
	        }
        }
        if($footer_note) {
    	    $html .= '<tfoot>' . PHP_EOL;
    	    $html .= '<tr>' . PHP_EOL;    
    	    $html .= '<th></th><th class="tablefootnote" colspan="' .  (count($aHeaders) - 1) . '">' . $footer_note .  '</th>' . PHP_EOL;         
    	    $html .= '</tr>' . PHP_EOL;
    	    $html .= '</tfoot>' . PHP_EOL;
        }
	}
    $html .= '<tbody>' . PHP_EOL;

    $i = 1;
    foreach($aData as $key => $value){        
        $html .= '<tr' . ($i % 2 == 0 ? '' : ' class="alternate"') . '>' . PHP_EOL;
        $j = 0;
        foreach($value as $key2=>$value2){
            $html .= ($j == 0 ? '<th class="manage-column" scope="row"' : '<td') . ' ' . $aAlign[$j] . '>' . $value2 . ($j == 0 ? '</th>' : '</td>') . PHP_EOL;
            $j++;
        }
        $html .= '</tr>' . PHP_EOL;
        $i++;
    }
    
    $html .= '</tbody>' . PHP_EOL;
    $html .= '</table>' . PHP_EOL;
    return $html;
}	

// Get taxonomies terms links
if ( ! function_exists( 'csl_custom_taxonomies_terms_links' ) ) :
	/**
	 * csl_custom_taxonomies_terms_links function.
	 * 
	 * @access public
	 * @param mixed $post_id
	 * @param string $type (default: 'text')
	 * @return void
	 */
	function csl_custom_taxonomies_terms_links( $post_id, $type = 'text' ){
		$post = get_post( $post_id );
		$post_type = $post->post_type;
		
		// get post type taxonomies
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		
		$out = array();
		foreach ( $taxonomies as $taxonomy_slug => $taxonomy ){
		
			// get the terms related to post
			$terms = get_the_terms( $post_id, $taxonomy_slug );
			
			if ( !empty( $terms ) ) {
				$out[] = "" . $taxonomy->label . "";
                $tmp = array();
				foreach ( $terms as $term ) {
					$tmp[] =
					'<a href="'
					.    get_term_link( $term->slug, $taxonomy_slug ) .'">'
					.$term->name
					. "</a>";
				}
				$out[] = implode( ', ', $tmp );
			}
		}
		
		return implode('. ', $out );
	}
endif;

if ( !function_exists('csl_array_to_list') ) :
    /**
     * csl_array_to_list function.
     * 
     * @access public
     * @param mixed $array
     * @param string $type (default: 'ol')
     * @return void
     */
    function csl_array_to_list($array, $type = 'ol') {
        $output = "<$type>" . PHP_EOL;
        foreach ($array as $key => $value) {
            $function = is_array($value) ? __FUNCTION__ : 'trim';
            $output .= '<li><strong>' . $key . ':</strong> <em>' . $function($value) . '</em></li>';
        }
        return $output . "</$type>" . PHP_EOL;
    }
endif;

if ( !function_exists('csl_array_to_paragraph_or_br') ) :
    /**
     * csl_array_to_paragraph_or_br function.
     * 
     * @access public
     * @param mixed $array
     * @param string $type (default: 'p')
     * @return void
     */
    function csl_array_to_paragraph_or_br($array, $type = 'p') {
        $output = "" ;
        foreach ($array as $key => $value) {
            $function = is_array($value) ? __FUNCTION__ : 'trim';
            $output .= ($type == 'p' ? '<p>' : '') . $function($value) . ($type == 'p' ? '</p>' : '<br />') . PHP_EOL;
        }
        return $output;
    }
endif;

if ( ! function_exists( 'csl_format_admin_notice' ) ) :
    /**
     * csl_format_admin_notice function.
     * 
     * @access public
     * @param string $text (default: '')
     * @param string $icon (default: 'bug')
     * @param string $class (default: 'info')
     * @return void
     */
    function csl_format_admin_notice( $text = '', $icon = 'bug', $class = 'info' ) {
        $output  = "";
	    $output .= "<div class=\"notice notice-$class is-dismissible\">" . PHP_EOL;
	    $output .= "<p>" . PHP_EOL; 
	    $output .= "<i class=\"fa fa-$icon\" style=\"margin-right: 5px;\"></i>" . PHP_EOL;
		$output .= $text;
	    $output .= "</p>" . PHP_EOL; 
	    $output .= "</div>" . PHP_EOL; 

        return $output;
    }
endif;

if ( ! function_exists( 'csl_new_chat_activity_notice' ) ) :
    /**
     * csl_new_chat_activity_notice function.
     * 
     * @access public
     * @return void
     */
    function csl_new_chat_activity_notice() {
        if( current_user_can( 'edit_posts' ) ) {
    	    $new_messages = csl_get_last_chat_activity(get_current_user_id());
            echo $new_messages > 0 ? csl_format_admin_notice(
    	        sprintf(
    		    	__( '%s new message(s) in Dashboard chat since last visit. %s', CSL_TEXT_DOMAIN_PREFIX ),
    		    	number_format_i18n( $new_messages, 0 ),
    		    	'<a href="' . admin_url( '/index.php?page=csl_app_status&tab=help_and_messages' ) . '">' .
    		    	__( 'Go to Dashboard chat', CSL_TEXT_DOMAIN_PREFIX ) . '</a>'  
    	        ),
    	        'envelope-o',
    	        'info'
            ) : false;
        } else {
            return false;
        }
    }
endif;

if ( !function_exists( 'csl_set_href_for_urls_array' ) ) :
    /**
     * csl_set_href_for_urls_array function.
     * 
     * @access public
     * @param mixed $array
     * @param bool $blank_target (default: true)
     * @param bool $trim_length (default: false)
     * @param bool $ispost (default: false)
     * @param int $max_length (default: 0)
     * @return void
     */
    function csl_set_href_for_urls_array( $array, $blank_target = true, $trim_length = false, $ispost = false, $max_length = 0 ) {
        $aout = array();
        foreach($array as $key => $value) {
            $tvalue = $trim_length ? ((strlen($value) > $max_length) ? substr($value, 0, $max_length).'&hellip;' : $value) : $value;
            $lvalue = !$ispost ? $value : (strpos($value, ': ') !== false ? get_permalink((int)explode(': ', $value)[0]) : $value);
            $tvalue = !$ispost ? $tvalue : (strpos($tvalue, ': ') !== false ? explode(': ', $tvalue)[1] : $tvalue);
            $lvalue = is_email( $lvalue ) ? 'mailto:' . $lvalue : $lvalue;
            $aout []= '<a href="' . $lvalue . '"' . ($blank_target ? ' target="_blank"' : '') . '>' . $tvalue . '</a>';
        }
        return $aout;
    }
endif;

if ( !function_exists( 'csl_colorize_up_down' ) ) :
	function csl_colorize_up_down($text = '', $val = NULL, $top = NULL, $strong = true, $invert = false) {
	    if(!$val || !$top)
	    	return false;
	    $tag = $strong ? 'strong' : 'span';
	    $lev = round(($val / $top) * 100, 0);
	    if($lev < 33) {
		    $cls = !$invert ? 'green' : 'red';
	    } elseif($lev > 66) {
		    $cls = !$invert ? 'red' : 'green';
	    } else {
			$cls = 'orange';    
	    }
	    return "<$tag class=\"text-$cls\">$text</$tag>";	
	}
endif;

/**
 * Screen paint functions
 */

if ( !function_exists( 'csl_project_status_screen' ) ) :
	function csl_project_status_screen($title = null, $title_level = 3) {
	    $justnow = current_time('timestamp');
	    $aStatus = csl_count_for_target_status();
	    $expected_date = $justnow + $aStatus['current_status']['target_probably_end'];
	
		$aTBL = array(
			array(
				__( 'Project name', CSL_TEXT_DOMAIN_PREFIX ),
				CSL_PROJECT_NAME
			),
			array(
				__( 'Project start', CSL_TEXT_DOMAIN_PREFIX ),
				strtolower(date_i18n(get_option( 'date_format' ), $aStatus['target_dates']['project_start']))
			),
			array(
				__( 'Project end', CSL_TEXT_DOMAIN_PREFIX ),
				strtolower(date_i18n(get_option( 'date_format' ), $aStatus['target_dates']['project_end']))
			),
			array(
				__( 'Duration', CSL_TEXT_DOMAIN_PREFIX ),
				human_time_diff($aStatus['target_dates']['project_start'], $aStatus['target_dates']['project_end'])
			),
			array(
				__( 'Elapsed', CSL_TEXT_DOMAIN_PREFIX ),
				human_time_diff($aStatus['target_dates']['project_start'], $aStatus['target_dates']['project_current_time'])
			),
			array(
				__( 'Remaining', CSL_TEXT_DOMAIN_PREFIX ),
				human_time_diff($aStatus['target_dates']['project_current_time'], $aStatus['target_dates']['project_end'])
			),
			array(
				__( 'Current execution level', CSL_TEXT_DOMAIN_PREFIX ),
				__( 'Real', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . 
				'<span class="text-' . 
				human_value_diff_valuation($aStatus['current_status']['current_valued_ops_due_per'], $aStatus['current_status']['current_valued_ops_per'], false) . 
				'"><strong>' . 
				number_format_i18n(($aStatus['current_status']['current_valued_ops_per']) * 100, 1) . '%' . '</strong></span> / ' . 
				__( 'Due', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . 
				number_format_i18n(($aStatus['current_status']['current_valued_ops_due_per']) * 100, 1) . '%'
			),
			array(
				__( 'Average operation time', CSL_TEXT_DOMAIN_PREFIX ),
				__( 'Real', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . 
				'<span class="text-' . 
				human_value_diff_valuation($aStatus['current_status']['current_valued_ops_time_due'], $aStatus['current_status']['current_valued_ops_time'], true) . 
				'"><strong>' . 
				human_time_diff($justnow, $justnow + $aStatus['current_status']['current_valued_ops_time']) . '</strong></span> / ' . 
				__( 'Due', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . 
				human_time_diff($justnow, $justnow + $aStatus['current_status']['current_valued_ops_time_due'])
			),
			array(
				__( 'Probable end', CSL_TEXT_DOMAIN_PREFIX ),
				'<span class="text-' . 
				human_value_diff_valuation($aStatus['target_dates']['project_end'], $expected_date, true) . 
				'"><strong>' . 
				date_i18n(get_option( 'date_format' ), $expected_date) . ' (' .
				($aStatus['target_dates']['project_end'] < $expected_date ? '+' : '-') . 
				human_time_diff($aStatus['target_dates']['project_end'], $expected_date) . ' ' . 
				__( 'of scheduled time', CSL_TEXT_DOMAIN_PREFIX ) . 
				')</strong></span>'
			),
		);
		if($title) {
			echo "<h$title_level>$title</h$title_level>" . PHP_EOL;
		}
		echo csl_build_table(
			'statusTable0', 
			NULL, 
			$aTBL, 
			NULL
		);
	}
endif;

if ( !function_exists( 'csl_recording_status' ) ) :
	function csl_recording_status($title = null, $title_level = 3) {
		$aTBL = array();
		$nTPU = 0;
		$nTDR = 0;
		foreach(CSL_CUSTOM_POST_TYPE_ARRAY as $key => $value) {
			$aTMP = array();
			$oCPO = wp_count_posts( $value );
			$aTMP []= get_post_type_object( $value )->labels->name;
			$aTMP []= sprintf(
				__( '%s records published, %s in draft mode', CSL_TEXT_DOMAIN_PREFIX ),
				'<strong>' . number_format_i18n( $oCPO->publish, 0) . '</strong>',
				'<strong>' . number_format_i18n( $oCPO->draft, 0) . '</strong>'
				);
			$aTBL []= $aTMP;
			$nTPU  += $oCPO->publish;
			$nTDR  += $oCPO->draft;
		}
		$aTMP = array();
		$aTMP []= __( 'Total processed records', CSL_TEXT_DOMAIN_PREFIX );
		$aTMP []= sprintf(
			__( '%s records published, %s in draft mode', CSL_TEXT_DOMAIN_PREFIX ),
			'<strong>' . number_format_i18n( $nTPU, 0) . '</strong>',
			'<strong>' . number_format_i18n( $nTDR, 0) . '</strong>'
			);
		$aTBL []= $aTMP;
		if($title) {
			echo "<h$title_level>$title</h$title_level>" . PHP_EOL;
		}
		echo csl_build_table(
			'statusTableRS', 
			NULL, 
			$aTBL, 
			NULL
		);
	}
endif;
 
if ( !function_exists( 'csl_completion_degree_bar' ) ) :
	function csl_completion_degree_bar($title, $real, $due) {
		$bcolor = $real < $due ? 'red' : 'green';
		return "<p>$title<span style=\"float: right; color: #cdcdcd;\">100%</span></p>
			<div class=\"gbar\">
			<div class=\"gpercentage\" style=\"width: $real%; background-color: $bcolor;\">$real</div></div>
			<div class=\"gline\" style=\"width: $due%\">$due%<span style=\"color: red;\">&uarr;</span></div>". PHP_EOL;
	}
endif;

if ( !function_exists( 'csl_draw_project_status_gantt_chart' ) ) :
	function csl_draw_project_status_gantt_chart($title = '',  $title_level = '3') {
        echo $title !== '' ? "<h$title_level>$title</h$title_level>" . PHP_EOL : '';
        echo "
            <script type='text/javascript' src='https://www.google.com/jsapi'></script>
            <script type='text/javascript'>
                google.load('visualization', '1.1', {packages:['gantt']});
                google.setOnLoadCallback(drawChart);
                
                function daysToMs(days) {
                    return days * 24 * 60 * 60 * 1000;
                }
                function drawChart() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '" . __( 'Task ID', CSL_TEXT_DOMAIN_PREFIX ) . "');
                    data.addColumn('string', '" . __( 'Task name', CSL_TEXT_DOMAIN_PREFIX ) . "');
                    data.addColumn('string', '" . __( 'Resource', CSL_TEXT_DOMAIN_PREFIX ) . "');
                    data.addColumn('date', '" . __( 'Start date', CSL_TEXT_DOMAIN_PREFIX ) . "');
                    data.addColumn('date', '" . __( 'End date', CSL_TEXT_DOMAIN_PREFIX ) . "');
                    data.addColumn('number', '" . __( 'Duration', CSL_TEXT_DOMAIN_PREFIX ) . "');
                    data.addColumn('number', '" . __( 'Percent complete', CSL_TEXT_DOMAIN_PREFIX ) . "');
                    data.addColumn('string', '" . __( 'Dependencies', CSL_TEXT_DOMAIN_PREFIX ) . "');
                    data.addRows([
        ";
    	foreach(csl_project_data() as $key => $value) {
    		echo "
                ['{$value->task_id}', '{$value->task_name}', '{$value->resource}', new Date({$value->start_date}), new Date({$value->end_date}), daysToMs({$value->duration}), {$value->percent_complete}, '{$value->dependencies}']," . 
                PHP_EOL;
        }
        echo "
                    ]);
                    var options = {
                        height: 530,
                        gantt: {
                            trackHeight: 30,
    						backgroundColor: 'transparent',
                            criticalPathEnabled: true,
                            arrow: {
                                angle: 100,
                                width: 1,
                                color: 'gray',
                                radius: 0,
                            },
                        },
                    };
                    var chart = new google.visualization.Gantt(document.getElementById('chart_div'));
                    chart.draw(data, options);
        			jQuery(window).resize(function(){
        	        	chart.draw(data, options);
        	    	});
                }
            </script>
            <div id='chart_div'></div>
        "; 	
	}
endif;

if ( !function_exists( 'csl_write_project_team_table' ) ) :
	function csl_write_project_team_table($title = '',  $title_level = '3') {
        global $csl_s_timestamp ;
        
        echo $title !== '' ? "<h$title_level>$title</h$title_level>" . PHP_EOL : '';
        $aROL = array(
            'editor' => __( 'Coordination', CSL_TEXT_DOMAIN_PREFIX ), 
            'administrator' => __( 'Site management', CSL_TEXT_DOMAIN_PREFIX ),
            'author' => __( 'Contribution', CSL_TEXT_DOMAIN_PREFIX )
        );
        foreach($aROL as $rol => $rolname) {
    	    echo '<h4>' . sprintf(__( '%s staff', CSL_TEXT_DOMAIN_PREFIX ), $rolname) . '</h4>' . PHP_EOL;
    	    echo '<table class="table team-table">' . PHP_EOL;
            $aARG = array(
                'role'         => $rol,
                'role__not_in' => array( 'demo_users', 'test_authors', 'test_contributors', 'test_subscribers' ),
                'meta_key'     => 'last_name',
                'orderby'      => 'meta_value',
            );
        	$systemusers = get_users($aARG);
        	foreach ( $systemusers as $user ) {
    	    	$isprojectmember = (int) get_user_meta( $user->ID, 'is_project_staff_member', true ) == 1 
    	    		?
    	    		' <strong class="text-green">' . sprintf(__( 'Is member of the %s project team', CSL_TEXT_DOMAIN_PREFIX ), CSL_PROJECT_NAME) . '</strong>. '
    	    		: '';
         		echo 
         			'<tr class="team-table-tr"><td class="team-table-td-avatar">' . get_csl_local_avatar($user->user_email, 64) . 
         			'</td><td class="team-table-td-text">' .
        			'<strong><a href="mailto:' . $user->user_email . '" style="text-decoration: none;">' . $user->first_name .  ' ' . $user->last_name . '</a></strong>' . 
        			'. ' . __( 'Position', CSL_TEXT_DOMAIN_PREFIX ) . ': <strong>' . get_user_meta( $user->ID, 'position', true ) . '</strong>. ' . 
        			__( 'Assignments', CSL_TEXT_DOMAIN_PREFIX ) . ': <strong>' . get_user_meta( $user->ID, 'assignments', true ) . '</strong>. ' . 
        			__( 'Skills', CSL_TEXT_DOMAIN_PREFIX ) . ': <strong>' . get_user_meta( $user->ID, 'skills', true ) . '</strong>. ' . 
        			__( 'Is user since', CSL_TEXT_DOMAIN_PREFIX ) . ' <strong>' . 
        			human_time_diff(strtotime(get_user_by('id', $user->ID)->user_registered), $csl_s_timestamp) . '</strong>.' . 
        			$isprojectmember . 
        			'<hr /><span style="font-size: smaller;">' . 
        			nl2br(stripslashes(get_user_meta( $user->ID, 'description', true ))) . '</span></td></tr>' . PHP_EOL;
        	}
        	echo '</table>' . PHP_EOL;
        }
    }
endif;

if ( !function_exists( 'csl_google_maps_clustered_map' ) ) :
	function csl_google_maps_clustered_map( $title = '', $title_level = '3', $type = CSL_ENTITIES_DATA_PREFIX ) {
	    global $wpdb;
	    global $csl_global_nonce;
	    
	    $atype = $type == CSL_ENTITIES_DATA_PREFIX ? '' : CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME . '_' ;	 
	    $ptype = $type == CSL_ENTITIES_DATA_PREFIX ? CSL_CUSTOM_POST_ENTITY_TYPE_NAME : CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME;
	    $dtype = $type == CSL_ENTITIES_DATA_PREFIX ? get_post_type_object( CSL_CUSTOM_POST_ENTITY_TYPE_NAME )->labels->name : get_post_type_object( CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME )->labels->name;
	    $datap = CSL_DATA_FIELD_PREFIX;	 
	    $results = $wpdb->get_results( "
	        SELECT 
	            SUBSTRING_INDEX(m.meta_value, ',', 1) AS lat,
	            SUBSTRING_INDEX(SUBSTRING_INDEX(m.meta_value, ',', 2), ',', -1) AS lon,
	            p.post_title as title,
	            mm.meta_value as town,
	            p.ID 
	        FROM 
	    	    ( 
	            {$wpdb->posts} AS p
	            INNER JOIN
	            {$wpdb->postmeta} m
	            ON
	            p.ID = m.post_id 
	    	    )
	            LEFT JOIN
	            (SELECT meta_value, post_id FROM {$wpdb->postmeta} WHERE meta_key = \"{$datap}{$type}{$atype}town\") mm
	            ON
	            p.ID = mm.post_id
	        WHERE 
	            post_type = \"{$ptype}\"
	            AND 
	            post_status = \"publish\"
	            AND
	            m.meta_key = \"{$datap}{$type}coordinates\"
	            AND
	            m.meta_value IS NOT NULL
	        ORDER BY
	            mm.meta_value,
	            p.post_title;
	    ",
	    OBJECT);

		echo $title !== '' ? "<h$title_level class='csl-$ptype'>" . 
			"$title <span class='alignright'>" . sprintf( 
			__( '%s %s', CSL_TEXT_DOMAIN_PREFIX ),
			number_format_i18n( count( $results ), 0),
			strtolower($dtype)
			) . 
			"</span></h$title_level>" . PHP_EOL : '';
	    echo '<div id="culsteredmap-canvas' . $type . '" style="height: 450px; width: 100%"></div>' . PHP_EOL;
	    echo '<div id="selectedElement' . $type . '" style="width: 100%; height: 18px; margin-top: 5px;"></div>' . PHP_EOL;
	    
	    echo '<script type="text/javascript">' . PHP_EOL;

	    echo "
	        var hmap{$type}, pointarray{$type}, culsteredmap{$type}, culsteredmapData{$type};
	        var markers_list{$type} = [];
            var clustererVisible{$type} = true;
            /*
            var heatmapVisible{$type} = false;
            var circlesVisible{$type} = false;
            */
            var markerCluster{$type} = null;
            var heatmap{$type} = null;
		";
		
        echo "
	        jQuery( document ).ready( function( $ ) {
	    ";
        
        echo "
			function CenterControl{$type}(controlDiv, map) {
				// Set CSS for the control border.
				var controlUI = document.createElement('div');
				controlUI.style.backgroundColor = '#fff';
				controlUI.style.border = '2px solid #fff';
				controlUI.style.borderRadius = '3px';
				controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
				controlUI.style.cursor = 'pointer';
				controlUI.style.marginBottom = '22px';
				controlUI.style.marginTop = '11px';
				controlUI.style.textAlign = 'center';
				controlUI.title = '" . __( 'Click to recenter map', CSL_TEXT_DOMAIN_PREFIX ) . "';
				controlDiv.appendChild(controlUI);
				// Set CSS for the control interior.
				var controlText = document.createElement('div');
				controlText.style.color = 'rgb(25,25,25)';
				//controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
				controlText.style.fontSize = '10px';
				controlText.style.lineHeight = '26px';
				controlText.style.paddingLeft = '10px';
				controlText.style.paddingRight = '10px';
				controlText.innerHTML = '" . __( 'Center Map', CSL_TEXT_DOMAIN_PREFIX ) . "';
				controlUI.appendChild(controlText);
				// Setup the click event listeners: simply set the map to default value.
				controlUI.addEventListener('click', function() {
					map.setCenter(new google.maps.LatLng(MAP_CENTER_DEFAULT_LAT, MAP_CENTER_DEFAULT_LON));
					map.setZoom(5);
				});
			}        

			function ToggleHeatMapControl{$type}(controlDiv, map) {
				// Set CSS for the control border.
				var controlUI = document.createElement('div');
				controlUI.style.backgroundColor = '#fff';
				controlUI.style.border = '2px solid #fff';
				controlUI.style.borderRadius = '3px';
				controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
				controlUI.style.cursor = 'pointer';
				controlUI.style.marginBottom = '22px';
				controlUI.style.marginTop = '11px';
				controlUI.style.textAlign = 'center';
				controlUI.title = '" . __( 'Click to show or hide markers', CSL_TEXT_DOMAIN_PREFIX ) . "';
				controlDiv.appendChild(controlUI);
				// Set CSS for the control interior.
				var controlText = document.createElement('div');
				controlText.style.color = 'rgb(25,25,25)';
				//controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
				controlText.style.fontSize = '10px';
				controlText.style.lineHeight = '26px';
				controlText.style.paddingLeft = '10px';
				controlText.style.paddingRight = '10px';
				controlText.innerHTML = '" . __( 'Toggle Markers', CSL_TEXT_DOMAIN_PREFIX ) . "';
				controlUI.appendChild(controlText);
				// Setup the click event listeners: simply set the map to default value.
				controlUI.addEventListener('click', function() {
		            if(clustererVisible{$type}) {
		            	markerCluster{$type}.clearMarkers();
		            } else {
			        	markerCluster{$type}.addMarkers(markers_list{$type});
			        }
		            clustererVisible{$type} = !clustererVisible{$type};
				});
			}        
        ";

	    echo "culsteredmapData{$type} = [\n";
	    foreach($results as $key => $value) {
	        echo '{location: new google.maps.LatLng(' . $value->lat . ',' . $value->lon . '), title: "' . esc_sql($value->title) . ' (' . esc_sql($value->town) . ')' . '", link: "<a href=\"' .(is_admin() ? admin_url() . 'post.php?post=' . $value->ID . '&action=edit' : get_permalink($value->ID)) . '\">' . esc_sql($value->title) . ' (' . esc_sql($value->town) . ')' . '</a>"},' . PHP_EOL;
	    }
	    echo "];\n";    

	    echo "         
            var hmap{$type} = new google.maps.Map(document.getElementById('culsteredmap-canvas{$type}'), {
                center: new google.maps.LatLng(MAP_CENTER_DEFAULT_LAT, MAP_CENTER_DEFAULT_LON),
                zoom: 5,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });

			// Create the DIV to hold the control and call the CenterControl() constructor
			// passing in this DIV.
			var centerControlDiv{$type} = document.createElement('div');
			var centerControl{$type} = new CenterControl{$type}(centerControlDiv{$type}, hmap{$type});
			
			centerControlDiv{$type}.index = 1;
			hmap{$type}.controls[google.maps.ControlPosition.TOP_LEFT].push(centerControlDiv{$type});

			// Create the DIV to hold the control and call the ToggleHeatMapControl() constructor
			// passing in this DIV.
			var toggleHeatMapControlDiv{$type} = document.createElement('div');
			var toggleHeatMapControl{$type} = new ToggleHeatMapControl{$type}(toggleHeatMapControlDiv{$type}, hmap{$type});
			
			toggleHeatMapControlDiv{$type}.index = 1;
			hmap{$type}.controls[google.maps.ControlPosition.TOP_CENTER].push(toggleHeatMapControlDiv{$type});

            for (var i = 0; i < culsteredmapData$type.length; i++) {
               var marker{$type} = new google.maps.Marker({
                    position:culsteredmapData{$type}[i].location,
                    map:hmap{$type},
                    title:culsteredmapData{$type}[i].title,
                });
                var storyClick{$type} = new Function(\"event\", \"document.getElementById('selectedElement{$type}').innerHTML = culsteredmapData{$type}[\" + i + \"].link;\");
                google.maps.event.addListener(marker{$type}, 'click', storyClick{$type});
                markers_list{$type}.push(marker{$type});
            }

            markerCluster{$type} = new MarkerClusterer(hmap{$type}, markers_list{$type}, {
                gridSize:40,
                minimumClusterSize: 4,
                ignoreHidden: true,
                calculator: function(markers{$type}, numStyles{$type}) {
                    return {
                        text: markers{$type}.length,
                        index: numStyles{$type}
                    };
                }
            });

			heatmap{$type} = new google.maps.visualization.HeatmapLayer({
				data: culsteredmapData{$type},
				radius: 20,
				opacity: 0.5,
				gradient: [
						'rgba(0, 255, 255, 0)',
						'rgba(0, 255, 255, 1)',
						'rgba(0, 191, 255, 1)',
						'rgba(0, 127, 255, 1)',
						'rgba(0, 63, 255, 1)',
						'rgba(0, 0, 255, 1)',
						'rgba(0, 0, 223, 1)',
						'rgba(0, 0, 191, 1)',
						'rgba(0, 0, 159, 1)',
						'rgba(0, 0, 127, 1)',
						'rgba(63, 0, 91, 1)',
						'rgba(127, 0, 63, 1)',
						'rgba(191, 0, 31, 1)',
						'rgba(255, 0, 0, 1)'
					],
			});
			
			heatmap{$type}.setMap(hmap{$type});
	    ";
	    
	    echo "           
	        });
	    ";
	    
	    echo '</script>' . PHP_EOL;
	}
endif;

/**
 * PHP & WPAF System functions
 */
 
if ( !function_exists( 'csl_database_size' ) ) :
	function csl_database_size() {
		global $wpdb;
		
		$dbname = DB_NAME;
		return $wpdb->get_results("
	            SELECT 
	            	SUM(data_length + index_length) AS db_size,
	            	SUM(data_free) AS free_space
	            FROM
	            	information_schema.TABLES
	            WHERE
	            	table_schema=\"$dbname\";
	        ",
		    ARRAY_A);
	}
endif;

/**
 * Function name:  Purge Transients
 * Description:    Purge old transients
 * Version:        0.2.1
 * Author:         Seebz
 */

if ( ! function_exists('csl_purge_transients') ) {
	function csl_purge_transients($older_than = '7 days', $safemode = true) {
		global $wpdb;
		$older_than_time = strtotime('-' . $older_than);
		if ($older_than_time > time() || $older_than_time < 1) {
			return false;
		}
		$transients = $wpdb->get_col(
			$wpdb->prepare( "
					SELECT REPLACE(option_name, '_transient_timeout_', '') AS transient_name 
					FROM {$wpdb->options} 
					WHERE option_name LIKE '\_transient\_timeout\__%%'
						AND option_value < %s
			", $older_than_time)
		);
		if ($safemode) {
			foreach($transients as $transient) {
				get_transient($transient);
			}
		} else {
			$options_names = array();
			foreach($transients as $transient) {
				$options_names[] = '_transient_' . $transient;
				$options_names[] = '_transient_timeout_' . $transient;
			}
			if ($options_names) {
				$options_names = array_map(array($wpdb, 'escape'), $options_names);
				$options_names = "'". implode("','", $options_names) ."'";
				
				$result = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name IN ({$options_names})" );
				if (!$result) {
					return false;
				}
			}
		}
		return $transients;
	}
}

/**
 * SERVER INFO FUNCTIONS
 */
 
function csl_get_server_info(){
	$free = shell_exec('free');
	$free = (string)trim($free);
	$free_arr = explode("\n", $free);
	$mem = explode(" ", $free_arr[1]);
	$mem = array_filter($mem);
	$mem = array_merge($mem);
		
	return array(
        'mem_available' => $mem[1],
        'mem_in_use' => $mem[2],
        'mem_usage' => $mem[2]/$mem[1]*100,
        'cpu_usage_1' => sys_getloadavg()[0]*100,
        'cpu_usage_5' => sys_getloadavg()[1]*100,
        'cpu_usage_15' => sys_getloadavg()[2]*100,
        'processes' => csl_askapache_get_process_count(),
    );
}

if ( !function_exists( 'csl_get_user_roles' ) ) :
	function csl_get_user_roles($id) {
	    $user = new WP_User($id);
	    return array_shift($user->roles);
	}			
endif;

if ( !function_exists( 'csl_get_only_valid_authors' ) ) :
	function csl_get_only_valid_authors() {
		$specus = array();
		foreach(get_users(array('role__in' => array( 'author', 'editor' ))) as $key => $value) {
			if(false === strpos( $value->user_login, CSL_FAKE_AUTHORS_PREFIX ) ) {
				$specus []= $value->ID;
			}
		}
		return $specus;
	}			
endif;

if ( !function_exists( 'count_user_posts_by_type' ) ) :
    function count_user_posts_by_type( $userid, $post_type = 'post' ) {
    	global $wpdb;
    	$where = get_posts_by_author_sql( $post_type, true, $userid );
    	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );
      	return apply_filters( 'count_user_posts_by_type', $count, $userid );
    }
endif;

if ( !function_exists( 'human_value_diff_valuation' ) ) :
    function human_value_diff_valuation( $target = 0, $current = 0, $inverted = false ) {
    	return !$inverted 
    		?
     		$current > $target ? 'green' : 'red'
	 		:
     		$current < $target ? 'green' : 'red';
    }
endif;

if ( !function_exists( 'human_schedule_periods' ) ) :
    function human_schedule_periods( $text = NULL ) {
        switch ($text) {
	    	case 'hourly':
	    		return __('Hourly', CSL_TEXT_DOMAIN_PREFIX);
	    		break;    
	    	case 'daily':
	    		return __('Daily', CSL_TEXT_DOMAIN_PREFIX);
	    		break;    
	    	case 'twicedaily':
	    		return __('Twice daily', CSL_TEXT_DOMAIN_PREFIX);
	    		break;    
	    	case 'fourtimesdaily':
	    		return __('Four times a day', CSL_TEXT_DOMAIN_PREFIX);
	    		break;    
	    	case 'weekly':
	    		return __('Once weekly', CSL_TEXT_DOMAIN_PREFIX);
	    		break;    
	    	case 'monthly':
	    		return __('Once a month', CSL_TEXT_DOMAIN_PREFIX);
	    		break;  
	    	default:
	    		return false;
	    		break;  
        }
    }
endif;

/** askapache_get_process_count()
 * Returns the number of running processes
 *
 * @version 1.4
 *
 * @return int
 */
if ( !function_exists( 'csl_askapache_get_process_count' ) ) :
	function csl_askapache_get_process_count() {
		static $ver, $runs = 0;
		// check if php version supports clearstatcache params, but only check once
		if ( is_null( $ver ) )
			$ver = version_compare( PHP_VERSION, '5.3.0', '>=' );
		// Only call clearstatcache() if function called more than once */
		if ( $runs++ > 0 ) { 
			// checks if $runs > 0, then increments $runs by one.
			// if php version is >= 5.3.0
			if ( $ver ) {
				clearstatcache( true, '/proc' );
			} else {
				// if php version is < 5.3.0
				clearstatcache();
			}
		}
		$stat = stat( '/proc' );
		return ( ( false !== $stat && isset( $stat[3] ) ) ? $stat[3] : 0 );
	}
endif;

function csl_normalize_uptime_dates($text) {
	$text = str_replace('days', __( 'days', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('day', __( 'day', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('weeks', __( 'weeks', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('week', __( 'week', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('months', __( 'months', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('month', __( 'month', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('years', __( 'years', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('year', __( 'year', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('hours', __( 'hours', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('hour', __( 'hour', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('minutes', __( 'minutes', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('minute', __( 'minute', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('seconds', __( 'seconds', CSL_TEXT_DOMAIN_PREFIX ), $text);
	$text = str_replace('second', __( 'second', CSL_TEXT_DOMAIN_PREFIX ), $text);
	//$text = str_replace(', ', ' ' . __( 'at', CSL_TEXT_DOMAIN_PREFIX ) . ' ', $text);
	return $text . ' ' . __( 'hours', CSL_TEXT_DOMAIN_PREFIX );
}

if ( !function_exists( 'count_user_posts_by_meta_key' ) ) :
    function count_user_posts_by_meta_key( $userid, $post_type = 'post', $meta_key = '' ) {
    	global $wpdb;
        $sql = "SELECT count(DISTINCT pm.post_id)
            FROM $wpdb->postmeta pm
            JOIN $wpdb->posts p ON (p.ID = pm.post_id)
            WHERE pm.meta_key = '$meta_key'
            AND p.post_author = $userid
            AND p.post_type = '$post_type'
            AND p.post_status = 'publish'
            ";
        return $wpdb->get_var($sql);
    }
endif;

if ( !function_exists( 'sanitize_record_custom_fields' ) ) :
    function sanitize_record_custom_fields( $custom_fields ) {
	    global $cls_a_custom_fields_nomenclature;

        $intermediate_array = array_intersect_key($custom_fields, $cls_a_custom_fields_nomenclature);
        $final_array = array();
        foreach($intermediate_array as $key => $value) {
            $final_array[$cls_a_custom_fields_nomenclature[$key]] = $value;
        }
        return $final_array;
    }
endif;

if ( !function_exists( 'csl_aux_get_human_name_for_log_action' ) ) :
    function csl_aux_get_human_name_for_log_action($action) {
        $ret = '';
        switch($action) {
            case 'log_in':
                $ret = __('Log in session', CSL_TEXT_DOMAIN_PREFIX);
                break;
            case 'log_out':
                $ret = __('Log out session', CSL_TEXT_DOMAIN_PREFIX);
                break;
            case 'record_publish':
                $ret = __('Published record', CSL_TEXT_DOMAIN_PREFIX);
                break;
            case 'record_trash':
                $ret = __('Record sent to trash', CSL_TEXT_DOMAIN_PREFIX);
                break;
            case 'record_untrash':
                $ret = __('Record rescued from trash', CSL_TEXT_DOMAIN_PREFIX);
                break;
            default:
                $ret = __('Unknown action', CSL_TEXT_DOMAIN_PREFIX) . ': ' . $action;
                break;
        }
        return $ret;
    }
endif;

function get_user_id_by_display_name( $display_name ) {
    global $wpdb;
    if (!$user = $wpdb->get_row($wpdb->prepare("SELECT `ID` FROM $wpdb->users WHERE `display_name` = %s", $display_name)))
        return false;
    return $user->ID;
}

function get_uris_by_user_id( $user_id ) {
    global $wpdb;
    $results = $wpdb->get_results( "SELECT
        	tt.taxonomy AS uri_type,
        	COUNT(t.term_id) AS num_uris        
        FROM
        	(($wpdb->terms t
            INNER JOIN
            $wpdb->term_taxonomy tt
            ON
            t.term_id = tt.term_id)
            INNER JOIN
            $wpdb->term_relationships tr
            ON
            tt.term_taxonomy_id = tr.term_taxonomy_id)
            INNER JOIN
            $wpdb->posts p
            ON
            tr.object_id = p.ID
        WHERE
        	p.post_author = \"$user_id\"
            AND
            p.post_type = \"organismo\" 
        GROUP BY
        	tt.taxonomy", 
    OBJECT);
    return $results;
}

/**
 * Performs a full STRING escape
 *
 * @param $string
 * @return array|mixed
 */
function csl_safe_string_escape( $string ) {
    global $wpdb;
 
    // Recursively go through if it is an array
    if ( is_array( $string ) ) {
        foreach ($string as $k => $v) {
            $string[$k] = x123safe_escape($v);
        }
        return $string;
    }
 
    if ( is_float( $string ) )
        return $string;
 
    // Escape for 4.0 >=
    if ( method_exists( $wpdb, 'esc_like' ) )
        return $wpdb->esc_like( $string );
 
    // Escape support for WP < 4.0
    return like_escape( $string );
}

/**
 * Converts a string to number, array of strings to array of numbers
 *
 * Since esc_like() does not escape numeric values, casting them is the easiest way to go
 *
 * @param $number string or array of strings
 * @return mixed number or array of numbers
 */
function csl_safe_force_numeric ( $number ) {
    if ( is_array( $number ) )
        foreach ( $number as $k => $v )
            $number[$k] = $v + 0;
    else
        $number = $number + 0;
 
    return $number;
}

function csl_get_user_posts($posttyp = 'post', $uri_prefix = array(), $user = NULL, $grouped = FALSE) {
    global $wpdb;
    $urifilter = count($uri_prefix) > 0 ? "AND m.meta_key IN (\"" . implode('","', $uri_prefix) . "\") " : "";
    $results = $wpdb->get_results("SELECT 
			u.display_name AS display_name,
			u.ID AS user_id,
			DATE_FORMAT(p.post_modified, \"%Y-%u\") AS week_date,
			DATE_FORMAT(p.post_modified, \"%Y-%m-%d\") AS work_date,
			COUNT(DISTINCT p.ID) AS num_posts,
            SUM(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(m.meta_value,\":\",2),\":\",-1) AS UNSIGNED)) AS num_taxonomies
		FROM
			({$wpdb->posts} p
            INNER JOIN
            {$wpdb->postmeta} m
            ON
            p.ID = m.post_id)
			INNER JOIN
			{$wpdb->users} u
			ON
			p.post_author = u.ID
		WHERE
			p.post_type = \"{$posttyp}\"
			AND
			p.post_status = \"publish\"
            {$urifilter}
		GROUP BY
			u.display_name,
			DATE_FORMAT(p.post_modified, \"%Y-%m-%d\")
		ORDER BY
			u.display_name ASC,
			DATE_FORMAT(p.post_modified, \"%Y-%m-%d\") DESC;", 
    OBJECT);
	$aresint = array();
    for($i = 0; $i < count($results); $i++) {
	    if($user) {
	        if ($results[$i]->user_id == $user) {
	            $aresint []= array(
	            	'display_name' => $results[$i]->display_name, 
	            	'week_date' => $results[$i]->week_date, 
	            	'work_date' => $results[$i]->work_date,
	            	'num_posts' => $results[$i]->num_posts,
                    'num_taxonomies' => $results[$i]->num_taxonomies,
                    'avg_taxonomies' => number_format_i18n((int)$results[$i]->num_taxonomies / (int)$results[$i]->num_posts, 2)); 
	        } 
	    } else {
			$aresint []= array(
            	'display_name' => $results[$i]->display_name, 
            	'week_date' => $results[$i]->week_date, 
            	'work_date' => $results[$i]->work_date,
            	'num_posts' => number_format_i18n($results[$i]->num_posts, 0),
                'num_taxonomies' => number_format_i18n($results[$i]->num_taxonomies, 0),
                'avg_taxonomies' => number_format_i18n((int)$results[$i]->num_taxonomies / (int)$results[$i]->num_posts, 2)); 
	    }
    }
    return $aresint;
}

//***************************************
// ADDITIONAL CLASSES AND FUNCTIONS
//***************************************

/**
 * Data export functions
 */
 
/**
 * Class ExportData
 * Based in php-export-data by Eli Dickinson, http://github.com/elidickinson/php-export-data
 **/
 
abstract class ExportData {
	protected $exportTo; // Set in constructor to one of 'browser', 'file', 'string'
	protected $stringData; // stringData so far, used if export string mode
	protected $tempFile; // handle to temp file (for export file mode)
	protected $tempFilename; // temp file name and path (for export file mode)

	public $filename; // file mode: the output file name; browser mode: file name for download; string mode: not used

	public function __construct($exportTo = "browser", $filename = "exportdata") {
		if(!in_array($exportTo, array('browser','file','string') )) {
			throw new Exception("$exportTo is not a valid ExportData export type");
		}
		$this->exportTo = $exportTo;
		$this->filename = $filename;
	}
	
	public function initialize() {
		
		switch($this->exportTo) {
			case 'browser':
				$this->sendHttpHeaders();
				break;
			case 'string':
				$this->stringData = '';
				break;
			case 'file':
				$this->tempFilename = tempnam(sys_get_temp_dir(), 'exportdata');
				$this->tempFile = fopen($this->tempFilename, "w");
				break;
		}
		
		$this->write($this->generateHeader());
	}
	
	public function addRow($row) {
		$this->write($this->generateRow($row));
	}
	
	public function finalize() {
		
		$this->write($this->generateFooter());
		
		switch($this->exportTo) {
			case 'browser':
				flush();
				break;
			case 'string':
				// do nothing
				break;
			case 'file':
				// close temp file and move it to correct location
				fclose($this->tempFile);
				rename($this->tempFilename, $this->filename);
				break;
		}
	}
	
	public function getString() {
		return $this->stringData;
	}
	
	abstract public function sendHttpHeaders();
	
	protected function write($data) {
		switch($this->exportTo) {
			case 'browser':
				echo $data;
				break;
			case 'string':
				$this->stringData .= $data;
				break;
			case 'file':
				fwrite($this->tempFile, $data);
				break;
		}
	}
	
	protected function generateHeader() {
		// can be overridden by subclass to return any data that goes at the top of the exported file
	}
	
	protected function generateFooter() {
		// can be overridden by subclass to return any data that goes at the bottom of the exported file		
	}
	
	// In subclasses generateRow will take $row array and return string of it formatted for export type
	abstract protected function generateRow($row);
	
}

/**
 * ExportDataTSV - Exports to TSV (tab separated value) format.
 */
class ExportDataTSV extends ExportData {
	
	function generateRow($row) {
		foreach ($row as $key => $value) {
			// Escape inner quotes and wrap all contents in new quotes.
			// Note that we are using \" to escape double quote not ""
			$row[$key] = '"'. str_replace('"', '\"', $value) .'"';
		}
		return implode("\t", $row) . PHP_EOL;
	}
	
	function sendHttpHeaders() {
		header("Content-type: text/tab-separated-values");
		header("Content-Disposition: attachment; filename=".basename($this->filename));
	}
}

/**
 * ExportDataCSV - Exports to CSV (comma separated value) format.
 */
class ExportDataCSV extends ExportData {
	
	function generateRow($row) {
		foreach ($row as $key => $value) {
			// Escape inner quotes and wrap all contents in new quotes.
			// Note that we are using \" to escape double quote not ""
			$row[$key] = '"'. str_replace('"', '\"', $value) .'"';
		}
		return implode(",", $row) . PHP_EOL;
	}
	
	function sendHttpHeaders() {
		header("Content-type: text/csv");
		//if( $this->exportTo )
		header("Content-Disposition: attachment; filename=".basename($this->filename));
	}
}


/**
 * ExportDataExcel exports data into an XML format  (spreadsheetML) that can be 
 * read by MS Excel 2003 and newer as well as OpenOffice
 * 
 * Creates a workbook with a single worksheet (title specified by
 * $title).
 * 
 * Note that using .XML is the "correct" file extension for these files, but it
 * generally isn't associated with Excel. Using .XLS is tempting, but Excel 2007 will
 * throw a scary warning that the extension doesn't match the file type.
 * 
 * Based on Excel XML code from Excel_XML (http://github.com/oliverschwarz/php-excel)
 *  by Oliver Schwarz
 */
class ExportDataExcel extends ExportData {
	
	const XmlHeader = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
	const XmlFooter = "</Workbook>";
	
	public $encoding = 'UTF-8'; // encoding type to specify in file. 
	// Note that you're on your own for making sure your data is actually encoded to this encoding
	
	public $title = 'Sheet1'; // title for Worksheet 
	
	function generateHeader() {
		
		// workbook header
		$output = stripslashes(sprintf(self::XmlHeader, $this->encoding)) . PHP_EOL;
		
		// Set up styles
		$output .= "<Styles>\n";
		$output .= "<Style ss:ID=\"sDT\"><NumberFormat ss:Format=\"Short Date\"/></Style>\n";
		$output .= "</Styles>\n";
		
		// worksheet header
		$output .= sprintf("<Worksheet ss:Name=\"%s\">\n    <Table>\n", htmlentities($this->title));
		
		return $output;
	}
	
	function generateFooter() {
		$output = '';
		
		// worksheet footer
		$output .= "    </Table>\n</Worksheet>\n";
		
		// workbook footer
		$output .= self::XmlFooter;
		
		return $output;
	}
	
	function generateRow($row) {
		$output = '';
		$output .= "        <Row>\n";
		foreach ($row as $k => $v) {
			$output .= $this->generateCell($v);
		}
		$output .= "        </Row>\n";
		return $output;
	}
	
	private function generateCell($item) {
		$output = '';
		$style = '';
		
		// Tell Excel to treat as a number. Note that Excel only stores roughly 15 digits, so keep 
		// as text if number is longer than that.
		if(preg_match("/^-?\d+(?:[.,]\d+)?$/",$item) && (strlen($item) < 15)) {
			$type = 'Number';
		}
		// Sniff for valid dates; should look something like 2010-07-14 or 7/14/2010 etc. Can
		// also have an optional time after the date.
		//
		// Note we want to be very strict in what we consider a date. There is the possibility
		// of really screwing up the data if we try to reformat a string that was not actually 
		// intended to represent a date.
		elseif(preg_match("/^(\d{1,2}|\d{4})[\/\-]\d{1,2}[\/\-](\d{1,2}|\d{4})([^\d].+)?$/",$item) &&
					($timestamp = strtotime($item)) &&
					($timestamp > 0) &&
					($timestamp < strtotime('+500 years'))) {
			$type = 'DateTime';
			$item = strftime("%Y-%m-%dT%H:%M:%S",$timestamp);
			$style = 'sDT'; // defined in header; tells excel to format date for display
		}
		else {
			$type = 'String';
		}
				
		$item = str_replace('&#039;', '&apos;', htmlspecialchars($item, ENT_QUOTES));
		$output .= "            ";
		$output .= $style ? "<Cell ss:StyleID=\"$style\">" : "<Cell>";
		$output .= sprintf("<Data ss:Type=\"%s\">%s</Data>", $type, $item);
		$output .= "</Cell>\n";
		
		return $output;
	}
	
	function sendHttpHeaders() {
		header("Content-Type: application/vnd.ms-excel; charset=" . $this->encoding);
		header("Content-Disposition: inline; filename=\"" . basename($this->filename) . "\"");
	}
	
}

/*

	Stemm_es a stemming class for spanish / Un lexemador para español
    Copyright (C) 2007  Paolo Ragone

    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public
    License along with this library; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
	or go to: http://www.gnu.org/licenses/lgpl.txt

	You may contact me at pragone@gmail.com

*/

class stemm_es {

	public static function is_vowel($c) {
		return ($c == 'a' || $c == 'e' || $c == 'i' || $c == 'o' || $c == 'u' || $c == 'á' || $c == 'é' ||
			$c == 'í' || $c == 'ó' || $c == 'ú');
	}

	public static function getNextVowelPos($word, $start = 0) {
		$len = strlen($word);
		for ($i = $start; $i < $len; $i++)
			if (stemm_es::is_vowel($word[$i])) return $i;
		return $len;
	}

	public static function getNextConsonantPos($word, $start = 0) {
		$len = strlen($word);
		for ($i = $start; $i < $len; $i++)
			if (!stemm_es::is_vowel($word[$i])) return $i;
		return $len;		
	}

	public static function endsin($word, $suffix) {
		if (strlen($word) < strlen($suffix)) return false;
		return (substr($word, -strlen($suffix)) == $suffix);
	}

	public static function endsinArr($word, $suffixes) {
		foreach ($suffixes as $suff) {
			if (stemm_es::endsin($word, $suff)) return $suff;
		}
		return '';
	}

	public static function removeAccent($word) {
		return str_replace(array('á','é','í','ó','ú'), array('a','e','i','o','u'), $word);
	}

	public static function stemm($word) {
		$len = strlen($word);
		if ($len <=2) return $word;

		$word = strtolower($word);

		$r1 = $r2 = $rv = $len;
		//R1 is the region after the first non-vowel following a vowel, or is the null region at the end of the word if there is no such non-vowel.
		for ($i = 0; $i < ($len-1) && $r1 == $len; $i++) {
			if (stemm_es::is_vowel($word[$i]) && !stemm_es::is_vowel($word[$i+1])) { 
					$r1 = $i+2;
			}
		}

		//R2 is the region after the first non-vowel following a vowel in R1, or is the null region at the end of the word if there is no such non-vowel. 
		for ($i = $r1; $i < ($len -1) && $r2 == $len; $i++) {
			if (stemm_es::is_vowel($word[$i]) && !stemm_es::is_vowel($word[$i+1])) { 
				$r2 = $i+2; 
			}
		}

		if ($len > 3) {
			if(!stemm_es::is_vowel($word[1])) {
				// If the second letter is a consonant, RV is the region after the next following vowel
				$rv = stemm_es::getNextVowelPos($word, 2) +1;
			} elseif (stemm_es::is_vowel($word[0]) && stemm_es::is_vowel($word[1])) { 
				// or if the first two letters are vowels, RV is the region after the next consonant
				$rv = stemm_es::getNextConsonantPos($word, 2) + 1;
			} else {
				//otherwise (consonant-vowel case) RV is the region after the third letter. But RV is the end of the word if these positions cannot be found.
				$rv = 3;
			}
		}

		$r1_txt = substr($word,$r1);
		$r2_txt = substr($word,$r2);
		$rv_txt = substr($word,$rv);

		$word_orig = $word;

		// Step 0: Attached pronoun
		$pronoun_suf = array('me', 'se', 'sela', 'selo', 'selas', 'selos', 'la', 'le', 'lo', 'las', 'les', 'los', 'nos');	
		$pronoun_suf_pre1 = array('éndo', 'ándo', 'ár', 'ér', 'ír');	
		$pronoun_suf_pre2 = array('ando', 'iendo', 'ar', 'er', 'ir');
		$suf = stemm_es::endsinArr($word, $pronoun_suf);
		if ($suf != '') {
			$pre_suff = stemm_es::endsinArr(substr($rv_txt,0,-strlen($suf)),$pronoun_suf_pre1);
			if ($pre_suff != '') {
				$word = stemm_es::removeAccent(substr($word,0,-strlen($suf)));
			} else {
				$pre_suff = stemm_es::endsinArr(substr($rv_txt,0,-strlen($suf)),$pronoun_suf_pre2);
				if ($pre_suff != '' ||
					(stemm_es::endsin($word, 'yendo' ) && 
					(substr($word, -strlen($suf)-6,1) == 'u'))) {
					$word = substr($word,0,-strlen($suf));
				}
			}
		}
		
		if ($word != $word_orig) {
			$r1_txt = substr($word,$r1);
			$r2_txt = substr($word,$r2);
			$rv_txt = substr($word,$rv);
		}
		$word_after0 = $word;
		
		if (($suf = stemm_es::endsinArr($r2_txt, array('anza', 'anzas', 'ico', 'ica', 'icos', 'icas', 'ismo', 'ismos', 'able', 'ables', 'ible', 'ibles', 'ista', 'istas', 'oso', 'osa', 'osos', 'osas', 'amiento', 'amientos', 'imiento', 'imientos'))) != '') {
			$word = substr($word,0, -strlen($suf));	
		} elseif (($suf = stemm_es::endsinArr($r2_txt, array('icadora', 'icador', 'icación', 'icadoras', 'icadores', 'icaciones', 'icante', 'icantes', 'icancia', 'icancias', 'adora', 'ador', 'ación', 'adoras', 'adores', 'aciones', 'ante', 'antes', 'ancia', 'ancias'))) != '') {
			$word = substr($word,0, -strlen($suf));	
		} elseif (($suf = stemm_es::endsinArr($r2_txt, array('logía', 'logías'))) != '') {
			$word = substr($word,0, -strlen($suf)) . 'log';
		} elseif (($suf = stemm_es::endsinArr($r2_txt, array('ución', 'uciones'))) != '') {
			$word = substr($word,0, -strlen($suf)) . 'u';
		} elseif (($suf = stemm_es::endsinArr($r2_txt, array('encia', 'encias'))) != '') {
			$word = substr($word,0, -strlen($suf)) . 'ente';
		} elseif (($suf = stemm_es::endsinArr($r2_txt, array('ativamente', 'ivamente', 'osamente', 'icamente', 'adamente'))) != '') {
			$word = substr($word,0, -strlen($suf));
		} elseif (($suf = stemm_es::endsinArr($r1_txt, array('amente'))) != '') {
			$word = substr($word,0, -strlen($suf));
		} elseif (($suf = stemm_es::endsinArr($r2_txt, array('antemente', 'ablemente', 'iblemente', 'mente'))) != '') {
			$word = substr($word,0, -strlen($suf));
		} elseif (($suf = stemm_es::endsinArr($r2_txt, array('abilidad', 'abilidades', 'icidad', 'icidades', 'ividad', 'ividades', 'idad', 'idades'))) != '') {
			$word = substr($word,0, -strlen($suf));
		} elseif (($suf = stemm_es::endsinArr($r2_txt, array('ativa', 'ativo', 'ativas', 'ativos', 'iva', 'ivo', 'ivas', 'ivos'))) != '') {
			$word = substr($word,0, -strlen($suf));
		}

		if ($word != $word_after0) {
			$r1_txt = substr($word,$r1);
			$r2_txt = substr($word,$r2);
			$rv_txt = substr($word,$rv);
		}
		$word_after1 = $word;
		
		if ($word_after0 == $word_after1) {
			// Do step 2a if no ending was removed by step 1. 
			if (($suf = stemm_es::endsinArr($rv_txt, array('ya', 'ye', 'yan', 'yen', 'yeron', 'yendo', 'yo', 'yó', 'yas', 'yes', 'yais', 'yamos'))) != '' && (substr($word,-strlen($suf)-1,1) == 'u')) {
				$word = substr($word,0, -strlen($suf));
			}
			
			if ($word != $word_after1) {
				$r1_txt = substr($word,$r1);
				$r2_txt = substr($word,$r2);
				$rv_txt = substr($word,$rv);
			}
			$word_after2a = $word;
			
			// Do Step 2b if step 2a was done, but failed to remove a suffix. 
			if ($word_after2a == $word_after1) {
				if (($suf = stemm_es::endsinArr($rv_txt, array('en', 'es', 'éis', 'emos'))) != '') {
					$word = substr($word,0, -strlen($suf));
					if (stemm_es::endsin($word, 'gu')) {
						$word = substr($word,0,-1);
					}
				} elseif (($suf = stemm_es::endsinArr($rv_txt, array('arían', 'arías', 'arán', 'arás', 'aríais', 'aría', 'aréis', 'aríamos', 'aremos', 'ará', 'aré', 'erían', 'erías', 'erán', 'erás', 'eríais', 'ería', 'eréis', 'eríamos', 'eremos', 'erá', 'eré', 'irían', 'irías', 'irán', 'irás', 'iríais', 'iría', 'iréis', 'iríamos', 'iremos', 'irá', 'iré', 'aba', 'ada', 'ida', 'ía', 'ara', 'iera', 'ad', 'ed', 'id', 'ase', 'iese', 'aste', 'iste', 'an', 'aban', 'ían', 'aran', 'ieran', 'asen', 'iesen', 'aron', 'ieron', 'ado', 'ido', 'ando', 'iendo', 'ió', 'ar', 'er', 'ir', 'as', 'abas', 'adas', 'idas', 'ías', 'aras', 'ieras', 'ases', 'ieses', 'ís', 'áis', 'abais', 'íais', 'arais', 'ierais', '  aseis', 'ieseis', 'asteis', 'isteis', 'ados', 'idos', 'amos', 'ábamos', 'íamos', 'imos', 'áramos', 'iéramos', 'iésemos', 'ásemos'))) != '') {
					$word = substr($word,0, -strlen($suf));
				}
			}
		}

		// Always do step 3. 
		$r1_txt = substr($word,$r1);
		$r2_txt = substr($word,$r2);
		$rv_txt = substr($word,$rv);

		if (($suf = stemm_es::endsinArr($rv_txt, array('os', 'a', 'o', 'á', 'í', 'ó'))) != '') {
			$word = substr($word,0, -strlen($suf));
		} elseif (($suf = stemm_es::endsinArr($rv_txt ,array('e','é'))) != '') {
			$word = substr($word,0,-1);
			$rv_txt = substr($word,$rv);
			if (stemm_es::endsin($rv_txt,'u') && stemm_es::endsin($word,'gu')) {
				$word = substr($word,0,-1);
			}
		}
		
		return stemm_es::removeAccent($word);
	}
}  


/**
 * Display a custom taxonomy dropdown in admin
 * @author Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_action( 'restrict_manage_posts', 'csl_filter_post_type_by_taxonomy' );
function csl_filter_post_type_by_taxonomy() {
	global $typenow;
    $taxonomies = get_object_taxonomies( $typenow, 'objects' );
	foreach ( $taxonomies as $taxonomy ) {
		$selected      = isset($_GET[$taxonomy->name]) ? $_GET[$taxonomy->name] : '';
		$info_taxonomy = get_taxonomy($taxonomy->name);
		wp_dropdown_categories(array(
			'show_option_all' => sprintf( __( "Show all %s", CSL_TEXT_DOMAIN_PREFIX ), $info_taxonomy->label ),
			'taxonomy'        => $taxonomy->name,
			'name'            => $taxonomy->name,
			'orderby'         => 'name',
			'selected'        => $selected,
			'show_count'      => true,
			'hide_empty'      => true,
		));
	};
}
/**
 * Filter posts by taxonomy in admin
 * @author  Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_filter( 'parse_query', 'csl_convert_id_to_term_in_query' );
function csl_convert_id_to_term_in_query( $query ) {
	global $pagenow;
	global $typenow;
    $taxonomies = get_object_taxonomies( $typenow, 'objects' );
	foreach ( $taxonomies as $taxonomy ) {
    	$q_vars    = &$query->query_vars;
    	if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $typenow && isset($q_vars[$taxonomy->name]) && is_numeric($q_vars[$taxonomy->name]) && $q_vars[$taxonomy->name] != 0 ) {
    		$term = get_term_by('id', $q_vars[$taxonomy->name], $taxonomy->name);
    		$q_vars[$taxonomy->name] = $term->slug;
    	}
    }
}

/**
 * Get uppercase first three-letters acronym for a word
 */
function csl_get_acronym( $sWord = '' ) {
    return $sWord == '' ? '' : strtoupper( substr( $sWord, 0, 3 ) );
}

/**
 * Get remote client IP
 */
function csl_get_remote_client_ip() {
    return getenv('HTTP_CLIENT_IP')?:
    getenv('HTTP_X_FORWARDED_FOR')?:
    getenv('HTTP_X_FORWARDED')?:
    getenv('HTTP_FORWARDED_FOR')?:
    getenv('HTTP_FORWARDED')?:
    getenv('REMOTE_ADDR');
}

/**
 * Simplified user agent parser
 */
/**
 * Parses a user agent string into its important parts
 *
 * @author Jesse G. Donat <donatj@gmail.com>
 * @link https://github.com/donatj/PhpUserAgent
 * @link http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
 * @param string|null $u_agent User agent string to parse or null. Uses $_SERVER['HTTP_USER_AGENT'] on NULL
 * @throws \InvalidArgumentException on not having a proper user agent to parse.
 * @return string[] an array with browser, version and platform keys
 */
function csl_parse_user_agent( $u_agent = null ) {
	if( is_null($u_agent) ) {
		if( isset($_SERVER['HTTP_USER_AGENT']) ) {
			$u_agent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			throw new \InvalidArgumentException('parse_user_agent requires a user agent');
		}
	}

	$platform = null;
	$browser  = null;
	$version  = null;

	$empty = array( 'platform' => $platform, 'browser' => $browser, 'version' => $version );

	if( !$u_agent ) return $empty;

	if( preg_match('/\((.*?)\)/im', $u_agent, $parent_matches) ) {
		preg_match_all('/(?P<platform>BB\d+;|Android|CrOS|Tizen|iPhone|iPad|iPod|Linux|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(New\ )?Nintendo\ (WiiU?|3?DS)|Xbox(\ One)?)
				(?:\ [^;]*)?
				(?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);

		$priority = array( 'Xbox One', 'Xbox', 'Windows Phone', 'Tizen', 'Android', 'CrOS', 'X11' );

		$result['platform'] = array_unique($result['platform']);
		if( count($result['platform']) > 1 ) {
			if( $keys = array_intersect($priority, $result['platform']) ) {
				$platform = reset($keys);
			} else {
				$platform = $result['platform'][0];
			}
		} elseif( isset($result['platform'][0]) ) {
			$platform = $result['platform'][0];
		}
	}

	if( $platform == 'linux-gnu' || $platform == 'X11' ) {
		$platform = 'Linux';
	} elseif( $platform == 'CrOS' ) {
		$platform = 'Chrome OS';
	}

	preg_match_all('%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|Safari|MSIE|Trident|AppleWebKit|TizenBrowser|Chrome|
				Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|CriOS|UCBrowser|
				Baiduspider|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|
				Valve\ Steam\ Tenfoot|
				NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
				(?:\)?;?)
				(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
		$u_agent, $result, PREG_PATTERN_ORDER);

	// If nothing matched, return null (to avoid undefined index errors)
	if( !isset($result['browser'][0]) || !isset($result['version'][0]) ) {
		if( preg_match('%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $u_agent, $result) ) {
			return array( 'platform' => $platform ?: null, 'browser' => $result['browser'], 'version' => isset($result['version']) ? $result['version'] ?: null : null );
		}

		return $empty;
	}

	if( preg_match('/rv:(?P<version>[0-9A-Z.]+)/si', $u_agent, $rv_result) ) {
		$rv_result = $rv_result['version'];
	}

	$browser = $result['browser'][0];
	$version = $result['version'][0];

	$lowerBrowser = array_map('strtolower', $result['browser']);

	$find = function ( $search, &$key, &$value = null ) use ( $lowerBrowser ) {
		$search = (array)$search;

		foreach( $search as $val ) {
			$xkey = array_search(strtolower($val), $lowerBrowser);
			if( $xkey !== false ) {
				$value = $val;
				$key   = $xkey;

				return true;
			}
		}

		return false;
	};

	$key = 0;
	$val = '';
	if( $browser == 'Iceweasel' ) {
		$browser = 'Firefox';
	} elseif( $find('Playstation Vita', $key) ) {
		$platform = 'PlayStation Vita';
		$browser  = 'Browser';
	} elseif( $find(array( 'Kindle Fire', 'Silk' ), $key, $val) ) {
		$browser  = $val == 'Silk' ? 'Silk' : 'Kindle';
		$platform = 'Kindle Fire';
		if( !($version = $result['version'][$key]) || !is_numeric($version[0]) ) {
			$version = $result['version'][array_search('Version', $result['browser'])];
		}
	} elseif( $find('NintendoBrowser', $key) || $platform == 'Nintendo 3DS' ) {
		$browser = 'NintendoBrowser';
		$version = $result['version'][$key];
	} elseif( $find('Kindle', $key, $platform) ) {
		$browser = $result['browser'][$key];
		$version = $result['version'][$key];
	} elseif( $find('OPR', $key) ) {
		$browser = 'Opera Next';
		$version = $result['version'][$key];
	} elseif( $find('Opera', $key, $browser) ) {
		$find('Version', $key);
		$version = $result['version'][$key];
	} elseif( $find(array( 'IEMobile', 'Edge', 'Midori', 'Vivaldi', 'Valve Steam Tenfoot', 'Chrome' ), $key, $browser) ) {
		$version = $result['version'][$key];
	} elseif( $browser == 'MSIE' || ($rv_result && $find('Trident', $key)) ) {
		$browser = 'MSIE';
		$version = $rv_result ?: $result['version'][$key];
	} elseif( $find('UCBrowser', $key) ) {
		$browser = 'UC Browser';
		$version = $result['version'][$key];
	} elseif( $find('CriOS', $key) ) {
		$browser = 'Chrome';
		$version = $result['version'][$key];
	} elseif( $browser == 'AppleWebKit' ) {
		if( $platform == 'Android' && !($key = 0) ) {
			$browser = 'Android Browser';
		} elseif( strpos($platform, 'BB') === 0 ) {
			$browser  = 'BlackBerry Browser';
			$platform = 'BlackBerry';
		} elseif( $platform == 'BlackBerry' || $platform == 'PlayBook' ) {
			$browser = 'BlackBerry Browser';
		} else {
			$find('Safari', $key, $browser) || $find('TizenBrowser', $key, $browser);
		}

		$find('Version', $key);
		$version = $result['version'][$key];
	} elseif( $pKey = preg_grep('/playstation \d/i', array_map('strtolower', $result['browser'])) ) {
		$pKey = reset($pKey);

		$platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $pKey);
		$browser  = 'NetFront';
	}

	return array( 'platform' => $platform ?: null, 'browser' => $browser ?: null, 'version' => $version ?: null );
}

// Get a color code from a string
function csl_get_color_from_string( $str ) {
	return substr( dechex( crc32( $str ) ), 0, 6 );
}

?>
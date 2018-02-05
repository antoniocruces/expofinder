<?php

// FRONT END & BACK END. Styles and scripts enqueuing
// -----------------------------------------------------------------------------

// FE & BE. Check whether the browser supports javascript
if ( ! function_exists( 'csl_html_js_class' ) ) :
	function csl_html_js_class() {
	    echo '<script>document.documentElement.className = document.documentElement.className.replace("no-js","js");</script>'. PHP_EOL;
	}
endif;

// FE & BE. Language variable. Iserted in <head> tag
if ( ! function_exists( 'csl_insert_language_variable_in_head' ) ) :
	function csl_insert_language_variable_in_head() {
	    echo '<script type="text/javascript">' . PHP_EOL;
	    echo 'var csl_LANG_D = "'.get_locale().'"' . PHP_EOL;
	    echo 'var csl_LANG_S = "'.substr(get_locale(), 0, 2).'"' . PHP_EOL;
	    echo '</script>' . PHP_EOL;
	}
endif;

// FRONT END
// -----------------------------------------------------------------------------

// FE. Register and enqueue CSS styles
if ( ! function_exists( 'csl_load_fe_style_files' ) ) :
    function csl_load_fe_style_files() {
	    wp_register_style( 'csl_google_fonts', '//fonts.googleapis.com/css?family=Montserrat:400,700|Crimson+Text:400,700,400italic,700italic' );
	    wp_register_style( 'csl-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
	    wp_register_style( 'csl-jquery-ui-style', 'http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css' );
	    wp_register_style( 'csl-bootstrap-style', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css' );
	    wp_register_style( 'csl-datatables-style', 'https://cdn.datatables.net/s/bs/dt-1.10.10,b-1.1.0/datatables.min.css' );

	    wp_register_style( 'csl-c3-style', 'https://cdnjs.cloudflare.com/ajax/libs/c3/0.4.10/c3.min.css' );
	    wp_register_style( 'csl-pivot-custom-style', get_template_directory_uri() . '/assets/css/pivot/pivot-custom.css' );
	    wp_register_style( 'csl-tour-css', get_template_directory_uri() . '/assets/css/bootstrap-tour.min.css' );
	    
	    //cdn.datatables.net/plug-ins/1.10.10/integration/font-awesome/dataTables.fontAwesome.css
	    
		wp_register_style( 'csl_style', get_stylesheet_uri() );
	    
		wp_enqueue_style( 'wp-pointer' );

	    wp_enqueue_style ( 'csl_google_fonts' );
		wp_enqueue_style ( 'csl-font-awesome' );
		wp_enqueue_style ( 'csl-jquery-ui-style' );		
		wp_enqueue_style ( 'csl-bootstrap-style' );		
		wp_enqueue_style ( 'csl-datatables-style' );		
	    
		wp_enqueue_style ( 'csl-c3-style' );		
		wp_enqueue_style ( 'csl-pivot-custom-style' );		
		wp_enqueue_style ( 'csl-tour-css' );		

	    wp_enqueue_style ( 'csl_style' );
    }
endif;

// BACK END
// -----------------------------------------------------------------------------

// Add BE admin scripts
if ( ! function_exists( 'csl_admin_scripts' ) ) :
	function csl_admin_scripts($hook) {
        global $csl_global_nonce;
        global $post_type;
        $a_tr_BE_scripts = array(
            'gnonce' =>  $csl_global_nonce,
        	'document_ready' => __( 'Document ready.', CSL_TEXT_DOMAIN_PREFIX ),
        	'report' => __( 'Report', CSL_TEXT_DOMAIN_PREFIX ),
        	'searching' => __( 'Searching&hellip;', CSL_TEXT_DOMAIN_PREFIX ),
        	'linked_rss_text' => __( 'Found RSS/Atom linked URIs', CSL_TEXT_DOMAIN_PREFIX ),
        	'query' => __( 'Query', CSL_TEXT_DOMAIN_PREFIX ),
        	'result' => __( 'Result', CSL_TEXT_DOMAIN_PREFIX ),
        	'results' => __( 'results', CSL_TEXT_DOMAIN_PREFIX ),
        	'not_enough_rss_info' => __( 'There is not enough information to find linked RSS/Atom URIs.', CSL_TEXT_DOMAIN_PREFIX ),
        	'not_linked_rss_info' => __( 'Any linked RSS/Atom URIs found.', CSL_TEXT_DOMAIN_PREFIX ),
        	'record_checked' => __( 'Record checked', CSL_TEXT_DOMAIN_PREFIX ),
        	'related_text' => __( 'Found RSS/Atom related URIs', CSL_TEXT_DOMAIN_PREFIX ),
        	'not_enough_related_info' => __( 'There is not enough information to find related RSS/Atom URIs.', CSL_TEXT_DOMAIN_PREFIX ),
        	'not_related_rss_info' => __( 'Any related RSS/Atom URIs found.', CSL_TEXT_DOMAIN_PREFIX ),
        	'verified_fields_error' => __( 'There are some errors in record verified fields.', CSL_TEXT_DOMAIN_PREFIX ),
        	'verified_fields_no_error' => __( 'There are no errors in record verified fields.', CSL_TEXT_DOMAIN_PREFIX ),
        	'confirm_new_tab' => __( 'Do you want to open this link using a new window or tab?', CSL_TEXT_DOMAIN_PREFIX ),
        	'separate_comma' => __( 'If the term to be inserted include some commas (,), replace them by semicolon (;)', CSL_TEXT_DOMAIN_PREFIX ),
            'thousand_i18n_sep' => get_locale() == 'es_ES' ? '.' : ',',
            'decimal_i18n_sep' => get_locale() == 'es_ES' ? ',' : '.',
            'quantity_of_records' => __( 'Quantity of records (logarithmic scale)', CSL_TEXT_DOMAIN_PREFIX ),
            'percent_of_records' => __( 'Percent of records', CSL_TEXT_DOMAIN_PREFIX ),
            'record_status' => __( 'Record status', CSL_TEXT_DOMAIN_PREFIX ),
            'error_description' => __( 'Error description', CSL_TEXT_DOMAIN_PREFIX ),
            'typologies' => __( 'Typologies', CSL_TEXT_DOMAIN_PREFIX ),
            'regions' => __( 'Regions', CSL_TEXT_DOMAIN_PREFIX ),
            'cities' => __( 'Cities', CSL_TEXT_DOMAIN_PREFIX ),
            'users' => __( 'Users', CSL_TEXT_DOMAIN_PREFIX ),
            'quantity_log' => __( 'Quantity (logarithmic scale)', CSL_TEXT_DOMAIN_PREFIX ),
            'quantity' => __( 'Quantity', CSL_TEXT_DOMAIN_PREFIX ),
            'dates' => __( 'Dates', CSL_TEXT_DOMAIN_PREFIX ),
            'verif_error_text' => __( 'The form is not properly completed and there are some errors', CSL_TEXT_DOMAIN_PREFIX ),
            'verif_error_ques' => __( 'However would you save the form?', CSL_TEXT_DOMAIN_PREFIX ),
            'mandatory_field' => __( 'mandatory field', CSL_TEXT_DOMAIN_PREFIX ),
            'kwURI' => get_template_directory_uri() . '/assets/keywords/' . get_locale() . '/' . get_locale() . '.kws',
        );
		
        $a_tr_BE_clusterer = array(
            'assetsURI' => get_template_directory_uri() . '/assets',
        );
        
	    add_thickbox();
		
		wp_register_script( 'csl-google-apis', 'https://maps.googleapis.com/maps/api/js?libraries=visualization,geometry,places&key=AIzaSyDE7T9DvlSpA4WYxku_qYFESDlTWFy2nlI', array('jquery'), '', true );
		wp_register_script( 'csl-google-jsapi', 'https://www.google.com/jsapi', array('jquery'), '', true );
		wp_register_script( 'csl-google-markerclusterer', get_template_directory_uri() . '/assets/js/markerclusterer.js', array('jquery'), '', true );
		wp_register_script( 'csl-be-helper', get_template_directory_uri() . '/assets/js/csl-be-helper.js', array('jquery'), '', true );
		wp_register_script( 'csl-smoothie', get_template_directory_uri() . '/assets/js/smoothie.js', array('jquery'), '', true );

        /*
        if ($post_type == 'evalcsl') {
            wp_register_script ( 'csl-clipboard', get_template_directory_uri() . '/assets/js/clipboard.min.js', array('jquery'), '', true );
        }
	    */
        
		wp_localize_script( 'csl-be-helper', 'beTXT', $a_tr_BE_scripts );	    
		wp_localize_script( 'csl-google-markerclusterer', 'beCLS', $a_tr_BE_clusterer );

	    wp_enqueue_script( 'jquery-ui-core' );
	    wp_enqueue_script( 'jquery-ui-sortable' );
        
		wp_enqueue_script( 'csl-google-apis' );
		wp_enqueue_script( 'csl-google-jsapi' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'csl-google-markerclusterer' );
		wp_enqueue_script( 'csl-be-helper' );
		wp_enqueue_script( 'csl-smoothie' );

	    if ('edit.php' != $hook && 'post-new.php' != $hook && 'post.php' != $hook && 'index.php' != $hook && 'dashboard_page_csl_settings' != $hook) {
	        return;
	    }
	    if ('edit.php' != $hook && 'post-new.php' != $hook && 'post.php' != $hook && 'index.php' != $hook) {
	        return;
	    }
		wp_register_script( 'csl-jeoquery', get_template_directory_uri() . '/assets/js/jeoquery.js', array('jquery-ui-core'), '', true );
		wp_register_script( 'csl-locationpicker', get_template_directory_uri() . '/assets/js/locationpicker.jquery.min.js', array('jquery'), '', true );
	    
	    wp_enqueue_script( 'suggest' );
		wp_enqueue_script( 'csl-jeoquery' );
		wp_enqueue_script( 'csl-locationpicker' );
        /*
        if ($post_type == 'evalcsl') {
	       wp_enqueue_script( 'csl-clipboard' );
        }
        */
	}
endif;
	
// FRONT END
// -----------------------------------------------------------------------------

// Add FE scripts
if ( ! function_exists( 'csl_frontend_scripts' ) ) :
	function csl_frontend_scripts($hook) {
        global $post_type;
    
        $a_tr_FE_scripts = array(
        	'document_ready' => __( 'Document ready.', CSL_TEXT_DOMAIN_PREFIX ),
            'quantity_of_records' => __( 'Quantity of records (logarithmic scale)', CSL_TEXT_DOMAIN_PREFIX ),
            'record_status' => __( 'Record status', CSL_TEXT_DOMAIN_PREFIX ),
            'quantity' => __( 'Quantity (logarithmic scale)', CSL_TEXT_DOMAIN_PREFIX ),
            'dates' => __( 'Dates', CSL_TEXT_DOMAIN_PREFIX ),
            'quantity_of_records' => __( 'Quantity of records (logarithmic scale)', CSL_TEXT_DOMAIN_PREFIX ),
            'cities' => __( 'Cities', CSL_TEXT_DOMAIN_PREFIX ),

			'copyTitle' =>  __( 'Copying data to Clipboard', CSL_TEXT_DOMAIN_PREFIX ),
			'copySuccess1' =>  __( '%d rows copied', CSL_TEXT_DOMAIN_PREFIX ),
			'copySuccess2' =>  __( '1 row copied', CSL_TEXT_DOMAIN_PREFIX ),

			'totals1' =>  __( 'Totalized value for last column', CSL_TEXT_DOMAIN_PREFIX ),
			'totals2' =>  __( '(current page)', CSL_TEXT_DOMAIN_PREFIX ),
			'totals3' =>  __( '(all pages)', CSL_TEXT_DOMAIN_PREFIX ),

			'copyText' =>  __( '<u>C</u>opy', CSL_TEXT_DOMAIN_PREFIX ),
			'csvText' =>  __( 'CSV', CSL_TEXT_DOMAIN_PREFIX ),
			'excelText' =>  __( 'Excel', CSL_TEXT_DOMAIN_PREFIX ),
			'pdfText' =>  __( 'PDF', CSL_TEXT_DOMAIN_PREFIX ),
			'printText' =>  __( '<u>P</u>rint', CSL_TEXT_DOMAIN_PREFIX ),
			'copyHotKey' =>  __( 'c', CSL_TEXT_DOMAIN_PREFIX ),
			'printHotKey' =>  __( 'p', CSL_TEXT_DOMAIN_PREFIX ),
			
	    	'bar' => __( 'Bar', CSL_TEXT_DOMAIN_PREFIX ),
	        'pie' => __( 'Pie', CSL_TEXT_DOMAIN_PREFIX ),
	        'column' => __( 'Columns', CSL_TEXT_DOMAIN_PREFIX ),
	        'area' => __( 'Area', CSL_TEXT_DOMAIN_PREFIX ),
	        'line' => __( 'Line', CSL_TEXT_DOMAIN_PREFIX ),
			'showChart' => __( 'Show chart', CSL_TEXT_DOMAIN_PREFIX ),
			'hideChart' => __( 'Hide chart', CSL_TEXT_DOMAIN_PREFIX ),
			'showTable' => __( 'Show table', CSL_TEXT_DOMAIN_PREFIX ),
			'hideTable' => __( 'Hide table', CSL_TEXT_DOMAIN_PREFIX ),
			'createChart' => __( 'Create chart', CSL_TEXT_DOMAIN_PREFIX ),
			'changeChart' => __( 'Edit chart', CSL_TEXT_DOMAIN_PREFIX ),
			'cookieName' => __( 'expoFinderCookie', CSL_TEXT_DOMAIN_PREFIX ),
			'cookieValue' => __( 'on', CSL_TEXT_DOMAIN_PREFIX ),
			'bannerTitle' => '<i class="fa fa-info-circle"></i>&nbsp;' . __( 'Cookies:', CSL_TEXT_DOMAIN_PREFIX ),
			'bannerMessage' => __( 'We use own and third party cookies to improve our services and show related advertising to your preferences by analyzing your browsing habits. If you go on surfing, we will consider you accepting its use. You can change the settings or get more information using the link below.', CSL_TEXT_DOMAIN_PREFIX ),
			'bannerButton' => __( 'I Accept', CSL_TEXT_DOMAIN_PREFIX ),
			'bannerLinkURL' => get_home_url() . __( '/legal', CSL_TEXT_DOMAIN_PREFIX ),
			'bannerLinkText' => __( 'Get more info about legal and security conditions', CSL_TEXT_DOMAIN_PREFIX ),
		);
        $a_tr_FE_clusterer = array(
            'assetsURI' => get_template_directory_uri() . '/assets',
        );

	    add_thickbox();
		
		wp_register_script( 'csl-google-apis', 'https://maps.googleapis.com/maps/api/js?libraries=visualization,places&sensor=false', array('jquery'), '', true );
		wp_register_script( 'csl-google-jsapi', 'https://www.google.com/jsapi', array('jquery'), '', true );
		wp_register_script( 'csl-google-markerclusterer', get_template_directory_uri() . '/assets/js/markerclusterer.js', array('jquery'), '', true );
		wp_register_script( 'csl-bootstrap-script', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', array('jquery'), '', true );
		wp_register_script( 'csl-datatables-js', 'https://cdn.datatables.net/s/bs/jszip-2.5.0,pdfmake-0.1.18,dt-1.10.10,b-1.1.0,b-html5-1.1.0,b-print-1.1.0/datatables.min.js', array('jquery'), '', true );

		wp_register_script( 'csl-d3-js', 'https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js', array('jquery'), '', true );
		wp_register_script( 'csl-touch-js', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js', array('jquery'), '', true );
		wp_register_script( 'csl-csv-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-csv/0.71/jquery.csv-0.71.min.js', array('jquery'), '', true );
		wp_register_script( 'csl-c3-js', 'https://cdnjs.cloudflare.com/ajax/libs/c3/0.4.10/c3.min.js', array('jquery'), '', true );

		$pivot_locale = strtolower( explode( "_", get_locale() )[0] );
		wp_register_script( 'csl-pivot-js', get_template_directory_uri() . '/assets/js/pivot/pivot.js', array('jquery'), '', true );
		wp_register_script( 'csl-pivot-locale-js', get_template_directory_uri() . '/assets/js/pivot/pivot.' . $pivot_locale . '.js', array('jquery'), '', true );
		wp_register_script( 'csl-export-js', get_template_directory_uri() . '/assets/js/pivot/export_renderers.js', array('jquery'), '', true );
		wp_register_script( 'csl-rend-d3-js', get_template_directory_uri() . '/assets/js/pivot/d3_renderers.js', array('jquery'), '', true );
		wp_register_script( 'csl-rend-c3-js', get_template_directory_uri() . '/assets/js/pivot/c3_renderers.js', array('jquery'), '', true );
		wp_register_script( 'csl-chartify-js', get_template_directory_uri() . '/assets/js/chartify.js', array('jquery'), '', true );
        wp_register_script( 'csl-toursteps', get_template_directory_uri() . '/assets/js/tour/' . get_locale() . '_tour.js', array('jquery'), '', true );
        wp_register_script( 'csl-tour', get_template_directory_uri() . '/assets/js/bootstrap-tour.min.js', array('jquery'), '', true );
 
		wp_register_script( 'csl-fe-helper', get_template_directory_uri() . '/assets/js/csl-fe-helper.js', array('jquery','csl-datatables-js'), '', true );
	    
		wp_localize_script( 'csl-fe-helper', 'feTXT', $a_tr_FE_scripts );
		wp_localize_script( 'csl-google-markerclusterer', 'beCLS', $a_tr_FE_clusterer );

	    wp_enqueue_script( 'jquery-ui-core' );
	    wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'wp-pointer' );

		wp_enqueue_script( 'csl-google-apis' );
		wp_enqueue_script( 'csl-google-jsapi' );
		wp_enqueue_script( 'csl-google-markerclusterer' );
		wp_enqueue_script( 'csl-bootstrap-script' );
		wp_enqueue_script( 'csl-datatables-js' );

		wp_enqueue_script( 'csl-d3-js' );
		wp_enqueue_script( 'csl-touch-js' );
		wp_enqueue_script( 'csl-csv-js' );
		wp_enqueue_script( 'csl-c3-js' );

		wp_enqueue_script( 'csl-pivot-js' );
		wp_enqueue_script( 'csl-pivot-locale-js' );
		wp_enqueue_script( 'csl-export-js' );
		wp_enqueue_script( 'csl-rend-d3-js' );
		wp_enqueue_script( 'csl-rend-c3-js' );
		wp_enqueue_script( 'csl-chartify-js' );
        wp_enqueue_script( 'csl-toursteps' );
        wp_enqueue_script( 'csl-tour' );

		wp_enqueue_script( 'csl-fe-helper' );
        
	}
endif;

// BE. Register and enqueue CSS styles
if ( ! function_exists( 'csl_load_be_style_files' ) ) :
    function csl_load_be_style_files() {
	    wp_register_style( 'csl-font-awesome-style', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
	    wp_register_style( 'csl-jquery-ui-style', 'http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css' );
	    wp_register_style( 'csl-be', get_template_directory_uri() . '/assets/css/csl-be.css' );
		
		wp_enqueue_style( 'csl-font-awesome-style' );
		wp_enqueue_style( 'csl-jquery-ui-style' );			
		wp_enqueue_style( 'csl-be' );
    }
endif;


add_action( 'wp_head', 'csl_html_js_class', 1 );
add_action( 'admin_head', 'csl_insert_language_variable_in_head');
add_action( 'wp_head', 'csl_insert_language_variable_in_head');
add_action( 'wp_print_styles', 'csl_load_fe_style_files');
add_action( 'admin_enqueue_scripts', 'csl_admin_scripts' );
add_action( 'wp_enqueue_scripts', 'csl_frontend_scripts' );
add_action( 'admin_print_styles', 'csl_load_be_style_files');

?>
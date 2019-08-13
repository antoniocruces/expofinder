<?php

/**
 * CSL. Theme functions main file
 *
 * The functions file behaves like a WordPress Plugin, adding features and functionality to a WordPress site. 
 * You can use it to call functions, both PHP and built-in WordPress, and to define your own functions. 
 * You can produce the same results by adding code to a WordPress Plugin or through the WordPress Theme functions file.
 *
 * PHP version 5
 *
 * LICENSE: CreativeCommons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0),
 * available at https://creativecommons.org/licenses/by-sa/4.0/
 *
 * @category   Main theme files
 * @package    CSL ExpoFinder
 * @author     Antonio Cruces Rodríguez <antonio.cruces@uma.es>
 * @author     iArtHis_LAB Research Group http://iarthis.hdplus.es
 * @copyright  Copyleft 2015 Antonio Cruces Rodríguez & iArtHis_LAB Research Group
 * @license    https://creativecommons.org/licenses/by-sa/4.0/ CC BY-SA 4.0
 * @version    1.4.0 RC2
 * @link       http://admin.expofinder.es
 * @see        changelog.txt
 * @since      File available since v1.0.0 alpha
 */


/**
 * PRELIMINARY SETTINGS
 * Whole theme scope general difinitions, functions and includes
 */

// {{{ Includes
require_once get_template_directory() . '/includes/csl_custom_definitions_helper.php'; // Global scope definitions
require_once get_template_directory() . '/includes/csl_custom_scripts_helper.php'; // Scripts functions
require_once get_template_directory() . '/includes/csl_custom_global_helper.php'; // Global scope helpers functions
require_once get_template_directory() . '/includes/csl_custom_data_retrieval_helper.php'; // Database queries helper
require_once get_template_directory() . '/includes/csl_custom_post_helper.php'; // Custom posts creation
require_once get_template_directory() . '/includes/csl_custom_ajax_helper.php'; // Ajax operations functions
require_once get_template_directory() . '/includes/csl_custom_dashboard_helper.php'; // Custom dashboard creation
require_once get_template_directory() . '/includes/csl_custom_log_helper.php'; // Logs maintenance
require_once get_template_directory() . '/includes/csl_custom_shortcodes_helper.php'; // Shortcodes creation
require_once get_template_directory() . '/includes/csl_custom_local_avatars.php'; // Local avatars management
require_once get_template_directory() . '/includes/csl_custom_dashboard_chat.php'; // Local chat controlled by Dashboard help page
require_once get_template_directory() . '/includes/csl_custom_idle_autologout.php'; // Automatic logout for idle users
require_once get_template_directory() . '/includes/csl_custom_terms_management_helper.php'; // Tools for custom terms & taxonomies management
if( current_user_can( 'edit_users' ) ) {
    require_once get_template_directory() . '/includes/csl_custom_secure_xmlrpc_server.php'; // Secure XMLRPC management. Only can be managed by administrator
}
//require_once get_template_directory() . '/includes/csl_custom_admin_posts_helper.php'; // Admin-only custom post types (queries and others)
require_once get_template_directory() . '/includes/csl_custom_mail_helper.php'; // Mail utilities (via PHPMailer: settings for secured TLS or SSL SMTP server)
//require_once get_template_directory() . '/includes/csl_custom_survey_helper.php'; // Custom quality survey management

if( isset( $_GET['debug'] ) ) {
    require_once get_template_directory() . '/includes/csl_custom_profiling_tools.php'; // Profiling and performance tools
}
// }}}


// remove x-pingback HTTP header
add_filter('wp_headers', function($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});

// Security
//add_filter( 'rest_enabled', '__return_false' );
add_filter( 'rest_jsonp_enabled', '__return_false' );
add_filter( 'json_enabled', '__return_false' );
add_filter( 'json_jsonp_enabled', '__return_false' );
add_filter( 'xmlrpc_enabled', '__return_false' );

remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'adjacent_posts_rel_link' );

remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );

// {{{ Beagle. Cron scheduled task

/**
 * SCHEDULED BEAGLE EVENT
 * Controls and launches the Beagle cron scheduled process
 */

/**
 * csl_add_cron_new_intervals function.
 * 
 * Add weekly and monthly intervals to deault WPAF cron intervals.
 *
 * @access public
 * @param mixed $schedules
 * @return array $schedules
 */
function csl_add_cron_new_intervals($schedules) {
	$schedules['fourtimesdaily'] = array(
		'interval' => 21600,
		'display' => __('Four times a day', CSL_TEXT_DOMAIN_PREFIX)
	);
	$schedules['weekly'] = array(
		'interval' => 604800,
		'display' => __('Once weekly', CSL_TEXT_DOMAIN_PREFIX)
	);
	$schedules['monthly'] = array(
		'interval' => 2635200,
		'display' => __('Once a month', CSL_TEXT_DOMAIN_PREFIX)
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'csl_add_cron_new_intervals');

if(isset($csl_a_options['beagle_isactive'])) {
	$timestamp = wp_next_scheduled( 'beagle_hook' );
	if( $timestamp == false ){
		wp_schedule_event( 
			strtotime(date('Y-m-d', $csl_a_options['beagle_next_exec_date']) . ' 00:00:00'), 
			$csl_a_options['beagle_schedule_plan'], 
			'beagle_hook' 
		); 
	}
	add_action( 'beagle_hook', 'csl_cron_beagle_function' );
	
	/**
	 * csl_cron_beagle_function function.
	 * 
	 * Callback function for Beagle's cron schedules task launcher.
	 *
	 * @access public
	 * @return void
	 */
	function csl_cron_beagle_function() {
		$args = array(
		    'timeout'     => 500000,
		    'user-agent'  => CSL_NAME . ' ' . CSL_VERSION . '; ' . get_bloginfo( 'url' )
		);
		$response = wp_remote_get( get_template_directory_uri() . '/_csl_cron_beagle.php', $args );
		if( is_array($response) ) {
	        error_log(
	        	current_time("mysql") . ". SUCCESS FROM CRON JOB: " . implode(" ", array_slice(explode(PHP_EOL, $response['body']), -2)) . PHP_EOL, 
	        	3, 
	        	get_template_directory() . "/assets/logs/csl_cron_beagle.log"
	        );
		} else {
	        error_log(
	        	current_time("mysql") . ". ERROR FROM CRON JOB: Unrecoverable error during Beagle cron scheduled task execution.\n", 
	        	3, 
	        	get_template_directory() . "/assets/logs/csl_cron_beagle.log"
	        );
		}
	}	
} else {
	wp_clear_scheduled_hook( 'beagle_hook' );
}

// }}}

/**
 * INITIALIZATION TASK
 ********************************
 */

function csl_theme_switch_routines() {
    global $wpdb;
    
	$sql = "
        CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_total_meta AS 
        SELECT SQL_CACHE 
            p.post_type, 
            p.ID, 
            p.post_author, 
            u.display_name,
            p.post_modified, 
            p.post_date, 
            p.post_title, 
            p.post_excerpt, 
            p.post_status, 
            pm.meta_key, 
            pm.meta_value
        FROM 
            (
            {$wpdb->posts} p 
            LEFT JOIN 
            {$wpdb->users} u 
            ON 
            p.post_author = u.ID
            ) 
            LEFT JOIN 
            {$wpdb->postmeta} pm 
            ON 
            p.ID = pm.post_id
        WHERE
            p.post_type IN (
            '" . CSL_CUSTOM_POST_ENTITY_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_PERSON_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_BOOK_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_COMPANY_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME . "'
            )
            AND
            p.post_status IN ('publish') 
            AND
            pm.meta_key LIKE '" . CSL_DATA_FIELD_PREFIX . "%' 
        ORDER BY 
            p.post_type,
            u.display_name, 
            p.post_modified DESC, 
            p.post_date DESC;";
    $results = $wpdb->get_results($sql);
	$sql = "
        CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_total_taxonomy AS 
        SELECT SQL_CACHE 
            p.post_type, 
            p.ID, p.post_author, 
            u.display_name, 
            p.post_modified, 
            p.post_date, 
            p.post_title, 
            p.post_excerpt, 
            p.post_status, 
            tt.taxonomy, t.name
        FROM 
            (
            {$wpdb->posts} AS p 
            LEFT JOIN 
            {$wpdb->users} AS u 
            ON 
            p.post_author = u.ID
            ) 
            LEFT JOIN 
            (
            (
            {$wpdb->terms} AS t 
            RIGHT JOIN 
            {$wpdb->term_taxonomy} AS tt 
            ON t.term_id = tt.term_id
            ) 
            RIGHT JOIN 
            {$wpdb->term_relationships} AS tr
            ON tt.term_taxonomy_id = tr.term_taxonomy_id
            ) 
            ON p.ID = tr.object_id
        WHERE
            p.post_type IN (
            '" . CSL_CUSTOM_POST_ENTITY_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_PERSON_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_BOOK_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_COMPANY_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME . "'
            ) 
            AND
            p.post_status IN ('publish') 
        ORDER BY 
            p.post_type, 
            u.display_name, 
            p.post_modified DESC, 
            p.post_date DESC;";
    $results = $wpdb->get_results($sql);

	// Statistics view
	$sql = "
		CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_stats_terms AS
		SELECT SQL_CACHE
			wp.post_type,
			wp.post_title,
			wp.ID as post_id,
			wp.post_modified,
			u.display_name as post_author,
			'meta' as info_type,
			SUBSTRING(wm.meta_key, 10) as info_key,
			wm.meta_key as info_fieldname,
			wm.meta_value as info_value
		FROM 
			{$wpdb->posts} wp
			LEFT JOIN 
			{$wpdb->postmeta} wm 
			ON (wm.post_id = wp.ID AND wm.meta_key LIKE '_cp_%')
			LEFT JOIN
			{$wpdb->users} u 
			ON (wp.post_author = u.ID)
		WHERE
			wp.post_status = 'publish' 
			AND 
			wp.post_type IN (
            '" . CSL_CUSTOM_POST_ENTITY_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_PERSON_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_BOOK_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_COMPANY_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME . "'
            ) 
		UNION ALL
		SELECT 
			wp.post_type,
			wp.post_title,
			wp.ID as post_id,
			wp.post_modified,
			u.display_name as post_author,
			'taxonomy' as info_type,
			SUBSTRING(wtt.taxonomy, 5) as info_key,
			wtt.taxonomy as info_fieldname,
			wt.name as info_value
		FROM 
			{$wpdb->posts} wp
			LEFT JOIN 
			{$wpdb->term_relationships} wtr 
			ON (wp.ID = wtr.object_id)
			LEFT JOIN 
			{$wpdb->term_taxonomy} wtt 
			ON (wtr.term_taxonomy_id = wtt.term_taxonomy_id AND wtt.taxonomy like 'tax_%')
			LEFT JOIN 
			{$wpdb->terms} wt 
			ON (wt.term_id = wtt.term_id)
			LEFT JOIN
			{$wpdb->users} u 
			ON (wp.post_author = u.ID)
		WHERE
			wp.post_status = 'publish' 
			AND 
			wp.post_type IN (
            '" . CSL_CUSTOM_POST_ENTITY_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_PERSON_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_BOOK_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_COMPANY_TYPE_NAME . "',
            '" . CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME . "'
            ) 
		ORDER BY 
			post_type,
			post_title,
			info_type,
			info_key,
			info_value
	";
    $results = $wpdb->get_results($sql);
		
	// Unfolded postmeta view
	$sql = "
        CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_unfolded_postmeta AS
        SELECT SQL_CACHE
            x.ID,
            x.post_type,
            x.post_title,
            x.post_status,   
            x.meta_id,
            x.meta_key, 
            x.meta_value, 
            x.n_ext_id,
            x.s_geo_town,
            x.s_geo_region,
            x.s_geo_country,
            x.n_year,
            x.n_month,
            x.n_day,
            r.post_type AS s_ext_post_type,
            r.post_title AS s_ext_post_title,
            r.post_status AS s_ext_post_status
        FROM
            (
            SELECT
                p.ID,
                p.post_type,
                p.post_title,
                p.post_status,   
                m.meta_id,
                m.meta_key, 
                m.meta_value, 
                IF(
                    m.meta_value LIKE \"%: %\" 
                    AND 
                    CAST(SUBSTRING_INDEX(m.meta_value, \": \", 1) AS UNSIGNED) * 1 = CAST(SUBSTRING_INDEX(m.meta_value, \": \", 1) AS UNSIGNED), 
                    CAST(SUBSTRING_INDEX(m.meta_value, \": \", 1) AS UNSIGNED), 
                    NULL
                    ) AS n_ext_id,
                IF(m.meta_value LIKE \"%; %; %\", SUBSTRING_INDEX( m.meta_value, \"; \", 1 ), NULL) AS s_geo_town,
                IF(m.meta_value LIKE \"%; %; %\", SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, \"; \", 2 ), \"; \", -1 ), NULL) AS s_geo_region,
                IF(m.meta_key = \"_cp__peo_country\", m.meta_key, IF(m.meta_value LIKE \"%; %; %\", SUBSTRING_INDEX( m.meta_value, \"; \", -1 ), NULL)) AS s_geo_country,
                IF(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\"), STR_TO_DATE(m.meta_value, \"%Y-%m-%d\"), NULL) AS d_date,
                IF(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\"), YEAR(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\")), NULL) AS n_year,
                IF(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\"), MONTH(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\")), NULL) AS n_month,
                IF(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\"), DAY(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\")), NULL) AS n_day
            FROM 
                {$wpdb->postmeta} AS m 
                LEFT JOIN
                {$wpdb->posts} AS p
                ON
                p.ID = m.post_id
            ) AS x
            LEFT JOIN
            {$wpdb->posts} AS r
            ON
            r.ID = x.n_ext_id
	";
    $results = $wpdb->get_results($sql);

	// Unfolded taxonomies view
	$sql = "
        CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_unfolded_taxonomies AS
        SELECT SQL_CACHE 
            p.ID, 
            p.post_type, 
            p.post_title, 
            tt.taxonomy,
            t.name
        FROM 
            (
            {$wpdb->posts} AS p 
            LEFT JOIN 
            {$wpdb->term_relationships} AS tr 
            ON p.ID = tr.object_id
            ) 
            LEFT JOIN 
            (
            {$wpdb->terms} AS t 
            RIGHT JOIN 
            {$wpdb->term_taxonomy} AS tt 
            ON t.term_id = tt.term_id
            ) 
            ON 
            tr.term_taxonomy_id = tt.term_taxonomy_id
	";
    $results = $wpdb->get_results($sql);

	// Unfolded people
	$sql = "
        CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_unfolded_person AS
        SELECT SQL_CACHE
            p.ID,
            p.post_type,
            p.post_title,
            t.meta_value AS s_person_type,
            c.meta_value AS s_country,
            b.n_year AS n_birth_year,    
            d.n_year AS n_death_year,
            IF(d.n_year IS NOT NULL, d.n_year - b.n_year, YEAR(CURDATE()) - b.n_year) AS n_age,
            IF(b.n_year IS NOT NULL, (b.n_year DIV 100)+1, NULL) AS n_birth_century,
            IF(d.n_year IS NOT NULL, (d.n_year DIV 100)+1, NULL) AS n_death_century,
            IF(b.n_year IS NOT NULL, IF(b.n_year MOD 100 > 49, 2, 1), NULL) AS n_birth_half_century,
            IF(d.n_year IS NOT NULL, IF(d.n_year MOD 100 > 49, 2, 1), NULL) AS n_death_half_century,
            g.meta_value AS s_gender,
            y.taxonomy AS s_taxonomy,
            y.name AS s_term
        FROM
            {$wpdb->posts} AS p
            LEFT JOIN
            {$wpdb->prefix}xtr_vw_unfolded_postmeta AS t
            ON
            t.ID = p.ID
            LEFT JOIN
            {$wpdb->prefix}xtr_vw_unfolded_postmeta AS c
            ON
            c.ID = p.ID
            LEFT JOIN
            {$wpdb->prefix}xtr_vw_unfolded_postmeta AS b
            ON
            b.ID = p.ID
            LEFT JOIN
            {$wpdb->prefix}xtr_vw_unfolded_postmeta AS d
            ON
            d.ID = p.ID
            LEFT JOIN
            {$wpdb->prefix}xtr_vw_unfolded_postmeta AS g
            ON
            g.ID = p.ID
            LEFT JOIN
            {$wpdb->prefix}xtr_vw_unfolded_taxonomies AS y
            ON
            y.ID = p.ID
        WHERE
            p.post_status = \"publish\"
            AND
            p.post_type = \"" . CSL_CUSTOM_POST_PERSON_TYPE_NAME . "\"
            AND
            t.meta_key = \"_cp__peo_person_type\"
            AND
            c.meta_key = \"_cp__peo_country\"
            AND
            b.meta_key = \"_cp__peo_birth_date\"
            AND
            d.meta_key = \"_cp__peo_death_date\"
            AND
            g.meta_key = \"_cp__peo_gender\";        
	";
    $results = $wpdb->get_results($sql);

	// Unfolded exhibitions
	$sql = "
        CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_unfolded_exhibition AS
        SELECT SQL_CACHE
            p.ID,
            p.post_type,
            p.post_title,
            p.post_date,
            p.post_modified,
		    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, \",\", 1 ), \",\", -1 ) AS n_latitude,
		    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, \",\", 2 ), \",\", -1 ) AS n_longitude,
            c.s_geo_country AS s_geo_country,
            c.s_geo_region AS s_geo_region,
            c.s_geo_town AS s_geo_town,
            s.n_year AS n_start_year,    
            s.n_month AS n_start_month,    
            s.n_day AS n_start_day,
            s.meta_value AS n_start_date,    
            e.n_year AS n_end_year,    
            e.n_month AS n_end_month,    
            e.n_day AS n_end_day,
            e.meta_value AS n_end_date,    
            IF(e.meta_value IS NOT NULL, DATEDIFF(e.meta_value, s.meta_value), NULL) AS n_duration,
            y.taxonomy AS s_taxonomy,
            y.name AS s_term
        FROM
            {$wpdb->posts} AS p
		    LEFT JOIN
		    {$wpdb->prefix}xtr_vw_unfolded_postmeta AS g
		    ON
		    g.ID = p.ID
            LEFT JOIN
            {$wpdb->prefix}xtr_vw_unfolded_postmeta AS c
            ON
            c.ID = p.ID
            LEFT JOIN
            {$wpdb->prefix}xtr_vw_unfolded_postmeta AS s
            ON
            s.ID = p.ID
            LEFT JOIN
            {$wpdb->prefix}xtr_vw_unfolded_postmeta AS e
            ON
            e.ID = p.ID
            LEFT JOIN
            {$wpdb->prefix}xtr_vw_unfolded_taxonomies AS y
            ON
            y.ID = p.ID
        WHERE
            p.post_status = \"publish\"
            AND
            p.post_type = \"exhibition\"
		    AND
		    g.meta_key = \"_cp__exh_coordinates\"
            AND
            c.meta_key = \"_cp__exh_exhibition_town\"
            AND
            s.meta_key = \"_cp__exh_exhibition_start_date\"
            AND
            e.meta_key = \"_cp__exh_exhibition_end_date\"
	";
    $results = $wpdb->get_results($sql);

	// Unfolded entities
	$sql = "
		CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_unfolded_entity AS
		SELECT SQL_CACHE
		    p.ID,
		    p.post_type,
		    p.post_title,
		    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, \",\", 1 ), \",\", -1 ) AS n_latitude,
		    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, \",\", 2 ), \",\", -1 ) AS n_longitude,
		    c.s_geo_country AS s_geo_country,
		    c.s_geo_region AS s_geo_region,
		    c.s_geo_town AS s_geo_town,
		    y.taxonomy AS s_taxonomy,
		    y.name AS s_term,
		    h.meta_value AS s_html_uri,
		    r.meta_value AS s_rss_uri,
		    w.ID as n_exh_id,
		    w.post_title as s_exh_title
		FROM
		    {$wpdb->posts} AS p
		    LEFT JOIN
		    {$wpdb->prefix}xtr_vw_unfolded_postmeta AS g
		    ON
		    p.ID = g.ID
		    LEFT JOIN
		    {$wpdb->prefix}xtr_vw_unfolded_postmeta AS c
		    ON
		    p.ID = c.ID
		    LEFT JOIN
		    {$wpdb->prefix}xtr_vw_unfolded_postmeta AS h
		    ON
		    p.ID = h.ID
		    LEFT JOIN
		    {$wpdb->prefix}xtr_vw_unfolded_postmeta AS r
		    ON
		    p.ID = r.ID
		    LEFT JOIN
		    {$wpdb->prefix}xtr_vw_unfolded_postmeta AS w
		    ON
		    p.ID = w.n_ext_id
		    LEFT JOIN
		    {$wpdb->prefix}xtr_vw_unfolded_taxonomies AS y
		    ON
		    p.ID = y.ID
		WHERE
		    p.post_status = \"publish\"
		    AND
		    p.post_type = \"entity\"
		    AND
		    g.meta_key = \"_cp__ent_coordinates\"
		    AND
		    c.meta_key = \"_cp__ent_town\"
		    AND
		    h.meta_key = \"_cp__ent_html_uri\"
		    AND
		    r.meta_key = \"_cp__ent_rss_uri\"
		    AND
		    w.meta_key IN (\"_cp__exh_source_entity\", \"_cp__exh_info_source\")
		    AND
		    y.taxonomy IN (\"tax_typology\", \"tax_ownership\");
	";
    $results = $wpdb->get_results($sql);

	// URIs productivity
	$sql = "
		CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_uris_productivity AS
		SELECT SQL_CACHE
		    p.ID,
		    p.post_type,
		    p.post_title,
		    y.name AS term,
		    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, \",\", 1 ), \",\", -1 ) AS latitude,
		    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, \",\", 2 ), \",\", -1 ) AS longitude,
		    SUBSTRING_INDEX( c.meta_value, \"; \", -1 ) AS country,
		    SUBSTRING_INDEX( SUBSTRING_INDEX( c.meta_value, \"; \", 2 ), \"; \", -1 ) AS region,
		    SUBSTRING_INDEX( c.meta_value, \"; \", 1 ) AS town,
		    COUNT(DISTINCT h.meta_id) AS html_uris,
		    COUNT(DISTINCT r.meta_id) AS rss_uris,
		    COUNT(DISTINCT x.meta_id) AS exhibitions
		FROM
		    {$wpdb->posts} AS p
		    LEFT JOIN
		    {$wpdb->postmeta} AS g
		    ON
		    g.post_id = p.ID
		    LEFT JOIN
		    {$wpdb->postmeta} AS c
		    ON
		    c.post_id = p.ID
		    LEFT JOIN
		    (SELECT post_id, meta_id FROM {$wpdb->postmeta} WHERE meta_key = \"_cp__ent_html_uri\") AS h
		    ON
		    h.post_id = p.ID
		    LEFT JOIN
		    (SELECT post_id, meta_id FROM {$wpdb->postmeta} WHERE meta_key = \"_cp__ent_rss_uri\") AS r
		    ON
		    r.post_id = p.ID
		    LEFT JOIN
		    (SELECT ID, name FROM {$wpdb->prefix}xtr_vw_unfolded_taxonomies WHERE taxonomy = \"tax_typology\") AS y
		    ON
		    y.ID = p.ID
		    LEFT JOIN
		    (SELECT meta_id,meta_value FROM {$wpdb->postmeta} WHERE meta_key = \"_cp__exh_info_source\") AS x
		    ON
		    CONCAT(p.ID, \": \", p.post_title) = x.meta_value
		WHERE
		    p.post_status = \"publish\"
		    AND
		    p.post_type = \"entity\"
		    AND
		    g.meta_key = \"_cp__ent_coordinates\"
		    AND
		    c.meta_key = \"_cp__ent_town\"
		GROUP BY
		    p.ID,
		    p.post_type,
		    p.post_title,
		    y.name,
		    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, \",\", 1 ), \",\", -1 ),
		    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, \",\", 2 ), \",\", -1 ),
		    SUBSTRING_INDEX( c.meta_value, \"; \", -1 ),
		    SUBSTRING_INDEX( SUBSTRING_INDEX( c.meta_value, \"; \", 2 ), \"; \", -1 ),
		    SUBSTRING_INDEX( c.meta_value, \"; \", 1 )
	";
    $results = $wpdb->get_results($sql);

	// Valid exhibitions and Beagle yield
	$sql = "
		CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_valid_exhibitions_and_beagle_yield AS
		SELECT SQL_CACHE
			e.capture_date,
			DATE_FORMAT(b.log_date, \"%Y-%m-%d\") AS log_date,
			MAX(b.checked_uris) AS checked_uris,
			MAX(b.valid_uris) AS valid_uris,
			MAX(b.checked_entries) AS checked_entries,
			MAX(b.sapfull_entries) AS sapfull_entries,
			MAX(b.added_entries) AS added_entries,
			MAX(e.n_exhibitions) AS valid_exhibitions
		FROM 
			(
			SELECT
				DATE_FORMAT(post_date, \"%Y-%m-%d\") AS capture_date,
				COUNT(DISTINCT ID) AS n_exhibitions
			FROM
				{$wpdb->prefix}xtr_vw_unfolded_exhibition 
			GROUP BY
				DATE_FORMAT(post_date, \"%Y-%m-%d\")
			) AS e 
			LEFT JOIN 
			{$wpdb->prefix}xtr_beaglecr_log AS b 
			ON
			e.capture_date = DATE_FORMAT(b.log_date, \"%Y-%m-%d\")
		 GROUP BY 
			e.capture_date,
			DATE_FORMAT(b.log_date, \"%Y-%m-%d\")
		 ORDER BY
		 	e.capture_date
	";
    $results = $wpdb->get_results($sql);

	// Normalized relations
	$sql = "
		CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_normalized_relations AS
        SELECT SQL_CACHE
            m.post_id,
            m.meta_id,
            m.meta_key, 
            m.meta_value,
            p.ID AS n_int_id, 
            p.post_title AS s_int_post_title,
            p.post_type AS s_int_post_type,
            x.ID AS n_ext_id,
            x.post_title AS s_ext_post_title,
            x.post_type AS s_ext_post_type
        FROM
            {$wpdb->postmeta} AS m
            LEFT JOIN
            {$wpdb->posts} AS x
            ON
            CAST(SUBSTRING_INDEX(m.meta_value, \": \", 1) AS UNSIGNED) = x.ID
            LEFT JOIN
            {$wpdb->posts} AS p
            ON
            m.post_id = p.ID
        WHERE
            m.meta_key IN(\"" . implode( '","', CSL_NORMALIZED_RELATIONS_META_KEYS ) . "\")
            AND
            CAST(SUBSTRING_INDEX(m.meta_value, \": \", 1) AS UNSIGNED) <> 0
            AND
            x.post_status = \"publish\";
	";
    $results = $wpdb->get_results($sql);

	// Normalized dates
	$sql = "
		CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_normalized_dates AS
        SELECT SQL_CACHE
            p.ID, 
            p.post_title,
            p.post_type,
            m.meta_id,
            m.meta_key, 
            m.meta_value,
            STR_TO_DATE(m.meta_value, \"%Y-%m-%d\") AS d_date,
            YEAR(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\")) AS n_year,
            MONTH(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\")) AS n_month,
            DAY(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\")) AS n_day,
            (YEAR(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\")) DIV 100)+1 AS n_century,
            IF(YEAR(STR_TO_DATE(m.meta_value, \"%Y-%m-%d\")) MOD 100 > 49, 2, 1) AS n_half_century
        FROM
            {$wpdb->postmeta} AS m
            LEFT JOIN
            {$wpdb->posts} AS p
            ON
            m.post_id = p.ID
        WHERE
            m.meta_key IN(\"" . implode( '","', CSL_NORMALIZED_DATES_META_KEYS ) . "\") 
            AND
            STR_TO_DATE(m.meta_value, \"%Y-%m-%d\");
	";
    $results = $wpdb->get_results($sql);

	// Normalized places
	$sql = "
		CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_normalized_places AS
        SELECT SQL_CACHE
            p.ID, 
            p.post_title,
            p.post_type,
            m.meta_id,
            m.meta_key, 
            m.meta_value,
            IF(m.meta_key = \"_cp__peo_country\", m.meta_key, SUBSTRING_INDEX( m.meta_value, \"; \", -1 )) AS s_geo_country,
            SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, \"; \", 2 ), \"; \", -1 ) AS s_geo_region,
            SUBSTRING_INDEX( m.meta_value, \"; \", 1 ) AS s_geo_town
        FROM
            {$wpdb->postmeta} AS m
            LEFT JOIN
            {$wpdb->posts} AS p
            ON
            m.post_id = p.ID
        WHERE
            m.meta_key IN(\"" . implode( '","', CSL_NORMALIZED_PLACES_META_KEYS ) . "\") 
            AND
            m.meta_value LIKE \"%; %; %\";
    	";
    $results = $wpdb->get_results($sql);

	// Normalized coordinates
	$sql = "
		CREATE OR REPLACE VIEW {$wpdb->prefix}xtr_vw_normalized_coordinates AS
        SELECT SQL_CACHE
            p.ID, 
            p.post_title,
            p.post_type,
            m.meta_id,
            m.meta_key, 
            m.meta_value,
            m.meta_value AS s_coordinates,
            CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, \",\", 1 ), \",\", -1 ) AS DECIMAL(11,8)) AS n_latitude,
            CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, \",\", 2 ), \",\", -1 ) AS DECIMAL(11,8)) AS n_longitude
        FROM
            {$wpdb->postmeta} AS m
            LEFT JOIN
            {$wpdb->posts} AS p
            ON
            m.post_id = p.ID
        WHERE
            m.meta_key IN(\"" . implode( '","', CSL_NORMALIZED_COORDINATES_META_KEYS ) . "\") 
            AND
            m.meta_value IS NOT NULL;
    	";
    $results = $wpdb->get_results($sql);

	// New roles
    if( get_role( 'demo_users' ) ) remove_role( 'demo_users' );
	$rresult = add_role(
	    'demo_users',
	    __( 'Demo users', CSL_TEXT_DOMAIN_PREFIX),
	    array(
	        'read'                     => true,
	        'edit_posts'               => true,
	        'delete_posts'             => false,
	    )
	);

    if( get_role( 'test_authors' ) ) remove_role( 'test_authors' );
	$rresult = add_role(
	    'test_authors',
	    __( 'Test authors', CSL_TEXT_DOMAIN_PREFIX),
	    array(
            'edit_published_posts'    => true,
            'upload_files'            => true,
            'publish_posts'           => true,
            'delete_published_posts'  => true,
            'edit_posts'              => true,
            'delete_posts'            => true,
	        'read'                    => true,
	    )
	);

    if( get_role( 'test_contributors' ) ) remove_role( 'test_contributors' );
	$rresult = add_role(
	    'test_contributors',
	    __( 'Test contributors', CSL_TEXT_DOMAIN_PREFIX),
	    array(
            'edit_posts'              => true,
            'delete_posts'            => true,
	        'read'                    => true,
	    )
	);

    if( get_role( 'test_subscribers' ) ) remove_role( 'test_subscribers' );
	$rresult = add_role(
	    'test_subscribers',
	    __( 'Test subscribers', CSL_TEXT_DOMAIN_PREFIX),
	    array(
	        'read'                    => true,
	    )
	);

}
add_action('after_switch_theme', 'csl_theme_switch_routines');

function csl_add_categories_and_tags_for_pages() {
    register_taxonomy_for_object_type('post_tag', 'page');
    register_taxonomy_for_object_type('category', 'page');   
}
add_action( 'admin_init', 'csl_add_categories_and_tags_for_pages' );

function csl_init_routines() {
    global $csl_s_user_is_manager;

    // Connected users tracking
	$users = get_option( CSL_DATA_PREFIX . 'users', array() );
	if (!in_array(get_current_user_id(), $users)) {
		$users []= get_current_user_id();
	}    
    foreach($users as $k => $u) {
        if (false === get_transient( CSL_DATA_PREFIX . 'user_' . $u )) {
            if($u == get_current_user_id()) {
                set_transient( CSL_DATA_PREFIX . 'user_' . $u, current_time('timestamp') );
            } else {
                unset($users[$k]);
            }
        } else {
	        $old_transient = get_transient( CSL_DATA_PREFIX . 'user_' . $u);
			delete_transient( CSL_DATA_PREFIX . 'user_' . $u );
			set_transient( CSL_DATA_PREFIX . 'user_' . $u, $old_transient );
        }
    }
	update_option( CSL_DATA_PREFIX . 'users', $users );

    if(isset($_GET['debug']) && $csl_s_user_is_manager) {
        add_action('admin_notices', 'csl_list_performance');
    } 
        
}
add_action( 'init', 'csl_init_routines' );

function csl_add_custom_post_types_to_query( $query ) {
  if ( ( is_home() && $query->is_main_query() ) || $query->is_search() )
    $query->set( 'type', array( 
    	'post', 
    	'page', 
    	CSL_CUSTOM_POST_ENTITY_TYPE_NAME,
    	CSL_CUSTOM_POST_PERSON_TYPE_NAME,
    	CSL_CUSTOM_POST_BOOK_TYPE_NAME,
    	CSL_CUSTOM_POST_COMPANY_TYPE_NAME,
    	CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME,
    	CSL_CUSTOM_POST_ARTWORK_TYPE_NAME 
    ) );
  return $query;
}
add_action( 'pre_get_posts', 'csl_add_custom_post_types_to_query' );

/**
 * INCLUDE POSTMETA IN SEARCH
 */
 
/**
 * SORTING POSTS
 */

function csl_custom_posts_orderby( $orderby, $query ) {
	if( ! is_admin() ) {
	    if ( 'post' != $query->query_vars['post_type'] && 'page' != $query->query_vars['post_type'] ) {
	        // order by title
	        $orderby = "post_title ASC";
	    }
	}
    return $orderby;
}
add_filter('posts_orderby', 'csl_custom_posts_orderby', 10, 2 );
  
/**
 * Join posts and postmeta tables
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
function csl_search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {    
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' pm ON '. $wpdb->posts . '.ID = pm.post_id ';
    }
    
    return $join;
}
add_filter('posts_join', 'csl_search_join' ); 

/**
 * Modify the search query with posts_where
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
 */
function csl_search_where( $where ) {
    global $pagenow, $wpdb;
   
    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (pm.meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', 'csl_search_where' );

/**
 * Prevent duplicates
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
 */
function csl_search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', 'csl_search_distinct' );

/**
 * FILTER POSTS
 */


function csl_filter_by_post_type( $query ) {
	$post_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : false;
	if (!$post_type) {
		$post_type = 'any';
	}
    if ($query->is_search) {
        $query->set('type', $post_type);
    };
    return $query;
};
add_filter('pre_get_posts','csl_filter_by_post_type');


/**
 * STYLIZE LOGIN FORM
 */

function csl_login_stylesheet() {
    wp_enqueue_style( 'csl-login', get_template_directory_uri() . '/assets/css/csl-login.css' );
}
add_action( 'login_enqueue_scripts', 'csl_login_stylesheet' );

function csl_login_logo() { ?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <style type="text/css">
        body.login div#login h1 a {
            /*background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/assets/img/expofinderlogo_320x71.png'); 
            background-size: auto;
            width: auto;
            */
            display: none !important;
        }
    </style>
    <script type="text/javascript">
    var sec_messg = function() {
        alert(<?php _e("\"Important Notice.\\n\\nYou are about trying to access a restricted area of ​​this site. You are only authorized to access to information held in this area if you have a username and password. Otherwise, you should leave the area immediately. Access to restricted areas at this site is recorded, and the records are checked daily. Unauthorized access is a flagrant violation of the rules of the European Union (Directive 95/46 / EC, because the authorship of this application is made in Spain, a member of the EU), the Spanish legislation on data security ('Law 15/1999, of December 13, Protection of Personal Data' and 'Royal Decree 1720/2007 of 21 December, approving the Regulations implementing the Data Protection Act') and local laws the country or region where you are running the 'software' (in this case, Spain). Any suspected violation of these restrictions will be communicated immediately to the Europol Cybersecurity Group (EC3) and the local prosecutor, from Spain in this case, and requested relevant judicial and policing actions.\"", CSL_TEXT_DOMAIN_PREFIX); ?>);
    }
	window.onload = function() {
		//document.body.style.backgroundImage = "url(/wp-content/themes/csl/assets/img/seasons/<?php echo csl_get_season_prefix();?>0" + Math.floor(Math.random()*10) + ".jpg)";
		//document.body.className += " pattern0" + Math.floor(Math.random()*10);
	}
	
    </script>
<?php }
add_action( 'login_enqueue_scripts', 'csl_login_logo' );

add_filter( 'login_headertitle', create_function( false, "return '" . get_template_directory_uri() . '/assets/img/expofinderlogo_360x80.png' . "';" ) );

function csl_login_URL() {
    return get_site_url();;
}
add_filter('login_headerurl', 'csl_login_URL');

function csl_login_URL_text() {
    return CSL_NAME . '. Admin Backend';
}
add_filter('login_headertitle', 'csl_login_URL_text');

if ( ! function_exists( 'csl_load_script_files' ) ) :
	function csl_login_message() {
        $remoteip = csl_get_remote_client_ip();
        $remotelc = json_decode( file_get_contents( 'http://freegeoip.net/json/' . $remoteip ) );
        $location = $remotelc->city . ' (' . $remotelc->country_name . ', ' . $remotelc->region_name . ')';
        $useragnt = csl_parse_user_agent( $_SERVER['HTTP_USER_AGENT'] );
        $ltooltip = sprintf(
            __( 'Trying to start session from %s whit IP address %s using %s', CSL_TEXT_DOMAIN_PREFIX ),
            $location,
            $remoteip,
            $useragnt['platform'] . ' ' . $useragnt['browser'] . ' ' . $useragnt['version'] 
        ); 
        $maplocat = 'http://maps.googleapis.com/maps/api/staticmap?center=' . $remotelc->latitude . ',' . $remotelc->longitude . '&size=292x120&zoom=7&sensor=false';
	    return 
            '<h1 style="font-size: 46px; margin-bottom: 15px;">' . CSL_LOGO . '</h1>' . PHP_EOL . 
            '<p class="message">' .  
            sprintf(
                __('Welcome to security login screen. Fill out the form to access system and please read carefully our <i class="fa fa-shield" style="color: #2d99e5; margin-right: 2px;"></i><a onclick="sec_messg();" style="cursor:help;">Security & Legal Warning</a>.%s', CSL_TEXT_DOMAIN_PREFIX), 
                '<a href="#" data-tooltip data-tooltip-label="' . __( 'Remote data', CSL_TEXT_DOMAIN_PREFIX ) . '" data-tooltip-message="' . $ltooltip . '"><img id="locationmap" src="' . $maplocat . '" alt="" /></a>'
            ) . 
            '</p>';
	}
endif;
add_filter('login_message', 'csl_login_message');


// FRONT END. CSL FE Theme functions
// -----------------------------------------------------------------------------

// Set content-width
if ( ! isset( $content_width ) ) $content_width = 672;

// Theme setup
if ( ! function_exists( 'csl_setup' ) ) :
    function csl_setup() {
        global $wpdb;
    	add_theme_support( 'menus' );
    	add_theme_support( 'automatic-feed-links' );
    	add_theme_support( 'post-thumbnails' ); 
    	add_image_size( 'post-image', 800, 9999 );
    	//add_theme_support( 'post-formats', array( 'gallery', 'quote' ) );
    }
endif;
add_action( 'after_setup_theme', 'csl_setup' );

register_nav_menus( 
	array(
		'primary'	=>	__( 'Primary', CSL_TEXT_DOMAIN_PREFIX ),
		'secondary'	=>	__( 'Secondary', CSL_TEXT_DOMAIN_PREFIX ),
	)
);

// Custom title function
function csl_wp_title( $title, $sep ) {
	global $paged, $page;
	if ( is_feed() )
		return $title;
	$title .= get_bloginfo( 'name' );
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', CSL_TEXT_DOMAIN_PREFIX ), max( $paged, $page ) );
	return $title;
}
add_filter( 'wp_title', 'csl_wp_title', 10, 2 );

// Add classes to next_posts_link and previous_posts_link
function csl_posts_link_attributes_older() {
    return 'class="archive-nav-older"';
}
function csl_posts_link_attributes_newer() {
    return 'class="archive-nav-newer"';
}
add_filter('next_posts_link_attributes', 'csl_posts_link_attributes_older');
add_filter('previous_posts_link_attributes', 'csl_posts_link_attributes_newer');

// Custom more-link text
function csl_custom_more_link( $more_link, $more_link_text ) {
	return str_replace( $more_link_text, __('Read more', CSL_TEXT_DOMAIN_PREFIX) . ' &rarr;', $more_link );
}
add_filter( 'the_content_more_link', 'csl_custom_more_link', 10, 2 );

// Add class to the post and body elements if the post/page has a featured image
function csl_if_featured_image_class($classes) {
	global $post;
	if ( has_post_thumbnail() ) {
		$classes[] = 'has-featured-image';
	} else {
		$classes[] = 'no-featured-image';
	}
	return $classes;
}
add_filter('post_class','csl_if_featured_image_class');
add_filter('body_class','csl_if_featured_image_class');

function force_404() {
    global $wp_query; 
	$filter = isset($_REQUEST['f']) ? $_REQUEST['f'] : CSL_CUSTOM_POST_TYPE_ARRAY;
	if(!is_array($filter)) {
		if(!in_array($filter, CSL_CUSTOM_POST_TYPE_ARRAY)) {
	        status_header( 404 );
	        nocache_headers();
	        include( get_query_template( '404' ) );
	        die();
		}
	}
    if(is_page()){ // your condition
    }
}
add_action( 'wp', 'force_404' );

// USER WORK HOURS MANAGEMENT (SESSION MANAGEMENT)
// -----------------------------------------------------------------------------

function csl_track_login($login) {
    $user = get_user_by('login', $login);
    if($user->ID == 0)
        return;
    $curuser = $user->ID;
    csl_insert_log(array('user_id' => $curuser, 'activity' => 'log_in', 'object_type' => '@'));
	$users = get_option( CSL_DATA_PREFIX . 'users', array() );
	if (in_array($user->ID, $users)) {
		return;
	}
	$users[] = $user->ID;
	update_option( CSL_DATA_PREFIX . 'users', $users );
    if ( false === ( $user_transient = get_transient( CSL_DATA_PREFIX . 'user_' . $user->ID ) ) ) {
         set_transient( CSL_DATA_PREFIX . 'user_' . $user->ID, current_time('timestamp'), 15 * MINUTE_IN_SECONDS  );
    }
}
add_action( 'wp_login', 'csl_track_login', 99 );

function csl_track_logout($login) {
    $curuser = wp_get_current_user()->ID;
    csl_insert_log(array('user_id' => $curuser, 'activity' => 'log_out', 'object_type' => '@'));    
	$users = get_option( CSL_DATA_PREFIX . 'users', array() );
	$user_id = $curuser;
	if (!in_array($user_id, $users)){
		return;
	}
	update_option( CSL_DATA_PREFIX . 'users', array_diff($users , array($user_id)) );
    delete_transient( CSL_DATA_PREFIX . 'user_' . $user_id );
}
add_action('wp_logout', 'csl_track_logout', 99);

function csl_get_connected_users( $include = array(), $exclude = array() ){
	$users = get_option( CSL_DATA_PREFIX . 'users', array() );
	if ( is_string( $exclude ) ){
		$exclude = array_map( 'trim', explode( ',', $exclude ) );
	}
	$users = array_diff( (array) $users, (array) $exclude );
	if ( is_string( $include ) ){
		$include = array_map( 'trim', explode( ',', $include ) );
	}
	$users = array_merge( (array) $users, (array) $include );
	$users = array_unique( array_filter( (array) $users ) );
	return $users;
}

/*** INICIO CORTE ZONA CUSTOM POSTS ***/
/*** FIN CORTE ***/

/*************************************/

function csl_generic_custom_updated_messages( $messages ) {
    $screen           = get_current_screen();
	$post             = get_post();
	$post_type        = get_post_type( $post );
	$post_type_object = get_post_type_object( $post_type );
    $human_name       = get_locale() == 'es_ES' ? strtolower($post_type_object->labels->singular_name) : ucfirst($post_type_object->labels->singular_name) ;
	$messages[$post_type] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf(__('%s data have been saved.', CSL_TEXT_DOMAIN_PREFIX), $human_name),
		2  => __('User field data have been updated.', CSL_TEXT_DOMAIN_PREFIX),
		3  => __('User fields have been deleted.', CSL_TEXT_DOMAIN_PREFIX),
		4  => sprintf(__('%s data have been updated.', CSL_TEXT_DOMAIN_PREFIX), $human_name),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ?
                sprintf(__('%1$s data have been restored from a %2$s revision.', CSL_TEXT_DOMAIN_PREFIX), $human_name, wp_post_revision_title((int) $_GET['revision'], false)) : 
                false,
		6  => sprintf(__('%s data have been published.', CSL_TEXT_DOMAIN_PREFIX), $human_name),
		7  => sprintf(__('%s data have been stored.', CSL_TEXT_DOMAIN_PREFIX), $human_name),
		8  => sprintf(__('%s data have been sent.', CSL_TEXT_DOMAIN_PREFIX), $human_name),
		9  => sprintf(__('%1$s data publishing have been planned to <strong>%2$s</strong>.', CSL_TEXT_DOMAIN_PREFIX), $human_name, date_i18n('Y M j @ G:i', strtotime($post->post_date))),
		10 => sprintf(__('%s draft have been updated.', CSL_TEXT_DOMAIN_PREFIX), $human_name),
	);
	if ( $post_type_object->publicly_queryable ) {
		$permalink = get_permalink( $post->ID );

		$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), sprintf(__('See %s', CSL_TEXT_DOMAIN_PREFIX), strtolower($human_name)));
		$messages[ $post_type ][1] .= $view_link;
		$messages[ $post_type ][6] .= $view_link;
		$messages[ $post_type ][9] .= $view_link;

		$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
		$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), sprintf(__('%s preview', CSL_TEXT_DOMAIN_PREFIX), $human_name));
		$messages[ $post_type ][8]  .= $preview_link;
		$messages[ $post_type ][10] .= $preview_link;
	}

	return $messages;
}
add_filter( 'post_updated_messages', 'csl_generic_custom_updated_messages' );

/***************************************/
 
function csl_revisions_to_keep( $num, $post ) {
    if ( 'entity' == $post->post_type ||  'exhibition' == $post->post_type ) {
        return 0;
    }
    return $num;
}
add_filter( 'wp_revisions_to_keep', 'csl_revisions_to_keep', 10, 2 );

/***************************************/

/**
 * NAIVE BAYESIAN CLASSICATION FUNCTION
 */

function csl_naive_bayesian_classification( $text, $action ) {
    global $csl_config_nbc;
    
	require_once get_template_directory()  . '/includes/libraries/b8' . DIRECTORY_SEPARATOR . 'b8.php';
	try {
		$b8 = new b8($csl_config_nbc['general'], $csl_config_nbc['storage'], $csl_config_nbc['lexer'], $csl_config_nbc['degenerator']);
	}
	catch(Exception $e) {
        new WP_Error( 'nbc', __( 'Could not initialize NBC Engine [B8]', CSL_TEXT_DOMAIN_PREFIX ), $e->getMessage() );
		exit();
	}

    $text   = wp_kses($text, array());
    $text   = wp_strip_all_tags($text);
    $text   = csl_remove_stop_words($text);
    $text   = remove_accents(strtolower($text));
    $text   = sanitize_text_field($text);                           
    $text   = iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
    $text   = preg_replace("/[^a-zA-Z0-9\s]/", "", $text);                                
    $text   = preg_replace('/[0-9]+/', '', $text);
	$text   = stripslashes($text);
	$text   = strtolower(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
    $output = NULL;
        
	switch($action) {
		case 'SS': // Learn as SPAM
			$b8->learn($text, b8::SPAM);
			break;
		case 'SH': // Learn as HAM
			$b8->learn($text, b8::HAM);
			break;
		case 'DS': // Delete as SPAM
			$b8->unlearn($text, b8::SPAM);
			break;
		case 'DH': // Delete as HAM
			$b8->unlearn($text, b8::HAM);
			break;
		case 'CL': // Classify
        default:
			$output  = $b8->classify($text);
			break;
	}
    return !$output ? true : $output;    
}

function csl_remove_stop_words($txt, $lematize = false) {
    $aSW = array();
    $aTX = array();
    foreach(mb_split(' +', mb_strtolower($txt)) as $key => $value) {
        $aTX []= $lematize ? csl_get_stem(trim($value)) : trim($value);
    }
    foreach(explode("\n", file_get_contents( get_template_directory(). '/assets/keywords/' . get_locale() . '/' . get_locale() . ($lematize ? '.swl' : '.sws') )) as $key => $value) {
        $aSW []= mb_strtolower(trim($value), "utf-8");
    }
	return implode(' ', array_diff($aTX, $aSW));
}

function csl_get_stem($txt) {
	return trim(stemm_es::stemm($txt));
}

/* INICIO OJO INACABADA */

function csl_keyword_count_sort($first, $sec) {
	return $sec[1] - $first[1];
}

function csl_extract_keywords($str, $minWordLen = 3, $minWordOccurrences = 2, $asArray = false, $maxWords = 8, $restrict = false, $pst = false) {
	$str = str_replace(array("?","!",";","(",")",":","[","]"), " ", $str);
	$str = str_replace(array("\n","\r","  "), " ", $str);
	strtolower($str);
 
	$str = preg_replace('/[^\p{L}0-9 ]/', ' ', $str);
	$str = trim(preg_replace('/\s+/', ' ', $str));
	
	$words = explode(' ', $str);
 
	/* 	
	Only compare to common words if $restrict is set to false
	Tags are returned based on any word in text
	If we don't restrict tag usage, we'll remove common words from array 
	*/
 
	if ($restrict == false) {
		/* Full list of common words in the downloadable code */
		$commonWords = explode("\n", file_get_contents( get_template_directory(). '/assets/keywords/' . get_locale() . '/' . get_locale() . '.sws' ));
		$words = array_udiff($words, $commonWords,'strcasecmp');
	}
 
	/* Restrict Keywords based on values in the $allowedWords array */
	/* Use if you want to limit available tags */
	if ($restrict == true) {
		$allowedWords = array();
		foreach(explode("\n", remove_accents(file_get_contents( get_template_directory(). '/assets/keywords/' . get_locale() . '/' . get_locale() . ($pst ? '.pst' : '.kws') ))) as $k => $v) {
			$allowedWords []= $pst ? trim($v) : explode(',', $v)[2];	
		}
		if($pst) {
			foreach(explode("\n", remove_accents(file_get_contents( get_template_directory(). '/assets/keywords/' . get_locale() . '/' . get_locale() . '.kws' ))) as $k => $v) {
				$allowedWords []= explode(',', $v)[2];	
			}
		}
		$words = array_uintersect($words, $allowedWords, 'strcasecmp');
	}
 
	$keywords = array();
	
	while(($c_word = array_shift($words)) !== null) {
		if(strlen($c_word) < $minWordLen) continue;
		$c_word = strtolower($c_word);
		if(array_key_exists($c_word, $keywords)) {
			$keywords[$c_word][1]++;
		} else {
			$keywords[$c_word] = array($c_word, 1);
		}
	}
 
	usort($keywords, 'csl_keyword_count_sort');
	$final_keywords = array();
	
	foreach($keywords as $keyword_det) {
		if($keyword_det[1] < $minWordOccurrences) break;
		array_push($final_keywords, $keyword_det[0]);
	}
	
	$final_keywords = array_slice($final_keywords, 0, $maxWords);
	return $asArray ? $final_keywords : implode(', ', $final_keywords);
}
/* FIN OJO INACABADA */

/* PATCH: Word Popularity Calculation */
function cls_str_word_count_utf8($string) {
    preg_match_all("/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*/u",$string,$matches,PREG_PATTERN_ORDER);
    return $matches[0];
}

function csl_find_kw_value( $aKW, $sKW ) {
	foreach( $aKW as $key => $val ) {
		$aTMP = explode( ',', $val );
		if( $sKW == $aTMP[2] ) {
			return (int)$aKW[3];
			break;
		}
	}
	return 0;	
}

function cls_calculate_word_popularity( $string, $min_word_char = 3, $exclude_words = array() ) {
	$initial_words_array  =  str_word_count($string, 1);
	$total_words = sizeof($initial_words_array);
	$aVals = explode( "\n", file_get_contents(get_template_directory(). '/assets/keywords/' . get_locale() . '/' . get_locale() . '.kws' ) );

	$new_string = csl_remove_stop_words( $string );
	$words_array = cls_str_word_count_utf8($new_string, 1);
	
	$words_array = array_filter( $words_array, create_function('$var', 'return ( strlen($var) >= '.$min_word_char.' );' ) ) ;

	$popularity = array();

	$unique_words_array = array_unique( $words_array );

	$final_words_array  = $unique_words_array;

	foreach($final_words_array as $key => $word) {
		$nVal = csl_find_kw_value( $aVals, $word );
		if( $nVal > 0 ) {
			preg_match_all('/\b'.$word.'\b/i', $string, $out);
			$count = count($out[0]);
			$popularity[$key]['word']    = $word;
			$popularity[$key]['count']   = $count;
			$popularity[$key]['percent'] = $count / $total_words;
			$popularity[$key]['value']   = $nVal;
		}
	}

	usort( $popularity, create_function('$a,$b', 'return ( $a["count"] > $b["count"] ) ? +1 : -1;' ) );

	return $popularity;
}
/* PATCH END */

/*
function csl_log_trash_action( $post_id ) {
	csl_insert_log(array('object_id' => $post_id, 'object_type' => get_post_type( $post_id ), 'activity' => 'record_trash'));
    $post = get_post($post_id);
    if('exhibition' == $post->post_type) {
        $text = $post->post_title . ' ' . $post->post_excerpt . ' ' . $post->post_content;
        $dump = csl_naive_bayesian_classification($text, 'DH');  
        $dump = csl_naive_bayesian_classification($text, 'SS');  
    }
}
add_action( 'trashed_post', 'csl_log_trash_action' );

function csl_log_untrash_action( $post_id ) {
	csl_insert_log(array('object_id' => $post_id, 'object_type' => get_post_type( $post_id ), 'activity' => 'record_untrash'));  
    $post = get_post($post_id);
    if('exhibition' == $post->post_type) {
        $text = $post->post_title . ' ' . $post->post_excerpt . ' ' . $post->post_content;
        $dump = csl_naive_bayesian_classification($text, 'DS');
        $dump = csl_naive_bayesian_classification($text, 'SH');
    }  
}
add_action( 'untrashed_post', 'csl_log_untrash_action' );

function csl_log_status_change_action( $new_status, $old_status, $post ) {
    //if ( 'publish' !== $new_status ) // or 'publish' === $old_status ) 
    //    return;
    csl_insert_log(array('object_id' => $post->ID, 'object_type' =>$post->post_type, 'activity' => 'record_' . $new_status));
    if('exhibition' == $post->post_type && 'publish' == $new_status) {
        $text = $post->post_title . ' ' . $post->post_excerpt . ' ' . $post->post_content;
        $dump = csl_naive_bayesian_classification($text, 'DS');
        $dump = csl_naive_bayesian_classification($text, 'SH');
    }
}
add_action( 'transition_post_status', 'csl_log_status_change_action', 10, 3 );
*/

/* CUSTOM POST TYPE: Help tabs for all */

function csl_custom_help_tabs() {
	global $current_screen;
    global $wpdb;
    $screen = get_current_screen();
	    
    $contextual_help = file_get_contents(get_template_directory() . '/assets/docs/' . get_locale() . '/generic_help.html');

    $args = array(
        'id'      => 'hlp_tab_1',
        'title'   => __('Form completion help', CSL_TEXT_DOMAIN_PREFIX), 
        'content' => $contextual_help
    );
    $screen->add_help_tab( $args );

	if( $screen->base == 'edit' || $screen->base == 'post' ) {
	    $taxonomies_help = csl_get_all_taxonomies_and_terms( $current_screen->post_type );
		$curobject_label = strtolower( get_post_type_object( $current_screen->post_type )->labels->singular_name );
	    $args = array(
	        'id'      => 'hlp_tab_2',
	        'title'   => sprintf( 
	        				__('Terms and taxonomies for %s', CSL_TEXT_DOMAIN_PREFIX),
	        				$curobject_label ), 
	        'content' => $taxonomies_help
	    );
	    $screen->add_help_tab( $args );        

		$counts        = _csl_get_author_post_type_counts();
		$custom_string = '';
        $custom_list   = array();
        $wp_user_search = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users ORDER BY display_name, ID");
        foreach ( $wp_user_search as $userid ) {
    		$custom_column = array();
        	$user_id       = ( int ) $userid->ID;
        	$display_name  = stripslashes( $userid->display_name );

    		if ( isset( $counts[$user_id] ) && is_array($counts[$user_id] ) ) {
    			foreach( $counts[$user_id] as $count )
    				$custom_column []= "{$count['label']}: <strong>" . number_format_i18n( $count['count'], 0 ) . "</strong>";
                $custom_string = implode(", ", $custom_column);
                if (empty($custom_column)) {
                    $custom_string = __( 'No records', CSL_TEXT_DOMAIN_PREFIX );
                }
                $custom_list []= '<li>' . $display_name . ": " . $custom_string . '</li>' . PHP_EOL;
            }
        } 
	    $args = array(
	        'id'      => 'hlp_tab_3',
	        'title'   => __('Users and records', CSL_TEXT_DOMAIN_PREFIX),
	        'content' => '<ul>' . implode( '', $custom_list ) . '</ul>' . PHP_EOL,
	    );
	    $screen->add_help_tab( $args );        
               
	}
}
//add_action('admin_head', 'csl_custom_help_tabs');

function csl_get_all_taxonomies_and_terms( $post_type ) {
	global $current_screen;
	
	$strOutput = '';
	$args = array(
		'object_type' => array( $post_type ),
		'public'   => true,
		'_builtin' => false
	); 
	$output = 'objects'; // or objects
	$operator = 'and'; // 'and' or 'or'
	$taxonomies = get_taxonomies( $args, $output, $operator ); 
	$strOutput = '';
	if ( $taxonomies ) {
		$strOutput .= '<script type="text/javascript">
			(function(jQuery) {
			    jQuery.fn.minitabs = function() {
			        return this.each(function() {
			            jQuery(this).find(".tabnames li").on("click", jQuery.proxy(function(e){
			                jQuery(e.currentTarget).addClass("activetab").siblings().removeClass("activetab");
			                jQuery(this).find(".tabcontent").removeClass("activetab").eq(jQuery(e.currentTarget).index()).addClass("activetab");
			            }, this)).eq(0).trigger("click");
			        });
			    };
			})(jQuery);
			
			/* Run it! */
			jQuery(document).ready(function() {
			    jQuery(".minitabs, .verticaltabs").minitabs();
			});
			</script>
			';
		$strOutput .= '<div class="minitabs">' . PHP_EOL;
		$strOutput .= '<ul class="tabnames">' . PHP_EOL;
		foreach ( $taxonomies  as $taxonomy ) {
			$strOutput .= '<li>' . $taxonomy->labels->name . '</li>' . PHP_EOL;
		}
		$strOutput .= '</ul>' . PHP_EOL;
		foreach ( $taxonomies  as $taxonomy ) {
			$terms = get_terms( $taxonomy->name  );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$strOutput .=  '<div class="tabcontent" id="listtax' . $taxonomy->name . '"><h4>' . $taxonomy->labels->name . 
                    '&nbsp;<a class="no-print" href="javascript:jQuery(\'#listtax' . $taxonomy->name . '\').printThis();">' .
                    '<i class="fa fa-print"></i>' .
                    '</a></h4><ol>' . PHP_EOL;
				foreach ( $terms as $term ) {
					$strOutput .=  '<li>';
					$strOutput .= '<a href="' . admin_url() . '/edit.php?post_type=' . $post_type . '&' . $taxonomy->name . '=' . $term->slug . '">';
					$strOutput .= $term->name;
					$strOutput .= '</a><br />';
                    $strOutput .= '<small>' . $term->description . '</small>';
                    $strOutput .= '</li>' . PHP_EOL;
				}
				$strOutput .=  '</ol></div>' . PHP_EOL;
			} else {
				$strOutput .=  '<div class="tabcontent" id="listtax' . $taxonomy->name . '"><h4>' . $taxonomy->labels->name . 
                    '&nbsp;<a class="no-print" href="javascript:jQuery(\'#listtax' . $taxonomy->name . '\').printThis();">' .
                    '<i class="fa fa-print"></i>' .
                    '</a></h4><p>' . PHP_EOL;
				$strOutput .=  '<em>' . __( 'No terms in the taxonomy', CSL_TEXT_DOMAIN_PREFIX ) . '</em>' . PHP_EOL;
				$strOutput .=  '</p></div>' . PHP_EOL;
			}
		}
		$strOutput .= '</div>' . PHP_EOL;
	}
	return $strOutput;
}

function cls_lock_adding_new_taxonomies_for_authors( $term, $taxonomy ) {
    global $csl_s_user_is_manager;
    if(!in_array($taxonomy, CSL_CUSTOM_POST_TAXONOMIES_ARRAY))
        return $term;
    if(!$csl_s_user_is_manager) {
		add_action('admin_notices', 'cls_error_message_for_no_managers_adding_taxonomies');
        return new WP_Error( 'invalid_term', sprintf(__('You are not allowed to add the "%s" term to "%s" taxonomy.', CSL_TEXT_DOMAIN_PREFIX),
        $term,
        strtolower(get_taxonomy($taxonomy)->labels->singular_name)) );
		add_action('admin_notices', 'cls_error_message_for_no_managers_adding_taxonomies');
    }
    return $term;
}
add_filter( 'pre_insert_term', 'cls_lock_adding_new_taxonomies_for_authors', 20, 2 );

function cls_error_message_for_no_managers_adding_taxonomies() {
	return csl_format_admin_notice(
        __( "You're not allowed for adding taxonomies", CSL_TEXT_DOMAIN_PREFIX ),
        'exclamation-circle', 
        'error'
    );
}

// Filter draft post only by user
// Show only posts and media related to logged in author
add_action('pre_get_posts', 'query_set_only_author' );
function query_set_only_author( $wp_query ) {
    global $current_user;
    if( is_admin() && !current_user_can('edit_others_posts') && ( $wp_query->get( 'post_status') == 'draft') ) {
        $wp_query->set( 'author', $current_user->ID );
    }
}

?>
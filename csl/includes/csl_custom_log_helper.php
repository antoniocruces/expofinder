<?php

/**
* Store our table name in $wpdb with correct prefix
* Prefix will vary between sites so hook onto switch_blog too
* @since 1.0
*/
function csl_register_activity_log_table( ) {
	global $wpdb;
	$wpdb->xtr_activity_log = "{$wpdb->prefix}xtr_activity_log";
	$wpdb->xtr_urierror_log = "{$wpdb->prefix}xtr_urierror_log";
	$wpdb->xtr_beaglecr_log = "{$wpdb->prefix}xtr_beaglecr_log";
    // B8 Naive Bayesian Classifier
	$wpdb->xtr_nbc_b8_wordlist = "{$wpdb->prefix}xtr_nbc_b8_wordlist";    
    // Gantt Project data
	$wpdb->xtr_projectd_log = "{$wpdb->prefix}xtr_projectd_log";
    // Country codes data
	$wpdb->xtr_country_code = "{$wpdb->prefix}xtr_country_code";
}
add_action('init', 'csl_register_activity_log_table', 1);
add_action('after_switch_theme', 'csl_register_activity_log_table');

/**
* Creates our table
* Hooked onto activate_[plugin] (via register_activation_hook)
* @since 1.0
*/
function csl_create_activity_log_table( ) {
	global $wpdb;
	global $charset_collate;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	//Call this manually as we may have missed the init hook
    //NOTICE: Mandatory DOUBLE SPACE AFTER "PRIMARY KEY". So, it must be read as "PRIMARY KEY  (XXX)"
	csl_register_activity_log_table();
	$sql_create_table = "CREATE TABLE {$wpdb->xtr_activity_log} (
		log_id bigint(20) unsigned NOT NULL auto_increment,
		user_id bigint(20) unsigned NOT NULL default '0',
		activity varchar(30) NOT NULL default 'log_in',
		object_id bigint(20) unsigned NOT NULL default '0',
		object_type varchar(20) NOT NULL default 'post',
		activity_date datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (log_id),
		KEY abc (user_id)
		) $charset_collate; ";
	dbDelta($sql_create_table);
	$sql_create_table = "CREATE TABLE {$wpdb->xtr_urierror_log} (
		log_id int(11) NOT NULL AUTO_INCREMENT,
		log_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		post_author bigint(20) NOT NULL,
		invalid_rss_uri longtext COLLATE utf8mb4_unicode_ci,
		invalid_html_uri longtext COLLATE utf8mb4_unicode_ci,
        KEY def (log_date),
        KEY post_author (post_author)
        PRIMARY KEY  (log_id) 
        ) $charset_collate; ";
	dbDelta($sql_create_table);
	$sql_create_table = "CREATE TABLE {$wpdb->xtr_beaglecr_log} (
        log_id int(11) NOT NULL AUTO_INCREMENT,
        log_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        checked_uris bigint(20) NOT NULL,
        invalid_uris bigint(20) NOT NULL,
        valid_uris bigint(20) NOT NULL,
        sapless_uris bigint(20) NOT NULL,
        checked_entries bigint(20) NOT NULL,
        sapless_entries bigint(20) NOT NULL,
        sapfull_entries bigint(20) NOT NULL,
        added_entries bigint(20) NOT NULL,
        discarded_entries bigint(20) NOT NULL,
        entries_by_valid_uri float NOT NULL,
        entries_by_useful_uri float NOT NULL,
        operation_time int(11) NOT NULL,
        average_time float NOT NULL,
        PRIMARY KEY  (log_id) 
        KEY def (log_date)
        ) $charset_collate; ";
	dbDelta($sql_create_table);
    // B8 Naive Bayesian Classifier
	$sql_create_table = "CREATE TABLE {$wpdb->xtr_nbc_b8_wordlist} (
        token varchar(255) character set utf8 collate utf8_bin NOT NULL,
        count_ham int unsigned default NULL,
        count_spam int unsigned default NULL,
        PRIMARY KEY  (token)
        ) $charset_collate;
        INSERT IGNORE INTO {$wpdb->xtr_nbc_b8_wordlist} (token, count_ham) VALUES ('b8*dbversion', '3');
        INSERT IGNORE INTO {$wpdb->xtr_nbc_b8_wordlist} (token, count_ham, count_spam) VALUES ('b8*texts', '0', '0');       
        ";
	dbDelta($sql_create_table);
    // Gantt Project data
	$sql_create_table = "CREATE TABLE {$wpdb->xtr_projectd_log} (
        task_id varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        task_name varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        start_date date NOT NULL,
        end_date date NOT NULL,
        duration int(11) NOT NULL,
        percent_complete int(11) DEFAULT NULL,
        dependencies varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY  (task_id)
        ) $charset_collate;
        " . PHP_EOL . file_get_contents(get_template_directory() . '/assets/keywords/' . get_locale() . '/' . get_locale() . '.gnt.sql');
	dbDelta($sql_create_table);  
    // Country code data
	$sql_create_table = "CREATE TABLE {$wpdb->xtr_country_code} (
        country_id int(5) NOT NULL AUTO_INCREMENT,
        iso2 char(2) DEFAULT NULL,
        short_name varchar(80) NOT NULL DEFAULT '',
        long_name varchar(80) NOT NULL DEFAULT '',
        iso3 char(3) DEFAULT NULL,
        numcode varchar(6) DEFAULT NULL,
        un_member varchar(12) DEFAULT NULL,
        calling_code varchar(8) DEFAULT NULL,
        cctld varchar(5) DEFAULT NULL,
        spanish_name varchar(150) DEFAULT NULL,
        PRIMARY KEY  (country_id)
        ) $charset_collate;
        " . PHP_EOL . file_get_contents(get_template_directory() . '/assets/keywords/' . get_locale() . '/' . get_locale() . '_countries.sql');
	dbDelta($sql_create_table);  
}
//register_activation_hook(__FILE__,'csl_create_activity_log_table');
add_action('after_switch_theme', 'csl_create_activity_log_table');

function csl_get_log_table_columns( ) {
	return array(
		'log_id' => '%d',
		'user_id' => '%d',
		'activity' => '%s',
		'object_id' => '%d',
		'object_type' => '%s',
		'activity_date' => '%s'
	);
}

function csl_get_urierror_log_table_columns( ) {
	return array(
		'log_id' => '%d',
		'log_date' => '%d',
        'post_author' => '%d',
        'invalid_rss_uri' => '%s',
        'invalid_html_uri' => '%s',
	);
}

function csl_get_beaglecr_log_table_columns( ) {
	return array(
		'log_id' => '%d',
		'log_date' => '%d',
        'checked_uris' => '%d',
        'invalid_uris' => '%d',
        'valid_uris' => '%d',
        'sapless_uris' => '%d',
        'checked_entries' => '%d',
        'sapless_entries' => '%d',
        'sapfull_entries' => '%d',
        'added_entries' => '%d',
        'discarded_entries' => '%d',
        'entries_by_valid_uri' => '%d',
        'entries_by_useful_uri' => '%d',
        'operation_time' => '%d',
        'average_time' => '%d',
	);
}

/**
* Inserts a log into the database
*
*@param $data array An array of key => value pairs to be inserted
*@return int The log ID of the created activity log. Or WP_Error or false on failure.
*/
function csl_insert_log($data = array( )) {
	global $wpdb;
	//Set default values
	$data = wp_parse_args($data, array(
		'user_id' => get_current_user_id(),
		'date' => current_time('timestamp')
	));
	//Check date validity
	if (!is_float($data['date']) || $data['date'] <= 0)
		return 0;
	//Convert activity date from local timestamp to GMT mysql format
	$data['activity_date'] = date_i18n('Y-m-d H:i:s', $data['date'], true);
	//Initialise column format array
	$column_formats        = csl_get_log_table_columns();
	//Force fields to lower case
	$data                  = array_change_key_case($data);
	//White list columns
	$data                  = array_intersect_key($data, $column_formats);
	//Reorder $column_formats to match the order of columns given in $data
	$data_keys             = array_keys($data);
	$column_formats        = array_merge(array_flip($data_keys), $column_formats);
	$wpdb->insert($wpdb->xtr_activity_log, $data, $column_formats);
	return $wpdb->insert_id;
}

/**
* Inserts a urierror log into the database
*
*@param $data array An array of key => value pairs to be inserted
*@return int The log ID of the created activity log. Or WP_Error or false on failure.
*/
function csl_insert_urierror_log($data = array( )) {
	global $wpdb;

	//Set default values
	$data = wp_parse_args($data, array());
	$column_formats        = csl_get_urierror_log_table_columns();
	//White list columns
	$data                  = array_intersect_key($data, $column_formats);
	//Reorder $column_formats to match the order of columns given in $data
	$data_keys             = array_keys($data);
	$column_formats        = array_merge(array_flip($data_keys), $column_formats);
	$wpdb->insert($wpdb->xtr_urierror_log, $data, $column_formats);
	return $wpdb->insert_id;
}

/**
* Inserts a beaglecr log into the database
*
*@param $data array An array of key => value pairs to be inserted
*@return int The log ID of the created activity log. Or WP_Error or false on failure.
*/
function csl_insert_beaglecr_log($data = array( )) {
	global $wpdb;

	//Set default values
	$data = wp_parse_args($data, array());
	$column_formats        = csl_get_beaglecr_log_table_columns();
	//White list columns
	$data                  = array_intersect_key($data, $column_formats);
	//Reorder $column_formats to match the order of columns given in $data
	$data_keys             = array_keys($data);
	$column_formats        = array_merge(array_flip($data_keys), $column_formats);
	$wpdb->insert($wpdb->xtr_beaglecr_log, $data, $column_formats);
	return $wpdb->insert_id;
}

/**
* Updates an activity log with supplied data
*
*@param $log_id int ID of the activity log to be updated
*@param $data array An array of column=>value pairs to be updated
*@return bool Whether the log was successfully updated.
*/
function csl_update_log($log_id, $data = array( )) {
	global $wpdb;
	//Log ID must be positive integer
	$log_id = absint($log_id);
	if (empty($log_id))
		return false;
	//Convert activity date from local timestamp to GMT mysql format
	if (isset($data['activity_date']))
		$data['activity_date'] = date_i18n('Y-m-d H:i:s', $data['date'], true);
	//Initialise column format array
	$column_formats = csl_get_log_table_columns();
	//Force fields to lower case
	$data           = array_change_key_case($data);
	//White list columns
	$data           = array_intersect_key($data, $column_formats);
	//Reorder $column_formats to match the order of columns given in $data
	$data_keys      = array_keys($data);
	$column_formats = array_merge(array_flip($data_keys), $column_formats);
	if (false === $wpdb->update($wpdb->xtr_activity_log, $data, array(
		'log_id' => $log_id
	), $column_formats)) {
		return false;
	} //false === $wpdb->update($wpdb->xtr_activity_log, $data, array( 'log_id' => $log_id ), $column_formats)
	return true;
}

/**
* Retrieves activity logs from the database matching $query.
* $query is an array which can contain the following keys:
*
* 'fields' - an array of columns to include in returned roles. Or 'count' to count rows. Default: empty (all fields).
* 'orderby' - datetime, user_id or log_id. Default: datetime.
* 'order' - asc or desc
* 'user_id' - user ID to match, or an array of user IDs
* 'since' - timestamp. Return only activities after this date. Default false, no restriction.
* 'until' - timestamp. Return only activities up to this date. Default false, no restriction.
*
*@param $query Query array
*@return array Array of matching logs. False on error.
*/
function csl_get_logs($query = array( )) {
	global $wpdb;
	/* Parse defaults */
	$defaults  = array(
		'fields' => array( ),
		'orderby' => 'datetime',
		'order' => 'desc',
		'user_id' => false,
		'since' => false,
		'until' => false,
		'number' => -1,
		'offset' => 0
	);
 
	$query     = wp_parse_args($query, $defaults);
	/* Form a cache key from the query */
	$cache_key = 'csl_logs:' . md5(serialize($query));
	$cache     = wp_cache_get($cache_key);
	if (false !== $cache) {
		$cache = apply_filters('csl_get_logs', $cache, $query);
		return $cache;
	} //false !== $cache
	extract($query);
	/* SQL Select */
	//Whitelist of allowed fields
	$allowed_fields = csl_get_log_table_columns();
	if (is_array($fields)) {
		//Convert fields to lowercase (as our column names are all lower case - see part 1)
		$fields = array_map('strtolower', $fields);
		//Sanitize by white listing
		$fields = array_intersect($fields, $allowed_fields);
	} //is_array($fields)
	else {
		$fields = strtolower($fields);
	}
	//Return only selected fields. Empty is interpreted as all
	if (empty($fields)) {
		$select_sql = "SELECT * FROM {$wpdb->xtr_activity_log}";
	} //empty($fields)
	elseif ('count' == $fields) {
		$select_sql = "SELECT COUNT(*) FROM {$wpdb->xtr_activity_log}";
	} //'count' == $fields
	else {
		$select_sql = "SELECT " . implode(',', array_keys($fields)) . " FROM {$wpdb->xtr_activity_log}";
	}
	/*SQL Join */
	//We don't need this, but we'll allow it be filtered (see 'csl_logs_clauses' )
	$join_sql  = '';
	/* SQL Where */
	//Initialise WHERE
	$where_sql = 'WHERE 1=1';
	if (!empty($log_id))
		$where_sql .= $wpdb->prepare(' AND log_id=%d', $log_id);
	if (!empty($user_id)) {
		//Force $user_id to be an array
		if (!is_array($user_id))
			$user_id = array(
				$user_id
			);
		$user_id     = array_map('absint', $user_id); //Cast as positive integers
		$user_id__in = implode(',', $user_id);
		$where_sql .= " AND user_id IN($user_id__in)";
	} //!empty($user_id)
	$since = absint($since);
	$until = absint($until);
	if (!empty($since))
		$where_sql .= $wpdb->prepare(' AND activity_date >= %s', date_i18n('Y-m-d H:i:s', $since, true));
	if (!empty($until))
		$where_sql .= $wpdb->prepare(' AND activity_date <= %s', date_i18n('Y-m-d H:i:s', $until, true));
	/* SQL Order */
	//Whitelist order
	$order = strtoupper($order);
	$order = ('ASC' == $order ? 'ASC' : 'DESC');
	switch ($orderby) {
		case 'log_id':
			$order_sql = "ORDER BY log_id $order";
			break;
		case 'user_id':
			$order_sql = "ORDER BY user_id ASC, activity_date $order";
			break;
		case 'datetime':
			$order_sql = "ORDER BY activity_date $order";
		default:
			break;
	} //$orderby
	/* SQL Limit */
	$offset = absint($offset); //Positive integer
	if ($number == -1) {
		$limit_sql = "";
	} //$number == -1
	else {
		$number    = absint($number); //Positive integer
		$limit_sql = "LIMIT $offset, $number";
	}
	/* Filter SQL */
	$pieces  = array(
		'select_sql',
		'join_sql',
		'where_sql',
		'order_sql',
		'limit_sql'
	);
	$clauses = apply_filters('csl_logs_clauses', compact($pieces), $query);
	foreach ($pieces as $piece)
		$$piece = isset($clauses[$piece]) ? $clauses[$piece] : '';
	/* Form SQL statement */
	$sql = "$select_sql $where_sql $order_sql $limit_sql";
	if ('count' == $fields) {
		return $wpdb->get_var($sql);
	} //'count' == $fields
	/* Perform query */
	$logs = $wpdb->get_results($sql);
	/* Add to cache and filter */
	wp_cache_add($cache_key, $logs, 24 * 60 * 60);
	$logs = apply_filters('csl_get_logs', $logs, $query);
    //var_dump($sql);
    //wp_die();
	return $logs;
}

/**
* Retrieves grouped activity logs for login/logout ans session time stored using $query.
* $query is an array which can contain the following keys:
*
* 'sess_start' - string, activity meaning session start.
* 'sess_end' - string, activity meaning session end.
* 'grouping' - day, week, month, year (implies using a SQL funtion to group records by its date)
* 'user_id' - user ID to match, or an array of user IDs
* 'since' - timestamp. Return only activities after this date. Default false, no restriction.
* 'until' - timestamp. Return only activities up to this date. Default false, no restriction.
*
*@param $query Query array
*@return array Array of matching logs. False on error.
*/
function csl_get_grouped_logs($query = array( )) {
	global $wpdb;
	/* Parse defaults */
	$defaults  = array(
		'sess_start' => 'log_in',
		'sess_end' => 'log_out',
		'sql_date_format' => '%Y %m %d',
		'log_id' => false,
		'user_id' => false,
		'since' => false,
		'until' => false,
		'number' => -1,
		'offset' => 0,        
	);
	$query = wp_parse_args($query, $defaults);

	/* Form a cache key from the query */
	$cache_key = 'csl_grouped_logs:' . md5(serialize($query));
	$cache     = wp_cache_get($cache_key);
	if (false !== $cache) {
		$cache = apply_filters('csl_get_grouped_logs', $cache, $query);
		return $cache;
	} //false !== $cache
	extract($query);
	
	/* SQL Select */
	//Whitelist of allowed fields
    //{$wpdb->xtr_activity_log}, $sql_date_format, $sess_start, $sess_end
    $select_sql = "";
    $select_sql = "
        SELECT
            u.display_name AS s_display_name,
            d_activity_date AS d_activity_date,
            MAX(d_max_logout) AS d_max_logout,
            MAX(d_min_login) AS d_min_login,
            MAX(n_num_logins) AS n_num_logins,
            IF(MAX(n_num_logins) <> MAX(n_num_logouts), 'Yes', 'No') AS s_impaired_logouts,
            MAX(n_num_activities) AS n_num_activities,
            TIMEDIFF(MAX(d_max_logout), MAX(d_min_login)) AS t_sessions_time,
            TIMEDIFF(MAX(d_max_activity), MAX(d_min_activity)) AS t_activities_time,
            TIMEDIFF(MAX(d_max_logout), MAX(d_min_login)) / MAX(n_num_activities) AS t_activities_per_session,
            TIMEDIFF(MAX(d_max_activity), MAX(d_min_activity)) / MAX(n_num_activities) AS t_activity_average_time 
        FROM
            (
            SELECT
                user_id AS n_user_id,
                DATE_FORMAT(activity_date, \"$sql_date_format\") AS d_activity_date,
                MAX(activity_date) AS d_max_logout,
                COUNT(log_id) AS n_num_logouts,
                NULL AS d_min_login,
                NULL AS n_num_logins,
                NULL AS d_min_activity,
                NULL AS d_max_activity,
                NULL AS n_num_activities
            FROM
            	{$wpdb->xtr_activity_log}
            WHERE
            	activity=\"$sess_end\"
            GROUP BY
            	user_id,
                DATE_FORMAT(activity_date, \"$sql_date_format\")
        
            UNION ALL
        
            SELECT
                user_id AS n_user_id,
                DATE_FORMAT(activity_date, \"$sql_date_format\") AS d_activity_date,
                NULL AS d_max_logout,
                NULL AS n_num_logouts,
                MIN(activity_date) AS d_min_login,
                COUNT(log_id) AS n_num_logins,
                NULL AS d_min_activity,
                NULL AS d_max_activity,
                NULL AS n_num_activities
            FROM
            	{$wpdb->xtr_activity_log}
            WHERE
            	activity=\"$sess_start\"
            GROUP BY
            	user_id,
                DATE_FORMAT(activity_date, \"$sql_date_format\")
        
            UNION ALL
        
            SELECT
                user_id AS n_user_id,
                DATE_FORMAT(activity_date, \"$sql_date_format\") AS d_activity_date,
                NULL AS d_max_logout,
                NULL AS n_num_logouts,
                NULL AS d_min_login,
                NULL AS n_num_logins,
                MIN(activity_date) AS d_min_activity,
                MAX(activity_date) AS d_max_activity,
                COUNT(log_id) AS n_num_activities
            FROM
            	{$wpdb->xtr_activity_log}
            WHERE
            	object_type <> \"@\"
            GROUP BY
            	user_id,
                DATE_FORMAT(activity_date, \"$sql_date_format\")
            ) AS l
            INNER JOIN
            {$wpdb->users} AS u
            ON
            l.n_user_id = u.ID
        ";
    
	/*SQL Join */
	//We don't need this, but we'll allow it be filtered (see 'csl_logs_clauses' )
	$join_sql  = '';
	
	/* SQL Where */
	//Initialise WHERE
	$where_sql = 'WHERE 1=1';
	if (!empty($log_id))
		$where_sql .= $wpdb->prepare(' AND log_id=%d', $log_id);
	if (!empty($user_id)) {
		//Force $user_id to be an array
		if (!is_array($user_id))
			$user_id = array(
				$user_id
			);
		$user_id     = array_map('absint', $user_id); //Cast as positive integers
		$user_id__in = implode(',', $user_id);
		$where_sql .= " AND n_user_id IN($user_id__in)";
	} //!empty($user_id)
	$since = absint($since);
	$until = absint($until);
	if (!empty($since))
		$where_sql .= $wpdb->prepare(' AND activity_date >= %s', date_i18n('Y-m-d H:i:s', $since, true));
	if (!empty($until))
		$where_sql .= $wpdb->prepare(' AND activity_date <= %s', date_i18n('Y-m-d H:i:s', $until, true));
		
	/* SQL Order */
	//Whitelist order
    $order_sql = " GROUP BY u.display_name, u.ID, d_activity_date ORDER BY u.display_name, u.ID, d_activity_date DESC";
	
	/* SQL Limit */
	$offset = absint($offset); //Positive integer
	if ($number == -1) {
		$limit_sql = "";
	} //$number == -1
	else {
		$number    = absint($number); //Positive integer
		$limit_sql = "LIMIT $offset, $number";
	}
	
	/* Filter SQL */
	$pieces  = array(
		'select_sql',
		'join_sql',
		'where_sql',
		'order_sql',
		'limit_sql'
	);
	$clauses = apply_filters('csl_logs_clauses', compact($pieces), $query);
	foreach ($pieces as $piece)
		$$piece = isset($clauses[$piece]) ? $clauses[$piece] : '';

	/* Form SQL statement */
	$sql = "$select_sql $where_sql $order_sql $limit_sql";

	/* Perform query */
	$logs = $wpdb->get_results($sql);
	/* Add to cache and filter */
	wp_cache_add($cache_key, $logs, 24 * 60 * 60);
	$logs = apply_filters('csl_get_grouped_logs', $logs, $query);
	return $logs;
}

/**
* Deletes an activity log from the database
*
*@param $log_id int ID of the activity log to be deleted
*@return bool Whether the log was successfully deleted.
*/
function csl_delete_log($log_id) {
	global $wpdb;
	//Log ID must be positive integer
	$log_id = absint($log_id);
	if (empty($log_id))
		return false;
	do_action('csl_delete_log', $log_id);
	$sql = $wpdb->prepare("DELETE from {$wpdb->xtr_activity_log} WHERE log_id = %d", $log_id);
	if (!$wpdb->query($sql))
		return false;
	do_action('csl_deleted_log', $log_id);
	return true;
}

/**
* Retrieves Beagle CR logs from the database matching $query.
* $query is an array which can contain the following keys:
*
* 'fields' - an array of columns to include in returned roles. Or 'count' to count rows. Default: empty (all fields).
* 'orderby' - datetime, user_id or log_id. Default: datetime.
* 'order' - asc or desc
* 'user_id' - user ID to match, or an array of user IDs
* 'since' - timestamp. Return only activities after this date. Default false, no restriction.
* 'until' - timestamp. Return only activities up to this date. Default false, no restriction.
*
*@param $query Query array
*@return array Array of matching logs. False on error.
*/
function csl_get_beaglecr_logs($query = array( )) {
	global $wpdb;
	/* Parse defaults */
	$defaults  = array(
		'fields' => array( ),
		'orderby' => 'datetime',
		'order' => 'desc',
		'user_id' => false,
		'since' => false,
		'until' => false,
		'number' => -1,
		'offset' => 0
	);
 
	$query     = wp_parse_args($query, $defaults);
	/* Form a cache key from the query */
	$cache_key = 'csl_beaglecr_logs:' . md5(serialize($query));
	$cache     = wp_cache_get($cache_key);
	if (false !== $cache) {
		$cache = apply_filters('csl_get_beaglecr_logs', $cache, $query);
		return $cache;
	} //false !== $cache
	extract($query);
	/* SQL Select */
	//Whitelist of allowed fields
	$allowed_fields = csl_get_log_table_columns();
	if (is_array($fields)) {
		//Convert fields to lowercase (as our column names are all lower case - see part 1)
		$fields = array_map('strtolower', $fields);
		//Sanitize by white listing
		$fields = array_intersect($fields, $allowed_fields);
	} //is_array($fields)
	else {
		$fields = strtolower($fields);
	}
	//Return only selected fields. Empty is interpreted as all
	if (empty($fields)) {
		$select_sql = "SELECT * FROM {$wpdb->xtr_beaglecr_log}";
	} //empty($fields)
	elseif ('count' == $fields) {
		$select_sql = "SELECT COUNT(*) FROM {$wpdb->xtr_beaglecr_log}";
	} //'count' == $fields
	else {
		$select_sql = "SELECT " . implode(',', array_keys($fields)) . " FROM {$wpdb->xtr_beaglecr_log}";
	}
	/*SQL Join */
	//We don't need this, but we'll allow it be filtered (see 'csl_beaglecr_logs_clauses' )
	$join_sql  = '';
	/* SQL Where */
	//Initialise WHERE
	$where_sql = 'WHERE 1=1';
	if (!empty($log_id))
		$where_sql .= $wpdb->prepare(' AND log_id=%d', $log_id);
	if (!empty($user_id)) {
		//Force $user_id to be an array
		if (!is_array($user_id))
			$user_id = array(
				$user_id
			);
		$user_id     = array_map('absint', $user_id); //Cast as positive integers
		$user_id__in = implode(',', $user_id);
		$where_sql .= " AND user_id IN($user_id__in)";
	} //!empty($user_id)
	$since = absint($since);
	$until = absint($until);
	if (!empty($since))
		$where_sql .= $wpdb->prepare(' AND log_date >= %s', date_i18n('Y-m-d H:i:s', $since, true));
	if (!empty($until))
		$where_sql .= $wpdb->prepare(' AND log_date <= %s', date_i18n('Y-m-d H:i:s', $until, true));
	/* SQL Order */
	//Whitelist order
	$order = strtoupper($order);
	$order = ('ASC' == $order ? 'ASC' : 'DESC');
	switch ($orderby) {
		case 'log_id':
			$order_sql = "ORDER BY log_id $order";
			break;
		case 'datetime':
			$order_sql = "ORDER BY log_date $order";
		default:
			break;
	} //$orderby
	/* SQL Limit */
	$offset = absint($offset); //Positive integer
	if ($number == -1) {
		$limit_sql = "";
	} //$number == -1
	else {
		$number    = absint($number); //Positive integer
		$limit_sql = "LIMIT $offset, $number";
	}
	/* Filter SQL */
	$pieces  = array(
		'select_sql',
		'join_sql',
		'where_sql',
		'order_sql',
		'limit_sql'
	);
	$clauses = apply_filters('csl_beaglecr_logs_clauses', compact($pieces), $query);
	foreach ($pieces as $piece)
		$$piece = isset($clauses[$piece]) ? $clauses[$piece] : '';
	/* Form SQL statement */
	$sql = "$select_sql $where_sql $order_sql $limit_sql";
	if ('count' == $fields) {
		return $wpdb->get_var($sql);
	} //'count' == $fields
	/* Perform query */
	$logs = $wpdb->get_results($sql);
	/* Add to cache and filter */
	wp_cache_add($cache_key, $logs, 24 * 60 * 60);
	$logs = apply_filters('csl_get_beaglecr_logs', $logs, $query);
    //var_dump($sql);
    //wp_die();
	return $logs;
}


?>
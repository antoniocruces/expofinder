<?php

/**
 * Quality control functions (part of Data retrieval functions)
 */
 
// NOTE: Specific application function query
if ( !function_exists( 'csl_global_quality_control' ) ) :
    function csl_global_quality_control($grouped = false, $pivoted = false, $author = null, $onlySQL = false) {
	    global $wpdb;
		
		$specific_users = csl_get_only_valid_authors();		
		$authorfilter = $author ? " AND a.post_author = $author " : " AND a.post_author IN (" . implode(',', $specific_users) . ")";
        $query = $grouped ?
            ($pivoted ?
            "SELECT DISTINCT
            	e.display_name,
            	e.post_author, 
                e.post_type,
                SUM(IF(e.c_error_type='ALL', 1, 0)) AS n_total_records,
                SUM(IF(e.c_error_type='DUP', 1, 0)) AS n_duplicated_title,
                SUM(IF(e.c_error_type='MAN', 1, 0)) AS n_mandatory_fields_lack,
                SUM(IF(e.c_error_type='TOW', 1, 0)) AS n_geonames_error,
                SUM(IF(e.c_error_type='SRF', 1, 0)) AS n_autolookup_error,
                SUM(IF(e.c_error_type='TAX', 1, 0)) AS n_taxonomy_lack,
                SUM(IF(e.c_error_type<>'ALL', 1, 0)) AS n_total_errors
            FROM (" 
            :
            "SELECT DISTINCT
            	e.display_name,
            	e.post_author, 
                SUM(IF(e.c_error_type='ALL', 1, 0)) AS n_total_records,
                SUM(IF(e.c_error_type='DUP', 1, 0)) AS n_duplicated_title,
                SUM(IF(e.c_error_type='MAN', 1, 0)) AS n_mandatory_fields_lack,
                SUM(IF(e.c_error_type='TOW', 1, 0)) AS n_geonames_error,
                SUM(IF(e.c_error_type='SRF', 1, 0)) AS n_autolookup_error,
                SUM(IF(e.c_error_type='TAX', 1, 0)) AS n_taxonomy_lack,
                SUM(IF(e.c_error_type<>'ALL', 1, 0)) AS n_total_errors
            FROM ("
            ) :
            "";
	    $query .= "
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'ALL' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
            	$wpdb->posts AS a
            	INNER JOIN
            	$wpdb->users AS u
            	ON
            	a.post_author = u.ID
            	AND 
                a.post_type IN ('entity','person','book','company','exhibition') 
            	AND 
                a.post_status = 'publish' $authorfilter
            
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'DUP' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
            	(
                $wpdb->posts AS a
            	INNER JOIN (
            		SELECT post_type, post_title, MIN( id ) AS min_id
            		FROM $wpdb->posts
            		WHERE 
                    post_type IN ('entity','person','book','company','exibition') 
            		AND post_status = 'publish'
            		GROUP BY post_title
            		HAVING COUNT( * ) > 1
            		) AS b ON a.post_title = b.post_title AND a.post_type = b.post_type
            	)
            	INNER JOIN
            	$wpdb->users u
            	ON
            	a.post_author = u.ID
            	AND b.min_id <> a.id
            	AND a.post_type IN ('entity','person','book','company','exibition')
            	AND a.post_status = 'publish' $authorfilter
            
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'TOW' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
            	(
                $wpdb->posts AS a
            	LEFT JOIN 
                $wpdb->postmeta m
            	ON
                a.ID = m.post_id
                )
            	LEFT JOIN
            	$wpdb->users u
            	ON
            	a.post_author = u.ID
            WHERE
                a.post_type IN ('entity','person','book','company','exhibition')
                AND
                (m.meta_key LIKE '%_town' OR m.meta_key LIKE '%_place')  
                AND
                LENGTH(m.meta_value) - LENGTH(REPLACE(m.meta_value, ';', '')) <> 2 
                AND
            	a.post_status = 'publish' $authorfilter
            
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'SRF' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
            	(
                $wpdb->posts AS a
            	LEFT JOIN 
                $wpdb->postmeta m
            	ON
                a.ID = m.post_id AND m.meta_key IN 
                    (
                    '_cp__ent_parent_entity',
                    '_cp__peo_entity_relation',
                    '_cp__peo_person_relation',
                    '_cp__boo_paper_author',
                    '_cp__boo_sponsorship',
                    '_cp__exh_parent_exhibition',
                    '_cp__exh_info_source',
                    '_cp__exh_artwork_author',
                    '_cp__exh_supporter_entity',
                    '_cp__exh_funding_entity',
                    '_cp__exh_curator',
                    '_cp__exh_catalog',
                    '_cp__exh_museography'
                    )
                )
            	LEFT JOIN
            	$wpdb->users u
            	ON
            	a.post_author = u.ID
            WHERE
                a.post_type IN ('entity','person','book','company','exhibition')
                AND
                SUBSTRING_INDEX(m.meta_value, ': ', 1) NOT REGEXP('(^[0-9]+$)') 
                AND
            	a.post_status = 'publish' $authorfilter
            
            UNION ALL
            
            SELECT 
            	x.display_name,
            	x.post_author, 
            	x.post_type,
                x.c_error_type, 
            	x.post_title,
            	x.ID
            FROM
            (
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'TAX' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
                (
                $wpdb->posts AS a
            	LEFT JOIN
            	$wpdb->users AS u
            	ON
            	a.post_author = u.ID
                )
                LEFT JOIN 
                (
                    SELECT 
                        tr.object_id 
                    FROM
                        $wpdb->term_relationships tr 
                        LEFT JOIN 
                        $wpdb->term_taxonomy tt
                        ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE
                        tt.taxonomy IN 
                        (
                        'tax_typology'
                        )
                ) AS t
                ON a.ID = t.object_id 
            WHERE
                a.post_type IN ('entity')
                AND
            	a.post_status = 'publish'
                AND
                t.object_id IS NULL $authorfilter
            
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'TAX' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
                (
                $wpdb->posts AS a
            	LEFT JOIN
            	$wpdb->users AS u
            	ON
            	a.post_author = u.ID
                )
                LEFT JOIN 
                (
                    SELECT 
                        tr.object_id 
                    FROM
                        $wpdb->term_relationships tr 
                        LEFT JOIN 
                        $wpdb->term_taxonomy tt
                        ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE
                        tt.taxonomy IN 
                        (
                        'tax_ownership'
                        )
                ) AS t
                ON a.ID = t.object_id 
            WHERE
                a.post_type IN ('entity')
                AND
            	a.post_status = 'publish'
                AND
                t.object_id IS NULL $authorfilter
            
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'TAX' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
                (
                $wpdb->posts AS a
            	LEFT JOIN
            	$wpdb->users AS u
            	ON
            	a.post_author = u.ID
                )
                LEFT JOIN 
                (
                    SELECT 
                        tr.object_id 
                    FROM
                        $wpdb->term_relationships tr 
                        LEFT JOIN 
                        $wpdb->term_taxonomy tt
                        ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE
                        tt.taxonomy IN 
                        (
                        'tax_activity'
                        )
                ) AS t
                ON a.ID = t.object_id     
            WHERE
                a.post_type IN ('person')
                AND
            	a.post_status = 'publish'
                AND
                t.object_id IS NULL $authorfilter
                
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'TAX' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
                (
                $wpdb->posts AS a
            	LEFT JOIN
            	$wpdb->users AS u
            	ON
            	a.post_author = u.ID
                )
                LEFT JOIN 
                (
                    SELECT 
                        tr.object_id 
                    FROM
                        $wpdb->term_relationships tr 
                        LEFT JOIN 
                        $wpdb->term_taxonomy tt
                        ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE
                        tt.taxonomy IN 
                        (
                        'tax_publisher'
                        )
                ) AS t
                ON a.ID = t.object_id     
            WHERE
                a.post_type IN ('book')
                AND
            	a.post_status = 'publish'
                AND
                t.object_id IS NULL $authorfilter
            
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'TAX' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
                (
                $wpdb->posts AS a
            	LEFT JOIN
            	$wpdb->users AS u
            	ON
            	a.post_author = u.ID
                )
                LEFT JOIN 
                (
                    SELECT 
                        tr.object_id 
                    FROM
                        $wpdb->term_relationships tr 
                        LEFT JOIN 
                        $wpdb->term_taxonomy tt
                        ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE
                        tt.taxonomy IN 
                        (
                        'tax_isic4_category'
                        )
                ) AS t
                ON a.ID = t.object_id     
            WHERE
                a.post_type IN ('company')
                AND
            	a.post_status = 'publish'
                AND
                t.object_id IS NULL $authorfilter
            
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'TAX' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
                (
                $wpdb->posts AS a
            	LEFT JOIN
            	$wpdb->users AS u
            	ON
            	a.post_author = u.ID
                )
                LEFT JOIN 
                (
                    SELECT 
                        tr.object_id 
                    FROM
                        $wpdb->term_relationships tr 
                        LEFT JOIN 
                        $wpdb->term_taxonomy tt
                        ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE
                        tt.taxonomy IN 
                        (
                        'tax_exhibition_type'
                        )
                ) AS t
                ON a.ID = t.object_id     
            WHERE
                a.post_type IN ('exhibition')
                AND
            	a.post_status = 'publish'
                AND
                t.object_id IS NULL $authorfilter
            
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'TAX' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
                (
                $wpdb->posts AS a
            	LEFT JOIN
            	$wpdb->users AS u
            	ON
            	a.post_author = u.ID
                )
                LEFT JOIN 
                (
                    SELECT 
                        tr.object_id 
                    FROM
                        $wpdb->term_relationships tr 
                        LEFT JOIN 
                        $wpdb->term_taxonomy tt
                        ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE
                        tt.taxonomy IN 
                        (
                        'tax_artwork_type'
                        )
                ) AS t
                ON a.ID = t.object_id     
            WHERE
                a.post_type IN ('exhibition')
                AND
            	a.post_status = 'publish'
                AND
                t.object_id IS NULL $authorfilter
            
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'TAX' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
                (
                $wpdb->posts AS a
            	LEFT JOIN
            	$wpdb->users AS u
            	ON
            	a.post_author = u.ID
                )
                LEFT JOIN 
                (
                    SELECT 
                        tr.object_id 
                    FROM
                        $wpdb->term_relationships tr 
                        LEFT JOIN 
                        $wpdb->term_taxonomy tt
                        ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE
                        tt.taxonomy IN 
                        (
                        'tax_movement'
                        )
                ) AS t
                ON a.ID = t.object_id     
            WHERE
                a.post_type IN ('exhibition')
                AND
            	a.post_status = 'publish'
                AND
                t.object_id IS NULL $authorfilter
            
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'TAX' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
                (
                $wpdb->posts AS a
            	LEFT JOIN
            	$wpdb->users AS u
            	ON
            	a.post_author = u.ID
                )
                LEFT JOIN 
                (
                    SELECT 
                        tr.object_id 
                    FROM
                        $wpdb->term_relationships tr 
                        LEFT JOIN 
                        $wpdb->term_taxonomy tt
                        ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE
                        tt.taxonomy IN 
                        (
                        'tax_period'
                        )
                ) AS t
                ON a.ID = t.object_id     
            WHERE
                a.post_type IN ('exhibition')
                AND
            	a.post_status = 'publish'
                AND
                t.object_id IS NULL $authorfilter
            ) AS x
            
            UNION ALL
            
            SELECT
            	x.display_name,
            	x.post_author, 
            	x.post_type,
                x.c_error_type, 
            	x.post_title,
            	x.ID
            FROM
            (
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'MAN' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
            	(
                $wpdb->posts AS a
            	LEFT JOIN 
                (
                SELECT
                    pm.post_id,
                    pm.meta_key,
                    pm.meta_value
                FROM
                    $wpdb->postmeta AS pm
                WHERE
                    pm.meta_key IN
                        (
                        '_cp__ent_town',
                        '_cp__ent_address'    
                        )  
                ) AS m
            	ON
                a.ID = m.post_id
                )
            	LEFT JOIN
            	$wpdb->users u
            	ON
            	a.post_author = u.ID
            WHERE
                a.post_type IN ('entity')
                AND
            	a.post_status = 'publish'
                AND
                m.post_id IS NULL $authorfilter
            
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'MAN' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
            	(
                $wpdb->posts AS a
            	LEFT JOIN 
                (
                SELECT
                    pm.post_id,
                    pm.meta_key,
                    pm.meta_value
                FROM
                    $wpdb->postmeta AS pm
                WHERE
                    pm.meta_key IN
                        (
                        '_cp__peo_country'    
                        )  
                ) AS m
            	ON
                a.ID = m.post_id
                )
            	LEFT JOIN
            	$wpdb->users u
            	ON
            	a.post_author = u.ID
            WHERE
                a.post_type IN ('person')
                AND
            	a.post_status = 'publish'
                AND
                m.post_id IS NULL $authorfilter
                
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'MAN' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
            	(
                $wpdb->posts AS a
            	LEFT JOIN 
                (
                SELECT
                    pm.post_id,
                    pm.meta_key,
                    pm.meta_value
                FROM
                    $wpdb->postmeta AS pm
                WHERE
                    pm.meta_key IN
                        (
                        '_cp__boo_publishing_place',
                        '_cp__boo_paper_author'    
                        )  
                ) AS m
            	ON
                a.ID = m.post_id
                )
            	LEFT JOIN
            	$wpdb->users u
            	ON
            	a.post_author = u.ID
            WHERE
                a.post_type IN ('book')
                AND
            	a.post_status = 'publish'
                AND
                m.post_id IS NULL $authorfilter
                
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'MAN' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
            	(
                $wpdb->posts AS a
            	LEFT JOIN 
                (
                SELECT
                    pm.post_id,
                    pm.meta_key,
                    pm.meta_value
                FROM
                    $wpdb->postmeta AS pm
                WHERE
                    pm.meta_key IN
                        (
                        '_cp__com_company_headquarter_place'    
                        )  
                ) AS m
            	ON
                a.ID = m.post_id
                )
            	LEFT JOIN
            	$wpdb->users u
            	ON
            	a.post_author = u.ID
            WHERE
                a.post_type IN ('company')
                AND
            	a.post_status = 'publish'
                AND
                m.post_id IS NULL $authorfilter
                
            UNION ALL
            
            SELECT DISTINCT
            	u.display_name,
            	a.post_author, 
            	a.post_type,
                'MAN' as c_error_type, 
            	a.post_title,
            	a.ID
            FROM 
            	(
                $wpdb->posts AS a
            	LEFT JOIN 
                (
                SELECT
                    pm.post_id,
                    pm.meta_key,
                    pm.meta_value
                FROM
                    $wpdb->postmeta AS pm
                WHERE
                    pm.meta_key IN
                        (
                        '_cp__exh_exhibition_start_date',    
                        '_cp__exh_exhibition_end_date',    
                        '_cp__exh_exhibition_town',    
                        '_cp__exh_exhibition_site',    
                        '_cp__exh_address',    
                        '_cp__exh_artwork_author',    
                        '_cp__exh_supporter_entity'    
                        )  
                ) AS m
            	ON
                a.ID = m.post_id
                )
            	LEFT JOIN
            	$wpdb->users u
            	ON
            	a.post_author = u.ID
            WHERE
                a.post_type IN ('exhibition')
                AND
            	a.post_status = 'publish'
                AND
                m.post_id IS NULL $authorfilter
            ) AS x
                ";
        $query .= $grouped ?
            ($pivoted ?
            ") AS e   
            GROUP BY
                e.display_name,
                e.post_type
            ORDER BY
                e.display_name,
                e.post_type" 
            :            
            ") AS e   
            GROUP BY
                e.display_name
            ORDER BY
                e.display_name" 
            ) :
            "";

        return $onlySQL ? $query : $wpdb->get_results($query, OBJECT);
    }
endif;

/**
 * Data retrieval functions
 */

if ( ! function_exists( 'csl_get_user_activity_status' ) ) :
	function csl_get_user_activity_status ( $user, $additional_where = '' ) {
		global $wpdb;
		$aRet = array();
		$is_active = in_array($user, csl_get_connected_users());
		$aRet['logged_since'] = !$is_active ? false : get_transient( CSL_DATA_PREFIX . 'user_' . $user );
		$aRet['last_activity'] = $wpdb->get_var("
			SELECT 
				activity_date AS last_activity 
			FROM 
				{$wpdb->xtr_activity_log} 
			WHERE 
				user_id = $user 
				$additional_where  
			ORDER BY
				activity_date DESC
			LIMIT 1;");
		return $aRet;	
	}
endif;

if ( ! function_exists( 'csl_get_last_chat_activity' ) ) :
	function csl_get_last_chat_activity ( $user ) {
		global $wpdb;
		$last_activity = csl_get_user_activity_status( $user, ' AND activity = "read_chat_messages"' )['last_activity'];
		return $wpdb->get_var("
			SELECT
				SUM(x.num_messages) as num_messages 
			FROM
			(
			SELECT 
				count(id) as num_messages 
			FROM 
				{$wpdb->prefix}xtr_dashboard_chat_log
			WHERE 
				user_id <> $user 
				AND
				date >= '$last_activity'
			UNION ALL
			SELECT 
				count(ID) as num_messages 
			FROM 
				{$wpdb->prefix}posts
			WHERE 
				post_modified >= '$last_activity'
				AND
				post_type = 'post'
				AND post_status = 'private'
			) AS x 
			LIMIT 1;");
	}
endif;

if ( !function_exists( 'csl_get_pst' ) ) :
    function csl_get_pst( $surl ) {
        if($surl) {
			$response = wp_remote_get( $surl );
            if( is_array($response) ) {
                $ismarked = false;                 
                preg_match("/<body[^>]*>(.*?)<\/body>/is", $response['body'], $matches);
                if( is_array( $matches ) && count( $matches ) > 1 ) {
	                $txt = $matches[1];
	                // Match by class name
	                $aMatches = explode('|', 'content|contain|contenido|texto|entry|main|markdown|page|attach|post\-content|post|text|blog|story');
	                foreach($aMatches as $match) {
	                    if(!preg_match('/<div\s*class="' . $match . '">(.*?)<\/div>/si', $txt, $matches)) continue;
	                    $txt = $matches[1];
	                    $ismarked = true;
	                    break;
	                }
	                // Match by id
	                if(!$ismarked) {
	                    $aMatches = explode('|', 'content|contain|contenido|texto|entry|main|markdown|page|attach|post\-content|post|text|blog|story');
	                    foreach($aMatches as $match) {
	                        if(!preg_match('/<div\s*id="' . $match . '">(.*?)<\/div>/si', $txt, $matches)) continue;
	                        $txt = $matches[1];
	                        break;
	                    }
	                }
	                $subject = wp_kses(html_entity_decode(wp_strip_all_tags(html_entity_decode($txt))), array());
	                $subject = preg_replace("/[\r\n]{2,}/", "\n\n", $subject);
	                $results = array_map("strtolower", array_map("remove_accents", preg_split('/(?<=[.?!;:])\s+/', $subject, -1, PREG_SPLIT_NO_EMPTY)));
	                $tokens  = array_map("trim", explode("\n", file_get_contents(get_template_directory(). '/assets/keywords/' . get_locale() . '/' . get_locale() . '.pst')));
	                $areturn = array();
	                foreach($results as $result) {
	                    $arst = csl_extract_keywords($result, $minWordLen = 3, $minWordOccurrences = 1, $asArray = true, $maxWords = 8, $restrict = true, $pst = true);
	                    if(0 < count($arst)) {
	                        $areturn []= implode(', ', $arst) . '||' . $result;
	                    }    
	                }
	                return count($areturn) > 0 ? $areturn : false;
                } else {
	                return false;
                }
			} else {
				return false;	
			}
        } else {
			return false;	
        }
    }
endif;

if ( !function_exists( 'csl_get_title_content_from_html' ) ) :
	function csl_get_title_content_from_html( $html ) {
		$dom = new DOMDocument;
		$dom->loadHTML($html);
		$images = $dom->getElementsByTagName('img');
		foreach ($images as $image) {
		        $image->setAttribute('src', 'http://example.com/' . $image->getAttribute('src'));
		}
		$html = $dom->saveHTML();
	}
endif;

if ( !function_exists( 'csl_count_for_target_status' ) ) :
    function csl_count_for_target_status() {
    	global $wpdb;
    	global $csl_a_targets;

        $areturn = array();
        $post_types = '"' . implode('","', CSL_CUSTOM_POST_TYPE_ARRAY) . '"';
        
        $valuedops = wp_count_posts(CSL_CUSTOM_POST_ENTITY_TYPE_NAME)->publish + wp_count_posts(CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME)->publish;
		$target_valuedops = $csl_a_targets['target_records'][CSL_CUSTOM_POST_ENTITY_TYPE_NAME] + 
			$csl_a_targets['target_records'][CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME] +
			$csl_a_targets['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri'] +
			$csl_a_targets['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri'];

        $operations = 0;
        foreach(CSL_CUSTOM_POST_TYPE_ARRAY as $key => $value) {
	    	$operations += wp_count_posts($value)->publish;	    
        }
        $due_operations = 0;
        $current_level = 0;
        $operations_time = 0;
        $due_operations_time = 0;
        $elements = 0;
        
        $areturn['target_dates']['project_start'] = $csl_a_targets['target_dates']['project_start'];
        $areturn['target_dates']['project_end'] = $csl_a_targets['target_dates']['project_end'];
        $areturn['target_dates']['project_duration'] = $areturn['target_dates']['project_end'] - $areturn['target_dates']['project_start'];
        $areturn['target_dates']['project_current_time'] = current_time( 'timestamp' );
        $areturn['target_dates']['project_past_time'] = $areturn['target_dates']['project_current_time'] - $areturn['target_dates']['project_start']; 
        $areturn['target_dates']['project_future_time'] = $areturn['target_dates']['project_duration'] - $areturn['target_dates']['project_past_time'];               
        $areturn['target_dates']['project_current_percent'] = $areturn['target_dates']['project_past_time'] / $areturn['target_dates']['project_duration'];               
        
        foreach($csl_a_targets['target_records'] as $key => $val) {
            $sql = "SELECT count(DISTINCT p.ID)
                FROM $wpdb->posts p
                WHERE p.post_type IN (\"$key\") 
                AND p.post_status = 'publish'
                ";
            $areturn['target_records'][$key] =  $val;
            $areturn['target_records'][$key . '_num'] =  $wpdb->get_var($sql);
            $areturn['target_records'][$key . '_per'] =  $areturn['target_records'][$key . '_num'] / $operations;
            $areturn['target_records'][$key . '_rng'] =  $areturn['target_records'][$key . '_num'] / $areturn['target_records'][$key];
            $areturn['target_records'][$key . '_due'] =  round((int) $areturn['target_records'][$key] * (float) $areturn['target_dates']['project_current_percent'], 0);
            $areturn['target_records'][$key . '_dif'] =  $areturn['target_records'][$key . '_num'] - $areturn['target_records'][$key . '_due'];
            $areturn['target_records'][$key . '_sta'] =  $areturn['target_records'][$key . '_rng'] - $areturn['target_dates']['project_current_percent'];
            $due_operations += $val;
            $current_level += $areturn['target_records'][$key . '_rng'];
            $elements++;
        }
		
        foreach($csl_a_targets['target_fields'] as $key => $val) {
            $sql = "SELECT COUNT(DISTINCT pm.meta_value)
                FROM $wpdb->postmeta pm
                JOIN $wpdb->posts p ON (p.ID = pm.post_id)
                WHERE pm.meta_key IN (\"$key\") 
                AND p.post_type IN ($post_types) 
                AND p.post_status = 'publish'
                ";
            $areturn['target_fields'][$key] =  $val;
            $areturn['target_fields'][$key . '_num'] =  $wpdb->get_var($sql);
            $areturn['target_fields'][$key . '_rng'] =  $areturn['target_fields'][$key . '_num'] / $areturn['target_fields'][$key];
            $areturn['target_fields'][$key . '_due'] =  round((int) $areturn['target_fields'][$key] * (float) $areturn['target_dates']['project_current_percent'], 0);
            $areturn['target_fields'][$key . '_dif'] =  $areturn['target_fields'][$key . '_num'] - $areturn['target_fields'][$key . '_due'];
            $areturn['target_fields'][$key . '_sta'] =  $areturn['target_fields'][$key . '_rng'] - $areturn['target_dates']['project_current_percent'];
            $operations += $areturn['target_fields'][$key . '_num'];
            $valuedops += $areturn['target_fields'][$key . '_num'];
            $due_operations += $val;
            $current_level += $areturn['target_fields'][$key . '_rng'];
            $elements++;
        }

		foreach($csl_a_targets['target_fields_human_name'] as $key => $val) {
			$areturn['target_fields_human_name'][$key] =  $val;
		}

		$areturn['target_level']['average_current_level'] = $current_level / $elements;
        $areturn['target_level']['average_operations_time'] = $areturn['target_dates']['project_past_time'] / $operations;
        $areturn['target_level']['due_average_operations_time'] = $areturn['target_dates']['project_past_time'] / $due_operations;        

        //(int) $csl_a_targets['target_records']['entity']
        /*
		$areturn['current_status']['0_per'] = 
			$areturn['target_records'][CSL_CUSTOM_POST_ENTITY_TYPE_NAME . '_num'] / $valuedops;   
		$areturn['current_status']['1_per'] = 
			$areturn['target_records'][CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME . '_num'] / $valuedops;   
		$areturn['current_status']['2_per'] = 
			$areturn['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri' . '_num'] / $valuedops;   
		$areturn['current_status']['3_per'] = 
			$areturn['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri' . '_num'] / $valuedops;   
        
		$areturn['current_status']['0_due'] = 
			$areturn['target_records'][CSL_CUSTOM_POST_ENTITY_TYPE_NAME . '_due'] / $valuedops;   
		$areturn['current_status']['1_due'] = 
			$areturn['target_records'][CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME . '_due'] / $valuedops;   
		$areturn['current_status']['2_due'] = 
			$areturn['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri' . '_due'] / $valuedops;   
		$areturn['current_status']['3_due'] = 
			$areturn['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri' . '_due'] / $valuedops;   
        */

		$areturn['current_status']['0_per'] = 
			$areturn['target_records'][CSL_CUSTOM_POST_ENTITY_TYPE_NAME . '_num'] / (int) $csl_a_targets['target_records'][CSL_CUSTOM_POST_ENTITY_TYPE_NAME];   
		$areturn['current_status']['1_per'] = 
			$areturn['target_records'][CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME . '_num'] / (int) $csl_a_targets['target_records'][CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME];   
		$areturn['current_status']['2_per'] = 
			$areturn['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri' . '_num'] / (int) $csl_a_targets['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri'];   
		$areturn['current_status']['3_per'] = 
			$areturn['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri' . '_num'] / (int) $csl_a_targets['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri'];   
        
		$areturn['current_status']['0_due'] = 
			$areturn['target_records'][CSL_CUSTOM_POST_ENTITY_TYPE_NAME . '_due'] / (int) $csl_a_targets['target_records'][CSL_CUSTOM_POST_ENTITY_TYPE_NAME];   
		$areturn['current_status']['1_due'] = 
			$areturn['target_records'][CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME . '_due'] / (int) $csl_a_targets['target_records'][CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME];   
		$areturn['current_status']['2_due'] = 
			$areturn['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri' . '_due'] / (int) $csl_a_targets['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri'];   
		$areturn['current_status']['3_due'] = 
			$areturn['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri' . '_due'] / (int) $csl_a_targets['target_fields'][CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri'];   

        
        $areturn['current_status']['current_valued_ops_num'] = $valuedops;
        $areturn['current_status']['target_valued_ops_num'] = $target_valuedops;
        $areturn['current_status']['current_valued_ops_per'] = $valuedops / $target_valuedops;
        $areturn['current_status']['current_valued_ops_due'] = $target_valuedops * $areturn['target_dates']['project_current_percent'];
        $areturn['current_status']['current_valued_ops_due_per'] = ($target_valuedops * $areturn['target_dates']['project_current_percent'])  / $target_valuedops;
        $areturn['current_status']['current_valued_ops_time'] = $areturn['target_dates']['project_past_time'] / $valuedops;
        $areturn['current_status']['current_valued_ops_time_due'] = $areturn['target_dates']['project_past_time'] / $areturn['current_status']['current_valued_ops_due'];
        $areturn['current_status']['target_probably_end'] = round(($target_valuedops - $valuedops) * $areturn['current_status']['current_valued_ops_time'], 0);
        
        return $areturn;
    }
endif;

if ( !function_exists( 'csl_get_rss_uris_array' ) ) :
    function csl_get_rss_uris_array($limit = NULL, $orderby = NULL, $order = NULL) {
	    global $wpdb;

        $qlimit = $limit ? ' LIMIT ' . $limit : '';
        $qorderby = $orderby ? ' ORDER BY ' . $orderby : '';
        $qorder = $order ? ' ' . $order : '';
	    return $wpdb->get_results("
            SELECT DISTINCT
            	p.post_title,
            	p.ID,
            	u.display_name,
            	u.ID,
            	m.meta_value as s_uri
            FROM
            	($wpdb->postmeta m
            	INNER JOIN
            	$wpdb->posts p
            	ON
            	p.ID = m.post_id)
            	INNER JOIN
            	$wpdb->users u
            	ON
            	u.ID = p.post_author
            WHERE
            	m.meta_key LIKE '%_rss_uri'
            	AND
            	p.post_status = 'publish'
            $qorderby $qorder 
            $qlimit
        ",
	    ARRAY_A);
    }
endif;

if ( !function_exists( 'csl_get_posts_relations' ) ) :
    function csl_get_posts_relations( $limit = NULL ) {
	    global $wpdb;

	    return $wpdb->get_results("
			SELECT SQL_CACHE
				c.s_post_type,
				c.meta_key,
				c.s_ref_type,
				COUNT(c.n_post_id) AS n_rel_count
			FROM
				(
					SELECT 
						p.ID AS n_post_id,
						p.post_title AS s_post_title,
						p.post_type AS s_post_type,
						r.ID AS n_ref_id,
						r.post_title AS s_ref_title,
						r.post_type AS s_ref_type,
						m.meta_key,
						m.meta_value
					FROM
						$wpdb->postmeta AS m
						INNER JOIN 
						$wpdb->posts AS p 
						ON
						p.ID = m.post_id
						INNER JOIN
						$wpdb->posts AS r 
						ON
						r.ID = CAST(SUBSTRING_INDEX(m.meta_value, \": \", 1) AS UNSIGNED)
					WHERE
						m.meta_key IN (
							\"_cp__boo_paper_author\", 
							\"_cp__boo_sponsorship\", 
							\"_cp__ent_parent_entity\", 
							\"_cp__exh_art_collector\", 
							\"_cp__exh_artwork_author\", 
							\"_cp__exh_catalog\", 
							\"_cp__exh_curator\", 
							\"_cp__exh_funding_entity\", 
							\"_cp__exh_info_source\", 
							\"_cp__exh_museography\", 
							\"_cp__exh_parent_exhibition\", 
							\"_cp__exh_source_entity\", 
							\"_cp__exh_supporter_entity\", 
							\"_cp__peo_person_relation\"
						)
						AND
						m.meta_value IS NOT NULL
				) AS c 
			GROUP BY 
				c.s_post_type,
				c.meta_key,
				c.s_ref_type
        " . ( $limit ? "LIMIT $limit" : "" ) . "	
        ",
	    ARRAY_A);
    }
endif;

if ( !function_exists( 'csl_get_exhibition_towns' ) ) :
    function csl_get_exhibition_towns( $limit = 10 ) {
	    global $wpdb;

	    return $wpdb->get_results("
			SELECT SQL_CACHE
				SUBSTRING_INDEX(meta_value, \";\", 1) AS s_town,
				COUNT(post_id) as n_exhibitions
			FROM
				$wpdb->postmeta
			WHERE 
				meta_key = \"_cp__exh_exhibition_town\"
				AND
				meta_value IS NOT NULL
			GROUP BY
				SUBSTRING_INDEX(meta_value, \";\", 1)
			ORDER BY 
				COUNT(post_id) DESC 
			LIMIT
				$limit
        ",
	    ARRAY_A);
    }
endif;

if ( !function_exists( 'csl_get_basic_stats_count' ) ) :
    function csl_get_basic_stats_count() {
	    global $wpdb;
        global $csl_a_targets;
        
	    $aData = $wpdb->get_results("
            SELECT
                p.post_type AS s_type,
            	SUM(p.post_status = 'publish') AS n_publish,
                SUM(p.post_status = 'draft') AS n_draft
            FROM
            	$wpdb->posts p
            WHERE
            	p.post_type IN('" . implode("','", CSL_CUSTOM_POST_TYPE_ARRAY) . "')
            	AND
            	p.post_status IN('publish', 'draft')
            GROUP BY
                p.post_type;    
        ",
	    ARRAY_A);
	    $aFiel = $wpdb->get_results("
            SELECT 
                m.meta_key,
            	COUNT(m.meta_id) AS n_uris
            FROM
            	$wpdb->postmeta m
            	INNER JOIN
            	$wpdb->posts p
            	ON
            	p.ID = m.post_id
            WHERE
            	(
                m.meta_key LIKE '%_rss_uri'
                OR
                m.meta_key LIKE '%_html_uri')
            	AND
            	p.post_status = 'publish'
            GROUP BY
                m.meta_key;
        ",
	    ARRAY_A);
        foreach( $aData as &$data ) {
            $data['s_label'] = get_post_type_object( $data['s_type'] )->labels->name;
            $data['s_label_acronym'] = csl_get_acronym( $data['s_label'] );
            $data['s_label_class'] = CSL_CUSTOM_POST_COLOR_ARRAY[$data['s_type']];
            $data['n_publish'] = (int) $data['n_publish'];
            $data['n_draft'] = (int) $data['n_draft'];
            $data['n_target'] = (int) $csl_a_targets['target_records'][$data['s_type']];
            $data['n_t_level'] = $data['n_publish'] / $data['n_target'] > 1 ? 1 : $data['n_publish'] / $data['n_target'];  
            $data['n_d_level'] = 1 - ( $data['n_publish'] / ( $data['n_publish'] + $data['n_draft'] ) );
        }
        foreach( $aFiel as &$fiel ) {
            $fiel['s_label'] = $csl_a_targets['target_fields_human_name'][$fiel['meta_key']];
            $fiel['n_target'] = (int) $csl_a_targets['target_fields'][$fiel['meta_key']];
            $fiel['n_uris'] = (int)$fiel['n_uris']; 
            $fiel['n_level'] =$fiel['n_uris'] / $fiel['n_target'];
        }
        $aVGen = array();
		foreach(explode("\n", file_get_contents( get_template_directory(). '/assets/keywords/' . get_locale() . '/' . get_locale() . '_valid_gender_names.gen' )) as $key => $value) {
			$aVGen []= $value;
		}
        
 	    $aPGen = $wpdb->get_results("
			SELECT SQL_CACHE
			    g.meta_key AS s_gender, 
			    g.meta_value,            
			    COUNT(DISTINCT g.post_id) AS n_records 
			FROM 
			    {$wpdb->postmeta} AS g 
			WHERE 
			    g.meta_key = \"_cp__peo_gender\"
			    AND
			    g.meta_value IN (\"" . implode( '","', $aVGen ) . "\")
			GROUP BY 
			    g.meta_key,
			    g.meta_value;            
        ",
	    ARRAY_A);
		return array( 'posts' => $aData, 'fields' => $aFiel, 'gender' => $aPGen );
    }
endif;

if ( !function_exists( 'csl_get_internal_notices' ) ) :
    function csl_get_internal_notices($limit = NULL, $orderby = NULL, $order = NULL) {
	    global $wpdb;

        $qlimit = $limit ? ' LIMIT ' . $limit : '';
        $qorderby = $orderby ? ' ORDER BY ' . $orderby : '';
        $qorder = $order ? ' ' . $order : '';
	    return $wpdb->get_results("
            SELECT DISTINCT
            	p.post_title,
            	p.ID as post_id,
            	u.display_name,
            	u.ID,
            	p.post_content,
            	p.post_modified
            FROM
            	$wpdb->posts p
            	INNER JOIN
            	$wpdb->users u
            	ON
            	u.ID = p.post_author
            WHERE
            	p.post_modified BETWEEN CURDATE() - INTERVAL 90 DAY AND SYSDATE()
            	AND
            	p.post_status = 'private'
            $qorderby $qorder 
            $qlimit
        ",
	    ARRAY_A);
    }
endif;

if ( !function_exists( 'csl_get_internal_notice' ) ) :
    function csl_get_internal_notice( $post_id ) {
	    global $wpdb;

	    return $wpdb->get_results("
            SELECT DISTINCT
            	p.post_title,
            	p.ID as post_id,
            	u.display_name,
            	u.ID,
            	p.post_content,
            	p.post_modified
            FROM
            	$wpdb->posts p
            	INNER JOIN
            	$wpdb->users u
            	ON
            	u.ID = p.post_author
            WHERE
            	p.ID = $post_id
            LIMIT 1
        ",
	    ARRAY_A);
    }
endif;

if ( !function_exists( 'csl_get_users_activities_stats' ) ) :
	/**
	 * csl_get_users_activities_stats function.
	 * 
	 * @access public
	 * @param bool $grouped (default: false)
	 * @param mixed $limit (default: NULL)
	 * @param mixed $orderby (default: NULL)
	 * @param mixed $order (default: NULL)
	 * @return array
	 */
	function csl_get_users_activities_stats($grouped = false, $limit = NULL, $orderby = NULL, $order = NULL) {
	    global $wpdb;

        $qlimit = $limit ? ' LIMIT ' . $limit : '';
        $qorderby = $orderby ? ' ORDER BY ' . $orderby : '';
        $qorder = $order ? ' ' . $order : '';
        $sgrouped = $grouped 
        	? 
        	"SELECT SQL_CACHE 
        		t.display_name, 
        		t.user_id,
        		COUNT(t.human_date) AS num_dates, 
        		SUM(t.time_diff) AS time_diff,
        		CONCAT(FLOOR((SUM(t.time_diff))/60),':',MOD(SUM(t.time_diff),60)) as human_time_diff, 
				SUM(t.num_activities) AS num_activities,
				ROUND(AVG(t.avg_time_for_activity), 1) AS avg_time_for_activity
			FROM ("
        	: 
        	"";
        $ggrouped = $grouped 
        	? 
        	") as t GROUP BY display_name"
        	: 
        	"";
	    return $wpdb->get_results("
	    	$sgrouped
			SELECT 
				u.display_name,
				a.user_id,
				a.date,
				a.human_date,
				SUM(a.time_diff) + 30 AS time_diff,
				CONCAT(FLOOR((SUM(a.time_diff)+30)/60),':',MOD(SUM(a.time_diff)+30,60)) as human_time_diff,
				SUM(a.num_activities) AS num_activities,
				ROUND(AVG(a.avg_time_for_activity), 1) AS avg_time_for_activity
			FROM 
				(
				SELECT
					user_id,
				    DATE_FORMAT(activity_date, '%Y%m%d') AS date,
				    DATE_FORMAT(activity_date, '%d-%m-%Y') AS human_date,
				    DATE_FORMAT(activity_date, '%H') AS hour,
				    MIN(TIME(activity_date)) as first_activity,
				    IF(MAX(TIME(activity_date)) = MIN(TIME(activity_date)), TIME(STR_TO_DATE(CONCAT(DATE_FORMAT(activity_date, '%Y-%m-%d %H:'), '59:59'), '%Y-%m-%d %H:%i:%s')) , MAX(TIME(activity_date))) as last_activity,
				    TIMESTAMPDIFF(MINUTE, MIN(TIME(activity_date)), IF(MAX(TIME(activity_date)) = MIN(TIME(activity_date)), TIME(STR_TO_DATE(CONCAT(DATE_FORMAT(activity_date, '%Y-%m-%d %H:'), '59:59'), '%Y-%m-%d %H:%i:%s')) , MAX(TIME(activity_date)))) as time_diff,
				    COUNT(log_id) AS num_activities,
				    ROUND(TIMESTAMPDIFF(MINUTE, MIN(TIME(activity_date)), IF(MAX(TIME(activity_date)) = MIN(TIME(activity_date)), TIME(STR_TO_DATE(CONCAT(DATE_FORMAT(activity_date, '%Y-%m-%d %H:'), '59:59'), '%Y-%m-%d %H:%i:%s')) , MAX(TIME(activity_date)))) / COUNT(log_id), 1) AS avg_time_for_activity
				FROM
					{$wpdb->xtr_activity_log}
				WHERE
					user_id > 1
				    AND activity LIKE \"record_%\"
				GROUP BY
					user_id,
				    DATE_FORMAT(activity_date, '%Y%m%d'),
				    DATE_FORMAT(activity_date, '%H')
				) AS a 
				INNER JOIN
				{$wpdb->users} AS u 
				ON
				a.user_id = u.ID
			GROUP BY
				u.display_name,
				date
	        $qorderby $qorder 
	        $qlimit
	        $ggrouped
        ",
	    ARRAY_A);
	}
endif;

if ( !function_exists( 'csl_project_data' ) ) :
    function csl_project_data() {
	    global $wpdb;
	    return $wpdb->get_results("
            SELECT 
            	task_id,
                task_name,
                resource,
                unix_timestamp(start_date) * 1000 as start_date,
                unix_timestamp(end_date) * 1000 as end_date,
                datediff(end_date, start_date) as duration,
                round(if(datediff(end_date, now()) < 0, 100, if(datediff(now(), start_date) < 0, 0, (datediff(now(), start_date) / datediff(end_date, start_date)) * 100)), 0) as percent_complete,
                dependencies
            FROM 
            	$wpdb->xtr_projectd_log
            ORDER BY
                task_order;
        ",
	    OBJECT);
    }
endif;

if ( !function_exists( 'csl_get_project_stats' ) ) :
    function csl_get_project_stats( $type = 'entity', $key = null, $limit = 10 ) {
	    global $wpdb;
		
		$where  = '';
		$where .= $key ? " AND s.info_fieldname = '$key' " : $where;
		$where .= $type ? " AND s.post_type = '$type' " : $where;
	    return $wpdb->get_results("
            SELECT DISTINCT
            	s.post_type,
            	s.info_fieldname,
            	s.info_key,
            	s.info_value,
            	COUNT(DISTINCT s.post_id) + 0 as n_posts
            FROM
            	{$wpdb->prefix}xtr_vw_stats_terms s
            WHERE 
            	1 = 1
            	{$where}
            GROUP BY
            	s.post_type,
            	s.info_key,
            	s.info_value
            ORDER BY
            	COUNT(DISTINCT s.post_id) DESC,
            	s.info_fieldname,
            	s.info_value
            LIMIT {$limit}
        ",
	    ARRAY_A);
    }
endif;

if ( !function_exists( 'csl_get_project_stats_pivot' ) ) :
    function csl_get_project_stats_pivot( $limit = 10 ) {
	    global $wpdb;
		
	    return $wpdb->get_results("
            SELECT DISTINCT
            	s.post_type,
            	s.info_fieldname,
            	s.info_key,
            	s.info_value,
            	COUNT(DISTINCT s.post_id) + 0 as n_posts
            FROM
            	{$wpdb->prefix}xtr_vw_stats_terms s
            WHERE 
            	s.post_type IN ('entity','person','book','company','exhibition')
            GROUP BY
            	s.post_type,
            	s.info_key,
            	s.info_value
            HAVING
            	COUNT(DISTINCT s.post_id) > 1
            ORDER BY
            	COUNT(DISTINCT s.post_id) DESC,
            	s.info_fieldname,
            	s.info_value
            LIMIT {$limit}
        ",
	    ARRAY_A);
    }
endif;

if ( !function_exists( 'csl_get_project_stats_unique_fields' ) ) :
    function csl_get_project_stats_unique_fields( $field_name = 'post_type', $where = '' ) {
	    global $wpdb;
		
		$where = '' !== $where ? ' AND ' . $where : '';
	    return $wpdb->get_results("
            SELECT DISTINCT
            	s.{$field_name}
            FROM
            	{$wpdb->prefix}xtr_vw_stats_terms s
            WHERE
            	s.{$field_name} IS NOT NULL{$where} 
            GROUP BY
            	s.{$field_name}
            ORDER BY
            	s.{$field_name}
        ",
	    ARRAY_A);
    }
endif;


?>
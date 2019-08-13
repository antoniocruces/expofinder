<?php

function csl_self_ajax_lookup() {
    global $wpdb;

    $search = esc_sql($_REQUEST['q']);
    $pt = esc_sql($_REQUEST['pt']);

    if( 'country' == $pt ) {
        $query = 'SELECT * FROM ' . $wpdb->prefix . 'xtr_country_code 
            WHERE ( spanish_name LIKE \'%' . $search . '%\' OR long_name LIKE \'%' . $search . '%\' OR short_name LIKE \'%' . $search . '%\' )
            ORDER BY short_name ASC';  
                
        foreach ($wpdb->get_results($query) as $row) {
            $post_title = $row->spanish_name . ' (' . $row->long_name . ')';
            $id = $row->country_id;
            echo $id . ': ' . $post_title . PHP_EOL;
        }
    } else {
        $query = 'SELECT ID,post_title FROM ' . $wpdb->posts . '
            WHERE post_title LIKE \'%' . $search . '%\'
            AND post_type IN (\'' . str_replace(",","','",$pt) . '\') 
            AND post_status = \'publish\'
            ORDER BY post_title ASC';  
                
        foreach ($wpdb->get_results($query) as $row) {
            $post_title = $row->post_title;
            $id = $row->ID;
            echo $id . ': ' . $post_title . PHP_EOL;
        }
    }
    
    die();  // Very important!!
}
add_action('wp_ajax_csl_self_ajax_lookup', 'csl_self_ajax_lookup');
add_action('wp_ajax_nopriv_csl_self_ajax_lookup', 'csl_self_ajax_lookup');

function csl_generic_ajax_call() {
    global $wpdb;

    if(
    	'entmap' != esc_sql($_REQUEST['q']) && 'tests' != esc_sql($_REQUEST['q']) 
        && 'countexp' != esc_sql($_REQUEST['q']) && 'leaflet' != esc_sql($_REQUEST['q']) 
        && 'geochart' != esc_sql($_REQUEST['q']) && 'geoworks' != esc_sql($_REQUEST['q']) 
        && 'geodraw' != esc_sql($_REQUEST['q']) && 'voronoi' != esc_sql($_REQUEST['q']) 
        && 'dashboard' != esc_sql($_REQUEST['q']) && 'geoda' != esc_sql($_REQUEST['q'])
    ) {
        check_ajax_referer( NONCE_KEY, 's' );
    }
    $operation = esc_sql($_REQUEST['q']);
    $param     = isset($_REQUEST['x']) ? esc_sql($_REQUEST['x']) : NULL;
    $outformat = isset($_REQUEST['f']) ? esc_sql($_REQUEST['f']) : NULL;
    $notset    = __( 'Not set', CSL_TEXT_DOMAIN_PREFIX );
    $extension = isset($_REQUEST['q']) ? substr($operation, -3) : '';
    $filename  = CSL_NAME . '_' . date('YmdHis') . '_export.' . $extension;
    $curtim    = current_time('timestamp');
    switch($operation) {
        case 'geoda':
        	switch( $param ){
	        	case 'm':
					$query = 
						"
						SELECT
							post_id AS ID,
							meta_id,
							meta_key,
							meta_value
						FROM
							{$wpdb->postmeta}
						WHERE 
							meta_key IN(\"" . implode( '","', CSL_META_FIELDS_FOR_QUERIES ). "\")
							AND
							SUBSTRING(meta_key, 1, 5) = \"_cp__\";
						";
					break;
				case 't':
					$query = 
						"
						SELECT 
							tr.object_id AS ID,
							tt.term_id,
							tt.taxonomy,
							t.name AS term
						FROM 
						    {$wpdb->term_relationships} AS tr 
						    INNER JOIN 
						    {$wpdb->term_taxonomy} AS tt 
						    ON 
						    (tr.term_taxonomy_id = tt.term_taxonomy_id)
						    INNER JOIN 
						    {$wpdb->terms} AS t 
						    ON 
						    (t.term_id = tt.term_id)
						WHERE
							SUBSTRING(tt.taxonomy, 1, 4) = \"tax_\";
						";
					break;
				case 'p':
				default:
					$query = 
						"
						SELECT
							ID,
							post_type,
							post_title
						FROM 
							{$wpdb->posts}
						WHERE
							post_type IN (\"entity\",\"book\",\"person\",\"company\",\"exhibition\",\"artwork\")
							AND
							post_status = \"publish\"
						";
					break;
			}
            $values = $wpdb->get_results( $query, ARRAY_A );

            $caches = 1 * 24 * 60 * 60; // 30 days * 24 hours * 60 minutes * 60 seconds
            $cachee = gmdate("D, d M Y H:i:s", time() + $caches) . " GMT";
            switch( $outformat ) {
                case 'tsv':
                    ob_start();
                	$title = array_keys( $values[0] );
                    echo implode( "\t", $title ) . PHP_EOL;
                    foreach( $values as $value ) {
        				echo implode( "\t", $value ) . PHP_EOL;
                    }                    
                    $length = ob_get_length();
                    header( "Content-Length: $length" );
                    header( "X-Content-Length: $length" );
                    header( "Accept-Ranges: bytes" );
                    header( "Expires: $cachee" );  
                    header( "Pragma: cache" );  
                    header( "Cache-Control: max-age=$caches" );  
                    header("Content-type: text/tab-separated-values");
                    ob_end_flush();                     
                	break;
                default:
                    header( "Content-Length: " . mb_strlen( json_encode( $values ) ) );
        			header( "Content-Type:application/json", true );
                    header( "Expires: $cachee" );  
                    header( "Pragma: cache" );  
                    header( "Cache-Control: max-age=$caches" );  
                    echo json_encode( $values, JSON_NUMERIC_CHECK );
                    break;
            }
            break;
        case 'dashboard':
            $query = 
            	"
                SELECT DISTINCT
                	e.ID,
                    e.n_unique_id,
                    e.post_title,
                    e.n_ent_latitude,
                    e.n_ent_longitude,
                    eg.s_ent_geo_country,
                    eg.s_ent_geo_region,
                    eg.s_ent_geo_town,
                    t.s_typology,
                    o.s_ownership,
                    ds.d_date,
                    es.ID AS n_exh_id,
                    sc.n_exh_latitude,
                    sc.n_exh_longitude,
                    sc.s_exh_post_title,
                    xg.s_exh_geo_country,
                    xg.s_exh_geo_region,
                    xg.s_exh_geo_town,
                    ev.s_exhibition_site,
                    et.s_exhibition_types,
                    em.s_exhibition_movements
                FROM
                    (SELECT DISTINCT ID, n_unique_id, post_title, n_latitude AS n_ent_latitude, n_longitude AS n_ent_longitude FROM {$wpdb->prefix}xtr_vw_complete_tax_meta_posts WHERE post_type = \"entity\" AND n_latitude IS NOT NULL and n_longitude IS NOT NULL) AS e
                    LEFT JOIN
                    (SELECT DISTINCT ID, s_geo_country AS s_ent_geo_country, s_geo_region AS s_ent_geo_region, s_geo_town AS s_ent_geo_town FROM {$wpdb->prefix}xtr_vw_complete_tax_meta_posts WHERE post_type = \"entity\" AND s_geo_country IS NOT NULL) AS eg
                    ON
                    (e.ID = eg.ID)
                    LEFT JOIN
                    (SELECT DISTINCT ID, GROUP_CONCAT(DISTINCT term ORDER BY term ASC SEPARATOR \", \") AS s_typology FROM {$wpdb->prefix}xtr_vw_complete_tax_meta_posts WHERE taxonomy = \"tax_typology\" GROUP BY ID) AS t
                    ON
                    (e.ID = t.ID)
                    LEFT JOIN
                    (SELECT DISTINCT ID, GROUP_CONCAT(DISTINCT term ORDER BY term ASC SEPARATOR \", \") AS s_ownership FROM {$wpdb->prefix}xtr_vw_complete_tax_meta_posts WHERE taxonomy = \"tax_ownership\" GROUP BY ID) AS o
                    ON
                    (e.ID = o.ID)      
                    LEFT JOIN
                    (SELECT DISTINCT ID, n_ext_id AS n_ent_id FROM {$wpdb->prefix}xtr_vw_complete_tax_meta_posts WHERE meta_key = \"_cp__exh_supporter_entity\" AND n_ext_id IS NOT NULL) AS es
                    ON
                    (e.ID = es.n_ent_id)
                    LEFT JOIN
                	(SELECT DISTINCT ID, d_date FROM {$wpdb->prefix}xtr_vw_complete_tax_meta_posts WHERE meta_key = \"_cp__exh_exhibition_start_date\" AND d_date IS NOT NULL) AS ds
                    ON
                    (es.ID = ds.ID)
                    LEFT JOIN
                    (SELECT DISTINCT ID, n_latitude AS n_exh_latitude, n_longitude AS n_exh_longitude, post_title AS s_exh_post_title FROM {$wpdb->prefix}xtr_vw_complete_tax_meta_posts WHERE post_type = \"exhibition\" AND n_latitude IS NOT NULL and n_longitude IS NOT NULL) AS sc
                    ON
                    (es.ID = sc.ID)   
                    LEFT JOIN
                    (SELECT DISTINCT ID, meta_value AS s_exhibition_site FROM {$wpdb->prefix}xtr_vw_complete_tax_meta_posts WHERE meta_key = \"_cp__exh_exhibition_site\" AND meta_value IS NOT NULL) AS ev
                    ON
                    (es.ID = ev.ID)
                    LEFT JOIN
                	(SELECT DISTINCT ID, GROUP_CONCAT(DISTINCT term ORDER BY term ASC SEPARATOR \", \") AS s_exhibition_types FROM {$wpdb->prefix}xtr_vw_complete_tax_meta_posts WHERE taxonomy = \"tax_exhibition_type\" GROUP BY ID) AS et
                    ON
                    (es.ID = et.ID)
                    LEFT JOIN
                	(SELECT DISTINCT ID, GROUP_CONCAT(DISTINCT term ORDER BY term ASC SEPARATOR \", \") AS s_exhibition_movements FROM {$wpdb->prefix}xtr_vw_complete_tax_meta_posts WHERE taxonomy = \"tax_movement\" GROUP BY ID) AS em
                    ON
                    (es.ID = em.ID)
                    LEFT JOIN
                    (SELECT DISTINCT ID, s_geo_country AS s_exh_geo_country, s_geo_region AS s_exh_geo_region, s_geo_town AS s_exh_geo_town FROM {$wpdb->prefix}xtr_vw_complete_tax_meta_posts WHERE post_type = \"exhibition\" AND s_geo_country IS NOT NULL) AS xg
                    ON
                    (es.ID = xg.ID);
                "
                ;
            $values = $wpdb->get_results( $query, ARRAY_A );
            switch( $outformat ) {
                case 'tsv':
                	$title = array_keys( $values[0] );
                    header('Content-type: text/tab-separated-values');
                    echo implode( "\t", $title ) . PHP_EOL;
                    foreach( $values as $value ) {
        				echo implode( "\t", $value ) . PHP_EOL;
                    }                    
                	break;
                default:
                    header( 'Content-Length: ' . mb_strlen( json_encode( $values ) ) );
        			header( "Content-Type:application/json", true );
                    echo json_encode( $values );
                    break;
            }
            break;
        case 'leaflet':
            $param = $param == NULL ? 'exhibition' : $param;
            $query = 
                "
                SELECT 
                    p.ID AS id,
                    SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, \",\", 1 ), \",\", -1 ) AS latitude,
                    SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, \",\", 2 ), \",\", -1 ) AS longitude,
                    QUOTE(REPLACE(p.post_title, \",\", \"&#44;\")) AS name,
                    p.post_type AS type
                FROM  
                    {$wpdb->posts} AS p
                    LEFT JOIN
                    {$wpdb->postmeta} AS m
                    ON
                    p.ID = m.post_id
                WHERE 
                    p.post_status = \"publish\"
                    AND
                    m.meta_key LIKE \"%coordinates\"
                    AND
                    p.post_type = \"$param\"
                ORDER BY 
                	post_type ASC,
                    post_title ASC;
                "
                ;
            $geojson = array(
				'type'      => 'FeatureCollection',
				'features'  => array()
			);
            $values = $wpdb->get_results( $query, ARRAY_A );
            switch( $outformat ) {
                case 'geojson':
                    foreach( $values as $value ) {
        				$feature = array(
        					'type' => 'Feature', 
        					'id' => $value['id'], 
        					'geometry' => array(
        						'type' => 'Point',
        						'coordinates' => array( (float)$value['longitude'], (float)$value['latitude'] )
        					),
        					'properties' => array(
        						'name' => $value['name'],
        						'type' => $value['type'],
        						'color' => csl_get_color_from_string( $value['name'] )
        					)
        				);
        				array_push( $geojson['features'], $feature );
                    }
        			header( "Content-Type:application/json", true );
        			echo json_encode( $geojson );
                    break;
                default:
        			header( "Content-Type:application/json", true );
                    echo json_encode( $values );
                    break;
            }
            break;
        case 'geochart':
            $awhere = array(); 
            $aparms = array(); 
            $aparams []= isset( $_REQUEST['y'] ) ? sanitize_text_field( $_REQUEST['y'] ) : date('Y');          
            $aparams []= isset( $_REQUEST['p'] ) ? sanitize_text_field( $_REQUEST['p'] ) : '';          
            $aparams []= isset( $_REQUEST['t'] ) ? sanitize_text_field( $_REQUEST['t'] ) : '';          
            $aparams []= isset( $_REQUEST['m'] ) ? sanitize_text_field( $_REQUEST['m'] ) : ''; 
            if( isset( $aparams[0] ) && '' != $aparams[0] ) $awhere[] = 'n_start_year = ' . $aparams[0];
            if( isset( $aparams[1] ) && '' != $aparams[1] ) $awhere[] = 's_taxonomy = "tax_period" AND s_term = "' . $aparams[1] . '"';
            if( isset( $aparams[2] ) && '' != $aparams[2] ) $awhere[] = 's_taxonomy = "tax_exhibition_type" AND s_term = "' . $aparams[2] . '"';
            if( isset( $aparams[3] ) && '' != $aparams[3] ) $awhere[] = 's_taxonomy = "tax_movement" AND s_term = "' . $aparams[3] . '"';
            $swhere = implode( ' AND ', $awhere );
            $query = 
                "
				SELECT SQL_CACHE 
					p.post_title,
					m.n_ent_id,
					e.n_latitude, 
					e.n_longitude, 
					e.s_geo_country, 
					e.s_geo_region, 
					e.s_geo_town, 
					e.n_start_year, 
					e.n_start_month, 
					e.n_start_day, 
					COUNT(DISTINCT e.ID) AS n_exhibitions 
				FROM 
					{$wpdb->prefix}xtr_vw_unfolded_exhibition AS e
					INNER JOIN
					(SELECT post_id, CAST(SUBSTRING_INDEX(meta_value, \": \", 1) AS UNSIGNED) AS n_ent_id FROM {$wpdb->postmeta} WHERE meta_key = \"_cp__exh_info_source\")  AS m 
					ON 
					e.ID = m.post_id
					INNER JOIN
					{$wpdb->posts} AS p 
					ON 
					m.n_ent_id = p.ID 	
				WHERE 
					$swhere 
				GROUP BY 
					p.post_title,
					m.n_ent_id,
					e.n_latitude, 
					e.n_longitude, 
					e.s_geo_country, 
					e.s_geo_region, 
					e.s_geo_town, 
					e.n_start_year, 
					e.n_start_month, 
					e.n_start_day 
                "
                ;
            $geojson = array(
				'type'      => 'FeatureCollection',
				'features'  => array()
			);
            $values = $wpdb->get_results( $query, ARRAY_A );
            switch( $outformat ) {
                case 'geojson':
                    foreach( $values as $value ) {
        				$feature = array(
        					'type' => 'Feature', 
        					'id' => $value['id'], 
        					'geometry' => array(
        						'type' => 'Point',
        						'coordinates' => array( (float)$value['longitude'], (float)$value['latitude'] )
        					),
        					'properties' => array(
        						'entity' => $value['post_title'],
        						'ent_id' => $value['n_ent_id'],
        						'country' => $value['s_geo_country'],
        						'region' => $value['s_geo_region'],
        						'town' => $value['s_geo_town'],
        						'year' => $value['n_start_year'],
        						'month' => $value['n_start_month'],
        						'day' => $value['n_start_day'],
        						'exhibitions' => $value['n_exhibitions'],
        					)
        				);
        				array_push( $geojson['features'], $feature );
                    }
                    header( 'Content-Length: ' . mb_strlen( $geojson ) );
        			header( "Content-Type:application/json", true );
        			echo json_encode( $geojson );
                    break;
                default:
                    header( 'Content-Length: ' . mb_strlen( json_encode( $values ) ) );
        			header( "Content-Type:application/json", true );
                    echo json_encode( $values );
                    break;
            }
            break;
        case 'geoworks':
            $query = 
               "
                SELECT SQL_CACHE
                    p.ID, 
                    p.post_title,
                    p.post_type,
                    m.meta_id,
                    IF(g.meta_key = \"_cp__peo_country\", g.meta_key, SUBSTRING_INDEX( g.meta_value, \"; \", -1 )) AS s_geo_country,
                    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, \"; \", 2 ), \"; \", -1 ) AS s_geo_region,
                    SUBSTRING_INDEX( g.meta_value, \"; \", 1 ) AS s_geo_town,
                    STR_TO_DATE(d.meta_value, \"%Y-%m-%d\") AS d_date,
                    YEAR(STR_TO_DATE(d.meta_value, \"%Y-%m-%d\")) AS n_year,
                    MONTH(STR_TO_DATE(d.meta_value, \"%Y-%m-%d\")) AS n_month,
                    DAY(STR_TO_DATE(d.meta_value, \"%Y-%m-%d\")) AS n_day,
                    (YEAR(STR_TO_DATE(d.meta_value, \"%Y-%m-%d\")) DIV 100)+1 AS n_century,
                    IF(YEAR(STR_TO_DATE(d.meta_value, \"%Y-%m-%d\")) MOD 100 > 49, 2, 1) AS n_half_century,
                    m.meta_value AS s_coordinates,
                    CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, \",\", 1 ), \",\", -1 ) AS DECIMAL(11,8)) AS n_latitude,
                    CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, \",\", 2 ), \",\", -1 ) AS DECIMAL(11,8)) AS n_longitude
                FROM
                    {$wpdb->postmeta} AS m
                    LEFT JOIN
                    {$wpdb->posts} AS p
                    ON
                    m.post_id = p.ID
                    LEFT JOIN
                    (SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN(\"" . implode( '","', CSL_NORMALIZED_PLACES_META_KEYS ) . "\")) AS g
                    ON
                    m.post_id = g.post_id
                    LEFT JOIN
                    (SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN(\"" . implode( '","', CSL_NORMALIZED_FIRST_DATES_META_KEYS ) . "\")) AS d
                    ON
                    m.post_id = d.post_id    
                WHERE
                    p.post_type IN(\"exhibition\",\"entity\")
                    AND
                    m.meta_key IN(\"" . implode( '","', CSL_NORMALIZED_COORDINATES_META_KEYS ) . "\") 
                    AND
                    m.meta_value IS NOT NULL;
                "
                ;
            $geojson = array(
				'type'      => 'FeatureCollection',
				'features'  => array()
			);
            $values = $wpdb->get_results( $query, ARRAY_A );
            switch( $outformat ) {
                case 'geojson':
                    foreach( $values as $value ) {
        				$feature = array(
        					'type' => 'Feature', 
        					'id' => $value['ID'], 
        					'geometry' => array(
        						'type' => 'Point',
        						'coordinates' => array( (float)$value['n_longitude'], (float)$value['n_latitude'] )
        					),
        					'properties' => $value,
        				);
        				array_push( $geojson['features'], $feature );
                    }
                    ob_start();
        			echo json_encode( $geojson );
                    $length = ob_get_length();
                    header('Content-Length: '.$length);
                    header('X-Content-Length: '.$length);
                    header('Accept-Ranges: bytes');
        			header( "Content-Type: application/json", true );
                    ob_end_flush();                     
                    //header( 'Content-Length: ' . mb_strlen( json_encode( $geojson ) ) );
                    break;
                default:
                    header( 'Content-Length: ' . mb_strlen( json_encode( $values ) ) );
        			header( "Content-Type:application/json", true );
                    echo json_encode( $values );
                    break;
            }
            break;
        case 'geodraw':
            $query = 
            	$param == 'voronoi'
            	?
            	"
				SELECT SQL_CACHE
					p.ID,
					p.post_title,
				    CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( c.meta_value, \",\", 1 ), \",\", -1 ) AS DECIMAL(11,8)) AS n_latitude,
				    CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( c.meta_value, \",\", 2 ), \",\", -1 ) AS DECIMAL(11,8)) AS n_longitude,
				    COUNT(DISTINCT e.post_id) AS n_num_exhibitions
				FROM
					{$wpdb->posts} AS p
					INNER JOIN
					(SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = \"_cp__ent_coordinates\") AS c 
					ON 
					p.ID = c.post_id
					LEFT JOIN
					(SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = \"_cp__exh_supporter_entity\") AS e 
					ON 
					CONCAT(p.ID, \": \", p.post_title) = e.meta_value
				WHERE
					p.post_type = \"entity\"
					AND
					p.post_status = \"publish\"  
				GROUP BY  
					p.ID,
					p.post_title,
				    c.meta_value;
            	"
            	:
                 "
				SELECT SQL_CACHE 
					e.ID AS ent_n_id,
					e.post_title AS ent_entity_name,
					e.n_latitude AS ent_n_latitude,
					e.n_longitude AS ent_n_longitude,
					x.n_latitude AS exh_n_latitude,
					x.n_longitude AS exh_n_longitude,
					s.meta_value AS exh_site,
					COUNT(DISTINCT x.ID) AS n_num_exhibitions
				FROM
					(SELECT post_id, n_ext_id FROM {$wpdb->prefix}xtr_vw_normalized_relations WHERE s_int_post_type = \"exhibition\" AND s_ext_post_type = \"entity\" AND meta_key = \"_cp__exh_supporter_entity\") AS l 
					INNER JOIN
					(SELECT ID, post_title, n_latitude, n_longitude FROM {$wpdb->prefix}xtr_vw_normalized_coordinates WHERE post_type = \"entity\") AS e
					ON
					l.n_ext_id = e.ID
					INNER JOIN 
					(SELECT ID, n_latitude, n_longitude FROM {$wpdb->prefix}xtr_vw_normalized_coordinates WHERE post_type = \"exhibition\") AS x 
					ON 
					l.post_id = x.ID 
					INNER JOIN
					(SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = \"_cp__exh_exhibition_site\") AS s
					ON
					l.post_id = s.post_id
				GROUP BY
					e.ID,
					e.post_title,
					e.n_latitude,
					e.n_longitude,
					x.n_latitude,
					x.n_longitude,
					s.meta_value;
                "
                ;
            $geojson = array(
				'type'      => 'FeatureCollection',
				'features'  => array()
			);
            $values = $wpdb->get_results( $query, ARRAY_A );
            switch( $outformat ) {
                case 'geojson':
                    foreach( $values as $value ) {
        				$feature = array(
        					'type' => 'Feature', 
        					'id' => $value['ID'], 
        					'geometry' => array(
        						'type' => 'Point',
        						'coordinates' => array( (float)$value['n_longitude'], (float)$value['n_latitude'] )
        					),
        					'properties' => $value,
        				);
        				array_push( $geojson['features'], $feature );
                    }
                    ob_start();
        			echo json_encode( $geojson );
                    $length = ob_get_length();
                    header('Content-Length: '.$length);
                    header('X-Content-Length: '.$length);
                    header('Accept-Ranges: bytes');
        			header( "Content-Type: application/json", true );
                    ob_end_flush();                     
                    //header( 'Content-Length: ' . mb_strlen( json_encode( $geojson ) ) );
                    break;
                case 'tsv':
                	$title = array_keys( $values[0] );
                    header('Content-type: text/tab-separated-values');
                    echo implode( "\t", $title ) . PHP_EOL;
                    foreach( $values as $value ) {
        				echo implode( "\t", $value ) . PHP_EOL;
                    }                    
                	break;
                default:
                    header( 'Content-Length: ' . mb_strlen( json_encode( $values ) ) );
        			header( "Content-Type:application/json", true );
                    echo json_encode( $values );
                    break;
            }
            break;
        case 'exportentitiesxls':
        case 'exportentitiescsv':
        case 'exportentitiestsv':
            $query = "
                SELECT 
                    p.ID,
                    IF(m.meta_value IS NOT NULL, m.meta_value, \"$notset\") AS town,
                    p.post_title,
                    u.display_name,
                    p.post_modified
                FROM  
                    (
                    {$wpdb->posts} AS p
                    LEFT JOIN
                    {$wpdb->users} AS u
                    ON
                    p.post_author = u.ID
                    )
                    LEFT JOIN
                    (SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = \"" . CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . "town\") AS m
                    ON
                    p.ID = m.post_id
                WHERE 
                    post_type = \"" . CSL_CUSTOM_POST_ENTITY_TYPE_NAME . "\"
                    AND 
                    post_status = \"publish\"
                ORDER BY 
                    post_title ASC;
                ";
            $outobj = $operation == 'exportentitiesxls' ? new ExportDataExcel('browser', $filename) : ( $operation == 'exportentitiescsv' ? new ExportDataCSV('browser', $filename) : new ExportDataTSV('browser', $filename) );
            $outobj->initialize();
            $title = array(
                __( 'Record ID', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Town', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Entity', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'User', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Date last updated', CSL_TEXT_DOMAIN_PREFIX ),    
            );
            $outobj->addRow($title);
            foreach ($wpdb->get_results($query, ARRAY_A) as $row) {
                $outobj->addRow($row);
            }
            $outobj->finalize();
            break;
        case 'exportexhibitionsxls':
        case 'exportexhibitionscsv':
        case 'exportexhibitionstsv':
            $query = "
                SELECT 
                    p.ID,
                    IF(m.meta_value IS NOT NULL, m.meta_value, \"$notset\") AS town, 
                    p.post_title,
                    u.display_name,
                    p.post_modified
                FROM  
                    (
                    {$wpdb->posts} AS p
                    LEFT JOIN
                    {$wpdb->users} AS u
                    ON
                    p.post_author = u.ID
                    )
                    LEFT JOIN
                    (SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = \"" . CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . "town\") AS m
                    ON
                    p.ID = m.post_id 
                WHERE 
                    post_type = \"" . CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME . "\"
                    AND 
                    post_status = \"publish\"
                ORDER BY 
                    post_title ASC;
                ";
            $outobj = $operation == 'exportexhibitionsxls' ? new ExportDataExcel('browser', $filename) : ( $operation == 'exportexhibitionscsv' ? new ExportDataCSV('browser', $filename) : new ExportDataTSV('browser', $filename) );
            $outobj->initialize();
            $title = array(
                __( 'Record ID', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Town', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Exhibition', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'User', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Date last updated', CSL_TEXT_DOMAIN_PREFIX ),    
            );
            $outobj->addRow($title);
            foreach ($wpdb->get_results($query, ARRAY_A) as $row) {
                $outobj->addRow($row);
            }
            $outobj->finalize();
            break;
		case 'exporterrorsxls':
		case 'exporterrorscsv':
		case 'exporterrorstsv':
            $outobj = $operation == 'exporterrorsxls' ? new ExportDataExcel('browser', $filename) : ( $operation == 'exporterrorscsv' ? new ExportDataCSV('browser', $filename) : new ExportDataTSV('browser', $filename) );
            $outerror = csl_global_quality_control(false, false, $param ? $param : NULL);
            $outobj->initialize();
            $title = array(
                __( 'User', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Type', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Title', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Error type', CSL_TEXT_DOMAIN_PREFIX ),    
            );
            $outobj->addRow($title);
            foreach ($outerror as $row) {
	            $falserow = array(
		            $row->display_name,
		            get_post_type_object( $row->post_type )->labels->singular_name,
		            $row->post_title,
		            $row->c_error_type,
	            );
                $outobj->addRow($falserow);
            }
            $outobj->addRow( array( __( 'Error type', CSL_TEXT_DOMAIN_PREFIX ), '', '', '' ) );
            $outobj->addRow( array( 'ALL', __( 'Total records', CSL_TEXT_DOMAIN_PREFIX ), '', '' ) );
            $outobj->addRow( array( 'DUP', __( 'Possible duplicated title', CSL_TEXT_DOMAIN_PREFIX ), '', '' ) );
            $outobj->addRow( array( 'MAN', __( 'Lack of one or more mandatory fields', CSL_TEXT_DOMAIN_PREFIX ), '', '' ) );
            $outobj->addRow( array( 'TOW', __( 'Bad Geonames local entity convention (semicolons rule) in one or more fields', CSL_TEXT_DOMAIN_PREFIX ), '', '' ) );
            $outobj->addRow( array( 'SRF', __( 'Bad self-reference convention (number + colon rule) in one or more fields', CSL_TEXT_DOMAIN_PREFIX ), '', '' ) );
            $outobj->addRow( array( 'TAX', __( 'Lack of one or more mandatory taxonomies', CSL_TEXT_DOMAIN_PREFIX ), '', '' ) );
            $outobj->finalize();			
			break;
		case 'exportactivitiesxls':
		case 'exportactivitiescsv':
		case 'exportactivitiestsv':
            $outobj = $operation == 'exportactivitiesxls' ? new ExportDataExcel('browser', $filename) : ( $operation == 'exportactivitiescsv' ? new ExportDataCSV('browser', $filename) : new ExportDataTSV('browser', $filename) );
            $aActArgum = array();
            if($param || !current_user_can('edit_others_posts')) {
                $aActArgum['user_id'] = get_current_user_id(); 
            }
            $aActArgum['fields'] = array(
                'user_id'=> '%d',
                'activity'=>'%s',
                'activity_date'=>'%s',
            );
            $aActivity =  csl_get_logs($aActArgum);
            foreach($aActivity as $k => &$v) {
                $disnam = get_the_author_meta('display_name', (int)$v->user_id);
                $v->user_id =$disnam;
                $v->activity = csl_aux_get_human_name_for_log_action($v->activity);
            }
            $outobj->initialize();
            $title = array(
                __( 'User', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Activity', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Activity date', CSL_TEXT_DOMAIN_PREFIX ),    
            );
            $outobj->addRow($title);
            foreach ($aActivity as $row) {
	            $falserow = array(
		            $row->user_id,
		            $row->activity,
		            $row->activity_date,
	            );
                $outobj->addRow($falserow);
            }
            $outobj->finalize();			
			break;
		case 'exportsessionsxls':
		case 'exportsessionscsv':
		case 'exportsessionstsv':
            $outobj = $operation == 'exportsessionsxls' ? new ExportDataExcel('browser', $filename) : ( $operation == 'exportsessionscsv' ? new ExportDataCSV('browser', $filename) : new ExportDataTSV('browser', $filename) );
            $aActArgum = array();
            if($param || !current_user_can('edit_others_posts')) {
                $aActArgum['user_id'] = get_current_user_id(); 
            }
            $aActArgum['fields'] = array(
        		's_display_name',
        		'd_activity_date',
        		'd_max_logout',
        		'd_min_login',
        		'n_num_logins',
        		's_impaired_logouts',
        		'n_num_activities',
        		't_sessions_time',
        		't_activities_time',
        		't_activities_per_session',
        		't_activity_average_time',
            );
    
            $aActivity = csl_get_grouped_logs($aActArgum);
            foreach($aActivity as $k => &$v) {
                $v->s_impaired_logouts = $v->s_impaired_logouts == 'Yes' ? __('Yes') : __('No');
                $v->t_activities_per_session = human_time_diff($curtim, $curtim + $v->t_activities_per_session);
                $v->t_activity_average_time = human_time_diff($curtim, $curtim + $v->t_activity_average_time);
            }
            $outobj->initialize();
            $title = array(
                __('User', CSL_TEXT_DOMAIN_PREFIX), 
                __('Activity date', CSL_TEXT_DOMAIN_PREFIX), 
                __('Last logout', CSL_TEXT_DOMAIN_PREFIX), 
                __('First login', CSL_TEXT_DOMAIN_PREFIX), 
                __('Sessions', CSL_TEXT_DOMAIN_PREFIX), 
                __('Impaired sessions', CSL_TEXT_DOMAIN_PREFIX), 
                __('Activities', CSL_TEXT_DOMAIN_PREFIX), 
                __('Sessions time', CSL_TEXT_DOMAIN_PREFIX), 
                __('Activities time', CSL_TEXT_DOMAIN_PREFIX), 
                __('Session time by activity', CSL_TEXT_DOMAIN_PREFIX), 
                __('Activity average time', CSL_TEXT_DOMAIN_PREFIX), 
            );
            $outobj->addRow($title);
            foreach ($aActivity as $row) {
	            $falserow = array(
		            $row->s_display_name,
		            $row->d_activity_date,
		            $row->d_max_logout,
                    $row->d_min_login,
                    $row->n_num_logins,
                    $row->s_impaired_logouts,
                    $row->n_num_activities,
                    $row->t_sessions_time,
                    $row->t_activities_time,
                    $row->t_activities_per_session,
                    $row->t_activity_average_time,
	            );
                $outobj->addRow($falserow);
            }
            $outobj->finalize();			
			break;
        case 'exporterrurisxls':
        case 'exporterruriscsv':
        case 'exporterruristsv':
            $query = "
                SELECT 
                    u.display_name,
                    p.post_title, 
                    m.meta_value
                FROM  
                    (
                    {$wpdb->posts} AS p
                    INNER JOIN
                    {$wpdb->users} AS u
                    ON
                    p.post_author = u.ID
                    )
                    INNER JOIN
                    {$wpdb->postmeta} m
                    ON
                    p.ID = m.post_id 
                WHERE 
                	p.post_author = $param 
                	AND
                    post_type = \"entity\"
                    AND 
                    post_status = \"publish\"
                    AND
                    m.meta_key = \"_cp__ent_rss_uri\"
                    AND
                    m.meta_value IN
                    (SELECT invalid_rss_uri FROM {$wpdb->xtr_urierror_log} WHERE post_author = p.post_author)
                ORDER BY 
                    u.display_name,
                    p.post_title, 
                    m.meta_value
                ";
            $outobj = $operation == 'exporterrurisxls' ? new ExportDataExcel('browser', $filename) : ( $operation == 'exporterruriscsv' ? new ExportDataCSV('browser', $filename) : new ExportDataTSV('browser', $filename) );
            $outobj->initialize();
            $title = array(
                __( 'Author', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'Entity', CSL_TEXT_DOMAIN_PREFIX ),    
                __( 'RSS URI', CSL_TEXT_DOMAIN_PREFIX ),    
            );
            $outobj->addRow($title);
            foreach ($wpdb->get_results($query, ARRAY_A) as $row) {
                $outobj->addRow($row);
            }
            $outobj->finalize();
            break;
		case 'exportuactxls':
		case 'exportuactcsv':
		case 'exportuacttsv':
			global $csl_s_user_is_manager;
			if($csl_s_user_is_manager) {
	            $outobj = $operation == 'exportuactxls' 
	            	? 
	            	new ExportDataExcel('browser', $filename) 
	            	: 
	            	( $operation == 'exportuactcsv' ? new ExportDataCSV('browser', $filename) : new ExportDataTSV('browser', $filename) );
	            $aActivity = csl_get_users_activities_stats();
	            $outobj->initialize();
	            $title = array(
	                __('User', CSL_TEXT_DOMAIN_PREFIX), 
	                __('Activity date', CSL_TEXT_DOMAIN_PREFIX), 
	                __('Minutes spent', CSL_TEXT_DOMAIN_PREFIX), 
	                __('Time spent', CSL_TEXT_DOMAIN_PREFIX), 
	                __('Activities', CSL_TEXT_DOMAIN_PREFIX), 
	                __('Activity average time', CSL_TEXT_DOMAIN_PREFIX), 
	            );
	            $outobj->addRow($title);
	            foreach ($aActivity as $row) {
		            $falserow = array(
			            $row['display_name'],
			            $row['human_date'],
			            $row['time_diff'],
			            $row['human_time_diff'],
	                    $row['num_activities'],
	                    $row['avg_time_for_activity'],
		            );
	                $outobj->addRow($falserow);
	            }
	            $outobj->finalize();			
			}
			break;
        case 'entmap':
            $query = "
	        SELECT 
	            SUBSTRING_INDEX(m.meta_value, ',', 1) AS lat,
	            SUBSTRING_INDEX(SUBSTRING_INDEX(m.meta_value, ',', 2), ',', -1) AS lon,
	            p.post_title as title,
	            p.post_type as type,
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
	            (SELECT meta_value, post_id FROM {$wpdb->postmeta} WHERE meta_key LIKE \"_cp__%_town\") mm
	            ON
	            p.ID = mm.post_id
	        WHERE 
	            post_type IN (\"entity\",\"exhibition\")
	            AND 
	            post_status = \"publish\"
	            AND
	            m.meta_key LIKE \"_cp__%_coordinates\"
	            AND
	            m.meta_value IS NOT NULL
	        ORDER BY
	            mm.meta_value,
	            p.post_title;
                ";
            echo json_encode($wpdb->get_results($query, OBJECT), JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK);
            break;
		case 'sinfo':
			echo json_encode(csl_get_server_info());
            break;
		case 'bbnotice':
			$output = csl_get_internal_notice( $param )[0];
			$template = file_get_contents(get_template_directory() . '/assets/docs/' . get_locale() . '/tmp_bootstrap_notices.html');
			$template = str_replace(
				'#AUTHOR',
				$output['display_name'],
				$template);
			$template = str_replace(
				'#CONTENT',
				$output['post_content'],
				$template);
			$template = str_replace(
				'#DATE',
				sprintf( __( '%s ago', CSL_TEXT_DOMAIN_PREFIX ), human_time_diff( strtotime($output['post_modified']), time() ) ),
				$template);
			$template = str_replace(
				'#TITLE',
				$output['post_title'],
				$template);
			echo $template;
            break;
		case 'fegetstats':
			global $cls_a_custom_fields_nomenclature;
			global $cls_a_custom_taxonomies_nomenclature;
		
			$nomenclature = array_merge( $cls_a_custom_fields_nomenclature, $cls_a_custom_taxonomies_nomenclature );
			
			$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
			$key = isset($_REQUEST['key']) ? $_REQUEST['key'] : null;
			$where = isset($_REQUEST['where']) ? $_REQUEST['where'] : null;
			$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;
			
			$data = csl_get_project_stats( $type, $key, $where, $limit );
			$output = array();
			//echo '{ "data": [' . PHP_EOL;
			foreach($data as $dat) {
				$aTMP = array();
				$aTMP []= '"' . get_post_type_object( $dat['post_type'] )->labels->singular_name . '"';
				$aTMP []= '"' . $nomenclature[$dat['info_fieldname']] . '"';
				$aTMP []= '"' . $dat['info_value'] . '"';
				$aTMP []= '"' . (int) $dat['n_posts'] . '"';
				$output []= '[ ' . implode( ',', $aTMP ) . ' ]';
			}
			echo '{ "data": [ ' . implode( ',', $output ) . ' ] }';
            break;
		case 'fepivot':
			global $cls_a_custom_fields_nomenclature;
			global $cls_a_custom_taxonomies_nomenclature;
		
			$nomenclature = array_merge( $cls_a_custom_fields_nomenclature, $cls_a_custom_taxonomies_nomenclature );
        	header('Content-Type: text/plain; charset=utf-8');
        	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        	header("Cache-Control: post-check=0, pre-check=0", false);
        	header("Pragma: no-cache");
			$data = csl_get_project_stats_pivot( $limit = 100000 );
        	echo "[ ";
			foreach($data as $key => $dat) {
				$aTMP = array();
				$aTMP [__( 'Type', CSL_TEXT_DOMAIN_PREFIX )]= wp_slash( get_post_type_object( $dat['post_type'] )->labels->singular_name );
				$aTMP [__( 'Field', CSL_TEXT_DOMAIN_PREFIX )]= wp_slash( $dat['info_fieldname'] . "" !== "" ? $nomenclature[$dat['info_fieldname']] : "" );
				$aTMP [__( 'Value', CSL_TEXT_DOMAIN_PREFIX )]= wp_slash( $dat['info_value'] );
				$aTMP [__( 'Records', CSL_TEXT_DOMAIN_PREFIX )]= (int) $dat['n_posts'];
				$aTBL [] = json_encode($aTMP, JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK);
			}
			echo implode(',', $aTBL);
			echo " ]";
			csl_output_buffer_flush();
            break;
		case 'countexp':
			$aPosts = array();
			foreach( CSL_CUSTOM_POST_TYPE_ARRAY as $cpost ) {
				$aPosts[$cpost] = (int) wp_count_posts( $cpost )->publish;
			}
			$nTerms = 0;
			foreach( CSL_CUSTOM_POST_TAXONOMIES_COMPLETE_ARRAY as $cterm ) {
				$nTerms += wp_count_terms( $cterm );	
			}
			$aPosts['terms'] = $nTerms;
			echo serialize( $aPosts );
			csl_output_buffer_flush();
            break;
		case 'pivotT':
            ini_set("memory_limit","256M");
        	header('Content-Type: text/plain; charset=utf-8');
        	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        	header("Cache-Control: post-check=0, pre-check=0", false);
        	header("Pragma: no-cache");
		    ob_implicit_flush(true);
            global $wpdb;

            $query = get_post_field('post_content', $param, 'raw');
		    $aCols = explode( ',', get_post_meta($param, 'csl_acpt_column_labels', true) );
		    $aCIDS = explode( ',', get_post_meta($param, 'csl_acpt_column_ids', true) );
            $aTMP  = $wpdb->get_results( $query, ARRAY_A );
            $aOUT  = array();
            foreach( $aTMP as $aElm ) {
	            $aELT = array();
	            for( $i = 0; $i < count( $aElm ); $i++ ) {
		            $aELT[$aCols[$i]] = $aElm[$aCIDS[$i]];
	            }
	            $aOUT []= $aELT;
            }
            echo json_encode( $aOUT );

            break;
		case 'dataT':
            ini_set("memory_limit","256M");
        	header('Content-Type: text/plain; charset=utf-8');
        	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        	header("Cache-Control: post-check=0, pre-check=0", false);
        	header("Pragma: no-cache");
		    ob_implicit_flush(true);
            global $wpdb;
            $query = get_post_field('post_content', $param, 'raw' );
            echo json_encode( array( 'data' => $wpdb->get_results( $query, ARRAY_N ) ) );

            break;
		case 'chartT':
            ini_set("memory_limit","256M");
        	header('Content-Type: text/plain; charset=utf-8');
        	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        	header("Cache-Control: post-check=0, pre-check=0", false);
        	header("Pragma: no-cache");
		    ob_implicit_flush(true);
            global $wpdb;

            $query = get_post_field('post_content', $param, 'raw');
            $sXLab = get_post_meta($param, 'csl_acpt_column_chart_descriptions', true);
            $sType = get_post_meta($param, 'csl_acpt_column_chart_type', true);
		    $aXLab = explode( ',', $sXLab );
		    $aVals = explode( ',', get_post_meta($param, 'csl_acpt_column_chart_values', true) );
		    $aTVal = array();
		    $aSVal = array();
		    foreach( $aVals as $ava ) {
			    $aTVal []= 'SUM(' . $ava . ') AS ' . $ava;
			    $aSVal []= $ava;
		    }
		    $sVals = implode( ',', $aTVal );
		    $sQury = 'SELECT ' . $sXLab . ',' . $sVals . ' FROM (' . $query . ') AS T GROUP BY ' . $sXLab;
            $aTMP  = $wpdb->get_results( $sQury, ARRAY_A );
            $aKEY  = array( 'x' => implode( ',', $aXLab ), 'value' => $aSVal );
            echo json_encode( array( 'json' => $aTMP, 'keys' => $aKEY, 'type' => $sType ) );

            break;
		case 'tests':
        	header('Content-Type: text/plain; charset=utf-8');
        	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        	header("Cache-Control: post-check=0, pre-check=0", false);
        	header("Pragma: no-cache");
		    ob_implicit_flush(true);
            global $wpdb;
			$query = "
				SELECT 
					TABLE_NAME as s_table,
					(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024 AS n_occupied_size,
					DATA_FREE / 1024 / 1024 AS free_size,
					TABLE_ROWS AS n_rows
				FROM 
					information_schema.TABLES
				WHERE
					TABLE_SCHEMA = \"expofinder_admin\"
					AND
					TABLE_NAME IN (\"" . implode( '","', explode( ',', CSL_VALID_STATS_TABLES ) ) . "\")
			";
            $values = $wpdb->get_results( $query, ARRAY_A );
            ob_start();
			echo json_encode( $values, JSON_NUMERIC_CHECK );
            $length = ob_get_length();
            header('Content-Length: '.$length);
            header('X-Content-Length: '.$length);
            header('Accept-Ranges: bytes');
			header( "Content-Type: application/json", true );
            ob_end_flush();                     
            break;
		default:
			break;
    }
    
    die();  // Very important!!
}
add_action('wp_ajax_csl_generic_ajax_call', 'csl_generic_ajax_call');
add_action('wp_ajax_nopriv_csl_generic_ajax_call', 'csl_generic_ajax_call');

/**
 * Ajax actions in order to control similar titles in posts
 * Based in th WP plugin "Similar post-title checker" v1.0.0, by WP-Parsi team (http://wp-parsi.com)
 */

add_action( 'wp_ajax_csl_st_ajax_hook_sc', 'csl_st_process_sc');
add_action( 'wp_ajax_csl_st_ajax_hook', 'csl_st_process');

/**
 * Main process
 * @return string
 */

function csl_st_process(){
	$post_types = get_post_types();
	global $wpdb;
	if($_POST['sttitle'] != ''){
		$sttitle = $_POST['sttitle'];
		$stlimit = get_option( '_csl_st_screen_options_limit', 10);
		$stminchar = get_option( 'csl_st_screen_options_minchar', 3);
		
		$stlen = mb_strlen($sttitle);
		if($stlen >= $stminchar){
			$results = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_title LIKE '%$sttitle%' AND (post_status = 'publish' OR post_status = 'draft')  LIMIT 0, $stlimit" );
			#echo "<xmp>".print_r($results, true)."</xmp>";
			$out = '';
			if(!empty($results)){
				$out .= "<ul class='postbox'>";
				foreach($results as $result){
					if( in_array( $result->post_type, $post_types ) ){
						$out .=  "<li><a href='".home_url()."/wp-admin/post.php?post=".$result->ID."&action=edit' target='_blank'>".$result->post_title."</a> [" .
                            get_post_type_object( $result->post_type )->labels->singular_name .
                            "]</li>";
					}
				}
				$out .= "</ul>";
			}
			echo $out;
		}
	}
}

//Hook option to screen-option

add_filter('set-screen-option', 'csl_st_set_option', 10, 3);
function csl_st_set_option($status, $option, $value) {
    return $value;
}
add_filter('screen_settings', 'csl_st_show_screen_options', 10, 2 );
function csl_st_show_screen_options( $status, $args ) {
	global $csl_s_user_is_manager;
    if( !$csl_s_user_is_manager )
        return;
	$return = $status;
    global $pagenow;
    if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {    
		$return .= "
		<h5>".__( 'Similar posts options', CSL_TEXT_DOMAIN_PREFIX )."</h5>
		<div class='metabox-prefs'>
		<div class='st_custom_fields'>
		    <label for='st_limit'>
                <input type='number' name='_csl_st_screen_options_limit' id='_csl_st_screen_options_limit' step='1' min='1' class='small-text' value='" . 
                get_option( '_csl_st_screen_options_limit', 10)."' /> ".__( 'Results limit', CSL_TEXT_DOMAIN_PREFIX ) . 
                " </label>
		    <label for='st_minchar'>
                <input type='number' name='_csl_st_screen_options_minchar' id='_csl_st_screen_options_minchar' step='1' min='1' class='small-text' value='" . 
                get_option( '_csl_st_screen_options_minchar', 3)."' /> ".__( 'Limit input character to search', CSL_TEXT_DOMAIN_PREFIX) . 
                " </label>
		    <input type='button' name='st-screen-options-apply' id='st-screen-options-apply' class='button' value='" . 
                __( 'Save Options', CSL_TEXT_DOMAIN_PREFIX ) . 
                "'/> <span class='msg success'>" . 
                __( 'Options saved.', CSL_TEXT_DOMAIN_PREFIX ) . 
                "</span><span class='msg error'>" . 
                __( 'Error. Options not saved.', CSL_TEXT_DOMAIN_PREFIX ) . 
                "</span>
		</div>
		</div>";
    }
    return $return;
}

/**
 * Save option process
 * @return string
 */

function cls_st_process_sc(){
	if ( isset($_POST['stlimit']) ) { 
		update_option( '_csl_st_screen_options_limit', $_POST['stlimit'] );
		update_option( '_csl_st_screen_options_minchar', $_POST['stminchar'] );
	}
}

?>
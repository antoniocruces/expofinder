<?php
/*

BACKUP 20151202

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
*/

// Detecting the server API (SAPI): is the script running from command line or from network?
$isCLI = strtolower(PHP_SAPI) == 'cli';

// Setup global $_SERVER variables to keep WP from trying to redirect
if($isCLI) {
	$_SERVER = array(
		"HTTP_HOST" => "http://admin.expofinder.es",
		"SERVER_NAME" => "http://admin.expofinder.es",
		"REQUEST_URI" => "/",
		"REQUEST_METHOD" => "GET",
		"SERVER_ADDR" => "5.196.92.181"
	);	
}

// Loading the WP bootstrap
require_once(dirname(__FILE__).'/../../../wp-load.php');

class WP_Command_Line {
	public function __construct() {
		//nothing here
	}
	
	public function main($args = array()) {
		$defaults = array(
		'cmd' => 'updateb'
		);
		$args = wp_parse_args($args, $defaults);
		
		switch($args['cmd']) {
			case 'updateb':
			default:
				start_capture();
				break;
		}
	}
}

if($isCLI) {
	$args = parseArgs($argv);
	$importer = new WP_Command_Line();
	$importer->main($args);
} else {
	$tmode = isset($_REQUEST['tmode']);
	start_capture( $tmode );
}

function start_capture( $tmode = false ) {
	global $csl_a_options;
	header('Content-Type: text/plain; charset=utf-8');
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	ob_implicit_flush(true);
	set_time_limit ( 0 );
	if( !$tmode ) {
		error_log(
			current_time("mysql") . ", CRON_TASK_START" . PHP_EOL, 
			3, 
			get_template_directory() . "/assets/logs/csl_cron_beagle.log"
		);
	}
	csl_cron_capture_rss( null, (int) $csl_a_options['beagle_global_threshold'], (int) $csl_a_options['beagle_relative_threshold'], $tmode );
	if( !$tmode ) {
		error_log(
			current_time("mysql") . ", CRON_TASK_END" . PHP_EOL, 
			3, 
			get_template_directory() . "/assets/logs/csl_cron_beagle.log"
		);
	}
}

function parseArgs($argv){
	array_shift($argv);
	$out = array();
	foreach ($argv as $arg){
		if (substr($arg,0,2) == '--'){
			$eqPos = strpos($arg,'=');
				if ($eqPos === false){
					$key = substr($arg,2);
					$out[$key] = isset($out[$key]) ? $out[$key] : true;
				} else {
					$key = substr($arg,2,$eqPos-2);
					$out[$key] = substr($arg,$eqPos+1);
				}
		} else if (substr($arg,0,1) == '-'){
			if (substr($arg,2,1) == '='){
				$key = substr($arg,1,1);
				$out[$key] = substr($arg,3);
			} else {
				$chars = str_split(substr($arg,1));
				foreach ($chars as $char){
					$key = $char;
					$out[$key] = isset($out[$key]) ? $out[$key] : true;
				}
			}
		} else {
			$out[] = $arg;
		}
	}
	return $out;
}

function csl_cron_capture_rss( $numberofrecords = 100, $threshold = 0, $relativethreshold = 0, $testingmode = false )  {
	global $isCLI;
	
	$uris = array_column(csl_cron_get_rss_uris_array($numberofrecords, 'p.post_modified', 'DESC'), 's_uri');
    $numberofrecords = is_null($numberofrecords) ? count($uris) : $numberofrecords;
    $aKW  = array();
    foreach(explode("\n", file_get_contents( get_template_directory(). '/assets/keywords/' . get_locale() . '/' . get_locale() . '.kws' )) as $key => $value) {
        $aKW []= array('weight' => (int) explode(',', $value)[3], 'word' => mb_strtolower(explode(',', $value)[2], "utf-8"));
    }	
    $datStart			= current_time('timestamp');
    $validURIs			= 0;
    $invalidURIs		= 0;
    $saplessURIs		= 0;
    $totalEntries		= 0;
    $sapfullEntr		= 0;
    $saplessEntr		= 0;
    $addedentries		= 0;
    $discardedentries	= 0;

	$aMSG = array(
		__( 'BEAGLE START', CSL_TEXT_DOMAIN_PREFIX ) => date_i18n( 'H:i:s' , $datStart ),	
		"**********************************" => "",
		__( 'SELECTED URIS', CSL_TEXT_DOMAIN_PREFIX ) => "",	
		"----------------------------------" => "",
		__( 'Number of selected URIs..........:', CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($numberofrecords, 0),
		"----------------------------------" => "",
	);    
	csl_print_text_screen_message($aMSG);
	csl_output_buffer_flush();

    foreach($uris as $uri) {
        $response = csl_cron_fetch_googleapis_feed($uri);
        if($response) {
	        $objData = json_decode($response, true);
	        if(!empty($objData['responseData']['feed']['entries'])) {
                $validURIs++;
                $passthreshold = 0;
	    		foreach($objData['responseData']['feed']['entries'] as $entry) {
                    $totalEntries++;
					$textValid = '';
                    $textValid .= !empty($entry['title']) ? $entry['title'] . ' ' : '';
                    if(CSL_EXHIBITION_HASH_INCLUDE_LINK)
                    	$textValid .= !empty($entry['link']) ? $entry['link'] . ' ' : '';
                    if(CSL_EXHIBITION_HASH_INCLUDE_AUTHOR)
                    	$textValid .= !empty($entry['author']) ? $entry['author'] . ' ' : '';
                    if(CSL_EXHIBITION_HASH_INCLUDE_DATE)
                    	$textValid .= !empty($entry['publishedDate']) ? $entry['publishedDate'] . ' ' : '';
                    $textValid .= !empty($entry['contentSnippet']) ? $entry['contentSnippet'] . ' ' : '';
                    $textValid .= !empty($entry['content']) ? $entry['content'] . ' ' : '';
                    $textValid  = wp_kses($textValid, array());
                    $textClean  = $textValid;
                    $textValid  = mb_strtolower(preg_replace('/[^[:alpha:]]/', '', $textValid), "utf-8" );
                    $hashValid  = hash('crc32b', $textValid);
                    
                    // Calculating weight
                    mb_regex_encoding( "utf-8" );
                    $aWords    = array_count_values(array_intersect(array_column($aKW, 'word'), mb_split( ' +', $textClean )));
                    $nSingleWr = count(array_unique(array_keys($aWords)));
                    $nWeights  = 0;
                    foreach($aWords as $key => $value) {
                        $nWeights += (csl_get_word_weight($aKW, $key) * $value);    
                    }
                    $nTotVal   = $nWeights + $nSingleWr;
                    $nRelVal   = $nSingleWr > 0 ? $nTotVal / $nSingleWr : 0;
                    if($nTotVal >= $threshold &&  $nRelVal >= $relativethreshold) {
                        $passthreshold++;

                        // Insert a new exhibition post IF NOT EXISTS $hashValid
                        $entry_exists_in_db  = csl_cron_exists_crc32b($hashValid);
                        $entry_exists_in_log = csl_cron_exists_logcrc($hashValid);
                        $discard_reason      = trim(($entry_exists_in_db ? 'DB' : '') . ' ' . ($entry_exists_in_log ? 'LOG' : ''));
                        //if(!$entry_exists_in_db && !$entry_exists_in_log) { 
                        if(!$entry_exists_in_log) {
                            $parg = array(
                                'post_content'   => !empty($entry['content']) ? preg_replace("/\n+/", "\n", wp_kses( $entry['content'], array( 'p' ) ) )  : '',
                                'post_title'     => !empty($entry['title']) ? preg_replace("/\n+/", "\n", wp_kses( $entry['title'], array() ))  : '',
                                'post_status'    => 'draft',
                                'post_type'      => CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME,
                                'post_author'    => csl_cron_get_uri_post_author($uri),
                                'ping_status'    => 'closed',
                                'post_excerpt'   => !empty($entry['contentSnippet']) ? wp_trim_words(preg_replace("/\n+/", "\n", wp_kses($entry['contentSnippet'], array()))) : '',
                                'comment_status' => 'closed'
                            );
                            if( !$testingmode ) {
                            	$newpost = wp_insert_post( $parg, true );
                            } else {
	                            $newpost = null;
								$aMSG = array(
									$totalEntries . ' ' . __( 'NEW EXIBITION FOUND AND NOT SAVED (TESTING MODE)', CSL_TEXT_DOMAIN_PREFIX ) => "",	
									"----------------------------------" => "",
									__( 'Total added......................:', CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($addedentries, 0),
									__( 'ID...............................:', CSL_TEXT_DOMAIN_PREFIX ) => $hashValid,
									__( 'Title............................:', CSL_TEXT_DOMAIN_PREFIX ) => (!empty($entry['title']) ? $entry['title'] : __( '[Unspecified or not readable]', CSL_TEXT_DOMAIN_PREFIX )),
									__( 'Date.............................:', CSL_TEXT_DOMAIN_PREFIX ) => (!empty($entry['publishedDate']) ? human_time_diff(strtotime($entry['publishedDate']), current_time('timestamp')) : __( '[Unspecified or not readable]', CSL_TEXT_DOMAIN_PREFIX )),
									__( 'Assessment.......................:', CSL_TEXT_DOMAIN_PREFIX ) => 'GLO = ' . number_format_i18n($nTotVal, 0) . ' - REL = ' . number_format_i18n($nRelVal, 2),
									__( 'Keywords.........................:', CSL_TEXT_DOMAIN_PREFIX ) => $nSingleWr . " (" . implode(", ", array_unique(array_keys($aWords))) . ")",
									"----------------------------------" => "",
								);    
								csl_print_text_screen_message($aMSG);
                            }
                            $fkpubdat = !empty($entry['publishedDate']) 
                                ?
                                date_i18n(get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime($entry['publishedDate'])) . 
                                ' (' . sprintf(__('%s ago', CSL_TEXT_DOMAIN_PREFIX), human_time_diff(strtotime($entry['publishedDate']), current_time('timestamp'))) . ')'
                                :
                                NULL;
                            if(!is_wp_error($newpost) || $testingmode ) {
                                $aNBC = csl_naive_bayesian_classification($textValid, 'SH');
                                $addedentries++; 
                                if ( !$testingmode ) {
	                                $metains = add_post_meta($newpost, CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'crc32b_identifier', $hashValid, true); 
	                                $metains = add_post_meta($newpost, CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'global_keywords_weight', number_format_i18n($nTotVal, 0), true); 
	                                $metains = add_post_meta($newpost, CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'relative_keywords_weight', number_format_i18n($nTotVal / count(array_unique(array_keys($aWords))), 2) , true); 
	                                $metains = add_post_meta($newpost, CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'found_keywords', implode(", ", array_unique(array_keys($aWords))), true); 
	                                $sourcep = csl_cron_get_uri_post_id($uri);
	                                foreach($sourcep as $sk => $sv) {
		                            	$metains = add_post_meta($newpost, CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'source_entity', $sv['s_post'], false);    
	                                }
	                                if(!empty($entry['link']))
	                                    $metains = add_post_meta($newpost, CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'original_source_link', $entry['link'], true);  
	                                if(!empty($entry['author']))
	                                    $metains = add_post_meta($newpost, CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'original_reference_author', $entry['author'], true);  
	                                if(!empty($entry['publishedDate']))
	                                    $metains = add_post_meta($newpost, CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'original_publishing_date', $fkpubdat, true);  
	                                csl_insert_log(array('object_id' => $newpost, 'object_type' => 'auto_' . get_post_type( $newpost ), 'activity' => $hashValid ));
                                }
								$aMSG = array(
									$totalEntries . ' ' . __( 'NEW EXIBITION FOUND', CSL_TEXT_DOMAIN_PREFIX ) => "",	
									"----------------------------------" => "",
									__( 'Total added......................:', CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($addedentries, 0),
									__( 'ID...............................:', CSL_TEXT_DOMAIN_PREFIX ) => $hashValid,
									__( 'Title............................:', CSL_TEXT_DOMAIN_PREFIX ) => (!empty($entry['title']) ? $entry['title'] : __( '[Unspecified or not readable]', CSL_TEXT_DOMAIN_PREFIX )),
									__( 'Date.............................:', CSL_TEXT_DOMAIN_PREFIX ) => (!empty($entry['publishedDate']) ? human_time_diff(strtotime($entry['publishedDate']), current_time('timestamp')) : __( '[Unspecified or not readable]', CSL_TEXT_DOMAIN_PREFIX )),
									__( 'Assessment.......................:', CSL_TEXT_DOMAIN_PREFIX ) => 'GLO = ' . number_format_i18n($nTotVal, 0) . ' - REL = ' . number_format_i18n($nRelVal, 2),
									__( 'Keywords.........................:', CSL_TEXT_DOMAIN_PREFIX ) => $nSingleWr . " (" . implode(", ", array_unique(array_keys($aWords))) . ")",
									"----------------------------------" => "",
								);    
								csl_print_text_screen_message($aMSG);
                            } else {
	                            // wp_delete_post($newpost, true);
	                            // There is an error during new post saving process
	                            if( $testingmode || $isCLI ) {
									$aMSG = array(
										$totalEntries . ' ' . __( 'UNKNOWN FAILURE', CSL_TEXT_DOMAIN_PREFIX ) => "",	
										"----------------------------------" => "",
										__( 'Reason...........................:', CSL_TEXT_DOMAIN_PREFIX ) => __( 'Testing mode variable', CSL_TEXT_DOMAIN_PREFIX ),
										"----------------------------------" => "",
									);  
									csl_print_text_screen_message($aMSG);
									//csl_output_buffer_flush();
	                        	}
                            }
                        } else {
	                        $aNBC = csl_naive_bayesian_classification($textValid, 'SS');
                            if( $testingmode || $isCLI ) {
								$aMSG = array(
									$totalEntries . ' ' . __( 'DISCARDED ENTRY', CSL_TEXT_DOMAIN_PREFIX ) => "",	
									"----------------------------------" => "",
									__( 'Reason...........................:', CSL_TEXT_DOMAIN_PREFIX ) => $discard_reason,
									"----------------------------------" => "",
								);  
								csl_print_text_screen_message($aMSG);
								//csl_output_buffer_flush();
                        	}
							$discardedentries++;
                        }
                        
                    } else {
						if( $testingmode || $isCLI ) {
							$aMSG = array(
								$totalEntries . ' ' . __( 'DISCARDED ENTRY', CSL_TEXT_DOMAIN_PREFIX ) => "",	
								"----------------------------------" => "",
								__( 'Reason...........................:', CSL_TEXT_DOMAIN_PREFIX ) => 
	                                sprintf(__( 'Below the minimum threshold: TO=%s, RE=%s', CSL_TEXT_DOMAIN_PREFIX ),
	                                    number_format_i18n($nTotVal, 2),
	                                    number_format_i18n($nRelVal, 2)
	                                ),
								"----------------------------------" => "",
							); 
							csl_print_text_screen_message($aMSG);
							//csl_output_buffer_flush();
						}
                        $saplessEntr++;    
                    }                  
				}
                $sapfullEntr +=  $passthreshold;
                $saplessURIs +=  $passthreshold > 0 ? 0 : 1;
				//csl_output_buffer_flush();
	        } else {
                if( $testingmode || $isCLI ) {
					$aMSG = array(
						$totalEntries . ' ' . __( 'INVALID URI', CSL_TEXT_DOMAIN_PREFIX ) => "",	
						"----------------------------------" => "",
						__( 'URI..............................:', CSL_TEXT_DOMAIN_PREFIX ) => $uri,
						"----------------------------------" => "",
					); 
					csl_print_text_screen_message($aMSG);
					//csl_output_buffer_flush();
                } else {
	                csl_delete_invalid_uri($uri);
	                if(!csl_cron_exists_urierror($uri))
	                    csl_insert_urierror_log(array('post_author' => csl_cron_get_uri_post_author($uri), 'invalid_rss_uri' => $uri)); 
                }
                $saplessEntr++;    
                $invalidURIs++;
	        }
        }
    }

    $datEnd = current_time('timestamp');
    $astats = array(
        'checked_uris' => $numberofrecords,
        'invalid_uris' => $invalidURIs,
        'valid_uris' => $validURIs,
        'sapless_uris' => $saplessURIs,
        'checked_entries' => $totalEntries,
        'sapless_entries' => $saplessEntr,
        'sapfull_entries' => $sapfullEntr,
        'added_entries' => $addedentries,
        'discarded_entries' => $discardedentries,
        'entries_by_valid_uri' => ($totalEntries / $validURIs),
        'entries_by_useful_uri' => ($validURIs - $saplessURIs) !=0 ? $sapfullEntr / ($validURIs - $saplessURIs) : 0,
        'operation_time' => ($datEnd - $datStart),
        'average_time' => (($datEnd - $datStart) / $totalEntries),    
    );
    
    if( !$testingmode ) {
	    csl_insert_beaglecr_log($astats);
	    error_log(
	    	current_time("mysql") . " CRON_TASK_SUCCESS, AE=$addedentries, DE=$discardedentries" . PHP_EOL, 
	    	3, 
	    	get_template_directory() . "/assets/logs/csl_cron_beagle.log"
	    );
    }
	
	$aMSG = array(
		__( 'BEAGLE END', CSL_TEXT_DOMAIN_PREFIX ) => date_i18n( 'H:i:s' , $datStart ),	
		"**********************************" => PHP_EOL,
		__( 'OPERATIONS SUMMARY', CSL_TEXT_DOMAIN_PREFIX ) => "",	
		"**********************************" => "",
		__( "01. Total URIs to check..........:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($numberofrecords, 0),	
		__( "02. Invalid URIs.................:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($invalidURIs, 0) . " (" . round(($invalidURIs / $numberofrecords) * 100, 0) . "% d/01)",	
		__( "03. Valid URIs...................:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($validURIs, 0) . " (" . round(($validURIs / $numberofrecords) * 100, 0) . "% d/01)",	
		__( "04. Sapless URIs.................:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($saplessURIs, 0) . " (" . round(($saplessURIs / $validURIs) * 100, 0) . "% d/03)",	
		__( "05. Total entries to check.......:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($totalEntries, 0),	
		__( "06. Entries by valid URI.........:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($totalEntries / $validURIs, 1),	
		__( "07. Sapless entries..............:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($saplessEntr, 0) . " (" . round(($saplessEntr / $totalEntries) * 100, 0) . "% d/05)",	
		__( "07. Sappy entries................:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($sapfullEntr, 0) . " (" . round(($sapfullEntr / $totalEntries) * 100, 0) . "% d/05)",	
		"----------------------------------" => "",
		__( "08. ADDED NEW ENTRIES............:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($addedentries, 0),	
		__( "09. DISCARDED NEW ENTRIES........:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($discardedentries, 0),	
		"----------------------------------" => "",
		__( "10. Entries by useful URI........:", CSL_TEXT_DOMAIN_PREFIX ) => (($validURIs - $saplessURIs) !=0 ? number_format_i18n($sapfullEntr / ($validURIs - $saplessURIs), 1) : 'N/A'),	
		__( "11. Operation time...............:", CSL_TEXT_DOMAIN_PREFIX ) => ($datEnd - $datStart) . " s",	
		__( "12. Average time for single test.:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n(($datEnd - $datStart) / $totalEntries, 5) . " s",	
		"**********************************" => "",
		"AE=$addedentries:DE=$discardedentries" => "",
	);    
	csl_print_text_screen_message($aMSG);
	csl_output_buffer_flush();
}

function csl_delete_invalid_uri($sURI = NULL) {
    global $wpdb;
    if(!$sURI) 
        return;
    $sql = "
        DELETE 
        FROM 
            $wpdb->postmeta
        WHERE
            meta_key IN ('"
            . CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . "rss_uri','" . CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . "html_uri" .   
            "')
            AND
            meta_value = '$sURI'; 
    ";
    $results = $wpdb->get_results($sql);
}

function csl_print_text_screen_message($aMSG = array()) {
	foreach($aMSG as $key => $val) {
		echo $key . ' ' . $val . "\n";
	}
	csl_output_buffer_flush();
}

function csl_get_word_weight($aKEY = array(), $aWOR = NULL) {
    if(!empty($aWOR)) {
        foreach($aKEY as $key => $value) {
            if($value['word'] == $aWOR)
                return (int)$value['weight'];
        }
        return false;
    } else {
        return false;
    }    
}

function csl_cron_get_rss_uris_array($limit = NULL, $orderby = NULL, $order = NULL) {
    global $wpdb;

    $qlimit = $limit ? ' LIMIT ' . $limit : '';
    $qorderby = $orderby ? ' ORDER BY ' . $orderby : '';
    $qorder = $order ? ' ' . $order : '';
    return $wpdb->get_results("
        SELECT DISTINCT
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
            m.meta_key IN ('"
            . CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . "rss_uri','" . CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . "html_uri" .   
            "')
        	AND
        	p.post_status = 'publish'
        	AND
        	p.post_type = '" . CSL_CUSTOM_POST_ENTITY_TYPE_NAME . "'
        $qorderby $qorder 
        $qlimit
    ",
    ARRAY_A);
}

function csl_cron_get_uri_post_author($uri = NULL) {
    if(is_null($uri)) 
        return false;
        
    global $wpdb;
    return (int) $wpdb->get_var("
        SELECT DISTINCT
        	p.post_author
        FROM
        	$wpdb->postmeta m
        	INNER JOIN
        	$wpdb->posts p
        	ON
        	p.ID = m.post_id
        WHERE
            meta_key IN ('"
            . CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . "rss_uri','" . CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . "html_uri" .   
            "')
        	AND
        	p.post_status = 'publish'
        	AND
        	p.post_type = '" . CSL_CUSTOM_POST_ENTITY_TYPE_NAME . "'
            AND
            m.meta_value = \"" . esc_sql($uri) . "\"
        LIMIT 1;
    ");
}

function csl_cron_get_uri_post_id($uri = NULL) {
    if(is_null($uri)) 
        return false;
        
    global $wpdb;
    return $wpdb->get_results("
        SELECT DISTINCT
        	CONCAT(p.ID, ': ', p.post_title) as s_post
        FROM
        	$wpdb->postmeta m
        	INNER JOIN
        	$wpdb->posts p
        	ON
        	p.ID = m.post_id
        WHERE
            meta_key IN ('"
            . CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . "rss_uri','" . CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . "html_uri" .   
            "')
        	AND
        	p.post_status = 'publish'
        	AND
        	p.post_type = '" . CSL_CUSTOM_POST_ENTITY_TYPE_NAME . "'
            AND
            m.meta_value = \"" . esc_sql($uri) . "\"
    ", ARRAY_A);
}

function csl_cron_exists_crc32b($crc = NULL) {
    if(is_null($crc)) 
        return false;
        
    global $wpdb;
    return (int) $wpdb->get_var("
        SELECT DISTINCT
        	COUNT(m.meta_id)
        FROM
        	$wpdb->postmeta m
        WHERE
        	m.meta_key = '_cp__exh_crc32b_identifier'
            AND
            m.meta_value = \"" . esc_sql($crc) . "\"
        LIMIT 1;
    ") > 0;
}

function csl_cron_exists_urierror($uri = NULL) {
    if(is_null($uri)) 
        return false;
        
    global $wpdb;
    return (int) $wpdb->get_var("
        SELECT DISTINCT
        	COUNT(e.log_id)
        FROM
        	$wpdb->xtr_urierror_log e
        WHERE
        	e.invalid_rss_uri = \"" . esc_sql($uri) . "\"
            OR
        	e.invalid_html_uri = \"" . esc_sql($uri) . "\"
        LIMIT 1;
    ") > 0;
}

function csl_cron_exists_logcrc($crc = NULL) {
    if(is_null($crc)) 
        return false;
        
    global $wpdb;
    return (int) $wpdb->get_var("
        SELECT DISTINCT
        	COUNT(e.log_id)
        FROM
        	$wpdb->xtr_activity_log e
        WHERE
            e.object_type = \"auto_" . CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME . "\" 
            AND
        	e.activity = \"" . esc_sql($crc) . "\"
        LIMIT 1;
    ") > 0;
}

function csl_cron_fetch_googleapis_feed($url) {
	$response = wp_remote_get( 'https://ajax.googleapis.com/ajax/services/feed/load?v=2.0&output=json&userip=' . $_SERVER["SERVER_ADDR"] . '&q=' . $url);
	if ( !is_wp_error( $response ) ) {
		if( $response['body'] ) {
			$resp = json_decode($response['body']);
			if(200 == $resp->responseStatus) {
				return $response['body']; 
			} else {
				$rhtml = wp_remote_get($url);
				if ( !is_wp_error( $rhtml ) ) {
					if( $rhtml['body'] ) {
						$html = html_entity_decode( $rhtml['body'], ENT_COMPAT, 'utf-8' );
						$meta = csl_get_meta_tags( $html );
						if( false === strpos( $url, 'https://www.facebook.com' ) ) {
							$pattern = '/article|body|content|entry|hentry|main|page|attachment|post|post-content|text|blog|story/i';
						} else {
							$pattern = '/userContent|usercontent/i';
						}
						if( false === strpos( $url, 'https://www.facebook.com' ) ) {
							$titltag = 'h1';
						} else {
							$titltag = 'h2';
						}
						if( false === strpos( $url, 'https://www.facebook.com' ) ) {
							$texttag = array('div','article');
						} else {
							$texttag = 'p';
						}
						if( false === strpos( $url, 'https://www.facebook.com' ) ) {
							$attrtag = 'id';
						} else {
							$attrtag = 'class';
						}
						
						if (function_exists('tidy_parse_string')) {
						    $tidy = tidy_parse_string($html, array(), 'UTF8');
						    $tidy->cleanRepair();
						    $html = $tidy->value;
						}
						$aOutput = array();
						// Get title
						$aTMP = array();
						$nodes = csl_extract_tags( $html, array( $titltag ), false );
						foreach($nodes as $node){
						    $aTMP []= wp_strip_all_tags( $node['contents'], true );
	                        if( count( $aTMP ) > 0 ) {
	                            break;
	                        }
						}
						if( count( $aTMP ) > 0  && trim($aTMP[0]) == '' ) {
							$aOutput['title'] = $aTMP[0];
	                    } else {
	                        $aOutput['title'] = wp_strip_all_tags( csl_get_html_title_tag( $html ), true );
	                    }
	
						// Get content
						$aTMP = array();
						$nodes = csl_extract_tags( $html, $texttag, false );
						foreach($nodes as $node){
							$sTMP = trim( ( isset($node['attributes'][$attrtag]) ? $node['attributes'][$attrtag] : '' ) );
	                        if( $sTMP != '' ) {
								if( false !== preg_match( $pattern, $sTMP ) || false !== preg_match( $pattern, $sTMP ) ) {
	                                $sTMP = trim( wp_strip_all_tags( $node['contents'], true ) );
	                                if( $sTMP != '' ) {
							    	 $aTMP []= $sTMP;
	                                }
	                            }
	                        }
						}
						if( count( $aTMP ) > 0 ) {
							$aOutput['content'] = trim( preg_replace( array( '/\s{2,}/', '/[\t\n]/' ), ' ', '<p>' . implode( '</p><p>', $aTMP ) . '</p>' ) );
							// $aOutput['content'] = preg_replace( array( '/\s{2,}/', '/[\t\n]/' ), ' ', $aTMP[0] );
							$aOutput['contentSnippet'] = wp_trim_words( $aOutput['content'], 55 );
						}
						$aOutput['link'] = $url;
						if( isset( $meta['author'] ) )
							$aOutput['author'] = $meta['author'];
						if( isset( $meta['date'] ) )
							$aOutput['publishedDate'] = $meta['date'];
						return json_encode( 
							array( 
								'responseData' => array (
									'feed' => array	(
										'entries' => array(
											$aOutput
										)
									)
								)
							)
						);
					} else {
						return false;	
					}
				} else {
					return false;
				}
			}
        
		} else {
			return false;	
		}		
	} else {
		return false;
	}
}

function csl_new_rss_capture( $url ) {

    include_once( ABSPATH . WPINC . '/feed.php' );
    $rss = fetch_feed( $url );
    $maxitems = 0;
    
    if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly
        $maxitems = $rss->get_item_quantity( 5 ); 
        $rss_items = $rss->get_items( 0, $maxitems );
    endif;

    $output = array();
    if ( $maxitems > 0 ) : 
        foreach ( $rss_items as $item ) :
            $outEntry = array();
            if( $item->get_title() ) $outEntry['title'] = $item->get_title();
            if( $item->get_permalink() ) $outEntry['link'] = $item->get_permalink();
            if( $item->get_author() ) $outEntry['author'] = $item->get_author()->name;
            if( $item->get_date('j F Y | g:i a') ) $outEntry['publishedDate'] = $item->get_date('j F Y | g:i a');
            if( $item->get_description() ) $outEntry['contentSnippet'] = $item->get_description();
            if( $item->get_content() <> $item->get_description() ) $outEntry['content'] = $item->get_content();
            $output['responseData']['feed']['entries'][] = $outEntry;
        endforeach;
    endif;
    return $output;
}

function csl_get_meta_tags( $str ) {
	$pattern = '
		~<\s*meta\s
		
		# using lookahead to capture type to $1
		(?=[^>]*?
		\b(?:name|property|http-equiv)\s*=\s*
		(?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
		([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
		)
		
		# capture content to $2
		[^>]*?\bcontent\s*=\s*
		(?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
		([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
		[^>]*>
		
		~ix';
	
	if(preg_match_all($pattern, $str, $out))
		return array_combine($out[1], $out[2]);
	return array();
}

/**
 * csl_extract_tags()
 * Extract specific HTML tags and their attributes from a string. Based in a code from W-Shadow
 * http: //w-shadow.com/blog/2009/10/20/how-to-extract-html-tags-and-their-attributes-with-php/
 *
 * You can either specify one tag, an array of tag names, or a regular expression that matches the tag name(s). 
 * If multiple tags are specified you must also set the $selfclosing parameter and it must be the same for 
 * all specified tags (so you can't extract both normal and self-closing tags in one go).
 * 
 * The function returns a numerically indexed array of extracted tags. Each entry is an associative array
 * with these keys :
 *  tag_name    - the name of the extracted tag, e.g. "a" or "img".
 *  offset      - the numberic offset of the first character of the tag within the HTML source.
 *  contents    - the inner HTML of the tag. This is always empty for self-closing tags.
 *  attributes  - a name -> value array of the tag's attributes, or an empty array if the tag has none.
 *  full_tag    - the entire matched tag, e.g. '<a href="http://example.com">example.com</a>'. This key 
 *                will only be present if you set $return_the_entire_tag to true.      
 *
 * @param string $html The HTML code to search for tags.
 * @param string|array $tag The tag(s) to extract.                           
 * @param bool $selfclosing Whether the tag is self-closing or not. Setting it to null will force the script to try and make an educated guess. 
 * @param bool $return_the_entire_tag Return the entire matched tag in 'full_tag' key of the results array.  
 * @param string $charset The character set of the HTML code. Originally defaults to ISO-8859-1.
 *
 * @return array An array of extracted tags, or an empty array if no matching tags were found. 
 */
function csl_extract_tags( $html, $tag, $selfclosing = null, $return_the_entire_tag = false, $charset = 'UTF-8' ){
     
    if ( is_array($tag) ){
        $tag = implode('|', $tag);
    }
     
    //If the user didn't specify if $tag is a self-closing tag we try to auto-detect it
    //by checking against a list of known self-closing tags.
    $selfclosing_tags = array( 'area', 'base', 'basefont', 'br', 'hr', 'input', 'img', 'link', 'meta', 'col', 'param' );
    if ( is_null($selfclosing) ){
        $selfclosing = in_array( $tag, $selfclosing_tags );
    }
     
    //The regexp is different for normal and self-closing tags because I can't figure out 
    //how to make a sufficiently robust unified one.
    if ( $selfclosing ){
        $tag_pattern = 
            '@<(?P<tag>'.$tag.')           # <tag
            (?P<attributes>\s[^>]+)?       # attributes, if any
            \s*/?>                   # /> or just >, being lenient here 
            @xsi';
    } else {
        $tag_pattern = 
            '@<(?P<tag>'.$tag.')           # <tag
            (?P<attributes>\s[^>]+)?       # attributes, if any
            \s*>                 # >
            (?P<contents>.*?)         # tag contents
            </(?P=tag)>               # the closing </tag>
            @xsi';
    }
     
    $attribute_pattern = 
        '@
        (?P<name>\w+)                         # attribute name
        \s*=\s*
        (
            (?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)    # a quoted value
            |                           # or
            (?P<value_unquoted>[^\s"\']+?)(?:\s+|$)           # an unquoted value (terminated by whitespace or EOF) 
        )
        @xsi';
 
    //Find all tags 
    if ( !preg_match_all($tag_pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ){
        //Return an empty array if we didn't find anything
        return array();
    }
     
    $tags = array();
    foreach ($matches as $match){
         
        //Parse tag attributes, if any
        $attributes = array();
        if ( !empty($match['attributes'][0]) ){ 
             
            if ( preg_match_all( $attribute_pattern, $match['attributes'][0], $attribute_data, PREG_SET_ORDER ) ){
                //Turn the attribute data into a name->value array
                foreach($attribute_data as $attr){
                    if( !empty($attr['value_quoted']) ){
                        $value = $attr['value_quoted'];
                    } else if( !empty($attr['value_unquoted']) ){
                        $value = $attr['value_unquoted'];
                    } else {
                        $value = '';
                    }
                     
                    //Passing the value through html_entity_decode is handy when you want
                    //to extract link URLs or something like that. You might want to remove
                    //or modify this call if it doesn't fit your situation.
                    $value = html_entity_decode( $value, ENT_QUOTES, $charset );
                     
                    $attributes[$attr['name']] = $value;
                }
            }
             
        }
         
        $tag = array(
            'tag_name' => $match['tag'][0],
            'offset' => $match[0][1], 
            'contents' => !empty($match['contents'])?$match['contents'][0]:'', //empty for self-closing tags
            'attributes' => $attributes, 
        );
        if ( $return_the_entire_tag ){
            $tag['full_tag'] = $match[0][0];            
        }
          
        $tags[] = $tag;
    }
     
    return $tags;
}

function csl_get_html_title_tag( $sTXT ) {
    if( preg_match( "#(.+)<\/title>#iU", $sTXT, $t ) )  {
        return trim( $t[1] );
    } else {
        return '';
    }
}

?>
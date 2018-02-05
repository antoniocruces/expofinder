<?php
/*
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
			'c' => 'updateb',
			'm' => NULL,
		);
		$args = wp_parse_args($args, $defaults);

		switch($args['c']) {
			case 'updateb':
			default:
				start_capture( $args['m'] );
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
	set_time_limit( 0 );
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

//** START TESTING

function contains_all_s($str,array $words) {
    if(!is_string($str))
        { return false; }
    return count(
        array_intersect(
        #   lowercase all words
            array_map('strtolower',$words),
        #   split by non-alphanumeric chars (hyphens are safe)
            preg_split('/[^a-z0-9\-]/',strtolower($str))
        )
    ) == count($words);
}


function csl_Rabin_Karp($A, $B)
{
	$retVal = array();
	$siga = 0;
	$sigb = 0;
	$Q = 100007;
	$D = 256;
	$BLen = strlen($B);
	$ALen = strlen($A);

	for ($i = 0; $i < $BLen; $i++)
	{
		$siga = ($siga * $D + $A[$i]) % $Q;
		$sigb = ($sigb * $D + $B[$i]) % $Q;
	}

	if ($siga == $sigb)
		array_push($retVal, 0);

	$pow = 1;

	for ($k = 1; $k <= $BLen - 1; $k++)
		$pow = ($pow * $D) % $Q;

	for ($j = 1; $j <= $ALen - $BLen; $j++)
	{
		$siga = ($siga + $Q - $pow * $A[$j - 1] % $Q) % $Q;
		$siga = ($siga * $D + $A[$j + $BLen - 1]) % $Q;

		if ($siga == $sigb)
			if (substr($A, $j, $BLen) == $B)
				array_push($retVal, $j);
	}

	return $retVal;
}

function csl_extract_keywords_and_frequences($string, $stopWords, $aKeyWords ){
      $string = preg_replace('/\s\s+/i', '', $string); // replace whitespace
      $string = trim($string); // trim the string
      $string = preg_replace('/[^a-zA-Z0-9 -]/', '', $string); // only take alphanumerical characters, but keep the spaces and dashes too…
      $string = strtolower($string); // make it lowercase
   
      preg_match_all('/\b.*?\b/i', $string, $matchWords);
      $matchWords = $matchWords[0];
      
      foreach ( $matchWords as $key=>$item ) {
          if ( $item == '' || in_array(strtolower($item), $stopWords) || strlen($item) <= 3 ) {
              unset($matchWords[$key]);
          }
      }
      $matchWords = array_intersect( $matchWords, $aKeyWords ); 
      $wordCountArr = array_count_values( $matchWords );
      arsort($wordCountArr);
      $wordCountArr = array_slice($wordCountArr, 0, 10);
      return $wordCountArr;
}

function extractKeyWords( $string, $stopwords, $aKeyWords ) {
	mb_internal_encoding('UTF-8');
	$string = preg_replace('/[\pP]/', '', trim(preg_replace('/\s\s+/i', '', mb_strtolower(utf8_encode($string)))));
	$matchWords = array_filter(explode(' ',$string) , function ( $item ) use ( $stopwords ) { return !( $item == '' || in_array( $item, $stopwords ) || mb_strlen( $item ) > 2 || is_numeric( $item )); } );
	
	$wordCountArr = array_count_values($matchWords);
	arsort($wordCountArr);
	return array_keys(array_slice($wordCountArr, 0, 10));
}

/**
 * HTML/XML Node
 * EXAMPLE
 * require 'Node.php';
 * $html = file_get_contents('http: / / www. theguardian. com /science/2014/jan/29/fifth-neanderthals-genetic-code-lives-on-humans');
 * $parser = new Node;
 * //$parser->debug = true;
 * $topContent = $parser->parse($html);
 * echo $topContent->content;
 */
class Node {
	/**
	 * Node name
	 * @var string
	 */
	public $name;
	
	/**
	 * Node attributes
	 * @var array
	 */
	public $attributes = array();
	
	/**
	 * Child nodes
	 * @var array
	 */
	public $childNodes = array();
	
	/**
	 * Parent nodes
	 * @var array
	 */
	public $parents = array();
	
	/**
	 * Node content
	 * @var string
	 */
	public $content;
	
	/**
	 * Output logs
	 * @var boolean
	 */
	public $debug = false;
	
	/**
	 * Creates a new Node object
	 * @param string $name The name of the node
	 * @param array $attributes The attributes of the node
	 */
	function __construct($name=null, $attributes=array()) {
		$this->name = $name;
		$this->attributes = $attributes;
	}
	
	/**
	 * Get the text of the node
	 * @return string
	 */
	function text() {
		return trim(preg_replace('#&lt;/?[a-z]+&gt;#', '', strip_tags($this->content)));
	}
	
	/**
	 * Calculates the text rank of the node
	 * @return float
	 */
	function rank() {
		$Ec = 0;
		$influence = array('p');
		$influence_acumulative = 1;
		
		// calculate child nodes rank
		if ($this->childNodes) {
			foreach ($this->childNodes as $node) {
				$Ec += strlen($node->text());
				
				if (in_array($node->name, $influence))
					$influence_acumulative *= 10;
			}
		}
		
		$Ec = $Ec + (strlen($this->text()) - $Ec);
		
		if ($influence_acumulative > 1)
			$Ec = $Ec * (($influence_acumulative / 100) + 1);
		
		return (count($this->childNodes) * $Ec) / (count($this->parents) + 1);
	}
	
	/**
	 * Parses HTML/XML string
	 * @param string $str The input string
	 * @return Node Returns the top rank node
	 */
	function parse($str) {
		// single tags
		$single = array('link', 'meta', 'input', 'br', 'img');
		// first step
		$is1 = 0;
		// second step
		$is2 = 0;
		// close
		$cl = 0;
		// in name
		$is_name = 1;
		// element name
		$name = '';
		// attributes of element
		$attr = array();
		// attribute temp name
		$attrn = '';
		// attribute temp value
		$attrv = '';
		// in attribute
		$in_attrv = 0;
		// char used to open attr value
		$cl_attrv = '';
		// content
		$content = '';
		// top rank
		$top_rank = 0;
		// top node
		$top_e = '';
		// stack of nodes
		$stack = array();
		// total characteres to parse
		$strn = strlen($str);

		for ($i=0; $i < $strn; $i++) {
			// character
			$c = $str[$i];
			
			// inside element
			if ($is1 && $is2) {
				// end of element
				if ($c == '>') {
					// create element
					if (!$cl || $str[$i-1] == '/') {
						if ($attrn)
							$attr[$attrn] = $attrv;
						
						// creates a new Node
						$node = new self($name, $attr);
						
						if ($stack) {
							$stack[count($stack)-1]->content .= $content;
							$stack[count($stack)-1]->childNodes[] = $node;
						}
						// first element
						else {
							if ($this->debug)
								echo "<h1>is the document!</h1>";
							$this->childNodes[] = $node;
						}
						
						// single element
						if (in_array($name, $single)) {
							$stack[count($stack)-1]->content .= '<'. $node->name .'/>';
						}
						else
							$stack[] = $node;
					}
					// closes the open tag
					else {
						$closed = array_pop($stack);
						$closed->content .= $content;
						
						if ($this->debug)
							echo "<h2>rank of {$closed->name} = {$closed->rank()}</h2>";
						
						if ($closed->rank() > $top_rank) {
							$top_rank = $closed->rank();
							$top_e = $closed;
						}
						
						// parent
						$node = $stack[count($stack) - 1];
						$node->parents = array_merge(
							is_array($node->parents) ? $node->parents : array(),
							array($closed->name),
							$closed->parents);
						$node->content .= '<'. $closed->name .'>'. $closed->content .'</'. $closed->name.'>';
						
						if ($this->debug)
							echo 'close ';
					}

					// reset
					$is_name = 1;
					$is1 = $is2 = $cl = $in_attrv = 0;
					$attr = array();
					$attrn = $attrv = $cl_attrv = $content = '';
					
					if ($this->debug)
						echo "element: -- <b>{$name}</b> -- i: {$i}, c: {$c}<br/>";
					
					$name = '';
					
				}
				// capturing node name and attributes
				else {
					if ($c == ' ' && !$cl_attrv) {					
						$is_name = 0;
						if ($attrn) {
							$attr[$attrn] = $attrv;
							$attrn = $attrv = '';
							$in_attrv = 0;
						}
					}
					else if ($c == '/' && $str[$i+1] == '>') {
						if ($this->debug)
							echo "close {$name}<br/>";
						$cl = 1;
					}
					else {
						// concatening to name
						if ($is_name) {
							$name .= $c;
						}
						// attributes
						else {
							if (!$in_attrv && $c == '=') {
								$in_attrv = 1;
							}
							else {
								// value of attr
								if ($in_attrv) {
									// if is opening value
									if ($str[$i-1] == '=' && ($c == '"' || $c == '\''))
										$cl_attrv = $c;
									else {
										if ($cl_attrv && $cl_attrv == $c) {
											$attr[$attrn] = $attrv;
											$attrn = $attrv = $cl_attrv = '';
											$in_attrv = 0;
										}
										else {
											$attrv .= $c;
										}
									}
								}
								// name of attr
								else {
									$attrn .= $c;
								}
							}
						}
					}
				}
			}
			else {
				// first step
				if ($is1) {
					if ($c >= 'a' && $c <= 'z' || $c == '/') {
						$is2 = 1;
						
						if ($c == '/')
							$cl = 1;
						else
							$name = $c;
					}
					else {
						$is1 = 0;
						// add to content
						$content .= $str[$i-1];
						if ($this->debug)
							echo 'not is element<br/>';
					}
				}		
				else {
					if ($c == '<') {
						$is1 = 1;
					}
					else {
						$content .= $c;
					}
				}
			}
		}
		
		if ($this->debug)
			echo '<h1>Top rank is '.$top_rank.' in '.$top_e->name.'. We talking about:</h1><h4>'.$top_e->content.'</h4>';
		
		return $top_e;
	}
}
//** END TESTING

function csl_cron_capture_rss( $numberofrecords = 100, $threshold = 0, $relativethreshold = 0, $testingmode = false )  {
	global $isCLI;

	@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', CSL_MAX_MEMOTY_LIMIT ) );
	$uris = array_column(csl_cron_get_rss_uris_array($numberofrecords, 'p.post_modified', 'DESC'), 's_uri');
	$numberofrecords = is_null($numberofrecords) ? count($uris) : $numberofrecords;
	$aKW  = array();
	foreach(explode("\n", file_get_contents( get_template_directory(). '/assets/keywords/' . get_locale() . '/' . get_locale() . '.kws' )) as $key => $value) {
		$aKW []= array('weight' => (int) explode(',', $value)[3], 'word' => mb_strtolower(explode(',', $value)[2], "utf-8"));
	}
	$aSW = file( get_template_directory(). '/assets/keywords/' . get_locale() . '/' . get_locale() . '.sws' );
	$datStart   = current_time('timestamp');
	$validURIs   = 0;
	$invalidURIs  = 0;
	$saplessURIs  = 0;
	$totalEntries  = 0;
	$sapfullEntr  = 0;
	$saplessEntr  = 0;
	$addedentries  = 0;
	$discardedentries = 0;

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
					$textValid  = sanitize_text_field( $textValid ); //wp_kses($textValid, array());
					$textClean  = $textValid;
					$textValid  = mb_strtolower(preg_replace('/[^[:alpha:]]/', '', $textValid), "utf-8" );
					$hashValid  = hash('crc32b', $textValid);

					// Calculating weight
					mb_regex_encoding( "utf-8" );
					// $aWords    = array_count_values(array_intersect(array_column($aKW, 'word'), mb_split( ' +', $textClean )));
					// DEBUG MARK
					// echo "DEBUG ==>> ARRAY AWORDS: ";
					// print_r( extractCommonWords( $textClean, $aSW, array_column($aKW, 'word') ) );
					/*
					foreach(array_column($aKW, 'word') as $wrd) {
						echo $textClean .PHP_EOL;
						echo $wrd .PHP_EOL;
						print_r( csl_RabinKarp($textClean, $wrd) );
						echo "----------------" . PHP_EOL;
					}
					*/
					//csl_output_buffer_flush();
					//$nSingleWr = count(array_unique(array_keys($aWords)));
					$aWords = csl_extract_keywords_and_frequences( $textClean, $aSW, array_column($aKW, 'word') );
					$nSingleWr = count( $aWords );
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
						if(!$entry_exists_in_db && !$entry_exists_in_log) {
							//if(!$entry_exists_in_log && ) {
							$parg = array(
								'post_content'   => !empty($entry['content']) ? preg_replace( "/\n+/", "\n", wp_kses( $entry['content'], array( 'p' ) ) )  : '',
								'post_title'     => !empty($entry['title']) ? preg_replace( "/\n+/", "\n", wp_kses( $entry['title'], array() ) )  : '',
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
									$uri,
									__( 'Total added......................:', CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($addedentries, 0),
									__( 'ID...............................:', CSL_TEXT_DOMAIN_PREFIX ) => $hashValid,
									__( 'Title............................:', CSL_TEXT_DOMAIN_PREFIX ) => (!empty($entry['title']) ? $entry['title'] : __( '[Unspecified or not readable]', CSL_TEXT_DOMAIN_PREFIX )),
									__( 'Date.............................:', CSL_TEXT_DOMAIN_PREFIX ) => (!empty($entry['publishedDate']) ? human_time_diff(strtotime($entry['publishedDate']), current_time('timestamp')) : __( '[Unspecified or not readable]', CSL_TEXT_DOMAIN_PREFIX )),
									__( 'Assessment.......................:', CSL_TEXT_DOMAIN_PREFIX ) => 'GLO = ' . number_format_i18n($nTotVal, 0) . ' - REL = ' . number_format_i18n($nRelVal, 2),
									__( 'Keywords.........................:', CSL_TEXT_DOMAIN_PREFIX ) => $nSingleWr . " (" . implode(", ", array_unique(array_keys($aWords))) . ")",
									"----------------------------------" => "",
								);
								csl_print_text_screen_message($aMSG);
								csl_output_buffer_flush();
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
								csl_output_buffer_flush();
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
									csl_output_buffer_flush();
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
								csl_output_buffer_flush();
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
							csl_output_buffer_flush();
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
					csl_output_buffer_flush();
				} else {
					csl_delete_invalid_uri($uri);
					if(!csl_cron_exists_urierror($uri))
						csl_insert_urierror_log(array('post_author' => csl_cron_get_uri_post_author($uri), 'invalid_rss_uri' => $uri));
				}
				$saplessEntr++;
				$invalidURIs++;
			}
			unset( $objData );
		}
		unset( $response );
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
		'entries_by_valid_uri' => $validURIs != 0 ? ($totalEntries / $validURIs) : 0,
		'entries_by_useful_uri' => ($validURIs - $saplessURIs) !=0 ? $sapfullEntr / ($validURIs - $saplessURIs) : 0,
		'operation_time' => ($datEnd - $datStart),
		'average_time' => $totalEntries != 0 ? (($datEnd - $datStart) / $totalEntries) : 0,
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
		__( "02. Invalid URIs.................:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($invalidURIs, 0) . " (" . round(($numberofrecords == 0 ? 0 : ($invalidURIs / $numberofrecords)) * 100, 0) . "% d/01)",
		__( "03. Valid URIs...................:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($validURIs, 0) . " (" . round(($numberofrecords == 0 ? 0 : ($validURIs / $numberofrecords)) * 100, 0) . "% d/01)",
		__( "04. Sapless URIs.................:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($saplessURIs, 0) . " (" . round(($validURIs == 0 ? 0 : ($saplessURIs / $validURIs)) * 100, 0) . "% d/03)",
		__( "05. Total entries to check.......:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($totalEntries, 0),
		__( "06. Entries by valid URI.........:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($validURIs == 0 ? 0 : $totalEntries / $validURIs, 1),
		__( "07. Sapless entries..............:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($saplessEntr, 0) . " (" . round(($totalEntries == 0 ? 0 : ($saplessEntr / $totalEntries)) * 100, 0) . "% d/05)",
		__( "07. Sappy entries................:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($sapfullEntr, 0) . " (" . round(($totalEntries == 0 ? 0 : ($sapfullEntr / $totalEntries)) * 100, 0) . "% d/05)",
		"----------------------------------" => "",
		__( "08. ADDED NEW ENTRIES............:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($addedentries, 0),
		__( "09. DISCARDED NEW ENTRIES........:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n($discardedentries, 0),
		"----------------------------------" => "",
		__( "10. Entries by useful URI........:", CSL_TEXT_DOMAIN_PREFIX ) => (($validURIs - $saplessURIs) !=0 ? number_format_i18n(($sapfullEntr / ($validURIs - $saplessURIs) == 0 ? 0 : $sapfullEntr / ($validURIs - $saplessURIs)), 1) : 'N/A'),
		__( "11. Operation time...............:", CSL_TEXT_DOMAIN_PREFIX ) => ($datEnd - $datStart) . " s",
		__( "12. Average time for single test.:", CSL_TEXT_DOMAIN_PREFIX ) => number_format_i18n(($totalEntries == 0 ? 0 : ($datEnd - $datStart) / $totalEntries), 5) . " s",
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

	@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', CSL_MAX_MEMOTY_LIMIT ) );
	$qlimit = $limit ? ' LIMIT ' . $limit : '';
	$qorderby = $orderby ? ' ORDER BY ' . $orderby : '';
	$qorder = $order ? ' ' . $order : '';
	//*** TMP PARA LIMITAR : AND p.ID = 29136
	
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
	/*
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
            m.meta_key IN ('" . CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . "rss_uri')
        	AND
        	p.post_status = 'publish'
        	AND
        	p.post_type = '" . CSL_CUSTOM_POST_ENTITY_TYPE_NAME . "'
        $qorderby $qorder
        $qlimit
    ",
    ARRAY_A);
    */
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
							$pattern = '/article|body|content|entry|hentry|main|page|attachment|pagination|post|text|contdcha|blog|elementolistado|story/i';
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

						// READABILITY TESDTING
						//$Readability     = new Readability($html); // default charset is utf-8
						//$ReadabilityData = $Readability->getContent(); // throws an exception when no suitable content is found

						// You can see more params by var_dump($ReadabilityData);
						//echo "TITLE = ".$ReadabilityData['title']." ***";
						//echo "CONTENT = " . $ReadabilityData['content'];
						//PRINT_R($ReadabilityData);
						//csl_output_buffer_flush();
						// end of READABILITY TESDTING

						// Get content
						/*
						$aTMP = array();
						$nodes = csl_extract_tags( $html, $texttag, false );
						foreach($nodes as $node){
							$sTMP = trim( ( isset($node['attributes'][$attrtag]) ? $node['attributes'][$attrtag] : '' ) );
	                        if( $sTMP != '' ) {
								if( false !== preg_match( $pattern, $sTMP ) )  {
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
                        */

						$Readability				= new Readability($html, mb_detect_encoding($html));		// default charset is utf-8
						$ReadabilityData			= $Readability->getContent();	// throws an exception when no suitable content is found
						$aOutput['content']			= sanitize_text_field($ReadabilityData->content); //strip_tags(html_entity_decode($ReadabilityData->content), array());
						$aOutput['contentSnippet']	= wp_trim_words( $aOutput['content'], 55 );

						$aOutput['link'] = $url;
						if( isset( $meta['author'] ) )
							$aOutput['author'] = $meta['author'];
						if( isset( $meta['date'] ) )
							$aOutput['publishedDate'] = $meta['date'];
							
						return json_encode(
							array(
								'responseData' => array (
									'feed' => array (
										'entries' => array(
											$aOutput
										)
									)
								)
							), JSON_FORCE_OBJECT |JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_NUMERIC_CHECK
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

/*
function csl_cron_fetch_googleapis_feed($url) {
////
	$response = wp_remote_get( 'https://ajax.googleapis.com/ajax/services/feed/load?v=2.0&output=json&userip=' . $_SERVER["SERVER_ADDR"] . '&q=' . $url);
	if ( !is_wp_error( $response ) ) {
		if( $response['body'] ) {
////
			$resp = csl_new_rss_capture( $url );
			var_dump($resp);
            if( !$resp ) return false;
            if( count( $resp ) > 0 ) {
				return $resp;
			} else {
				$rhtml = wp_remote_get($url);
				if ( !is_wp_error( $rhtml ) ) {
					if( $rhtml['body'] &&  strlen( $rhtml['body'] <= 600000 ) ) {
						$html = html_entity_decode( $rhtml['body'], ENT_COMPAT, 'utf-8' );
						$meta = csl_get_meta_tags( $html );
						if( false === strpos( $url, 'https://www.facebook.com' ) ) {
							$pattern = '/article|body|content|entry|hentry|main|page|attachment|pagination|post|text|contDcha|blog|elementoListado|story/i';
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
////
		} else {
			return false;
		}
	} else {
		return false;
	}
////
}
*/

function csl_new_rss_capture( $url ) {

	include_once( ABSPATH . WPINC . '/feed.php' );
	$rss = fetch_feed( $url );
	$maxitems = 0;

	if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly
		$maxitems = $rss->get_item_quantity( 5 );
	$rss_items = $rss->get_items( 0, $maxitems );
	else :
		return false;
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


/** NUEVO PORT DE READABILITY **/
class Readability {
	/**
	 * What character set the site should be parsed as.
	 */
	const CHARSET = 'utf-8';

	/**
	 * What attribute should we give each element
	 */
	const SCORE_ATTR = 'contentScore';

	/**
	 * Keep the source of our HTML document in a variable
	 * to modify later on.
	 */
	protected $source = '';

	/**
	 * Keep a DOM tree of our document to grab the content
	 * from and parse.
	 */
	protected $DOM = null;

	/**
	 * Keep a log of all the nodes that matched our filter.
	 */
	private $parentNodes = [];

	/**
	 * See if we can find and store a lead image.
	 */
	private $image = null;

	/**
	 * Tags to strip from our document
	 */
	private $junkTags = [
	//  External scripts
	'style', 'iframe', 'script', 'noscript', 'object',
	'applet', 'frame', 'embed', 'frameset', 'link',

	//  Ridiculously out-of-date tags
	'basefont', 'bgsound', 'blink', 'keygen', 'command',
	'menu', 'marquee',

	//  Form objects
	'form', 'button', 'input', 'textarea', 'select',
	'label', 'option',

	//  New HTML5 tags
	//  via ridcully/php-readability
	'canvas', 'datalist', 'nav', 'command',

	//  Other injected scripts
	'id="disqus_thread"', 'href="http://disqus.com"'
	];

	/**
	 * Attributes to remove from any tags we have, as they could
	 * pose a security risk/look shonky.
	 */
	private $junkAttrs = [
	'style', 'class', 'onclick', 'onmouseover',
	'align', 'border', 'margin'
	];

	/**
	 * Set up the source, load a DOM document to parse.
	 */
	public function __construct($source, $charset = 'utf-8') {
		if (!is_string($charset)) {
			$charset = self::CHARSET;
		}

		//  Store our source for later on
		$this->source = $source;

		//  Decode to UTF-8 (or whatever encoding you pick)
		//$source = mb_convert_encoding($source, 'HTML-ENTITIES', $charset);

		//  Remove some of the weird HTML before parsing as DOM
		$source = $this->prepare($source);

		//  Create our DOM
		$this->DOM = new \DOMDocument('1.0', $charset);

		try {
			//  If it doesn't parse as valid XML, it's not valid HTML.
			if (!@$this->DOM->loadHTML('<?xml encoding="' . self::CHARSET . '">' . $source)) {
				throw new Exception('Content is not valid HTML!');
			}

			foreach ($this->DOM->childNodes as $item) {
				//  If it's a ProcessingInstruction node
				//  (i.e, an inline PHP/ASP script)
				//  remove it. We don't want no virus shit.
				if ($item->nodeType == XML_PI_NODE) {
					$this->DOM->removeChild($item);
				}
			}

			//  Force UTF-8
			$this->DOM->encoding = self::CHARSET;
		} catch (Exception $e) {
		}
	}

	/**
	 * Statically call our Readability class and parse
	 *
	 * @return string
	 */
	public static function parse($url, $isContent = false) {
		if ($isContent === false) {
			$url = file_get_contents($url);
		}

		$class = new self($url, false);
		return $class->getContent();
	}

	/**
	 * See if we can grab the title from the document
	 *
	 * @return String
	 */
	public function getTitle($delimiter = ' - ') {
		$titleNode = $this->DOM->getElementsByTagName('title');

		if ($titleNode->length and $title = $titleNode->item(0)) {
			// stackoverflow.com/questions/717328/how-to-explode-string-right-to-left
			$title = trim($title->nodeValue);
			$result = array_map('strrev', explode($delimiter, strrev($title)));

			//  If there was a dash, return the bit before it
			//  If not, just return the whole thing
			$title = sizeof($result) > 1 ? array_pop($result) : $title;

			//  Split any other delimiters we might have missed
			$title = preg_replace('/[\-—–.•] */', '', $title);

			//  Strip out any dodgy characters
			return utf8_encode($title);
		}

		return null;
	}

	/**
	 * Find lead image (if possible)
	 *
	 * @return string
	 */
	public function getImage($node = false) {
		if ($node === false and $this->image) {
			return $this->image;
		}

		//  Grab all the images in our article
		$images = $node->getElementsByTagName('img');

		//  If we have some images
		//  and the first one is a valid element
		if ($images->length and $lead = $images->item(0)) {
			//  Return the image's URL
			return $lead->getAttribute('src');
		}

		return null;
	}

	/**
	 * Grab and process our content, get the main image,
	 * and return everything as an array for easy access.
	 *
	 * @return array
	 */
	public function getContent() {
		if (!$this->DOM) {
			return false;
		}

		//  We need to grab our content beforehand,
		//  so let's do that here.
		$content = $this->processContent();

		//  Bring everything together
		return (object) [
		'lead_image' => $this->image,
		'word_count' => mb_strlen(strip_tags($content), self::CHARSET),
		'title' => $this->getTitle(),
		'content' => $content
		];
	}

	/**
	 * Strip unwanted tags from a DOM node.
	 *
	 * @return DOMDocument
	 */
	private function removeJunkTag($node, $tag) {
		while ($item = $node->getElementsByTagName($tag)->item(0)) {
			$parent = $item->parentNode;
			$parent->removeChild($item);
		}

		return $node;
	}

	/**
	 * Remove any unwanted attributes from our DOM nodes.
	 *
	 * @return DOMDocument
	 */
	private function removeJunkAttr($node, $attr) {
		$tags = $node->getElementsByTagName('*');
		$i = 0;

		while ($tag = $tags->item($i++)) {
			$tag->removeAttribute($attr);
		}

		return $node;
	}

	/**
	 * Assign a score to our attribute.
	 *
	 * @return int
	 */
	private function score($attr) {
		$base = 25;
		$content = 'content|text|body|post';

		//  If we match anything that's definitely not content, kill the score
		if (preg_match("/(comment|meta|footer|footnote|sidebar|blogroll)/i", $attr)) {
			return -($base * 2);
		}

		$candidateRegex =
			"/((^|\\s)(post|hentry|entry[-]?(" . $content . ")?|article[-]?(" . $content . ")?)(\\s|$))/i";

		//  If we match anything that's likely to be an article or post, let's bump it up.
		if (preg_match($candidateRegex, $attr)) {
			return $base;
		}

		//  No matches, just leave the score as-is.
		return 1;
	}

	/**
	 * Find the lead paragraph
	 * Algorithm from: http://code.google.com/p/arc90labs-readability/
	 *
	 * @return DOMNode
	 */
	private function getTopBox() {
		//  Get all paragraphs
		$paragraphs = $this->DOM->getElementsByTagName('p');

		//  Loop our paragraphs
		$i = 0;
		while ($paragraph = $paragraphs->item($i++)) {
			$parent = $paragraph->parentNode;
			$score = intval($parent->getAttribute(self::SCORE_ATTR));

			//  Don't just check the text, we're going to examine the class and ID
			//  attributes as well
			$class = $parent->getAttribute('class');
			$id = $parent->getAttribute('id');

			//  Get scores for our attributes
			$score += $this->score($class);
			$score += $this->score($id);

			//  Add a point for every paragraph inside our element
			//  It's more likely that a big block of text is going to be the focal point.
			if (strlen($paragraph->nodeValue) > 10) {
				$score += strlen($paragraph->nodeValue);
			}

			//  Set a content score to each node
			$parent->setAttribute(self::SCORE_ATTR, $score);

			//  Add our element back to its parent
			array_push($this->parentNodes, $parent);
		}

		//  Assume we won't find a match for now
		$match = null;

		//  Find the highest-scoring element and return that
		for ($i = 0, $len = count($this->parentNodes); $i < $len; $i++) {
			$parent = $this->parentNodes[$i];
			$score = intval($parent->getAttribute(self::SCORE_ATTR));
			$orgScore = intval($match ? $match->getAttribute(self::SCORE_ATTR) : 0);

			if ($score and $score > $orgScore) {
				$match = $parent;
			}
		}

		return $match;
	}

	/**
	 * Process our page's content
	 *
	 * @return string
	 */
	private function processContent() {
		//  Get our page's main content
		$content = $this->getTopBox();

		//  If there's no decent match, we can't process
		//  the page. So just quit while we're ahead.
		if ($content === null) {
			return false;
		}

		//  Create another DOM to process everything in,
		//  one last time.
		$target = new \DOMDocument;
		$target->appendChild($target->importNode($content, true));

		//  Find an image if possible
		$this->image = $this->getImage($target);

		//  Strip the tags we don't want any more
		foreach ($this->junkTags as $tag) {
			$target = $this->removeJunkTag($target, $tag);
		}

		//  Strip any unwanted attributes as well
		foreach ($this->junkAttrs as $attr) {
			$target = $this->removeJunkAttr($target, $attr);
		}

		//  Hopefully we've got a lovely parsed document
		//  ready to give back to the user now.
		return mb_convert_encoding($target->saveHTML(), self::CHARSET, 'HTML-ENTITIES');
	}

	/**
	 * Get our text ready for converting to a DOM document
	 *
	 * @return string
	 */
	private function prepare($src) {
		//  Strip any character sets that don't match our ones
		preg_match('/charset=([\w|\-]+);?/', $src, $match);

		if (isset($match[1])) {
			$src = preg_replace('/charset=([\w|\-]+);?/', '', $src, 1);
		}

		//  Convert any double-line breaks to paragraphs
		$src = preg_replace('/<br\/?>[ \r\n\s]*<br\/?>/i', '</p><p>', $src);

		//  Remove any <font> tags
		$src = preg_replace("/<\/?font[^>]*>/i", '', $src);

		//  Strip any <script> tags
		$src = preg_replace("#<script(.*?)>(.*?)</script>#is", '', $src);

		//  Remove any extra whitespace
		return trim($src);
	}
}
?>
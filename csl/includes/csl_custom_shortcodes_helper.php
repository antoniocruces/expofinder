<?php

// Shortcodes functions

/**
 * csl_shc_expofinder function.
 * 
 * @access public
 * @param mixed $atts
 * @param mixed $content (default: null)
 * @return void
 */
function csl_shc_expofinder( $atts , $content = null ) {
    $text = file_get_contents(get_template_directory() . '/assets/docs/' . get_locale() . '/expofinder.html');
    echo $text ? $text : __('Not help file found.', CSL_TEXT_DOMAIN_PREFIX);
    csl_write_project_team_table($title = sprintf(__( '%s & %s work team', CSL_TEXT_DOMAIN_PREFIX ), CSL_PROJECT_NAME, CSL_LOGO),  $title_level = '3');            
}

/**
 * csl_shc_project_status function.
 * 
 * @access public
 * @param mixed $atts
 * @param mixed $content (default: null)
 * @return void
 */
function csl_shc_project_status( $atts , $content = null ) {
	csl_project_status_screen( $title = sprintf( __('%s project current status', CSL_TEXT_DOMAIN_PREFIX ), CSL_PROJECT_NAME ), $title_level = 3 );
    csl_draw_project_status_gantt_chart($title = sprintf(__( '%s project summarized Gantt chart', CSL_TEXT_DOMAIN_PREFIX ), CSL_PROJECT_NAME),  $title_level = '3');
    csl_recording_status( $title = sprintf(__( 'Recording status', CSL_TEXT_DOMAIN_PREFIX ), CSL_PROJECT_NAME),  $title_level = '3' );
	csl_google_maps_clustered_map( $title = __('Entities map', CSL_TEXT_DOMAIN_PREFIX ),  $title_level = '3', $type = CSL_ENTITIES_DATA_PREFIX );
	csl_google_maps_clustered_map( $title = __('Exhibitions map', CSL_TEXT_DOMAIN_PREFIX ),  $title_level = '3', $type = CSL_EXHIBITIONS_DATA_PREFIX );
}

/**
 * csl_shc_project_stats function.
 * 
 * @access public
 * @param mixed $atts
 * @param mixed $content (default: null)
 * @return void
 */
function csl_shc_project_stats( $atts , $content = null ) {
	global $csl_a_options;
	global $cls_a_custom_fields_nomenclature;
	global $cls_a_custom_taxonomies_nomenclature;

	$nomenclature = array_merge( $cls_a_custom_fields_nomenclature, $cls_a_custom_taxonomies_nomenclature );
	$current_url = get_permalink();

	$type  = isset($_REQUEST['t']) ? $_REQUEST['t'] : CSL_CUSTOM_POST_ENTITY_TYPE_NAME;
	$keyf  = isset($_REQUEST['k']) ? $_REQUEST['k'] : null;
	$limit = isset($_REQUEST['l']) ? $_REQUEST['l'] : $csl_a_options['top_records'];
	
	echo '<h3>' . __( 'Top records by record type and metainfo or taxonomy', CSL_TEXT_DOMAIN_PREFIX ) . '</h3>' . PHP_EOL;
	echo "<form id='selFilterStats' action='" . $current_url  . "' method='GET'>" . PHP_EOL;
	echo "<table class='widefat'>" . PHP_EOL;
	
	$aTBL = csl_get_project_stats_unique_fields( $field_name = 'post_type' );
	foreach($aTBL as &$val) {
		$val['translated_post_type'] = get_post_type_object( $val['post_type'] )->labels->name;
	}
	$aTBL = csl_sort_multiarray( $aTBL, 'translated_post_type' );
	echo "<tr>" . PHP_EOL;
	echo "<td><label for='t' style='margin-right: 10px;'>" .  __( 'Record type', CSL_TEXT_DOMAIN_PREFIX ) . '</label></td>' . PHP_EOL;
	echo "<td><select id='t' name='t'>" . PHP_EOL;
	foreach( $aTBL as $value ) {
		$selected = $type && $type == $value['post_type'] ? " selected='selected'" : "";
		echo "<option value='" . $value['post_type'] . "'$selected>" . $value['translated_post_type'] . '</option>' . PHP_EOL;		
	}
	echo "</select></td>" . PHP_EOL;	
	echo "</tr>" . PHP_EOL;

	$aTBL = csl_get_project_stats_unique_fields( $field_name = 'info_fieldname' );
	foreach($aTBL as &$val) {
		$val['translated_info_fieldname'] = $nomenclature[$val['info_fieldname']];
	}
	$aTBL = csl_sort_multiarray( $aTBL, 'translated_info_fieldname' );
	echo "<tr>" . PHP_EOL;
	echo "<td><label for='k' style='margin-right: 10px;'>" .  __( 'Info type', CSL_TEXT_DOMAIN_PREFIX ) . '</label></td>' . PHP_EOL;
	echo "<td><select id='k' name='k'>" . PHP_EOL;
	echo "<option value=''>" . __( 'Anyone', CSL_TEXT_DOMAIN_PREFIX ) . '</option>' . PHP_EOL;
	foreach( $aTBL  as $key => $value ) {
		$selected = ($keyf && $keyf == $value['info_fieldname']) ? " selected='selected'" : "";
		echo "<option value='" . $value['info_fieldname'] . "'$selected%>" . $value['translated_info_fieldname'] . '</option>' . PHP_EOL;			
	}
	echo "</select></td>" . PHP_EOL;	
	echo "</tr>" . PHP_EOL;

	echo "<tr>" . PHP_EOL;
	echo "<td><label for='l' style='margin-right: 10px;'>" .  __( 'Get the top', CSL_TEXT_DOMAIN_PREFIX ) . '</label></td>' . PHP_EOL;
	echo "<td><select id='l' name='l'>" . PHP_EOL;
	foreach (array_merge( range(0, 100, 10), range(200, 1000, 100) ) as $nlimit) {
		$selected = ($limit && $limit == $nlimit) ? " selected='selected'" : "";
		echo "<option value='" . $nlimit . "'$selected>" . number_format_i18n( $nlimit, 0 ) . '</option>' . PHP_EOL;		
	}
	echo "</select>&nbsp;" . __( 'values with more records', CSL_TEXT_DOMAIN_PREFIX ) . "</td>" . PHP_EOL;	
	echo "</tr>" . PHP_EOL;

	echo "<tr>" . PHP_EOL;
	echo "<td>&nbsp;</td>" . PHP_EOL;
	echo "<td><a id='searchsubmit' class='alignright' onClick=\"document.getElementById('selFilterStats').submit(); return false;\">" . __( 'Get results', CSL_TEXT_DOMAIN_PREFIX ) . "</a></td>" . PHP_EOL;
	echo "</tr>" . PHP_EOL;

	echo "</table>" . PHP_EOL;
	echo "</form>" . PHP_EOL;

	$data = csl_get_project_stats( $type, $keyf, $limit );
	$aTBL = array();
	foreach($data as $dat) {
		$aTMP = array();
		$aTMP []= get_post_type_object( $dat['post_type'] )->labels->singular_name;
		$aTMP []= $nomenclature[$dat['info_fieldname']];
		$aTMP []= $dat['info_value'];
		$aTMP []= number_format_i18n( (int) $dat['n_posts'], 0 );
		$aTBL []= $aTMP;
	}
	echo csl_build_table(
		'statsTable', 
		NULL, 
		$aTBL, 
		sprintf( 
			__( 'Top %s %s records by %s', CSL_TEXT_DOMAIN_PREFIX ),
			number_format_i18n( $limit, 0 ),
			strtolower( get_post_type_object( $type )->labels->name ),
			$keyf . '' !== '' ? strtolower( $nomenclature[$keyf] ) : strtolower( __( 'Anyone', CSL_TEXT_DOMAIN_PREFIX ) )
		)
	);	
}

function csl_shc_posts_pivot( $atts , $content = null ) {
	echo '<h3>' . __( 'Cross-reference data table', CSL_TEXT_DOMAIN_PREFIX ) . '</h3>' . PHP_EOL;
	
	echo '<p>'. PHP_EOL;
	echo __( 'In statistics, a contingency table (also referred to as cross tabulation or crosstab) is a type of table in a matrix format that displays the frequency distribution of the variables. They are heavily used in survey research, business intelligence, engineering and scientific research. They provide a basic picture of the interrelation between two variables and can help find interactions between them.', CSL_TEXT_DOMAIN_PREFIX ) . PHP_EOL;
	echo '</p>'. PHP_EOL;
	
	echo '<p>'. PHP_EOL;
	echo sprintf(
		__( 'Below is shown a toolkit to create a crosstab using %s project stored references. Moving the field buttons and selecting the appropriate operations you can get a quick overview of the essential relationships between different elements. For operational reasons, only the top %s values with more than one records are displayed.', CSL_TEXT_DOMAIN_PREFIX ),
		CSL_PROJECT_NAME,
		number_format_i18n( 40000, 0)
		) . PHP_EOL;
	echo '</p>'. PHP_EOL;

	echo '
        <script type="text/javascript">
            var pData   = "' . admin_url ( 'admin-ajax.php' ) . '?action=csl_generic_ajax_call&q=fepivot&s=' . wp_create_nonce( NONCE_KEY ) . '";
            var pScript = "' . get_template_directory_uri() . '/assets/js/csl-fe-pivot.' . get_locale() . '.js' . '";
			jQuery(document).ready(function($) {
				if (typeof pData !== "undefined" && typeof pScript !== "undefined" ) {
					$.getScript( pScript )
						.done(function( script, textStatus ) {
							console.log( textStatus );
					        $.getJSON(pData, function(mps) {
					            $("#pivotOutput").pivotUI(mps, {
						            unusedAttrsVertical: false
					            }, false, "' . strtolower( substr( get_locale(), 0, 2) ) . '");
					        });
						})
				}
				
			});
        </script>
        <div id="pivotSelection" style="width: 100%;"></div> 
        <div id="pivotOutput" style="width: 100%;"><i class="fa fa-refresh fa-spin" style="margin-right: 15px;"></i>' . 
        __( 'Loading data&hellip;', CSL_TEXT_DOMAIN_PREFIX ) . 
        '</div>
	';
}

/**
 * csl_shc_custom_queries function.
 * 
 * @access public
 * @param mixed $atts
 * @param mixed $content (default: null)
 * @return void
 */
function csl_shc_custom_queries( $atts , $content = null ) {
    global $wpdb;

    $secnonce = wp_create_nonce( NONCE_KEY );
    
    if(isset($_REQUEST['cq']) && $_REQUEST['cq'] == '') return false;
    $seltype = isset($_REQUEST['ct']) ? $_REQUEST['ct'] : NULL;
    
    $counter = 0;
    $aList = array();
    $args = array(
    	'posts_per_page'   => -1,
    	'post_type'        => 'csl_acpt_query',
    	'post_status'      => 'publish',
    	'orderby'          => 'post_title',
    	'order'            => 'ASC',
    );
    if( $seltype ) {
        $args ['tax_query']= array(
    		array(
    			'taxonomy' => 'csl_acpt_query_type',
    			'field'    => 'slug',
    			'terms'    => $seltype,
    		),
        );    
    }
    $posts_array = get_posts( $args );
    $exists_query = false;
    if( isset( $_REQUEST['cq'] ) ) {
        foreach( $posts_array as $pa ) {
            if(  $_REQUEST['cq'] == $pa->ID ) {
                $exists_query = true;
                break;    
            }
        }
    }
    $countposts = 0;
    foreach($posts_array as $pa) {
        if($countposts == 0) {
            $_REQUEST['cq'] = !isset($_REQUEST['cq']) || !$exists_query ? $pa->ID : $_REQUEST['cq'];  
        }
        $selection = isset($_REQUEST['cq']) ? $_REQUEST['cq'] : '';
        if($counter == 0) {
            $param = isset($_REQUEST['cq']) ? $_REQUEST['cq'] : $pa->ID;
        }
        $aList []= '<option value="' . $pa->ID . '"' . ($selection == $pa->ID ? ' selected' : '') . '>' . $pa->post_title . '</option>';
        $counter++;  
        $countposts++;
    }
     
    $query   = wp_kses(get_post($param)->post_content, array());
    $table_title = get_post($param)->post_title;
    $table_description = get_post($param)->post_excerpt;
    $columns = get_post_meta($param, 'csl_acpt_column_labels', true);
    $column_ids = get_post_meta($param, 'csl_acpt_column_ids', true);
    $chart_des = get_post_meta($param, 'csl_acpt_column_chart_descriptions', true);
    $chart_val = get_post_meta($param, 'csl_acpt_column_chart_values', true);

	$aCols = explode(',', $columns);
	$aCIDs = explode(',', $column_ids);
	$aCCls = array();
	for( $i = 0; $i < count( $aCols ); $i++ ) {
		$aCCls[$aCIDs[$i]] = $aCols[$i];
	}
	$sCols  = '';
	foreach( $aCCls as $key => $value ) {
		$sCols .= '<th id="' . $key . '">' . $value . '</th>';	
	}

    $qry_types = get_terms( 'csl_acpt_query_type', 'orderby=name' );
    $aQT = array();
    $aQT []= '<option value=""></option>';
    foreach( $qry_types as $qt ) {
        $aQT []= '<option value="' . $qt->slug . '"' . ( $qt->slug == $seltype ? ' selected' : '' ) . '>' . $qt->name . '</option>';
    }    
    
    $content .= 
        '<p>' . 
        sprintf( 
            _n( 'There is %s available query.', 'There are %s available queries.', count( $aList ), CSL_TEXT_DOMAIN_PREFIX ), 
            '<strong>' . number_format_i18n( count( $aList ), 0 ) . '</strong>'
            ) . 
        '</p>' . PHP_EOL;
    echo $content;

    echo '<script type="text/javascript">' . PHP_EOL;
    echo "\t" . 'var isCH = true;' . PHP_EOL;
    echo "\t" . 'var pivU = "' . admin_url('/admin-ajax.php' ) . '?action=csl_generic_ajax_call&q=pivotT&x=' . $param . '&s=' . $secnonce . '";' . PHP_EOL;
    echo "\t" . 'var datU = "' . admin_url('/admin-ajax.php' ) . '?action=csl_generic_ajax_call&q=dataT&x=' . $param . '&s=' . $secnonce . '";' . PHP_EOL;
    echo "\t" . 'var chaU = "' . admin_url('/admin-ajax.php' ) . '?action=csl_generic_ajax_call&q=chartT&x=' . $param . '&s=' . $secnonce . '";' . PHP_EOL;
    echo "\t" . 'var pivL = "' . strtolower( explode( "_", get_locale() )[0] ) . '";' . PHP_EOL;
    echo '</script>' . PHP_EOL;

    echo '<form class="form-inline well well-sm margin-top-25" role="form">' . PHP_EOL;
    echo '<div class="form-group">';
    echo '<label class="sr-only" for="query_select">' . __( 'Queries', CSL_TEXT_DOMAIN_PREFIX ) . '</label>' . PHP_EOL;
    echo '<select id="query_select" name="cq" class="form-control">' . PHP_EOL;
    echo implode(PHP_EOL, $aList);
    echo '</select>' . PHP_EOL;
    echo '<select id="query_type" name="ct" class="form-control">' . PHP_EOL;
    echo implode(PHP_EOL, $aQT);
    echo '</select>' . PHP_EOL;
    echo '</div>' . PHP_EOL;
    echo '<button type="submit" class="btn">' . __( 'Run', CSL_TEXT_DOMAIN_PREFIX ) . '</button>' . PHP_EOL;
    echo '</form>' . PHP_EOL;

	echo '<div class="row">' . PHP_EOL;
	echo '<div class="col-md-12">' . PHP_EOL;
	echo '<h3>' . $table_title . '</h3>' . PHP_EOL;
	echo '<p class="text-muted">' . $table_description . '</p>' . PHP_EOL;
	echo '</div>' . PHP_EOL;
	echo '</div>' . PHP_EOL;

    echo '<ul class="nav nav-tabs" role="tablist">' . PHP_EOL;
    echo '<li role="presentation" class="active"><a href="#table" aria-controls="table" role="tab" data-toggle="tab">' . 
    	__( 'Data table', CSL_TEXT_DOMAIN_PREFIX ) . '</a></li>' . PHP_EOL;
    echo '<li role="presentation"><a href="#pivot" aria-controls="pivot" role="tab" data-toggle="tab">' . 
    	__( 'Pivot table', CSL_TEXT_DOMAIN_PREFIX ) . '</a></li>' . PHP_EOL;
    if( "" !== $chart_des ) {
        echo '<li role="presentation"><a href="#chart" aria-controls="chart" role="tab" data-toggle="tab">' . 
        	__( 'Chart', CSL_TEXT_DOMAIN_PREFIX ) . '</a></li>' . PHP_EOL;
    }
    echo '</ul>' . PHP_EOL;

    echo '<div class="tab-content">' . PHP_EOL;
    
    echo '<div role="tabpanel" class="tab-pane active margin-top-25" id="table">' . PHP_EOL;
    
    echo '
        <table id="cqry" class="table table-striped table-bordered" cellspacing="0" width="100%" title="' . $table_title . '">
        <thead>
            <tr>
            ' . $sCols . '
            </tr>
        </thead>
        <tfoot>
            <tr>
            ' . $sCols . '
            </tr>
        </tfoot>
    </table>
    ' . PHP_EOL;
    
    echo '</div>' . PHP_EOL;

    echo '<div role="tabpanel" class="tab-pane margin-top-25" id="pivot">' . PHP_EOL;
    echo '<div id="pivotTable"></div>' . PHP_EOL;
    echo '</div>' . PHP_EOL;

    if( "" !== $chart_des ) {
        echo '<div role="tabpanel" class="tab-pane margin-top-25" id="chart">' . PHP_EOL;
        echo '<div id="chart"></div>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
    }
    echo '</div>' . PHP_EOL;
}

/**
 * csl_shc_base_stats function.
 * 
 * @access public
 * @param mixed $atts
 * @param mixed $content (default: null)
 * @return void
 */
function csl_shc_base_stats( $atts , $content = null ) {
	global $cls_a_custom_fields_nomenclature;
	 
    $aAttr = shortcode_atts( array(
        'type' => implode( ',', CSL_CUSTOM_POST_TYPE_ARRAY ),
        'toprecs' => 30,
        'toprels' => 5,
    ), $atts );
    $aData  = csl_get_basic_stats_count();
    $aRels  = csl_get_posts_relations( $aAttr['toprels'] );
    $aTwns  = csl_get_exhibition_towns( $aAttr['toprecs'] );
    $sTLHe  = '';
    $sTLPu  = '';
    $sTLTa  = '';
    $sTGAm  = '';
    $sTRel  = '';
    $sTTwn  = '';
    foreach( $aData['posts'] as $data ) {
        $sTLHe .= '<th abbr="' . $data['s_label'] . '">' . $data['s_label'] . '</th>' . PHP_EOL;    
        $sTLPu .= '<td>' . $data['n_publish'] . '</td>';    
        $sTLTa .= '<td>' . ( $data['n_target'] - $data['n_publish'] ) . '</td>';    
    }
    foreach( $aData['gender'] as $data ) {
        $sTGAm .= '<td>' . $data['n_records'] . '</td>';    
    }
    foreach( $aRels as $data ) {
        $sTRel .= '[';
        $sTRel .= '"' . get_post_type_object( $data['s_post_type'] )->labels->name . '...", '; 
        $sTRel .= '"' . $cls_a_custom_fields_nomenclature[$data['meta_key']] . '", ';
        $sTRel .= $data['n_rel_count'];
        $sTRel .= '],' . PHP_EOL;
        
        $sTRel .= '[';
        $sTRel .= '"' . $cls_a_custom_fields_nomenclature[$data['meta_key']] . '", ';    
        $sTRel .= '"...' . get_post_type_object( $data['s_ref_type'] )->labels->name . '", ';
        $sTRel .= $data['n_rel_count'];
        $sTRel .= '],' . PHP_EOL;
    }
    foreach( $aTwns as $data ) {
        $sTTwn .= '[';
        $sTTwn .= '"' . $data['s_town'] . '", '; 
        $sTTwn .= $data['n_exhibitions'];
        $sTTwn .= '],' . PHP_EOL;
    }    
    $sPPag  = esc_url(get_permalink( get_option( 'page_for_posts' ) ) ) . '/?type=';
    $aTyps  = explode( ',', $aAttr['type'] );
    $sOutp  = '';
	$sOutp .= '
    	<style type="text/css">
    		.gender-map .men {
    		  position: absolute;
    		  background: url("' . get_template_directory_uri() . '/assets/img/symbols/man.png") 0 0;
    		}
    		.gender-map .women {
    		  position: absolute;
    		  background: url("' . get_template_directory_uri() . '/assets/img/symbols/woman.png") 0 0;
    		}
    		.gender-map {
    		  margin: 0 0 20px 0;
    		}
    	</style>
        <div class="container text-center">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <h2 style="font-size: 60px;line-height: 60px;margin-bottom: 20px;font-weight: 900;"><span style="color: #2d99e5;">' . CSL_TEXT_ICON . '</span> ' . CSL_FIRST_LOGO_PART . 
                    '<span style="color: #2d99e5;">' . CSL_SECOND_LOGO_PART . '</span> ' . CSL_VERSION . '</h2>
                    <p class="lead">' . CSL_DESCRIPTION . ' ' . CSL_ORGANIZATION . '</p>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12">
                ' . $content . '
            </div>
        </div>
        <div id="canvas1" class="row">
    		<div class="col-sm-6 col-md-6 col-lg-6" id="gr1">
                <h2>' . __( 'Project completion degree', CSL_TEXT_DOMAIN_PREFIX ) . '</h2>
                <div id="completionch">
						<table class="table table-responsive table-striped table-hover table-condensed bar-chart-2">
						<caption>' . __( 'Published vs Target Records', CSL_TEXT_DOMAIN_PREFIX ) . '</caption>
						<thead>
							<tr>
								<td></td>
								' . $sTLHe . '
							</tr>
						</thead>
						<tbody>
							<tr><th>' . __( 'Publish', CSL_TEXT_DOMAIN_PREFIX ) . '</th>' . $sTLPu . '</tr>
							<tr><th>' . __( 'Target', CSL_TEXT_DOMAIN_PREFIX ) . '</th>' . $sTLTa . '</tr>
						</tbody>
					</table>
				</div>
            </div>
    		<div class="col-sm-6 col-md-6 col-lg-6" id="gr2">
                <h2>' . __( 'Recorded people by Gender', CSL_TEXT_DOMAIN_PREFIX ) . '</h2>
				<div id="genderch">
					<table class="table table-responsive table-striped table-hover table-condensed gender-chart">
						<caption>Gender distribution</caption>
						<thead>
							<tr><td></td><th abbr="woman">' . __( 'Woman', CSL_TEXT_DOMAIN_PREFIX ) . '</th><th abbr="men">' . __( 'Men', CSL_TEXT_DOMAIN_PREFIX ) . '</th></tr>
						</thead>
						<tbody>
							<tr><th>' . __( 'Amount', CSL_TEXT_DOMAIN_PREFIX ) . '</th>' . $sTGAm . '</tr>
						</tbody>
					</table>
				</div>
            </div>
        </div>
        <div id="canvas2" class="row">
    		<div class="col-sm-6 col-md-6 col-lg-6" id="gr3">
                <h2>' . sprintf( __( 'Top %s strong relations', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n( (int)$aAttr['toprels'], 0 ) ) . '</h2>
    			<div id="sankeych" style="width: 100%; height: 400px;"></div>
            </div>
    		<div class="col-sm-6 col-md-6 col-lg-6" id="gr3">
                <h2>' . sprintf( __( 'Top %s more active towns', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n( $aAttr['toprecs'], 0 ) ) . '</h2>
    			<div id="geoch" style="width: 100%; height: 400px;"></div>
            </div>
        </div>

	    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    	<script>
        jQuery(document).ready(function($) {
            $(".bar-chart-2").chartify("bar", {
                legendPosition: "b", 
                isDistribution: true, 
                isStacked: true, 
                showLabels: true, 
                unit: "%",
                colors: ["a7a7a7","d7d7d7"],
                barWidth: 30,
                barSpacing: 5,
            });
            $(".gender-chart").chartify("gender", {
	            numCols : 35,
	            numRows: 3,
	        	colors: ["00ccff","ff66ff"],	    
            });
            
			google.charts.load("current", {"packages":["sankey","geochart"]});
			google.charts.setOnLoadCallback(drawCharts);
			
			function drawCharts() {
				var datak = new google.visualization.DataTable();
			    datak.addColumn("string", "De");
			    datak.addColumn("string", "A");
			    datak.addColumn("number", "Relaciones");
				datak.addRows([
					' . $sTRel . '
				]);
				var datag = google.visualization.arrayToDataTable([
					["' . __( 'City', CSL_TEXT_DOMAIN_PREFIX ) . '",   "' . __( 'Exhibitions', CSL_TEXT_DOMAIN_PREFIX ) . '"],
					' . $sTTwn . '
				]);
				
				// Sets sankey chart options.
				var optionsk = {
					width: "100%",
					/*
					sankey: {
						node: {
							colors: ["#337ab7", "#5cb85c", "#5cb85c", "#f0ad4e", "#c9302c"]
						},
						link: {
							colorMode: "gradient",
							colors: ["#337ab7", "#5cb85c", "#5cb85c", "#f0ad4e", "#c9302c"]
						}
					}
					*/
					legend : { 
						position: "bottom"
					}
				};
				var optionsg = {
					region: "ES",
					displayMode: "markers",
					colorAxis: {colors: ["green", "blue"]}
				};
				
				// Instantiates and draws our chart, passing in some options.
				var chartg = new google.visualization.GeoChart(document.getElementById("geoch"));
				var chartk = new google.visualization.Sankey(document.getElementById("sankeych"));
				chartk.draw(datak, optionsk);
				chartg.draw(datag, optionsg);
			}
            
            
        });
    	</script>
	' .PHP_EOL;
	$sOutp .= '<hr />' . PHP_EOL;
    $sOutp .= '<div class="row">' . PHP_EOL;
    foreach( $aTyps as $type ) {
        foreach( $aData['posts'] as $data ) {
            if( $type == $data['s_type'] ) {
                $sOutp .= '
                    <div class="col-sm-12 col-md-12">
                ' . PHP_EOL;
                $sOutp .= '
                        <div class="alert-message alert-message-' . $data['s_label_class'] . '">
                            <a  href="' . $sPPag . $data['s_type'] . '" class="label label-' . $data['s_label_class'] . ' pull-right" style="font-size: larger;">' . $data['s_label_acronym'] . '</a>    
                            <h3>
                            <span class="dashicons ' . get_post_type_object( $data['s_type'] )->menu_icon. '"></span> ' . 
                                 $data['s_label'] . '
                            </h3>
                ';
                $sOutp .= '
                            <p>
                                ' . sprintf( __( 'Published: <strong>%s</strong>', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n( $data['n_publish'], 0 ) ) . '.
                                ' . sprintf( __( 'Draft: <strong>%s</strong>', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n( $data['n_draft'], 0 ) ) . '.
                                ' . sprintf( __( 'Target: <strong>%s</strong>', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n( $data['n_target'], 0 ) ) . '.
                                <small>' . sprintf( __( 'Completion: <strong>%s</strong>', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n( $data['n_t_level'] * 100, 1 ) ) . '%</small>.
                                <small>' . sprintf( __( 'Draft percent: <strong>%s</strong>', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n( $data['n_d_level'] * 100, 1 ) ) . '%</small>.                                  
                            </p>
                ';
                $aTaxs = array();
                foreach( get_object_taxonomies( $data['s_type'], 'objects' ) as $tax ) {
                    $aTaxs []= $tax->name;
                }
                $aCArgs = array(
                	'smallest'                  => 8, 
                	'largest'                   => 14,
                	'unit'                      => 'pt', 
                	'orderby'                   => 'count',
                    'order'                     => 'DESC',  
                	'number'                    => 20,  
                	'format'                    => 'flat',
                	'separator'                 => "\n",
                	'taxonomy'                  => $aTaxs, 
                	'echo'                      => false,
                );
                //$sOutp .= '<h4>' . $tax->labels->name . '</h4>' . PHP_EOL;
                $sOutp .= wp_tag_cloud( $aCArgs );

                $sOutp .= '
                        </div>
                ' . PHP_EOL;
                $sOutp .= '
                    </div>
                ' . PHP_EOL;
            }
        }
    }
    foreach( $aData['fields'] as $data ) {
        $sOutp .= '
            <div class="col-sm-6 col-md-6">
        ' . PHP_EOL;
        $sOutp .= '
                <div class="alert-message alert-message-notice">
                    <h3>' .    
                        $data['s_label'] . '
                    </h3>
        ';
        $sOutp .= '
                    <p>
                        ' . sprintf( __( 'URIs: <strong>%s</strong>', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n( $data['n_uris'], 0 ) ) . '.
                        ' . sprintf( __( 'Target: <strong>%s</strong>', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n( $data['n_target'], 0 ) ) . '.
                        <small>' . sprintf( __( 'Completion: <strong>%s</strong>', CSL_TEXT_DOMAIN_PREFIX ), number_format_i18n( $data['n_level'] * 100, 1 ) ) . '%</small>.
                    </p>
        ';
        $sOutp .= '
                </div>
        ' . PHP_EOL;
        $sOutp .= '
            </div>
        ' . PHP_EOL;
    }
    
    $sOutp .= '</div>' . PHP_EOL;
	echo $sOutp;
}

/**
 * csl_shc_splash function.
 * 
 * @access public
 * @param mixed $atts
 * @param mixed $content (default: null)
 * @return void
 */
function csl_shc_splash( $atts , $content = null ) {   
	$sScript = '
    <div class="container text-center">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <h2 style="font-size: 60px;line-height: 60px;margin-bottom: 20px;font-weight: 900;"><span style="color: #2d99e5;">' . CSL_TEXT_ICON . '</span> ' . CSL_FIRST_LOGO_PART . 
                '<span style="color: #2d99e5;">' . CSL_SECOND_LOGO_PART . '</span> ' . CSL_VERSION . '</h2>
                <p class="lead">' . CSL_DESCRIPTION . ' ' . CSL_ORGANIZATION . '</p>
            </div>
        </div>
    </div>
	' .PHP_EOL;
    $aData = csl_get_basic_stats_count();
    $sData  = '';
    $sData .= '["' . __( 'Record type', CSL_TEXT_DOMAIN_PREFIX ) . '", ';
    $sData .= '"' . __( 'Published', CSL_TEXT_DOMAIN_PREFIX ) . '", ';
    $sData .= '"' . __( 'Draft', CSL_TEXT_DOMAIN_PREFIX ) . '", ';
    $sData .= '"' . __( 'Target', CSL_TEXT_DOMAIN_PREFIX ) . '", ';
    $sData .= '{ role: "annotation" }],' . PHP_EOL;
    foreach( $aData as $data ) {
        $sData .= '["' . $data['s_label'] . '", '. $data['n_publish'] . ', '. $data['n_draft'] . ', ' . $data['n_target'] . ', "' . $data['s_label_acronym'] . '"],' . PHP_EOL; 
    }
    $sScript .= '
        <div class="row"><div id="chart_div" class=""col-sm-12 col-md-12 col-lg-12" style="height: 500px;"></div></div>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {"packages":["corechart"]});
            google.charts.setOnLoadCallback(drawVisualization);
            
            function drawVisualization() {
                var data = google.visualization.arrayToDataTable(' . '[' . $sData . ']' . ');
                var options = {
                    title : "' . __( 'Tasks completion degree', CSL_TEXT_DOMAIN_PREFIX ) . '",
                    vAxis: {title: "' . __( 'Records', CSL_TEXT_DOMAIN_PREFIX ) . '"},
                    hAxis: {title: "' . __( 'Record type', CSL_TEXT_DOMAIN_PREFIX ) . '"},
                    seriesType: "bars",
                    series: {2: {type: "line"}},
                    /* legend: { position: "top", maxLines: 3 }, */
                    bar: { groupWidth: "75%" },
                    isStacked: true,
                };
                
                var chart = new google.visualization.ComboChart(document.getElementById("chart_div"));
                chart.draw(data, options);
            }
        </script> 
    ' . PHP_EOL;
    echo $sScript;
	echo $content;
}

/**
 * csl_shc_documents function.
 * 
 * @access public
 * @param mixed $atts
 * @param mixed $content (default: null)
 * @return void
 */
function csl_shc_documents( $atts , $content = null ) {
    global $wpdb;

    $vatts = shortcode_atts( array(
        'category' => 'pivot_table',
    ), $atts );
    
    $args = array(
    	'posts_per_page'   => -1,
    	'post_type'        => 'document',
    	'post_status'      => 'publish',
    	'orderby'          => 'post_title',
    	'order'            => 'ASC',
    );
    $args ['tax_query']= array(
		array(
			'taxonomy' => 'tax_document_category',
			'field'    => 'slug',
			'terms'    => $vatts['category'],
		),
    );    
    echo $content;

    echo '<h2>';
    echo '<span class="dashicons dashicons-book-alt" style="font-size: xx-large;"></span>&nbsp;&nbsp;';
    echo get_term_by( 'slug', $vatts['category'], 'tax_document_category', OBJECT )->name ;
    echo '</h2>' . PHP_EOL;
    echo '<div id="accordion" class="panel-group">' . PHP_EOL;
  
    $posts_array = get_posts( $args );
    
    foreach($posts_array as $pa) {
        echo '<div class="panel panel-default">' . PHP_EOL;
        echo '<div class="panel-heading">' . PHP_EOL;
        echo '<h4 class="panel-title">' . PHP_EOL;
        echo '<span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;' . PHP_EOL;
        echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse' . $pa->ID . '" style="text-decoration: none; font-size: larger; font-weight: bolder;">' . PHP_EOL;
        echo $pa->post_title;
        echo '</a>' . PHP_EOL;
        echo '</h4>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
        echo '<div id="collapse' . $pa->ID . '" class="panel-collapse collapse">' . PHP_EOL;
        echo '<div class="panel-body">' . PHP_EOL;
        echo '<div class="alert alert-info" role="alert">' . PHP_EOL;
        echo '<span class="dashicons dashicons-testimonial"></span>&nbsp;' . PHP_EOL;
        echo $pa->post_excerpt;
        echo '</div>' . PHP_EOL;
        echo apply_filters( 'the_content', $pa->post_content ); // $pa->post_content;
        echo '</div>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
    }
    echo '</div>' . PHP_EOL;
     
}

function csl_html_contact_form_code( $content ) {
    global $csl_global_nonce;
    global $post;

    echo $content;
    echo "";
    echo '<hr />' . PHP_EOL;
    
    echo '<h3>' . __( 'Contact data', CSL_TEXT_DOMAIN_PREFIX ) . '</h3>' . PHP_EOL;
     
	echo '<form class="form-horizontal" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
    echo '<input type="hidden" name="security" value="' . $csl_global_nonce . '">';
    echo '<input type="hidden" name="cf-userlogin" value="' . wp_get_current_user()->user_login . '">';
    echo '<input type="hidden" name="cf-userdname" value="' . wp_get_current_user()->display_name . '">';
    echo '<input type="hidden" name="cf-ctime" value="' . date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ) . '">';
    echo '<input type="hidden" name="cf-email" value="' . wp_get_current_user()->user_email . '" id="cf-mail">';
    echo '
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-name">' . __( 'Name', CSL_TEXT_DOMAIN_PREFIX ) . '</label>
            <div class="col-sm-10">
            <input type="hidden" name="cf-name"value="' . wp_get_current_user()->display_name . '"id="cf-name">
            <p class="form-control-static">' . wp_get_current_user()->display_name . '</p>
            </div>
        </div>  
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-email">' . __( 'EMail', CSL_TEXT_DOMAIN_PREFIX ) . '</label>
            <div class="col-sm-10">
            <p class="form-control-static">' . wp_get_current_user()->user_email . '</p>
            </div>
        </div>  
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-time">' . __( 'Current time', CSL_TEXT_DOMAIN_PREFIX ) . '</label>
            <div class="col-sm-10">
            <p class="form-control-static">' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), current_time( 'timestamp', 0 ) ) . '</p>
            </div>
        </div>  
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-rtype">' . __( 'Request type', CSL_TEXT_DOMAIN_PREFIX ) . ' <code>*</code></label>
            <div class="col-sm-10">
                <label class="radio-inline">
                    <input type="radio" name="cf-rtype" id="cf-rtype1" value="IREQ" checked="checked"> ' . __( 'Info request', CSL_TEXT_DOMAIN_PREFIX ) . '
                </label>
                <label class="radio-inline">
                    <input type="radio" name="cf-rtype" id="cf-rtype2" value="HREQ"> ' . __( 'Help request', CSL_TEXT_DOMAIN_PREFIX ) . '
                </label>
            </div>
            </label>            
        </div>  
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-subject">' . __( 'Subject', CSL_TEXT_DOMAIN_PREFIX ) . ' <code>*</code></label>
            <div class="col-sm-10">
            <input type="text" name="cf-subject" pattern="[[:alpha:]]+" value="' . ( isset( $_POST["cf-subject"] ) ? esc_attr( $_POST["cf-subject"] ) : '' ) . '" class="form-control" id="cf-subject" placeholder="' . __( 'Subject', CSL_TEXT_DOMAIN_PREFIX ) . '">
            </div>
        </div>  
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-message">' . __( 'Message', CSL_TEXT_DOMAIN_PREFIX ) . ' <code>*</code></label>
            <div class="col-sm-10">
                <textarea rows="10" name="cf-message" class="form-control" id="cf-message">' . ( isset( $_POST["cf-message"] ) ? esc_attr( $_POST["cf-message"] ) : '' ) . '</textarea>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" name="cf-submitted" class="btn btn-default">' . __( 'Submit', CSL_TEXT_DOMAIN_PREFIX ) . '</button>
            </div>
        </div>  
    ';
	echo '</form>';
}

function csl_contact_form_deliver_mail() {
    if( isset( $_REQUEST['security'] ) ) {
    	if ( ! wp_verify_nonce( $_REQUEST['security'], NONCE_KEY ) ) { 
            wp_die( __( 'Security check error.', CSL_TEXT_DOMAIN_PREFIX ) );
        } else {
        	if ( isset( $_POST['cf-submitted'] ) ) {
        		$name    = sanitize_text_field( $_POST["cf-name"] );
        		$email   = sanitize_email( $_POST["cf-email"] );
        		$subject = sanitize_text_field( $_POST["cf-subject"] );
        		$message = esc_textarea( $_POST["cf-message"] );
                
                $message = __( 'User name', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $_POST["cf-name"] ) . PHP_EOL . 
                    __( 'User login', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $_POST["cf-userlogin"] ) . PHP_EOL .
                    __( 'Request type', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $_POST["cf-rtype"] ) . PHP_EOL .
                    __( 'Request time', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $_POST["cf-ctime"] ) . PHP_EOL .
                    __( 'Request subject', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $_POST["cf-subject"] ) . PHP_EOL .
                    '------------------------' . PHP_EOL . $message;
    
        		$to = get_option( 'admin_email' );
        
        		$headers = "From: $name <$email>" . "\r\n";
                if ( is_email( $email ) && $name != '' && $subject != '' && $message != '' ) {
            		if ( wp_mail( $to, $subject, $message, $headers ) ) {
            			echo '<div class="alert alert-success" role="alert">';
            			echo __( '<strong>Your request has been succesfully sent</strong>. You will receive an answer as soon as possible.', CSL_TEXT_DOMAIN_PREFIX );
            			echo '</div>';
            		} else {
            			echo '<div class="alert alert-danger" role="alert">';
            			echo sprintf( 
                            __( '<strong>Your request could not be sent</strong>. An error has occurred during process. You can contact with site administrator <a href="mailto:%s">here</a>.', CSL_TEXT_DOMAIN_PREFIX ),
                            $to
                            );
            			echo '</div>';
                    }
                } else {
        			echo '<div class="alert alert-danger" role="alert">';
        			echo __( '<strong>Error in mandatory fields</strong>. One or more required fields have not been correctly entered.', CSL_TEXT_DOMAIN_PREFIX );
        			echo '</div>';
                }
        	}
        }
    }
}

function csl_contact_form_shortcode( $atts , $content = null ) {
	ob_start();
	csl_contact_form_deliver_mail();
	csl_html_contact_form_code( $content );

	return ob_get_clean();
}

// Bug form
function csl_html_bug_form_code( $content, $pid ) {
    global $csl_global_nonce;
    global $post;

    echo $content;
    echo "";
    echo '<hr />' . PHP_EOL;
    
    echo '<h3>' . __( 'Error notification', CSL_TEXT_DOMAIN_PREFIX ) . '</h3>' . PHP_EOL;
     
	echo '<form class="form-horizontal" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
    echo '<input type="hidden" name="security" value="' . $csl_global_nonce . '">';
    echo '<input type="hidden" name="cf-userlogin" value="' . wp_get_current_user()->user_login . '">';
    echo '<input type="hidden" name="cf-userdname" value="' . wp_get_current_user()->display_name . '">';
    echo '<input type="hidden" name="cf-ctime" value="' . date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ) . '">';
    echo '<input type="hidden" name="cf-pageid" value="' . $pid . '">';
    echo '<input type="hidden" name="cf-page" value="' . get_the_title( $pid ) . '">';
    echo '<input type="hidden" name="cf-email" value="' . wp_get_current_user()->user_email . '" id="cf-mail">';
    echo '
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-name">' . __( 'Name', CSL_TEXT_DOMAIN_PREFIX ) . '</label>
            <div class="col-sm-10">
            <input type="hidden" name="cf-name"value="' . wp_get_current_user()->display_name . '"id="cf-name">
            <p class="form-control-static">' . wp_get_current_user()->display_name . '</p>
            </div>
        </div>  
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-email">' . __( 'EMail', CSL_TEXT_DOMAIN_PREFIX ) . '</label>
            <div class="col-sm-10">
            <p class="form-control-static">' . wp_get_current_user()->user_email . '</p>
            </div>
        </div>  
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-time">' . __( 'Current time', CSL_TEXT_DOMAIN_PREFIX ) . '</label>
            <div class="col-sm-10">
            <p class="form-control-static">' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), current_time( 'timestamp', 0 ) ) . '</p>
            </div>
        </div>  
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-page">' . __( 'Page where bug has been found', CSL_TEXT_DOMAIN_PREFIX ) . '</label>
            <div class="col-sm-10">
            <p class="form-control-static">' . get_the_title( $pid ) . '</p>
            </div>
        </div>  
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-btype">' . __( 'Request type', CSL_TEXT_DOMAIN_PREFIX ) . ' <code>*</code></label>
                <div class="col-sm-10">
                <div class="radio"><label>
                    <input type="radio" name="cf-btype" id="cf-btype1" value="BERR" checked="checked"> ' . __( 'Error message shown', CSL_TEXT_DOMAIN_PREFIX ) . '
                </label>
                </div>
                <div class="radio"><label>
                    <input type="radio" name="cf-btype" id="cf-btype2" value="BBLN"> ' . __( 'Blank page shown', CSL_TEXT_DOMAIN_PREFIX ) . '
                </label>
                </div>
                <div class="radio"><label>
                    <input type="radio" name="cf-btype" id="cf-btype3" value="BIRS"> ' . __( 'Incorrect results shown', CSL_TEXT_DOMAIN_PREFIX ) . '
                </label>
                </div>
                <div class="radio"><label>
                    <input type="radio" name="cf-btype" id="cf-btype4" value="BSRT"> ' . __( 'Slow response time', CSL_TEXT_DOMAIN_PREFIX ) . '
                </label>
                </div>
                <div class="radio"><label>
                    <input type="radio" name="cf-btype" id="cf-btype5" value="BSEC"> ' . __( 'Security risk detected', CSL_TEXT_DOMAIN_PREFIX ) . '
                </label>
                </div>
                <div class="radio"><label>
                    <input type="radio" name="cf-btype" id="cf-btype6" value="BLOG"> ' . __( 'Logical error (Content error)', CSL_TEXT_DOMAIN_PREFIX ) . '
                </label>
                </div>
                </div>
            </label>            
        </div>  
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-shortdesc">' . __( 'Bug short description', CSL_TEXT_DOMAIN_PREFIX ) . ' <code>*</code></label>
            <div class="col-sm-10">
            <input type="text" name="cf-shortdesc" pattern="[[:alpha:]]+" value="' . ( isset( $_POST["cf-shortdesc"] ) ? esc_attr( $_POST["cf-shortdesc"] ) : '' ) . '" class="form-control" id="cf-shortdesc" placeholder="' . __( 'Bug short description', CSL_TEXT_DOMAIN_PREFIX ) . '">
            </div>
        </div>  
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cf-longdesc">' . __( 'Message', CSL_TEXT_DOMAIN_PREFIX ) . ' <code>*</code></label>
            <div class="col-sm-10">
            <textarea rows="10" name="cf-longdesc" class="form-control" id="cf-longdesc">' . ( isset( $_POST["cf-longdesc"] ) ? esc_attr( $_POST["cf-longdesc"] ) : '' ) . '</textarea>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" name="cf-submitted" class="btn btn-default">' . __( 'Submit', CSL_TEXT_DOMAIN_PREFIX ) . '</button>
            </div>
        </div>  
    ';
	echo '</form>';
}

function csl_bug_form_deliver_mail() {
    if( isset( $_REQUEST['security'] ) ) {
    	if ( ! wp_verify_nonce( $_REQUEST['security'], NONCE_KEY ) ) { 
            wp_die( __( 'Security check error.', CSL_TEXT_DOMAIN_PREFIX ) );
        } else {
        	if ( isset( $_POST['cf-submitted'] ) ) {
                $btype   = array(
                    'BERR' => __( 'Error message shown', CSL_TEXT_DOMAIN_PREFIX ),
                    'BBLN' => __( 'Blank page shown', CSL_TEXT_DOMAIN_PREFIX ),
                    'BIRS' => __( 'Incorrect results shown', CSL_TEXT_DOMAIN_PREFIX ),
                    'BSRT' => __( 'Slow response time', CSL_TEXT_DOMAIN_PREFIX ),
                    'BSEC' => __( 'Security risk detected', CSL_TEXT_DOMAIN_PREFIX ),
                );
        		$name    = sanitize_text_field( $_POST["cf-name"] );
        		$email   = sanitize_email( $_POST["cf-email"] );
        		$subject = sanitize_text_field( $_POST["cf-shortdesc"] );
        		$message = esc_textarea( $_POST["cf-longdesc"] );
                
                $message = __( 'User name', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $_POST["cf-name"] ) . PHP_EOL . 
                    __( 'User login', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $_POST["cf-userlogin"] ) . PHP_EOL .
                    __( 'Bug type', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $btype[$_POST["cf-btype"]] ) . PHP_EOL .
                    __( 'Bug notification time', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $_POST["cf-ctime"] ) . PHP_EOL .
                    __( 'Buggy page ID', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $_POST["cf-pageid"] ) . PHP_EOL .
                    __( 'Buggy page title', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $_POST["cf-page"] ) . PHP_EOL .
                    __( 'Bug short description', CSL_TEXT_DOMAIN_PREFIX ) . ': ' . sanitize_text_field( $_POST["cf-shortdesc"] ) . PHP_EOL .
                    '------------------------' . PHP_EOL . $message;
                $subject = sprintf( __( '%s Bug Notification', CSL_TEXT_DOMAIN_PREFIX ), CSL_NAME ) . '. ' . $subject;
        		$to = get_option( 'admin_email' );
        
        		$headers = "From: $name <$email>" . "\r\n";
                if ( is_email( $email ) && $name != '' && $subject != '' && $message != '' ) {
            		if ( wp_mail( $to, $subject, $message, $headers ) ) {
            			echo '<div class="alert alert-success" role="alert">';
            			echo __( '<strong>Your bug notification has been succesfully sent</strong>. You will receive an administrative answer about as soon as possible.', CSL_TEXT_DOMAIN_PREFIX );
            			echo '</div>';
            		} else {
            			echo '<div class="alert alert-danger" role="alert">';
            			echo sprintf( 
                            __( '<strong>Your bug notification could not be sent</strong>. An error has occurred during process. You can contact with site administrator <a href="mailto:%s">here</a>.', CSL_TEXT_DOMAIN_PREFIX ),
                            $to
                            );
            			echo '</div>';
                    }
                } else {
        			echo '<div class="alert alert-danger" role="alert">';
        			echo __( '<strong>Error in mandatory fields</strong>. One or more required fields have not been correctly entered.', CSL_TEXT_DOMAIN_PREFIX );
        			echo '</div>';
                }
        	}
        }
    }
}

function csl_bug_form_shortcode( $atts , $content = null ) {
	ob_start();
	csl_bug_form_deliver_mail();
    $page_id = isset( $_REQUEST['pid'] ) ? $_REQUEST['pid'] : NULL;
	csl_html_bug_form_code( $content, $page_id );

	return ob_get_clean();
}

function csl_voronoi_shortcode( $atts , $content = null ) {	
	global $csl_global_nonce;
	
    $attribs = shortcode_atts( array(
        'mode' => 'voronoi',
    ), $atts );
    
    $parms = 'q=' . $attribs['mode'];
	ob_start();
    $text = file_get_contents(get_template_directory() . '/assets/geo/' . get_locale() . '/voronoi.html');
    
    $text = str_replace( "@DATAROUTE@", admin_url( 'admin-ajax.php?' . $parms . '&action=csl_generic_ajax_call&s=' . $csl_global_nonce ), $text );
    $text = str_replace( "@CSSROUTE@", get_template_directory_uri() . '/assets/geo/' . get_locale() . '/assets/css', $text );
    $text = str_replace( "@JSROUTE@", get_template_directory_uri() . '/assets/geo/' . get_locale() . '/assets/js', $text );
    
    echo $content;
    echo $text ? $text : __( 'Not help file found.', CSL_TEXT_DOMAIN_PREFIX );
    
	return ob_get_clean();
}

function csl_leaflet_shortcode( $atts , $content = null ) {	
	global $csl_global_nonce;
	
    $attribs = shortcode_atts( array(
        'mode' => 'leaflet',
    ), $atts );
    
    $parms = 'q=' . $attribs['mode'];
	ob_start();
    $text = file_get_contents(get_template_directory() . '/assets/geo/' . get_locale() . '/leaflet.html');
    
    $text = str_replace( "@DATAROUTE@", admin_url( 'admin-ajax.php?' . $parms . '&action=csl_generic_ajax_call&s=' . $csl_global_nonce ), $text );
    $text = str_replace( "@CSSROUTE@", get_template_directory_uri() . '/assets/geo/' . get_locale() . '/assets/css', $text );
    $text = str_replace( "@JSROUTE@", get_template_directory_uri() . '/assets/geo/' . get_locale() . '/assets/js', $text );
	$text = str_replace( "@GEOROUTE@", get_template_directory_uri() . '/assets/geo/' . get_locale() . '/assets/geo', $text );
   
    echo $content;
    echo $text ? $text : __( 'Not help file found.', CSL_TEXT_DOMAIN_PREFIX );
    
	return ob_get_clean();
}

function csl_crossfilter_shortcode( $atts , $content = null ) {	
	global $csl_global_nonce;
	
    $attribs = shortcode_atts( array(
        'mode' => 'crossfilter',
    ), $atts );
    
    $parms = 'q=' . $attribs['mode'];
	ob_start();
    $text = file_get_contents(get_template_directory() . '/assets/geo/' . get_locale() . '/crossfilter.html');
    
    $text = str_replace( "@DATAROUTE@", admin_url( 'admin-ajax.php?' . $parms . '&action=csl_generic_ajax_call&s=' . $csl_global_nonce ), $text );
    $text = str_replace( "@CSSROUTE@", get_template_directory_uri() . '/assets/geo/' . get_locale() . '/assets/css', $text );
    $text = str_replace( "@JSROUTE@", get_template_directory_uri() . '/assets/geo/' . get_locale() . '/assets/js', $text );
	$text = str_replace( "@GEOROUTE@", get_template_directory_uri() . '/assets/geo/' . get_locale() . '/assets/geo', $text );
   
    echo $content;
    echo $text ? $text : __( 'Not help file found.', CSL_TEXT_DOMAIN_PREFIX );
    
	return ob_get_clean();
}

function csl_geochart_shortcode( $atts , $content = null ) {	
	global $csl_global_nonce;
	global $wpdb;
	
    $attribs = shortcode_atts( array(
        'mode' => 'geochart',
    ), $atts );

    $parms = 'q=' . $attribs['mode'];
    
    $syears     = $wpdb->get_results( "SELECT SQL_CACHE DISTINCT n_start_year FROM {$wpdb->prefix}xtr_vw_unfolded_exhibition ORDER BY n_start_year DESC", ARRAY_A );
    $selectors  = '';
    $selectors .= '<form id="frm-selectors" action="' . admin_url( 'admin-ajax.php?' . $parms . '&action=csl_generic_ajax_call&s=' . $csl_global_nonce ) . '" method="POST" class="form-inline">' . PHP_EOL;

	$selectors .= '<select name="y" id="sel_start_year" class="form-control input-sm">';
	foreach( $syears as $syear ) {
		$selectors .= '<option value="' . $syear['n_start_year'] . '">' . sprintf( __( 'Year %s', CSL_TEXT_DOMAIN_PREFIX ), $syear['n_start_year'] ) . '</option>' . PHP_EOL;	
	}
	$selectors .= '</select>';
	
    $args = array(
    	'show_option_none'  => __( 'Don\'t filter by period', CSL_TEXT_DOMAIN_PREFIX ),
    	'option_none_value'  => '',
    	'orderby'            => 'name',
    	'order'              => 'ASC',
    	'echo'               => false,
    	'name'               => 'p',
    	'id'                 => 'sel_tax_period',
    	'class'              => 'form-control input-sm',
    	'taxonomy'           => 'tax_period',
    	'hide_if_empty'      => true,
    	'value_field'	     => 'name',
    );
    $selectors .= wp_dropdown_categories( $args );
    
    $args = array(
    	'show_option_none'  => __( 'Don\'t filter by exhibition type', CSL_TEXT_DOMAIN_PREFIX ),
    	'option_none_value'  => '',
    	'orderby'            => 'name',
    	'order'              => 'ASC',
    	'echo'               => false,
    	'name'               => 't',
    	'id'                 => 'sel_tax_exhibition_type',
    	'class'              => 'form-control input-sm',
    	'taxonomy'           => 'tax_exhibition_type',
    	'hide_if_empty'      => true,
    	'value_field'	     => 'name',
    );
    $selectors .= wp_dropdown_categories( $args );

    $args = array(
    	'show_option_none'  => __( 'Don\'t filter by movement', CSL_TEXT_DOMAIN_PREFIX ),
    	'option_none_value'  => '',
    	'option_none_value'  => '',
    	'orderby'            => 'name',
    	'order'              => 'ASC',
    	'echo'               => false,
    	'name'               => 'm',
    	'id'                 => 'sel_tax_movement',
    	'class'              => 'form-control input-sm',
    	'taxonomy'           => 'tax_movement',
    	'hide_if_empty'      => true,
    	'value_field'	     => 'name',
    );
    $selectors .= wp_dropdown_categories( $args );
    
    $selectors .= '<button type="submit" class="btn btn-default btn-sm">' . __( 'Filter', CSL_TEXT_DOMAIN_PREFIX ) . ' <i class="fa fa-filter"></i></button>';
    $selectors .= '</form>' . PHP_EOL;
    
	ob_start();
    $text = file_get_contents(get_template_directory() . '/assets/geo/' . get_locale() . '/geochart.html');
    
    $text = str_replace( "@DATAROUTE@", admin_url( 'admin-ajax.php?' . $parms . '&action=csl_generic_ajax_call&s=' . $csl_global_nonce ), $text );
    $text = str_replace( "@CSSROUTE@", get_template_directory_uri() . '/assets/geo/' . get_locale() . '/assets/css', $text );
    $text = str_replace( "@JSROUTE@", get_template_directory_uri() . '/assets/geo/' . get_locale() . '/assets/js', $text );
	$text = str_replace( "@GEOROUTE@", get_template_directory_uri() . '/assets/geo/' . get_locale() . '/assets/geo', $text );
	$text = str_replace( "@SELECTORS@", $selectors, $text );
   
    echo $content;
    echo $text ? $text : __( 'Not help file found.', CSL_TEXT_DOMAIN_PREFIX );
    
	return ob_get_clean();
}

// Calling shortcodes	
add_shortcode( 'expofinder', 'csl_shc_expofinder' );
add_shortcode( 'project-status', 'csl_shc_project_status' );
add_shortcode( 'project-stats', 'csl_shc_project_stats' );
add_shortcode( 'posts-pivot', 'csl_shc_posts_pivot' );
add_shortcode( 'custom-queries', 'csl_shc_custom_queries' );
add_shortcode( 'splash', 'csl_shc_splash' );
add_shortcode( 'stats', 'csl_shc_base_stats' );
add_shortcode( 'documents', 'csl_shc_documents' );
add_shortcode( 'contact', 'csl_contact_form_shortcode' );
add_shortcode( 'bug', 'csl_bug_form_shortcode' );
add_shortcode( 'voronoi', 'csl_voronoi_shortcode' );
add_shortcode( 'leaflet', 'csl_leaflet_shortcode' );
add_shortcode( 'crossfilter', 'csl_crossfilter_shortcode' );
add_shortcode( 'geochart', 'csl_geochart_shortcode' );

?>
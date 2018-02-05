<?php

if (!defined('SAVEQUERIES') && isset($_GET['debug'])) define('SAVEQUERIES', true);
    
if ( !function_exists('csl_list_performance') ) : 
    function csl_list_performance() {
        global $wpdb;
    	if ( defined( 'DOING_AJAX' ) ) {
    		return;
    	}
        $aQueries = array();
        foreach($wpdb->queries as $query) {
            $aQueries []= sprintf(
                __( '%s sec: %s', CSL_TEXT_DOMAIN_PREFIX ),
                number_format_i18n($query[1], 4),
                '<span style="color: #a0a0a0; font-size: smaller;">' . sanitize_text_field($query[0]) . '</span>' . 
                '&nbsp;&mdash;&nbsp;' . 
                '<span style="color: #aa3333; font-size: smaller;">' . sanitize_text_field($query[2]) . '</span>' 
                ); 
        }
    	echo 
            csl_format_admin_notice(
                sprintf(
                    __( '%s queries in %s seconds, using %s memory', CSL_TEXT_DOMAIN_PREFIX ),
                    '<strong>' . number_format_i18n(get_num_queries(), 0) . '</strong>',
                    '<strong>' . timer_stop( 0, 3 ) . '</strong>',
                    '<strong>' . size_format(memory_get_peak_usage()) . '</strong>'
                ) . 
                '<hr />' . 
                csl_array_to_paragraph_or_br($aQueries, 'br'),
                'bug', 
                'warning'
            );
    }
endif;

/**
 * Degug backtrace based in Development Debug Backtraces v0.2.2 by Storm Consultancy (//www.stormconsultancy.co.uk) 
 */

set_error_handler( 'csl_error_backtrace' );

function csl_error_backtrace($errno, $errstr, $errfile, $errline, $errcontext) {
    if ( $errno == E_STRICT ) return false;
    echo '<br />';
    echo '<a href="#" onclick="jQuery(this).next().css(\'display\',\'block\'); jQuery(this).next().next().css(\'display\',\'block\'); jQuery(this).css(\'display\',\'none\'); return false;">Show Backtrace For Error</a>';
    echo '<a href="#" style="display:none" onclick="jQuery(this).prev().css(\'display\',\'block\'); jQuery(this).next().css(\'display\',\'none\'); jQuery(this).css(\'display\',\'none\'); return false;">Hide Backtrace</a>';
    echo '<div style="display:none;">';
    echo '<pre>';
    debug_print_backtrace();
    echo '</pre>';
    echo '</div>';
    return false;
}

?>
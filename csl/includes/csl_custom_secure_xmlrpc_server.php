<?php

/**
 * SECURE XML-RPC
 * Hash private & public key couple for authentication
 * Based in a plugin (c) 2013-4 Eric Mann (https://  github . com /ericmann/secure-xmlrpc) under GPLv2 or later License
 */

// Useful global constants
define( 'CSL_XMLRPCS_VERSION', '1.0.0' );
define( 'CSL_XMLRPCS_URL', get_template_directory_uri() . '/includes/libraries/XMLRPC' );
define( 'CSL_XMLRPCS_PATH', dirname( __FILE__ ) . '/' );

// Require includes
require_once( 'libraries/XMLRPC/csl_xmlrpcs_profile.php' );
require_once( 'libraries/XMLRPC/csl_class_secure_xmlrpc_server.php' );

// Wireup actions
add_action( 'init',                    array( 'XMLRPCS_Profile', 'init' ),               10, 0 );
add_action( 'show_user_profile',       array( 'XMLRPCS_Profile', 'append_secure_keys' ), 10, 1 );
add_action( 'admin_enqueue_scripts',   array( 'XMLRPCS_Profile', 'admin_enqueues' )            );
add_action( 'profile_update',          array( 'XMLRPCS_Profile', 'profile_update' ),     10, 1 );

// Wireup filters
add_filter( 'wp_xmlrpc_server_class',  array( 'XMLRPCS_Profile', 'server' ),       10, 1 );
add_filter( 'authenticate',            array( 'XMLRPCS_Profile', 'authenticate' ), 10, 3 );

// Wireup Ajax
add_action( 'wp_ajax_xmlrpcs_new_app', array( 'XMLRPCS_Profile', 'new_app' ) );

?>

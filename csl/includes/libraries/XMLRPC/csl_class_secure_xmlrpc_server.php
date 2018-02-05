<?php
include_once(ABSPATH . 'wp-admin/includes/admin.php');
include_once(ABSPATH . WPINC . '/class-IXR.php');
include_once(ABSPATH . WPINC . '/class-wp-xmlrpc-server.php');

/**
 * Secure XML-RPC Server Implementation
 *
 * Extends OAuth-style security for remote procedure calls so they don't need to pass username/password credentials
 * in plaintext with the request.
 *
 * @subpackage Publishing
 */
 
class secure_xmlrpc_server extends wp_xmlrpc_server {

	protected $authenticated_user = false;
	/**
	 * Register all of the XML-RPC overrides we need to properly secure WordPress
	 *
	 * @return secure_xmlrpc_server
	 */
    
	public function __construct() {
		$this->authenticated_user = $this->pre_query_auth();
		add_filter( 'xmlrpc_methods', array( $this, 'remove_methods' ), 10, 1);
		add_filter( 'xmlrpc_methods', array( $this, 'add_methods' ), 10, 1 );
		parent::__construct();
		$this->setCallbacks();
	}
    
	public function remove_methods( $methods ) {
		// Built-in (obsolete included) methods
		unset( $methods['wp.getUsersBlogs'] );
		unset( $methods['wp.newPost'] );     
		unset( $methods['wp.editPost'] );     
		unset( $methods['wp.deletePost'] );   
		unset( $methods['wp.getPost'] );      
		unset( $methods['wp.getPosts'] );      
		unset( $methods['wp.newTerm'] );             
		unset( $methods['wp.editTerm'] );            
		unset( $methods['wp.deleteTerm'] );          
		unset( $methods['wp.getTerm'] );             
		unset( $methods['wp.getTerms'] );            
		unset( $methods['wp.getTaxonomy'] );         
		unset( $methods['wp.getTaxonomies'] );       
		unset( $methods['wp.getUser'] );             
		unset( $methods['wp.getUsers'] );            
		unset( $methods['wp.getProfile'] );          
		unset( $methods['wp.editProfile'] );         
		unset( $methods['wp.getPage'] );             
		unset( $methods['wp.getPages'] );            
		unset( $methods['wp.newPage'] );             
		unset( $methods['wp.deletePage'] );          
		unset( $methods['wp.editPage'] );            
		unset( $methods['wp.getPageList'] );         
		unset( $methods['wp.getAuthors'] );          
		unset( $methods['wp.getCategories'] );       
		unset( $methods['wp.getTags'] );             
		unset( $methods['wp.newCategory'] );         
		unset( $methods['wp.deleteCategory'] );      
		unset( $methods['wp.suggestCategories'] );   
		unset( $methods['wp.uploadFile'] );          
		unset( $methods['wp.deleteFile'] );
		unset( $methods['wp.downloadFile'] );
		unset( $methods['wp.getCommentCount'] );     
		unset( $methods['wp.getPostStatusList'] );  
		unset( $methods['wp.getPageStatusList'] );   
		unset( $methods['wp.getPageTemplates'] );    
		unset( $methods['wp.getOptions'] );          
		unset( $methods['wp.setOptions'] );          
		unset( $methods['wp.getComment'] );          
		unset( $methods['wp.getComments'] );         
		unset( $methods['wp.deleteComment'] );       
		unset( $methods['wp.editComment'] );         
		unset( $methods['wp.newComment'] );          
		unset( $methods['wp.getCommentStatusList'] );
		unset( $methods['wp.getMediaItem'] );        
		unset( $methods['wp.getMediaLibrary'] );     
		unset( $methods['wp.getPostFormats'] );      
		unset( $methods['wp.getPostType'] );         
		unset( $methods['wp.getPostTypes'] );        
		unset( $methods['wp.getRevisions'] );        
		unset( $methods['wp.restoreRevision'] );     
		// Blogger methods
		unset( $methods['blogger.getUsersBlogs'] ); 
		unset( $methods['blogger.getUserInfo'] );   
		unset( $methods['blogger.getPost'] );       
		unset( $methods['blogger.getRecentPosts'] );
		unset( $methods['blogger.newPost'] );       
		unset( $methods['blogger.editPost'] );      
		unset( $methods['blogger.deletePost'] );    
		// MetaWeblog methods
		unset( $methods['metaWeblog.newPost'] );       
		unset( $methods['metaWeblog.editPost'] );      
		unset( $methods['metaWeblog.getPost'] );       
		unset( $methods['metaWeblog.getRecentPosts'] );
		unset( $methods['metaWeblog.getCategories'] ); 
		unset( $methods['metaWeblog.newMediaObject'] );
		unset( $methods['metaWeblog.deletePost'] );    
		unset( $methods['metaWeblog.getUsersBlogs'] ); 
		// MovableType methods
		unset( $methods['mt.getCategoryList'] );    
		unset( $methods['mt.getRecentPostTitles'] );
		unset( $methods['mt.getPostCategories'] );  
		unset( $methods['mt.setPostCategories'] );  
		unset( $methods['mt.publishPost'] );        
		unset( $methods['mt.getTrackbackPings'] );
		unset( $methods['mt.supportedTextFilters'] );
		unset( $methods['mt.supportedMethods'] );

		// Various methods
		unset( $methods['demo.addTwoNumbers'] );
		unset( $methods['demo.sayHello'] );
		unset( $methods['pingback.extensions.getPingbacks'] );
		unset( $methods['pingback.ping'] );

		// Not removed method: system.multicall, system.listMethods, system.getCapabilities
		
	    return $methods;   
	}
	/**
	 * Filter default methods and overload with our secure implementation.
	 *
	 * @param array $methods
	 *
	 * @return array
	 */
	public function add_methods( $methods ) {
		// Fake WordPress API methods
		$methods['wp.getPosts'] = array ( $this, 'wp_getPosts' );
		$methods['wp.getTerms'] = array ( $this, 'wp_getValidTermsData' );
		// Application methods
		$methods['xmlrpcs.createApplication'] = array( $this, 'xmlrpcs_createApplication' );

		
		return $methods;
	}

	public function setCallbacks() {
		return false;
	}
	
	/**
	 * Add an X-Deprecated header.
	 *
	 * @param void
	 */
    
	protected function pre_query_auth( ) {
		global $wpdb;
		$aHeaders = getallheaders( );
		$sBody    = file_get_contents('php://input');
        $sBody    = preg_replace( '~[[:cntrl:]]~', '', $sBody );
		if ( ! isset( $aHeaders['Authorization'] ) ) {
			http_response_code( 401 );
			exit(0);
		} else {
			$aAuth = explode( '||', $aHeaders['Authorization'] );
			$sPubl = isset($aAuth[0]) ? $aAuth[0] : null;
			$sPriv = isset($aAuth[1]) ? $aAuth[1] : null;
			$sAppl = isset($aAuth[2]) ? $aAuth[2] : null;

			$query = "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = '_xmlrpcs_secret_{$sPubl}' LIMIT 1;";
			$sKPri = $wpdb->get_var( $query );
            $sLPri = hash( 'sha256', $sKPri . hash( 'sha256', $sKPri . $sBody ) );	
			$query = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '_xmlrpcs_app_{$sPubl}' AND meta_value = '{$sAppl}' LIMIT 1;";
			$nUApp = $wpdb->get_var( $query );
            return $sPriv == $sLPri ? $nUApp : false;
		}
	}
    
	// WordPress API Overloads

	/**
	 * wp_getPosts. Overload the existing wp.getPosts method.
	 *
	 * @param array $args
	 * @return void|boolean
	 */
	public function wp_getPosts( $args ) {
        @ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', CSL_MAX_MEMOTY_LIMIT ) ); // WP_MAX_MEMORY_LIMIT = 256M
        global $wpdb;
        // Security catch: if you try to use username and password as ever you will be "deprecated"...
		if ( !is_array( $args ) ) {
            $this->error(-32602, 'server error. invalid method parameters');
        }
		if ( empty( $args[0] ) || empty( $args[1] ) ) {
            $this->error(-32602, 'server error. invalid method parameters');
        }
		if ( !in_array( $args[0], CSL_CUSTOM_POST_TYPE_ARRAY ) ) {
            $this->error(-32602, 'server error. invalid method parameters');
        }
		if ( !in_array( $args[1], CSL_CUSTOM_XMLRPC_OUTPUT_FORMATS_ALLOWED ) ) {
            $this->error(-32602, 'server error. invalid method parameters');
        }
		if ( empty( $args[2] ) ) {
			$args[2] = 100;
        }
		if( !$this->authenticated_user ) {
            $this->error(-32099, 'server error. authentication fault');
		} else {
            $aArgs = array(
                'orderby' => 'name', 
                'order' => 'ASC', 
                'fields' => 'all',            
            );
            $aPosts = $wpdb->get_results(
                "
                SELECT 
                    p.ID,
                    p.post_type, 
                    p.post_title,
                    p.post_date_gmt,
                    p.post_modified_gmt                    
                FROM 
                    $wpdb->posts p
                WHERE 
                    p.post_type = '" . $args[0] . "'  
                    AND
                    p.post_status = 'publish'; 
                ", 
            OBJECT);
            $aQueryData = array( array(
                'queryDate' => current_time( 'mysql', 0 ),
                'queryDateGMT' => current_time( 'mysql', 1 ),
                'queryClientIP' => csl_get_client_ip(),
                'queryCountResults' => count( $aPosts ),
                'queryRecordType' => $args[0], 
            ) );
            $aPosts = array_merge( $aQueryData, $aPosts );
            foreach( $aPosts as &$aPost ) {
                $aPost->terms = wp_get_post_terms( $aPost->ID, CSL_CUSTOM_POST_TAXONOMIES_COMPLETE_ARRAY, $aArgs );
                $aPost->meta  = get_post_meta( $aPost->ID );
            }
            switch( strtolower( $args[1] ) ) {
                case 'json':
                    echo json_encode( $aPosts, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
                    die();
                    break;
                default: //xml
                    return $aPosts;
            }
		}
	}

	/**
	 * wp_getValidTaxonomiesData. Overload the existing wp.getPosts method.
	 *
	 * @param array $args
	 * @return void|boolean
	 */
	public function wp_getValidTermsData( $args ) {
        @ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', CSL_MAX_MEMOTY_LIMIT ) ); // WP_MAX_MEMORY_LIMIT = 256M
        global $wpdb;
        // Security catch: if you try to use username and password as ever you will be "deprecated"...
		if ( !is_array( $args ) ) {
            $this->error(-32602, 'server error. invalid method parameters');
        }
		if ( empty( $args[0] ) || empty( $args[1] ) ) {
            $this->error(-32602, 'server error. invalid method parameters');
        }
		if ( !in_array( $args[0], array_merge( array( 'terms' ), CSL_CUSTOM_POST_TYPE_ARRAY ) ) ) {
            $this->error(-32602, 'server error. invalid method parameters');
        }
		if ( !in_array( $args[1], CSL_CUSTOM_XMLRPC_OUTPUT_FORMATS_ALLOWED ) ) {
            $this->error(-32602, 'server error. invalid method parameters' );
        }
		if ( empty( $args[2] ) ) {
			$args[2] = 100;
        }
		if( !$this->authenticated_user ) {
            $this->error(-32099, 'server error. authentication fault');
		} else {
			$maxnum = $args[2] == 0 ? '' : $args[2];
            $aArgs = array(
                'orderby' => 'name', 
                'order' => 'ASC', 
                'fields' => 'all',            
                'hide_empty' => true, 
                'hierarchical' => true, 
                /* 'number' => $maxnum, */
            );

            $aPosts = get_terms( CSL_CUSTOM_POST_TAXONOMIES_COMPLETE_ARRAY, $aArgs );
            
            $aQueryData = array( array(
                'queryDate' => current_time( 'mysql', 0 ),
                'queryDateGMT' => current_time( 'mysql', 1 ),
                'queryClientIP' => csl_get_client_ip(),
                'queryCountResults' => count( $aPosts ),
                'queryRecordType' => $args[0], 
            ) );
            $aPosts = array_merge( $aQueryData, $aPosts );
            switch( strtolower( $args[1] ) ) {
                case 'json':
                    echo json_encode( $aPosts, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
                    die();
                    break;
                default: //xml
                    return $aPosts;
            }
		}
	}

	// Custom Security Methods

	/**
	 * Create a new set of application keys and return them to the requestor.
	 *
	 * @param array $args
	 *
	 * @return array Application information
	 */
	public function xmlrpcs_createApplication( $args ) {
		if ( ! $this->minimum_args( $args, 3 ) ) {
			return $this->error;
		}

		$this->escape( $args );

		$blog_id  = (int) $args[0];
		$username = $args[1];
		$password = $args[2];
		$app_name = $args[3];

		// Log the user in
		if ( ! $user = $this->login( $username, $password ) ) {
			return $this->error;
		}

		// If the application name isn't valid, err
		if ( empty( $app_name ) ) {
			return $this->error;
		}

		/** This action is documented in wp-includes/class-wp-xmlrpc-server.php */
		do_action( 'xmlrpc_call', 'xmlrpcs.createApplication' );

		// Create application keys
		$key = apply_filters( 'xmlrpcs_public_key', wp_hash( time() . rand(), 'auth' ) );
		$secret = apply_filters( 'xmlprcs_secret_key', wp_hash( time() . rand() . $key, 'auth' ) );

		// Add the application
		add_user_meta( $user->ID, '_xmlrpcs', $key, false );
		add_user_meta( $user->ID, "_xmlrpcs_secret_{$key}", $secret, true );
		add_user_meta( $user->ID, "_xmlrpcs_app_{$key}", $app_name, true );

		// Return our data to the requestor
		return array(
			'app'    => $app_name,
			'key'    => $key,
			'secret' => $secret
		);
	}
}
<?php
/*
CSL Dashboard Chat
Based onWP Dashboard Chat (nicholasbosch.com/wp-dashboard-chat) v.1.0.3, by Nicholas Bosch, under GPLv2 or later license
*/

class CSL_Dashboard_Chat {

	private $table_name;
	private $current_user;
	private $ajax;
	private $action;
	private $options;
	private $defaults;
	private $rolls = array(
		'Administrator' => 'activate_plugins',
		'Editor'        => 'moderate_comments',
		'Author'        => 'edit_published_posts',
		'Contributor'   => 'edit_posts',
		'Subscriber'    => 'read'
	);
    public $text_domain;
	
	/*
	function CSL_Dashboard_Chat($ajax = false) {
		$this->__construct($ajax);
	}
	*/
	function __construct( $ajax = false, $text_domain = CSL_TEXT_DOMAIN_PREFIX ) {
		global $current_user, $wpdb;
		wp_get_current_user();
		
		$this->register_table();
		$this->install();
		$this->table_name = $wpdb->xtr_dashboard_chat_log;
		$this->current_user = $current_user;
		$this->ajax = $ajax;
		$this->text_domain = $text_domain;
		$this->action = isset($_REQUEST['fn']) ? $_REQUEST['fn'] : null;
		
		$this->defaults = array(
			'hist_len' => 50,
			'title'    => __( 'Dashboard Chat', $this->text_domain )
		);
		
		$this->options = array(
			'hist_len'           => get_option( "csl_dashboard_chat_hist_len" ),
			'title'              => get_option( "csl_dashboard_chat_title" )
		);
		
		if ( $this->ajax ) {
			$this->ajax();
		} else {
			$this->display();
		}
	}
	
	private function register_table( ) {
		global $wpdb;
		$wpdb->xtr_dashboard_chat_log = "{$wpdb->prefix}xtr_dashboard_chat_log";
	}

	private function install( ) {
		global $wpdb;
		global $charset_collate;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		//Call this manually as we may have missed the init hook
	    //NOTICE: Mandatory DOUBLE SPACE AFTER "PRIMARY KEY". So, it must be read as "PRIMARY KEY  (XXX)"
		$sql_create_table = "CREATE TABLE {$wpdb->xtr_dashboard_chat_log} (
			id bigint(20) NOT NULL AUTO_INCREMENT, 
			user_id bigint(20) NOT NULL, 
			date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, 
			content text NOT NULL, 
			checked bigint(2) unsigned NOT NULL default '0', 
			PRIMARY KEY  (id)
			) $charset_collate; ";
		dbDelta($sql_create_table);  
	}
	
	private function ajax() {
		global $wpdb;
		if (wp_verify_nonce($_REQUEST['nonce'], 'message_nonce') ) {
			switch($this->action) {
				case 'add_message':
					$message = $this->addMessage($_REQUEST['message']);
					echo $wpdb->insert_id;
				break;
				case 'delete_message':
					$this->deleteMessage($_REQUEST['id']);
					echo '1';
				break;
				case 'get_message':
					$this->render_message($this->getMessageById($_REQUEST['id']));
				break;
				case 'refresh':
					foreach ($this->listMessages() as $message) {
						if ($message->id > $_REQUEST['id']) {
							$this->render_message($message);
						}
					}
				break;
			}
		} else {
			die('e');
		}
		die();
	}
	
	private function enqueue() {
		wp_register_script( 'csl-dashboard-chat', get_template_directory_uri() . '/assets/js/csl-be-dashboard-chat.js', array('jquery'), '', true );
        $a_tr_CH_scripts = array(
            'error' =>  __( 'An error has occured. Please try again.', $this->text_domain ),
        );
		wp_localize_script( 'csl-dashboard-chat', 'chTXT', $a_tr_CH_scripts );
    	wp_enqueue_script( 'csl-dashboard-chat' );

		wp_register_style( 'csl-dashboard-chat', get_template_directory_uri() . '/assets/css/csl-chat.css' );
    	wp_enqueue_style( 'csl-dashboard-chat' );  
	}
	
	private function display() {
 		
 		$this->enqueue();
 		
		$nonce = wp_create_nonce('message_nonce');
		
		echo "<div id=\"chat_wrapper\"><table id=\"messages\">";
		
		foreach ($this->listMessages() as $message) {
			$this->render_message($message);
		}
		
		echo "</table></div>";
		
		echo '<form id="new_message" class="" method="post" action="' . $_SERVER['PHP_SELF'] . '">';
    	echo '<textarea id="message" name="message"></textarea>';
    	echo '<input type="hidden" id="message_nonce" name="nonce" value="' . $nonce . '" />';
    	echo '<input type="submit" id="submit_message" name="submit_message" value="' . __( 'Send message', $this->text_domain ) . '" class="button-primary" />';
    	echo '<input type="button" id="scroll" name="scroll" value="' . __( 'Scroll to bottom', $this->text_domain ) . '" class="button" />';
    	echo '<input type="button" id="refresh" name="refresh" value="' . __( 'Refresh message list', $this->text_domain ) . '" class="button" />';
    	echo '</form>';
	}
	
	private function render_message($message) {
		date_default_timezone_set( get_option('timezone_string') );
		$author = get_userdata($message->user_id);
		$last_activity = csl_get_user_activity_status( get_current_user_id(), ' AND activity = "read_chat_messages"' )['last_activity'];
		$class_name = $message->date > strtotime($last_activity) ? ' settings-values' : '';
		echo "<tr id=\"message_" . $message->id . "\" data-mid=\"" . $message->id . "\">";
		echo '<td class="avatar' . $class_name . '">' . get_csl_local_avatar( $message->user_id , 32 ) . '</td>';
		echo '<td class="message' . $class_name . '">';
		echo nl2br(stripslashes($this->prettify($message->content)));
		echo '<span class="meta">' . 
			sprintf( 
				__( '%s. %s ago', $this->text_domain ),
				$author->display_name,
				human_time_diff( $message->date, time() )
			) . 
			'.</span>';
		echo '</td><td class="actions' . $class_name . '">';
		if ($message->user_id == $this->current_user->ID ) {
			echo '<a href="javascript:void(0)" class="del" data-mid="' . $message->id . '"></a>';
		} else {
			echo '<a href="javascript:void(0)" class="rep" data-mid="' . $message->id . '" username="' . $author->nickname . '"></a>';
		}
		echo '</td></tr>';
	}
	
	private function listMessages() {
		global $wpdb;
		return array_reverse($wpdb->get_results("
			SELECT 
				id,
				user_id,
				UNIX_TIMESTAMP(date) AS date,
				content 
			FROM " . $this->table_name . " 
			ORDER BY 
				id DESC 
			LIMIT " . $this->options['hist_len']));
	}
	
	private function addMessage($message) {
		global $wpdb;
		$message = $wpdb->prepare($message);
		$rows_affected = $wpdb->insert( $this->table_name, array( 
			'date' => current_time('mysql', 0),
			'user_id' => $this->current_user->ID,
			'content' => $message
		));
		return $rows_affected;
	}
	
	private function updateMessage($message, $id) {
		global $wpdb;
		$message = $wpdb->prepare($message);
		$rows_affected = $wpdb->update( $this->table_name, array( 
			'date' => current_time('mysql', 0),
			'user_id' => $this->current_user->ID,
			'content' => $message
		), array(
			'id' => $id
		));
	}
	
	private function deleteMessage($id) {
		global $wpdb;
		return $wpdb->query("DELETE FROM " . $this->table_name . " WHERE id = $id") ? true : false;
	}
	
	private function getMessageById($id) {
		global $wpdb;
		return $wpdb->get_row("SELECT * FROM " . $this->table_name . " WHERE id = $id");
	}
	
	private function prettify($msg) {
		$msg = strip_tags($msg, '<u><s><q><em><code><cite><caption><b><strong><strike><i><ul><li><ol><p><a><blockquote><br><pre>');
		$msg = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $msg);
		$msg = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $msg);
		//$msg = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $msg);
		//$msg = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $msg);
		//$msg = preg_replace("/@(\w+)/", "<a href=\"http://twitter.com/\\1\" target=\"_blank\">@\\1</a>", $msg);
		$msg = preg_replace("/@(\w+)/", "<a href=\"#\">@\\1</a>", $msg);
		//$msg = preg_replace("/@(\w+)/", "<a href=\"http://www.vambient.com/author/\\1\" target=\"_blank\">@\\1</a>", $msg);
		//$msg = preg_replace("/#(\w+)/", "<a href=\"http://www.vambient.com/?s=\\1\" target=\"_blank\">#\\1</a>", $msg);
		return wp_kses( $msg, array() );
	}
		
}

function ajax() {
	$chat = new CSL_Dashboard_Chat(true);
}

add_action('wp_ajax_dashboard_chat', 'ajax');

?>
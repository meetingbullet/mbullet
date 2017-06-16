// Update comment time continuously every minute
var updateCommentHumanizedTimeInterval = setInterval(updateCommentHumanizedTime, 60000);

// Update decider every 5 sec
var updateCommentInterval = setInterval(update_comment_data, 5000);

// Prevent duplicate binding function
$(document).off('.preview');

$(document).on('shown.bs.modal.preview', '#meeting-preview-modal', function(){
	// Read more Goal
	$('#meeting-preview-modal .goal').readmore({
		speed: 300,
		moreLink: '<a class=\'readmore rm-more\' href="#"><?php e(lang('show_more'))?></a>',
		lessLink: '<a class=\'readmore rm-less\' href="#"><?php e(lang('show_less'))?></a>',
	});

	updateCommentHeight();
	updateCommentHumanizedTime();
})

$(document).on('hide.bs.modal.preview', '#meeting-preview-modal', function(){
	clearInterval(updateCommentInterval);
	clearInterval(updateCommentHumanizedTimeInterval);
})

$(document).on('mouseleave.preview', '#comment .list-user-single.unread', function() {
	$(this).removeClass('unread');

	if ($('#comment .list-user-single.unread').length == 0) {
		$('#comment .badge-comment').fadeOut();
	}
})

// Click on comment badge will hide it and scroll to first unread message?
$(document).on('click.preview', '.badge-comment', function() {
	// Tho we need to scroll to the first unread message, yet I didn't figure out how to do it
	$('#comment-body').animate({
		scrollTop: $('#comment-body')[0].scrollHeight
	}, 200);

	$(this).fadeOut();
});

$(document).on('keypress.preview', '#send-comment', function(e) {
	var keyCode = e.keyCode || e.which;

	// On Enter
	if (keyCode == 13) {
		e.preventDefault();
		sendComment();
		return false;
	}
});

$(document).on('click.preview', '.btn-send-comment', function(e) {
	e.preventDefault();
	sendComment();
	return false;
})

function updateCommentHeight() {
	$('#meeting-preview-modal #comment .an-lists-body').height(
		$('#meeting-info').outerHeight() 
		- $('#meeting-preview-modal #comment .an-component-header').outerHeight() 
		- $('#meeting-preview-modal #comment .an-chat-form').outerHeight() 
		- 30 // Margin of the left collumn
	);

	$('#comment-body').scrollTop($('#comment-body')[0].scrollHeight);
}

/*
	Real-time insert comments

	@param comment {
		(string) full_name
		(string) avatar_url
		(bool)  mask_as_read // Highlight comment
		(string) created_on // Mysql formated Datetime 
		(string) comment // Content of the comment
		[(bool) is_owner = false] // Show Owner indicator
		[(string) show_notification = false] : Either show small badge count how much unread message or not
	}

	Ex: {
		full_name: "Hoang Duc Viet",
		avatar_url: "https://lh3.googleusercontent.com/-mQ34yrQ9rtA/AAAAAAAAAAI/AAAAAAAAAIo/g2dMArHMgyw/photo.jpg",
		mask_as_read: false,
		created_on: "2017-06-15 11:14:15",
		comment: "For all that you want and all you have don't seem so much"
	}
*/
function insertComment(comment) {
	var scrollToBot = false;

	// Scroll is at bottom
	if (	$('#comment-body')[0] && 
			$('#comment-body').scrollTop() + $('#comment-body').innerHeight() == $('#comment-body')[0].scrollHeight) {
		scrollToBot = true;
	} 

	$( $('#single-comment').render(comment) ).appendTo('#comment-body').slideDown();

	if (scrollToBot) {
		$('#comment-body').animate({
			scrollTop: $('#comment-body')[0].scrollHeight
		}, 600);
	}

	if ( !scrollToBot && !comment.mask_as_read) {
		$('#comment .badge-comment .number').text($('#comment .list-user-single.unread').length).fadeIn();
		$('#comment .badge-comment').fadeIn();
	}
}

function updateCommentHumanizedTime() {
	$('#comment .time[data-created-on]').each(function(index, item) {
		$(item).text( moment($(item).data('created-on')).fromNow() );
	});
}

function sendComment() {
	if ($('#send-comment').val().trim().length == 0) return;

	$.post('<?php echo site_url('meeting/comment') ?>', {comment: $('#send-comment').val().trim(), meeting_id: <?php e($meeting_id) ?>}, function(data) {
		data = JSON.parse(data);

		if (data.message_type != 'success') {
			$.mbNotify(data.message, data.message_type);
			return;
		}

		insertComment({
			id: data.data.id,
			full_name: $('#send-comment').data('my-full-name'),
			avatar_url: $('#send-comment').data('my-avatar-url'),
			mask_as_read: true,
			created_on: moment().format('YYYY-MM-DD HH:mm:ss'),
			comment: $('#send-comment').val().trim(),
			is_owner: $('#send-comment').data('i-am-owner')
		});

		// Clear input
		$('#send-comment').val('');
	}).fail(function() {
		$.mbNotify('<?php echo lang('mt_something_went_wrong_please_refresh_and_try_again') ?>', 'danger');
	});
}

function update_comment_data()
{	
	var commentOffset = $('#comment-body .list-user-single:last').data('id') 
						? $('#comment-body .list-user-single:last').data('id') 
						: 0;

	$.post('<?php echo site_url('meeting/get_comment_data/' . $meeting_id) ?>', {commentOffset}, function(data) {
		data = JSON.parse(data);

		if (data.message_type != 'success') {
			$.mbNotify(data.message, data.message_type);
			return;
		}

		// Comments processing
		data.data.comments.forEach(function(element) {
			insertComment(element);
		});
	});
}
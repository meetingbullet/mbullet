// Update comment time continuously every minute
var updateCommentHumanizedTimeInterval = setInterval(updateCommentHumanizedTime, 60000);

// Update decider every 5 sec
var updateCommentInterval = setInterval(update_comment_data, 5000);

// Enable jQuery tooltip
$('[data-toggle="tooltip"]').tooltip(); 

// Prevent duplicate binding function
$(document).off('.decider');

$(document).on('shown.bs.modal.decider', '#meeting-decider-modal', function(){
	// Read more Goal
	$('#meeting-decider-modal .goal').readmore({
		speed: 300,
		moreLink: '<a class=\'readmore rm-more\' href="#"><?php e(lang('show_more'))?></a>',
		lessLink: '<a class=\'readmore rm-less\' href="#"><?php e(lang('show_less'))?></a>',
	});

	updateCommentHeight();
	updateCommentHumanizedTime();
})

$(document).on('hide.bs.modal.decider', '#meeting-decider-modal', function(){
	clearInterval(updateCommentInterval);
	clearInterval(updateCommentHumanizedTimeInterval);
})

$(document).on('mouseleave.decider', '#comment .list-user-single.unread', function() {
	$(this).removeClass('unread');

	if ($('#comment .list-user-single.unread').length == 0) {
		$('#comment .badge-comment').fadeOut();
	}
})

// Click on comment badge will hide it and scroll to first unread message?
$(document).on('click.decider', '.badge-comment', function() {
	// Tho we need to scroll to the first unread message, yet I didn't figure out how to do it
	$('#comment-body').animate({
		scrollTop: $('#comment-body')[0].scrollHeight
	}, 200);

	$(this).fadeOut();
});

$(document).on('keypress.decider', '#send-comment', function(e) {
	var keyCode = e.keyCode || e.which;

	// On Enter
	if (keyCode == 13) {
		e.preventDefault();
		sendComment();
		return false;
	}
});

$(document).on('click.decider', '.btn-send-comment', function(e) {
	e.preventDefault();
	sendComment();
	return false;
})

// Scroll #Comment follow Goal column
$('.form-meeting-decider').on('scroll.decider', function() {
	var form_padding = parseInt($('.form-meeting-decider').css('padding'), 10);
	var header_height = $('.form-meeting-decider .an-body-topbar').outerHeight();
	var offset = form_padding + header_height;

	if ($(this).scrollTop() > offset) {
		$('#comment').css('position', 'absolute');
		$('#comment').css('top', $(this).scrollTop() - offset);
		$('#comment').css('padding-right', 15);
	} else {
		$('#comment').css('position', 'relative');
		$('#comment').css('top', 0);
		$('#comment').css('padding-right', 0);
	}
});

$(document).on('submit.decider', '.form-meeting-decider', function(e) {
	console.log('submitted');
	// Validation
	var is_valid = true;

	if ($('#rating-form').serializeArray().length != $('#rating-form ul li').length) {
		is_valid = false;
	}

	$('.form-meeting-decider .confirmation-status').each((i, item) => {
		if ($(item).val() === null) {
			$(item).addClass('danger');
			is_valid = false;
		} else {
			$(item).removeClass('danger');
		}
	});

	if ( ! is_valid) {
		$.notify({
			message: '<?php e(lang('st_please_select_all_confirmation_status'))?>'
		}, {
			type: 'danger',
			z_index: 1051
		});
		return false;
	}

	$.post($(this).attr('action'), $(this).serialize() + '&' + $('#rating-form').serialize(), (result) => {
		var data = JSON.parse(result);

		if (data.message_type) {
			$.mbNotify(data.message, data.message_type);
		}

		if (data.message_type == 'success') {
			$('#meeting-decider-modal').modal('hide');

			/* 
				If one of the agendas is marked as Open Parking Lot the meeting owner is redirected to 
				the Meeting creation screen and prompted to create a new meeting to resolve the Closed Parking Lot agenda.
			*/
			if ($('.confirmation-status option[value="open_parking_lot"]:selected').length > 0) {
				$.post('<?php e(site_url('meeting/create/' . $project_key)) ?>', {from_meeting: '<?php e($meeting_id) ?>'}, (data) => {
					data = JSON.parse(data);
					$('#create-meeting .modal-content').html(data.modal_content);
					$('#create-meeting').modal({backdrop: "static"});

					// Open Evaluator for Owner
					$('#create-meeting').on('hidden.bs.modal', function () {
						// merge evaluator for owner and decider screen
						// @Bao: Open Evaluator for Owner
						// $.mbOpenModalViaUrl('meeting-evaluator-modal' , "<?php e(site_url('meeting/evaluator/' . $meeting_key)) ?>", 'modal-80');
					});
				});
			} else {
				// merge evaluator for owner and decider screen
				// @Bao: Open Evaluator for Owner
				// $.mbOpenModalViaUrl('meeting-evaluator-modal' , "<?php e(site_url('meeting/evaluator/' . $meeting_key)) ?>", 'modal-80');
			}
		}
	})

	return false;
});

function updateCommentHeight() {
	$('#meeting-decider-modal #comment .an-lists-body').height(
		$('#meeting-info').outerHeight() 
		- $('#meeting-decider-modal #comment .an-component-header').outerHeight() 
		- $('#meeting-decider-modal #comment .an-chat-form').outerHeight() 
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
	if ( $('#comment-body').scrollTop() + $('#comment-body').innerHeight()  == $('#comment-body')[0].scrollHeight) {
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

$(document).on("click.decider", ".rating label", function(){
	$(this).parent().find("label").css({"color": "#D8D8D8"});
	$(this).css({"color": "#FFED85"});
	$(this).nextAll().css({"color": "#FFED85"});
	var input_id = $(this).attr('for');
	$(this).parent().find('#' + input_id).click();
});
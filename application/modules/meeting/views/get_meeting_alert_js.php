$(document).ready(function() {
	setInterval(function() {
		console.log('get meeting alert...');
		$.get('<?php echo site_url('meeting/get_meeting_alert') ?>').done(function(data) {
			data = JSON.parse(data);
			if (data.html != '') {
				if ($('.meeting-alert').length) {
					if ($('.meeting-alert div.alert').length) {
						var alert = $(data.html).html();
						if ($('.meeting-alert div.alert').data('meeting-id') != $(alert).data('meeting-id') && $('.meeting-alert div.alert').data('alert-type') != $(alert).data('alert-type')) {
							$('.meeting-alert div.alert').fadeOut(400, function() {
								$('.meeting-alert').html(alert).find('div.alert').fadeIn();
								$(window).trigger('mbChangeHeight');
							});

							console.log('new meeting alert...');
						}
					}
				} else {
					if ($('.alert-wrapper').length) {
						$('.alert-wrapper').prepend(data.html).find('.meeting-alert div.alert').fadeIn(400, function() {
							$(window).trigger('mbChangeHeight');
						});
					} else {
						$('.main-wrapper .an-page-content .an-content-body').prepend(data.html).find('.meeting-alert div.alert').fadeIn(400, function() {
							$(window).trigger('mbChangeHeight');
						});
					}

					console.log('new meeting alert...');
				}
			} else {
				$('.meeting-alert').fadeOut(400, function() {
					$('.meeting-alert').remove();
					$(window).trigger('mbChangeHeight');
				});

				console.log('no meeting alert...');
			}
		});
	}, 300000)
});
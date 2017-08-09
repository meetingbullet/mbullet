<?php if (has_permission('Project.Edit.All')): ?>
$(document).on('click', '.enable-edit-title', function(e){
	e.preventDefault();
	e.stopPropagation();
	$('.an-sidebar-nav .project-title').editable('toggle');
})
<?php endif; ?>

$('.mb-popover-project').click(function(e){
	e.preventDefault();
})

$('.mb-popover-project.new').click(function(e){
	var that = this;

	$.get('<?php echo site_url('dashboard/mark_as_read/project/') ?>' + $(this).data('project-id'), (data) => {
		data = JSON.parse(data);

		if (data.message_type == 'success') {
			$(that).removeClass('new');

			// Remove remaining My Project [new]
			if ($('.badge-new').length == 2) {
				$('.badge-new').fadeOut('fast', function(){
					$(this).remove();
				});
			} else {
				$(that).find('.badge-new').fadeOut('fast', function(){
					$(this).remove();
				});
			}
		}
	})
});

$(document).on('click', '#homework-content .child.new', function(e){
	var that = this;

	$.get('<?php echo site_url('dashboard/mark_as_read/homework/') ?>' + $(this).data('homework-id'), (data) => {
		data = JSON.parse(data);

		if (data.message_type == 'success') {
			var tr = 'tr[data-homework-id="'+ $(that).data('homework-id') +'"].child';
			$(tr).removeClass('new');

			// Remove remaining menu Homework [new] & My Todo new if all Rates has been read
			if ($('.badge-homework-new').length == 3) {
				$('.badge-homework-new').fadeOut('fast', function(){
					$(this).remove();
				});

				if ($('.badge-rate-new').length == 0) {
					$('.badge-todo-new').remove(); 
				}
			} else {
				$(tr + ' .badge-homework-new').fadeOut('fast', function(){
					$(this).remove();
				});
			}
		}
	})
});

$(document).on('click', '#rate-content .child.new', function(e){
	var that = this;
	var type = $(this).data('mode');
	var object_id = $(this).data('id');
	var user_id = "";

	if (type == 'user') {
		object_id = $(this).data('meeting-id');
		user_id += "/" + $(this).data('id');
	}

	$.get("<?php echo site_url('dashboard/mark_as_read/') ?>"+ type + "/" + object_id + user_id, (data) => {
		data = JSON.parse(data);

		if (data.message_type == 'success') {
			var tr = 'tr[data-id="'+ $(that).data('id') +'"].child.' + type;
			$(tr).removeClass('new');

			// Remove remaining menu Homework [new] & My Todo new if all Rates has been read
			if ($('.badge-rate-new').length == 3) {
				$('.badge-rate-new').fadeOut('fast', function(){
					$(this).remove();
				});

				if ($('.badge-homework-new').length == 0) {
					$('.badge-todo-new').remove(); 
				}
			} else {
				$(tr + ' .badge-rate-new').fadeOut('fast', function(){
					$(this).remove();
				});
			}
		}
	})
});

$(document).on('click', '.btn-confirm-homework', function() {
	var hw_id = $(this).data('homework-id');

	$.post("<?php echo site_url('homework/ajax_edit') ?>", {
		pk: hw_id,
		name: 'status',
		value: 'done'
	}, (data) => {
		data = JSON.parse(data);
		$.mbNotify(data.message, data.message_type);

		$(this)
		.parents('.child')
		.find('td')
		.wrapInner('<div style="display: block;" />')
		.parent()
		.find('td > div')
		.slideUp('fast', function(){
			$(this).parent().parent().remove();
		});

		$('#homework-popover tr.child[data-homework-id="'+ hw_id +'"]').remove();
		$('.homework-counter').text($('.homework-counter').text() - 1);
	})
})

// rating
$(document).on('click', '.todo-rating label', function(){
	$(this).parent().find("label").css({"color": "#D8D8D8"});
	$(this).css({"color": "#FFED85"});
	$(this).nextAll().css({"color": "#FFED85"});
	var input_id = $(this).attr('for');
	$(this).parent().find('input[type=radio]').removeAttr('checked');
	$(this).parent().find('input[type=radio]#' + input_id).attr('checked', '');
});

// evaluate
$(document).on("click", "#rate-content .submit", function(e) {
	e.preventDefault();
	var submit_btn = $(this);

	var todo_type = 'evaluate';

	var url = submit_btn.closest('.child').find('.data').data('url');

	var data = {};
	data.rate = submit_btn.closest('.child').find('.data').find('input[type=radio]:checked').val();

	if (typeof(data.rate) != 'undefined') {
		data.meeting_id = submit_btn.closest('.child').find('.data').data('id');

		if (submit_btn.closest('.child').hasClass('user')) {
			data.user_id = submit_btn.closest('.child').find('.data').data('id');
		}

		if (submit_btn.closest('.child').hasClass('agenda')) {
			data.agenda_id = submit_btn.closest('.child').find('.data').data('id');
		}

		if (submit_btn.closest('.child').hasClass('homework')) {
			data.homework_id = submit_btn.closest('.child').find('.data').data('id');
		}
	} else {
		var error = '<?php echo lang("db_rate_needed") ?>';
	}

	if (typeof(error) == 'undefined') {
		$.post({
			url: url,
			data: data,
		}).done(function(data) {
			data = JSON.parse(data);
			if (data.message_type == 'success') {
				$(submit_btn)
				.parents('.child')
				.find('td')
				.wrapInner('<div style="display: block;" />')
				.parent()
				.find('td > div')
				.slideUp('fast', function(){
					$(this).parent().parent().remove();
				});
			}

			$.notify({
				message: data.message
			}, {
				type: data.message_type,
				z_index: 1051
			});
		}).fail(function(xhr, statusText) {
			$.notify({
				message: data.message
			}, {
				type: data.message_type,
				z_index: 1051
			});
		});
	} else {
		$.notify({
			message: error
		}, {
			type: 'danger',
			z_index: 1051
		});
	}

});

$(document).on('click', '.btn-join-project', function() {
	var that = this;

	$.get("<?php echo site_url('project/join/') ?>" + $(this).data('project-id'), (data) => {
		data = JSON.parse(data);

		if (data.message_type == 'danger') {
			$.mbNotify(data.message, data.message_type);
			return;
		}

		$(that).text($(that).data('lang-joined'));
		$(that).prop('disabled', true);

		var currentTeam = $(that).closest('.popover').find('.team-wrapper .number').text();
		$(that).closest('.popover').find('.team-wrapper .number').text(parseInt(currentTeam )+ 1);
	})
})

$('.mb-popover-project').popover({
	html: true,
	content: function() {
		var that = this;

		$.get("<?php echo site_url('dashboard/get_project_detail/') ?>" + $(that).data('project-id'), function(data) {
			data = JSON.parse(data);

			data.project_id = $(that).data('project-id');
			data.name = $(that).data('name');
			data.owned_by_x = $(that).data('owned');
			data.cost_code = $(that).data('cost-code');
			data.team = $(that).data('team');
			data.type = $(that).data('type');

			renderPopover(that, data);
		})

		return "<div class='popover-loading'><?php echo lang('db_loading') ?></div>";
	}
});


$(document).on('show.bs.popover', function (e) {
	// Close popover project on click another one
	if ($('.an-sidebar-nav .popover.in').length > 0) {
		$('.mb-popover-project, #private-meeting-list').not(e.target).popover('hide');
	}

	// Add body blur
	if ($(e.target).hasClass('mb-popover-project, #private-meeting-list')) {
		$('.an-page-content').addClass('mb-blur');
	}
});

$(document).on('hidden.bs.popover', function (e) {
	$(e.target).data("bs.popover").inState.click = false;
	$(e.target).data("bs.popover").secondCall = false;

	// Add body blur
	if ($(e.target).hasClass('mb-popover-project')) {
		$('.an-page-content').removeClass('mb-blur');
	}
});

$(document).click(function(e) {
	if ($('.an-sidebar-nav .popover.in').length > 0
		&& $(e.target).closest('.an-page-content').length > 0) {

		$('.mb-popover-project').popover('hide');
	}
});

$(document).on('click', '.btn-remove-member', function(e){
	var that = this;
	swal({
		title: '<?php echo lang("db_are_you_sure") ?>',
		text: "<?php echo lang('db_remove_member_message') ?>",
		type: 'warning',
		html: true,
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: '<?php echo lang("db_yes_remove_x") ?>'.format($(this).data('full-name'))
		}, function () {
		
		$.post('<?php echo site_url("project/remove_member/") ?>', {
			user_id: $(that).data('user-id'),
			project_id: $(that).closest('.mb-popover-content').data('project-id')
		}, (data) => {
			data = JSON.parse(data);
			
			$.mbNotify(data.message, data.message_type);

			if (data.message_type == 'success') {
				$(that).closest('.member').slideUp();
			}
		})
	})
})

function renderPopover(that, data)
{
	// Prevent duplicate rendering due to Popover contents function runs twice?
	if ($('#popover-project').data('rendered') === true) {
		$('#popover-project').data('rendered', false)
		return;
	}

	if (data.message_type == 'danger' ) {
		$.mbNotify(data.message, data.message_type);
		$('#popover-project').data('rendered', false);
		return;
	}

	output = $('#popover-project').render(data, {
		round: function(a, b) {
			if (typeof a !== "number") {
				return 0;
			}

			return Math.round(a * b) / b
		},
		parseFloat,
		countingStars: function(n, icon = "ion-ios-star") {
			str = "";

			for (var i=0; i<n; i++) {
				str+= `<i class="${icon}"></i>\n`;
			}

			return str;
		}
	});

	that.cache = output;
	$(that).next().children('.popover-content').html(output);
	// Testing chart
	var pie = document.getElementById("pie-chart").getContext("2d");
	var progressData = {
		labels: [
			// pieceLabel plugin has problem with its Text width overflows the => hidden
			"<?php echo lang('db_pts_x') ?>".format(Math.round(data.total_used.point)), ""
		],
		datasets: [{
			data: [data.total_used.point, data.allowed_point],
			backgroundColor: [
			"#025d83",
			"#f5f5f5",
			],
			hoverBackgroundColor: [
				"#0080b5",
				"#f5f5f5",
			]
		}]
	};

	var pieChart = new Chart(pie, {
		type: 'doughnut',
		data: progressData,
		options: {
			legend: {
				display: false,
			},
			tooltips: {
				enabled: false
			},
			pieceLabel: {
				mode: 'label'
			},
			title: {
				display: true,
				text: "<?php echo lang('db_overall_x') ?>".format(Math.round(data.total_used.point / data.allowed_point * 100 * 10) / 10)
			},
			elements: {
				arc: {
					borderWidth: 0,
				}
			}
		}
	});

	var statsElement = document.getElementById("stats-chart").getContext("2d");
	var statsData = {
		labels: ["L-3", "L-2", "L-1", "Last"],
		datasets: [{
			label: "Team",
			fill: false,
			backgroundColor: 'rgb(54, 162, 235)',
			borderColor: 'rgb(54, 162, 235)',
			data: data.stats.team,
		}, {
			label: "Rating",
			fill: false,
			backgroundColor: 'rgb(255, 99, 132)',
			borderColor: 'rgb(255, 99, 132)',
			data: data.stats.rate,
		}, {
			label: "Hours",
			backgroundColor: 'rgb(75, 192, 192)',
			borderColor: 'rgb(75, 192, 192)',
			data: data.stats.hour,
			fill: false,
		}]
	};

	var statsChart = new Chart(statsElement, {
		type: 'line',
		data: statsData,
		options: {
			maintainAspectRatio: false,
		}
	});

	$(that).popover('reposition');
	$('#popover-project').data('rendered', true)
}

<?php if ( ! $current_user->inited): ?>
$.mbOpenModalViaUrl('init', "<?php echo site_url('dashboard/init') ?>", 'modal-95');
<?php endif; ?>

/*
	Popover dynamic content re-positioning function
	From: https://stackoverflow.com/a/45092467/3722765
	Usage: $element.popover('reposition')
*/
$.fn.popover.Constructor.prototype.reposition = function () {
	var $tip = this.tip()
	var autoPlace = true

	var placement = typeof this.options.placement === 'function' ? this.options.placement.call(this, $tip[0], this.$element[0]) : this.options.placement

	var pos = this.getPosition()
	var actualWidth = $tip[0].offsetWidth
	var actualHeight = $tip[0].offsetHeight

	if (autoPlace) {
		var orgPlacement = placement
		var viewportDim = this.getPosition(this.$viewport)

		placement = placement === 'bottom' &&
			pos.bottom + actualHeight > viewportDim.bottom ? 'top' : placement === 'top' &&
			pos.top - actualHeight < viewportDim.top ? 'bottom' : placement === 'right' &&
			pos.right + actualWidth > viewportDim.width ? 'left' : placement === 'left' &&
			pos.left - actualWidth < viewportDim.left ? 'right' : placement

		$tip
			.removeClass(orgPlacement)
			.addClass(placement)
	}

	var calculatedOffset = this.getCalculatedOffset(placement, pos, actualWidth, actualHeight)

	this.applyPlacement(calculatedOffset, placement)
}

// Child Nav Active
$('.mb-child-nav').click(function(e) {
	var project_id = $(this).closest('li.project').data('project-id');

	if ($(this).hasClass('active')) {
		bringToTop(1);

		$(this).removeClass('active');
		return;
	}

	// Switch order
	bringToTop($(this).data('order'));

	$('.mb-child-nav').removeClass('active');
	$(this).addClass('active');
});

function bringToTop(order_index, speed = 300)  {
	var row = $('.order-' + order_index);
	var h = row.outerHeight();
	var pos = row.position();

	row.css({
		position: 'absolute',
		left: pos.left,
		top: pos.top
	});
	row.next().css('margin-top', h);
	row.animate({
		top: 0
	}, speed, 'easeOutQuart');
	row.next().animate({
		marginTop: 2
	}, speed, 'easeOutQuart');
	row.parent().animate({
		paddingTop: h
	}, speed, 'easeOutQuart', function() {
		row.parent().css('padding-top', '');
		row.parent().children(':first').before(row);
		row.css('position', 'relative');
		row.siblings().css({
			marginTop: ''
		});

		row.find('.panel-collapse').collapse('show');
	});

	$('.mb-popover-content').animate({
		scrollTop: 0
	}, speed);
}

$('#private-meeting-list').popover({
	html: true,
	content: function() {
		var that = this;

		$.get({ url: '<?php echo site_url('meeting/get_private_meetings') ?>' }).done(function(data) {
			data = JSON.parse(data);
			if (data.status == 1) {
				$(that).next().children('.popover-content').html($('#popover-private-meetings').render(data));
				$(that).popover('reposition');
			}
		});

		return "<div class='popover-loading'><?php echo lang('db_loading') ?></div>";
	}
});

// Meeting invites
$('.invitations a.decision').click(function(e) {
	e.preventDefault();
	var that = $(this);
	$.get($(that).attr('href'), (data) => {
		data = JSON.parse(data);

		$.mbNotify(data.message, data.message_type);
		if (data.message_type === 'success' || data.message_type === 'warning') {
			$(that).closest('.alert').alert('close');
		}
	})
});
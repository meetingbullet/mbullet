Selectize.define('select-member', function(options) {
	var self = this;

	// Override updatePlaceholder method to keep the placeholder
	this.updatePlaceholder = (function() {
		var original = self.updatePlaceholder;
		return function() {
			// do your logic
			return false;
			// return original.apply(this, arguments);
		};
	})();
});

$('.team').selectize({
	plugins: ['remove_button', 'select-member'],
	persist: false,
	maxItems: null,
	valueField: 'id',
	labelField: 'name',
	searchField: ['name'],
	options: [
		<?php foreach($organization_members as $user) :
			if (strstr($user->avatar, 'http') === false) {
				$user->avatar = avatar_url($user->avatar, $user->email);
			}
		?>
		{id: '<?php e($user->user_id)?>', name: '<?php e($user->first_name . ' ' . $user->last_name)?>', avatar: '<?php echo $user->avatar?>'},
		<?php endforeach; ?>
	],
	render: {
		item: function(item, escape) {
			return '<div>' +
				'<img' + (item.avatar ? ' src="' + item.avatar + '"' : '')  + ' class="avatar" />' +
				(item.name ? '<span class="name">' + escape(item.name) + '</span>' : '') +
			'</div>';
		},
		option: function(item, escape) {
			return '<div>' +
				'<img' + (item.avatar ? ' src="' + item.avatar + '"' : '')  + ' class="avatar" />' +
				(item.name ? '<span class="name">' + escape(item.name) + '</span>' : '') +
			'</div>';
		}
	},
	create: false
});

$('#attachment').keypress(function(e) {
	var keyCode = e.keyCode || e.which;

	// On Enter
	if (keyCode == 13) {
		insertAttachment();
		e.preventDefault();
		return false;
	}
}).blur(function() {
	insertAttachment();
});

function insertAttachment()
{
	var url = $('#attachment').val();

	if (url == '') {
		return;
	}

	// Valid URL ?
	if ( ! validateURL(url) ) {
		$.mbNotify('<?php echo lang("hw_invalid_url") ?>', 'danger');
		return;
	}

	// Does this attachment already exist?
	if ( $('.single-attachment a[href="'+ url +'"]').length > 0 ) {
		$.mbNotify('<?php echo lang("hw_duplicated_attachment") ?>', 'danger');
		return;
	}
	
	var name = url.substring(0, 60).length < url.length ? url.substring(0, 60) + '...' : url;
	var attachment_index = Math.round(Math.random() * 1000000);

	// Begin insert
	$('#attachment-data').append(`
		<div class="single-attachment">
			<a href="${url}" class="an-control-btn" target="_blank">
				<span class="icon"><i class="icon-file"></i></span>
				<span class="filename">${name}</span>
			</a>

			<i class="ion-close-round remove-attachment pull-right"></i>

			<input type="hidden" name="attachments[${attachment_index}][url]" value="${url}"/>
			<input type="hidden" name="attachments[${attachment_index}][title]"/>
			<input type="hidden" name="attachments[${attachment_index}][favicon]"/>
		</div>
	`);

	// Clear attachment input
	$('#attachment').val('');

	// Get Title & Icon if possible

	$.get(url, function(data) {

		if (title = parseTitle(data)) {
			$('.single-attachment a[href="'+ url +'"] .filename').text(title);
			$(`.single-attachment input[name="attachments[${attachment_index}][title]"]`).val(title);
		}

		if (favicon = parseFavicon(url, data)) {
			$('.single-attachment a[href="'+ url +'"] .icon').html(`<img src="${favicon}"/>`);
			$(`.single-attachment input[name="attachments[${attachment_index}][favicon]"]`).val(favicon);
		} else {
			// Try to get default favicon.ico location
			var link = document.createElement("a");
			link.href = url;

			$.get(link.origin + '/favicon.ico', function() {
				var favicon = link.origin + '/favicon.ico';
				$('.single-attachment a[href="'+ url +'"] .icon').html(`<img src="${favicon}"/>`);
				$(`.single-attachment input[name="attachments[${attachment_index}][favicon]"]`).val(favicon);
			});
		}
	});
}

$(document).on('click.mb', '.remove-attachment', function() {
	var parent = $(this).parent();

	$(this).parent().slideUp(400, function() {
		$(parent).remove();
	});
})

function validateURL(textval) {
	var urlregex = /^(https?|ftp):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/;
	return urlregex.test(textval);
}

function parseTitle(html) {
	var matches = html.match(/<title>(.*?)<\/title>/);

	if (matches && matches.length > 1) {
		return matches[1].trim();
	}

	return null;
}

function parseFavicon(url, html) {
	// Get the 'href' attribute value in a <link rel="icon" ... />
	// Also works for IE style: <link rel="shortcut icon" href="http://www.example.com/myicon.ico" />
	// And for iOS style: <link rel="apple-touch-icon" href="somepath/image.ico">
	// Search for <link rel="icon" href="http://example.com/icon.png" />
	var matches = html.match(/<link.*?rel=("|\').*icon("|\').*?href=("|\')(.*?)("|\')/i);

	if (matches && matches.length > 4) {
		return matches[4].trim();
	}

	// Order of attributes could be swapped around: <link href="http://example.com/icon.png" rel="icon" />
	matches = html.match(/<link.*?href=("|\')(.*?)("|\').*?rel=("|\').*icon("|\')/i);

	if (matches && matches.length > 2) {
		return matches[2].trim();
	}
	// No match
	return null;
}
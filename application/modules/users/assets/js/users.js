$(document).ready(function() {
    // Set avatar preview as user input
    if (document.getElementById('user-avatar')) {
        document.getElementById('user-avatar').onchange = function(evt) {
            var tgt = evt.target || window.event.srcElement,
                files = tgt.files;

            // FileReader support
            if (FileReader && files && files.length) {
                var fr = new FileReader();
                fr.onload = function() {
                    document.getElementById("user-avatar-preview").src = fr.result;
                }
                fr.readAsDataURL(files[0]);
            }
            // Not supported
            else {
                // fallback -- perhaps submit the input to an iframe and temporarily store
                // them on the server until the user's session ends.
            }
        }
    }

	if (getParameterByName('code') != '' && getParameterByName('code') != null && (getParameterByName('timezone') == '' || getParameterByName('timezone') == null)) {
		// location.href = location.href + '&timezone=' + encodeURIComponent(get_local_timezone());
		location.href = location.href.split('?')[0] + '?timezone=' + encodeURIComponent(get_local_timezone()) + '&code=' + encodeURIComponent(getParameterByName('code'))
		// console.log(location.href.split('?')[0] + '?timezone=' + encodeURIComponent(get_local_timezone()) + '&code=' + encodeURIComponent(getParameterByName('code')));
	}
});

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function get_local_timezone() {
	var offset = -(new Date().getTimezoneOffset() / 60);

	if (offset > 0) {
		var timezone = 'UP' + decimal_convert(offset);
	} else if (offset < 0) {
		var timezone = 'UM' + decimal_convert(offset);
	} else {
		var timezone = 'UTC';
	}
	return timezone;
}

function decimal_convert(number) {
	number = Math.abs(number);
	if (number == parseInt(number)) {
		return number;
	}

	array_number = number.toString().split('.');
	return number * Math.pow(10, (array_number[1].length));
}
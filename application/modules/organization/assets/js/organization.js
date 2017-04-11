$(document).ready(function() {
	$('input#input_trigger').bind('keyup change', function() {
		var url = $(this).val().trim().replace(/[^A-Z0-9]+/ig, "_").toLowerCase();
		$('input#input_triggered').val(url);
	});
});
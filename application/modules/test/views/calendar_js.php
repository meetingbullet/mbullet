$('#date-time').datetimepicker({
                sideBySide: true
            });

$('#date-time').on('dp.change', function(e) {
	console.log(e)
});
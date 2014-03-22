$(document).ready(function() {
	$(window).resize(function() {
		var colWidth = ($(window).width() - 154 - 50 - 90) / 2;
		$('#col1, #col2').css('width', colWidth + 'px');
	});
	
	$(window).resize();
});
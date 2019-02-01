$(function () {
	$('.ui.dropdown').dropdown();

	$('.menu .ui.dropdown').dropdown({on: 'hover'});

	$('#toggle-sidebar-button').click(function () {
		$('.ui.sidebar').sidebar('toggle');
	});

	$("#gallery").unitegallery({
		gallery_theme: "compact",
		theme_panel_position: "right"
	});
});

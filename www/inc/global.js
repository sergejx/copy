function toggle_div(classname) {
	var div = document.getElementById(classname);
    if(div.style.display == 'none') {
			div.style.display = 'block';
		} else {
			div.style.display = 'none';
		}
}

/* Resize image according to container size */
function resizeImage() {
    var width = $("#preview").width() - 22;
    var margin = 0;
    var image = $("#preview img");
    if (width > image.attr('width')) {
        margin = (width - image.attr('width')) / 2
        width = image.attr('width');
    }
    $("#preview img").width(width);
    $("#preview img").css('margin-left', margin);
}

$(function() {
    if ($("#preview").length > 0) {
        resizeImage();
        $(window).resize(resizeImage);
    }
});

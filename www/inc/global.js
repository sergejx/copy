function toggle_div(classname) {
	var div = document.getElementById(classname);
    if(div.style.display == 'none') {
			div.style.display = 'block';
		} else {
			div.style.display = 'none';
		}
}

window.onkeyup = function(event) {
    console.log(prev, next);
    var prev = document.getElementById('previcon');
    var next = document.getElementById('nexticon');

    switch (event.keyCode) {
    case 37: // <-
        if (prev != null) {
            window.location = prev.href + '#image';
        }
        break;
    case 39: // ->
        if (next != null)
            window.location = next.href + '#image';
        break;
    }
};

$(document).ready(function() {
    $(".bigthumbnails").justifiedGallery({
        margins: 10,
        captions: false
    });
});

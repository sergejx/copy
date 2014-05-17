function toggle_div(classname) {
	var div = document.getElementById(classname);
    if(div.style.display == 'none') {
			div.style.display = 'block';
		} else {
			div.style.display = 'none';
		}
}


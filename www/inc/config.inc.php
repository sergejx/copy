<?php
# dir index
$sortinmonth = 0; // 1 - alphabetically
                  // 0 - by date (reverse)

# default languages
# use UA's accept language
require_once("inc/l10nget.inc.php"); //get from UA
if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
	$sclang = get_lang_from_agent($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
} else {
	$sclang = "en";
}

#Name to display on the gallery
$scnamegallery = "Photo Gallery Index";

# albums to show
$yearsince = 1999;

# Photos Copyright
$copy = "Copyright &copy; YEAR NAME";

# EXIF info to show
$exif_show = array("DateTime"=>__("Time Taken"),
                   "Make"=>__("Camera Manufacturer"),
                   "Model"=>__("Camera Model"),
                   "FocalLength"=>__("Real Focal Length"),
                   "FocalLengthIn35mmFilm"=>__("Focal Length Relative to 35mm Film"),
                   "FNumber"=>__("F Stop"),
                   "ExposureTime"=>__("Time of Exposure"),
                   "ISOSpeedRatings"=>__("Film/Chip Sensitivity"),
                   "Flash"=>__("Flash"));

## Gallery Directory
# This is a path relative to the directory where COPY is installed
# eg. it can be "../galleries" to use a galleries dir above the COPY dir.
$gallery_dir="galleries";

#css styles
$theme = "inc/styles/dark/dark.css";

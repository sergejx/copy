<?php
# uncomment this to check for uninitialized variables etc.:
# error_reporting (E_ALL);

# App info
define('APP_NAME', "COPY"); // Customized Original^1, Potentially Yummy
// ^1: Original: Opensource Remote Image Gallery, Initialy Not As Lovely
define('APP_URL', "https://github.com/sergejx/copy");
define('APP_VERSION', "0.90pre");

#language support
require_once("inc/lib.l10n.php");
require_once("config.php");
require_once("inc/page.inc.php");
require_once("inc/info.inc.php");

#set the language translation
l10n_set("l10n/".$sclang."/main.lang");
l10n_set("l10n/".$sclang."/date.lang");
l10n_set("l10n/".$GLOBALS['sclang']."/exif.lang");

$ThisScript = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);

# get variables passed in from the URL:
$gallery_id = '';
if (isset($_GET['gallery'])) $gallery_id=$_GET["gallery"];
// Old name (for backward compatibility):
if (isset($_GET['galerie'])) $gallery_id=$_GET["galerie"];
$gallery_id = preg_replace('/\//', '', $gallery_id);
$photo_id = 0;
if (isset($_GET["photo"])) $photo_id=$_GET["photo"];
// Old name (for backward compatibility):
if (isset($_GET["snimek"])) $photo_id=$_GET["snimek"];
$photo_id = intval($photo_id);


if (!is_dir("$gallery_dir/$gallery_id/thumbs")) {
    $gallery_id = "";
}

//read interesting stuff from info.yaml
if ($gallery_id) {
    $gallery = new Gallery($gallery_id);
}

// START RENDERING
if ($photo_id && $gallery_id)
    page_header("Photo", $gallery->get_photo($photo_id));
else
    page_header("Photos");

print "<div class=\"navigation\"><a href=\"$ThisScript\">" . $scnamegallery . "</a>";

// Main dispatch
try {
    if (!$gallery_id) {
        $galleries = list_galleries();
        require_once("inc/index.inc.php");
        render_index($galleries);
    } elseif (!$photo_id) {
        require_once("inc/gallery.inc.php");
        render_gallery($gallery);
    } else {
        $photo = $gallery->get_photo($photo_id);
        require_once("inc/photo.inc.php");
        render_photo($gallery, $photo);
    }
} catch (DomainException $e) {
    echo "</div>".$e->getMessage();
}

page_footer();
// STOP RENDERING


<?php
# uncomment this to check for uninitialized variables etc.:
# error_reporting (E_ALL);

#language support
require_once ("lib/lib.l10n.php");
require_once("inc/config.inc.php");
require_once("inc/page.php");
require_once("inc/funkce.inc.php");
require_once("inc/gallery_info.php");

#set the language translation
l10n_set("$root/l10n/".$sclang."/main.lang");
l10n_set("$root/l10n/".$sclang."/date.lang");

$ThisScript = str_replace('index.php', '', 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);

# get variables passed in from the URL:
$galerie='';
if (isset($_GET['galerie'])) $galerie=$_GET["galerie"];
if (isset($_GET['gallery'])) $galerie=$_GET["gallery"];
$galerie = preg_replace('/\//', '', $galerie);
$snimek = 0;
if (isset($_GET["snimek"])) $snimek=$_GET["snimek"];
if (isset($_GET["photo"])) $snimek=$_GET["photo"];
$snimek = intval($snimek);


if (!is_dir("$gallery_dir/$galerie/thumbs")) {
    $galerie = "";
}

//read interesting stuff from info.txt
if ($galerie) {
    $gallery = new Gallery($galerie);
    //check for restricted access
    if ($gallery->login) {
        access_check($gallery->login, $gallery->pw, $galerie);
    }
}

// START RENDERING
if ($snimek && $galerie)
    page_header("Photo", $gallery->get_photo($snimek));
else
    page_header("Photos");

// folder > tree
//print "<div class=\"navigation\"><a href=\"$ThisScript\">" . $scnamegallery . "</a>";
print "<div class=\"navigation\"><a href=\"./\">" . $scnamegallery . "</a>";

// Main dispatch
try {
    if (!$galerie) {
        require_once("inc/index.inc.php");
        render_index();
    } elseif (!$snimek) {
        require_once("inc/gallery.inc.php");
        render_gallery($gallery);
    } else {
        $photo = $gallery->get_photo($snimek);
        require_once("inc/photo.inc.php");
        render_photo($gallery, $photo);
    }
} catch (DomainException $e) {
    echo "</div>".$e->getMessage();
}

page_footer();
// STOP RENDERING


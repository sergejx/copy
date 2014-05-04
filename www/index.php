<?php
# uncomment this to check for uninitialized variables etc.:
# error_reporting (E_ALL);

#language support
require_once ("lib/lib.l10n.php");
require_once("inc/config.inc.php");
require_once("inc/page.php");
require_once("inc/funkce.inc.php");
require_once("inc/gallery_info.php");
require_once("inc/index.inc.php");
require_once("inc/gallery.inc.php");

#set the language translation
l10n_set("$root/l10n/".$sclang."/main.lang");
l10n_set("$root/l10n/".$sclang."/date.lang");

$ThisScript = str_replace('index.php', '', 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);

# always get sorted directory entries
$adr = new SortDir("$gallery_dir");

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

$galleries = array();
//read interesting stuff from info.txt
if ($galerie) {
    $galleries[$galerie] = new Gallery($galerie);
    //check for restricted access
    if ($galleries[$galerie]->login) {
        access_check($galleries[$galerie]->login,$galleries[$galerie]->pw,$galerie);
    }
}

// START RENDERING
if ($snimek && $galerie)
    page_header("Photo", $galleries[$galerie]->get_photo($snimek));
else
    page_header("Photos");

// folder > tree
//print "<div class=\"navigation\"><a href=\"$ThisScript\">" . $scnamegallery . "</a>";
print "<div class=\"navigation\"><a href=\"./\">" . $scnamegallery . "</a>";

// Main dispatch
if (!$galerie) {
    render_index($adr);
} elseif (!$snimek) {
    render_gallery($galerie);
} else {
    try {
        $gallery = new Gallery($galerie);
        $photo = $gallery->get_photo($snimek);
        require_once("inc/photo.inc.php");
        render_photo($gallery, $photo);
    } catch (DomainException $e) {
        echo "</div>".$e->getMessage();
    }
}

page_footer();
// STOP RENDERING


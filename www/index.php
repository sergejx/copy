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
    $galleries[$galerie] = new Gallery("$root/$gallery_dir/$galerie/info.txt", $galerie);
    //check for restricted access
    if ($galleries[$galerie]->login) {
        access_check($galleries[$galerie]->login,$galleries[$galerie]->pw,$galerie);
    }
}

// START RENDERING
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
    render_photo($galerie, $snimek);
}

page_footer();
// STOP RENDERING


#############################
#   Overall Gallery Index    #
#############################
function render_index($adr) {
    global $root, $gallery_dir, $yearsince, $sortinmonth;
    # finish off navigation bar
    print "</div>\n\n<!-- listing galleries-->\n\n";
    # I've nuked date.txt to replace it with a more generic info.txt
    # It optionally supplies i18n name, camera model, author and date
    # TODO: imgconv script support
    while ($file = $adr->Read()) {
        // checking for inc is only really needed when gallery_dir == $root
        // hopefully not many galleries will be named inc ;)
        if (is_dir("$gallery_dir/$file") && !ereg("\.", $file) && $file!="inc") {
            // Use date file for gallery date if avaliable
            // info.txt format described in README
            $galleries[$file] = new Gallery("$root/$gallery_dir/$file/info.txt", $file);
        }
    }

    //sort within month depending on $sortinmonth
    if ($sortinmonth) { //alphabetically
        ksort($galleries);
    } else {//by date
        uasort($galleries, 'cmp_galleries_by_day');
    }
    reset($galleries);


    $thisyear = 0;
    if (!isset($yearto)) $yearto = date("Y");
    for ($i = $yearto; $i >= $yearsince; $i--) {
        for ($thismonth=12; $thismonth>0; $thismonth--) { // go year by year, month by month
                                                          // down
            foreach ($galleries as $foldername => $info) { //using $galerieday (for when sorted)
                if ($info->month == $thismonth && $info->year == $i) { //such Y/M exists

                    $galerieyearordered["$foldername"]=$info->year;
                    $galeriemonthordered["$foldername"]=$info->month;
                }
            }
        }
    }

    $months = array(__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'),
        __('September'), __('October'), __('November'), __('December'));
    $one_out = false;
    foreach ($galerieyearordered as $foldername => $year) {
        $one_out = true;
        if (@$thisyear!=$year) { #if the year is not equal to the current year
            #This is the first year
            if (@$thisyear) { print "   </div>\n</div>\n";}// end last year if this is
                                                           // not the first one
            #This is a new year
            unset($thismonth);
            print "<div class=\"year\"><h3>$year</h3>\n";
            print "";
        }
        $month=$galleries["$foldername"]->month;
        # now months
        if (@$thismonth!=$month) {
            #first one
            if (@$thismonth) { print "   </div>\n"; } // end of last month if
                                                      // this is not the first one
            #new month
            $monthindex = $month - 1;
            $monthname = $months[$monthindex];
            print "   <div class=\"month\"><h4>$monthname</h4>\n";
        }
        #galleries within month
        if ($galleries[$foldername]->login) {
            print "      <p class=\"restricted\"><a ";
        } else {
            print "      <p><a ";
        }
        if (@$galleries[$foldername]->name) {
            print " href=\"$ThisScript?galerie=$foldername\">";
            print $galleries[$foldername]->name;
            print "</a>";
        } else {
            print " href=\"$ThisScript?galerie=$foldername\">$foldername</a>";
        }
        if (@$galleries[$foldername]->desc) {
            print "<span class=\"desc\">" . $galleries[$foldername]->desc;
            print "</span>\n";
        }
        if (@$galleries[$foldername]->author) {
            print "<span class=\"author\">by&nbsp;" . $galleries[$foldername]->author;
            print "</span>\n";
        }
        if (@$galleries[$foldername]->day) {
            print "<span class=\"date\">";
            print "$monthname&nbsp;" . $galleries[$foldername]->day;
            print "</span>\n";
        }
        print "</p>\n";
        $thisyear=$year;
        $thismonth=$month;
    }
    if ($one_out) print ("   </div>\n</div>\n\n");
}


##############################
#  Individual Gallery Index  #
##############################
function render_gallery($galerie) {
    global $ThisScript, $root, $gallery_dir, $galleries;
    # finish off navigation header

    print "\n &gt; ";
    if ($galleries[$galerie]->name) {
        print $galleries[$galerie]->name;
    } else {
        print $galerie;
    }
    print "</div>\n\n";

    //thumbnails
    print "<p class=\"bigthumbnails\">\n";
    $path = "$gallery_dir/$galerie/thumbs";
    $imgfiles = new SortDir($path);
    check($galerie); // check for nasty input
    while ($file = $imgfiles->read()) {
        if (is_file("$path/$file") && eregi("^img-([0-9]+)\.(png|jpe?g)", $file, $x)) {
            $thumb = "$gallery_dir/$galerie/thumbs/img-${x[1]}.${x[2]}";
            $imgsize = getimagesize("$root/$thumb");
            //check for portraits
            $portrait = "false";
            $class = "";
            if($imgsize[0]<100) {
                //portraits need a special class for styling
                $class = "portrait";
            }
            if (file_exists("$gallery_dir/$galerie/comments/${x[1]}.txt") &&
                $title = file_get_contents("$gallery_dir/$galerie/comments/${x[1]}.txt")) {
                $title = ereg_replace("(\"|\')","",trim(strip_tags($title)));
                $title = ereg_replace("(.{77}).*","\\1",$title);
            } else
                $title = "Photo ${x[1]}";

            print "   <a href=\"$ThisScript?galerie=$galerie&amp;photo=${x[1]}\"";
            print " title=\"$title\"";
            if ($class) print " class=\"$class\"";
            print ">";
            print "<img ";
            // scale portraits to 80 height
            if ($portrait) {
                //portrait
                print "width=\"";
                $scaled = round($imgsize[0] / 1.5);
                print $scaled;
                print "\" height=\"${imgsize[0]}\"";
            } else {
                //landscape
                print $imgsize[3];
            }
            print " src=\"$thumb\" ";
            print "alt=\"photo No. ${x[1]}\" />";
            print "</a>\n";
        }
    }
    print "</p>\n";

    //info
    print "<div id=\"info\">\n";
    if ($galleries[$galerie]->desc) {
        print "<p>";
        print "<span class=\"value\">";
        print $galleries[$galerie]->desc . "</span></p>\n";
    }
    if ($galleries[$galerie]->author) {
        print "<p><span class=\"key\">Author: </span>";
        print "<span class=\"value\">";
        print $galleries[$galerie]->author . "</span></p>\n";
    }
    print "</div>\n";

    //and links to archived images:
    print "\n<p class=\"archives\">\n";
    if (file_exists("$gallery_dir/$galerie/zip/mq.zip")) {
        print "[ <a href=\"$gallery_dir/$galerie/zip/mq.zip\">" . __('zipped MQ images') . "</a> ] ";
    }
    if (file_exists("$gallery_dir/$galerie/zip/mq.tar.bz2")) {
        print "[ <a href=\"$gallery_dir/$galerie/zip/mq.tar.bz2\">" . __('MQ images tarball') . "</a> ] ";
    }
    if (file_exists("$gallery_dir/$galerie/zip/hq.zip")) {
        print "[ <a href=\"$gallery_dir/$galerie/zip/hq.zip\">" . __('zipped HQ images') . "</a> ]";
    }
    if (file_exists("$gallery_dir/$galerie/zip/hq.tar.bz2")) {
        print "[ <a href=\"$gallery_dir/$galerie/zip/hq.tar.bz2\">" . __('HQ images tarball') . "</a> ]";
    }
    print "</p>";
}


#######################
#   Individual Image  #
#######################
function render_photo($galerie, $snimek) {
    global $ThisScript, $root, $gallery_dir, $exif_show, $galleries;
    # finish off header
    print "\n &gt; <a href=\"$ThisScript?galerie=$galerie\">";
    if ($galleries[$galerie]->name) {
        print $galleries[$galerie]->name;
    } else {
        print $galerie;
    }
    print "</a>\n &gt; Photo";
    print " $snimek</div>";
    $path = "$gallery_dir/$galerie/thumbs";
    $imgfiles = new SortDir("$path");
    check($galerie);
    $path = "$gallery_dir/$galerie/mq";
    $file = "$path/img-$snimek.jpg";
    if (!file_exists($file)) {
        print __('No such image');
        page_footer();
        exit;
    }

    if (!isset($picture)) { //picture may have been created if commentform submitted
        require_once("$root/inc/gallery_info.php");
        $picture = new C_photo($file, $snimek);
    }

    // mini thumbnail roll
    print "\n<!--mini thumbnail roll-->\n<div class=\"thumbroll\">";
    $start = $snimek - 3;
    $stop = $snimek + 3;
    $total = count($imgfiles->items);
    if ($snimek < 4)
        $stop = 7;
    if ($snimek > ($total - 4)) {
        $start = $total - 6;
    }
    while ($thumbfile = $imgfiles->read()) {
        if ( eregi("^img-([0-9]+)\.(png|jpe?g)", $thumbfile, $x)) {
            if ($x[1] < $start || $x[1] > $stop)
                continue;
            $thumb = "$gallery_dir/$galerie/thumbs/img-${x[1]}.${x[2]}";
            print "   <a href=\"$ThisScript?galerie=$galerie&amp;photo=${x[1]}\"";
            print " title=" . get_photo_title($galerie, $x[1]);
            if ($x[1] == $snimek)
                print " class='current'";
            print ">";
            print "<img class=\"thumb\" ";
            $minithumb=getimagesize("$root/$thumb");
            $h=60;
            $ratio = $minithumb[1]/60;
            $w=$minithumb[0]/$ratio;

            print " width=\"$w\" height=\"$h\"";
            print " src=\"$thumb\" ";
            print "alt=\"photo No. ${x[1]}\" />";
            print "</a> \n";
        }
    }
    if (file_exists("$gallery_dir/$galerie/zip/hq.zip")) {
        print "<a id=\"zip\" href=\"$gallery_dir/$galerie/zip/hq.zip\">";
        print "zip<span /></a>";
    }
    if (file_exists("$gallery_dir/$galerie/zip/hq.tar.bz2")) {
        print "<a id=\"zip\" href=\"$gallery_dir/$galerie/zip/hq.tar.bz2\">";
        print "zip<span /></a>";
    }
    print "</div>\n";

    /* main image + navigation (prev/next) */

    $picture->renderPreview();
    page_navigation($galerie, $snimek, "prev");
    page_navigation($galerie, $snimek, "next");
    print "</div>\n"; //end image div



    if (function_exists('exif_read_data')) require("$root/inc/exif.inc.php");
    /* Image comment
       really poor naming here, it is caption.
    */
    $picture->renderCaption();

    $picture->renderBigSize();

    page_navigation($galerie, $snimek, null);
}
?>

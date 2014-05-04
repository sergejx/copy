<?php
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
        $picture = $galleries[$galerie]->get_photo($snimek);
    }

    thumb_roll($galerie, $snimek, $imgfiles);

    /* main image + navigation (prev/next) */

    $picture->renderPreview();
    page_navigation($galerie, $picture, "prev");
    page_navigation($galerie, $picture, "next");
    print "</div>\n"; //end image div



    if (function_exists('exif_read_data')) require("$root/inc/exif.inc.php");
    /* Image comment
       really poor naming here, it is caption.
    */
    $picture->renderCaption();

    $picture->renderBigSize();

    page_navigation($galerie, $picture, null);
}

function page_navigation($gallery, $photo, $image) {
    if (!$image) { // this will render a navigation bar - max 3 buttons
        echo "\n<div class=\"navbuttons\">\n";
        echo "<div class=\"navbuttonsshell\">\n";
        if ($photo->has_prev()) { //previous
            echo "<a id=\"previcon\" href=\"{$photo->get_prev()->url}\"";
            echo " accesskey=\"p\">";
            echo "&lt; <span class=\"accesskey\">P</span>revious</a>\n";
        }
        echo "&nbsp;";
        if ($photo->has_next()) { //next
            $next = $photo->get_next()->number;
            echo "<a id=\"nexticon\" href=\"{$photo->get_next()->url}\"";
            echo " accesskey=\"n\">";
            echo "<span class=\"accesskey\">N</span>ext &gt;</a>\n";
        }
        echo "</div>\n</div>\n";
    } elseif ($image=="prev") { // previous image link
        if ($photo->has_prev()) {
            $prev = $photo->get_prev()->number;
            echo "<div class=\"prevthumb\">";
            echo "<a href=\"{$photo->get_prev()->url}\">";
            echo "</a></div>\n";
        }
    } else { // next image link
        if ($photo->has_next()) {
            $next = $photo->get_next()->number;
            echo "<div class=\"nextthumb\">";
            echo "<a href=\"{$photo->get_next()->url}\">";
            echo "</a></div>\n";
        }
    }
}


/** Mini thumbnail roll */
function thumb_roll($galerie, $snimek, $imgfiles) {
    global $ThisScript, $root, $gallery_dir;

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
    print "</div>\n";
}

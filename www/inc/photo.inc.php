<?php
#######################
#   Individual Image  #
#######################
function render_photo($gallery, $picture) {
    global $exif_show;
    # finish off header
    print "\n &gt; <a href=\"{$gallery->url}\">";
    if ($gallery->name) {
        print $gallery->name;
    } else {
        print $gallery->id;
    }
    print "</a>\n &gt; Photo";
    print " {$picture->number}</div>";

    thumb_roll($gallery, $picture->number);

    /* main image + navigation (prev/next) */
    $picture->renderPreview();
    page_navigation($picture, "prev");
    page_navigation($picture, "next");
    print "</div>\n"; //end image div

    if (function_exists('exif_read_data')) require("exif.inc.php");

    $picture->renderCaption();

    $picture->renderBigSize();

    page_navigation($picture, null);
}

function page_navigation($photo, $image) {
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
function thumb_roll($gallery, $snimek) {
    print "\n<!--mini thumbnail roll-->\n<div class=\"thumbroll\">";
    $start = $snimek - 3;
    $stop = $snimek + 3;
    $total = count($gallery->photos);
    if ($snimek < 4)
        $stop = 7;
    if ($snimek > ($total - 4)) {
        $start = $total - 6;
    }
    foreach ($gallery->photos as $num => $photo) {
            if ($num < $start || $num > $stop)
                continue;
            $thumb = $photo->thumbnail;
            print " <a href=\"{$photo->url}\" title=\"{$photo->name}\"";
            if ($num == $snimek)
                print " class='current'";
            print ">";
            print "<img class=\"thumb\" ";
            $minithumb=getimagesize($photo->thumbnail);
            $h=60;
            $ratio = $minithumb[1]/60;
            $w=$minithumb[0]/$ratio;

            print " width=\"$w\" height=\"$h\"";
            print " src=\"$thumb\" ";
            print "alt=\"photo No. $num\">";
            print "</a> \n";
    }
    print "</div>\n";
}

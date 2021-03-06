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
    render_preview($picture);
    page_navigation($picture, "prev");
    page_navigation($picture, "next");
    print "</div>\n"; //end image div

    if (function_exists('exif_read_data')) require("exif.inc.php");

    render_caption($picture);

    render_big_size($picture);

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
    $total = $gallery->get_photos_count();
    if ($snimek < 4)
        $stop = 7;
    if ($snimek > ($total - 4)) {
        $start = $total - 6;
    }
    for ($num = 1; $num <= $gallery->get_photos_count(); $num++) {
            if ($num < $start || $num > $stop)
                continue;
        $photo = $gallery->get_photo($num);
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


function render_big_size($photo) {
    if ($photo->mq || $photo->hq) {
        print "<div id=\"mqhq\">";
        if ($photo->mq) {
            print "<a href=\"" . $photo->mq . "\">". __('MQ') . "</a> ";
        }
        if ($photo->hq) {
            print "<a href=\"" . $photo->hq . "\">" . __('HQ') . "</a>";
        }
        print "</div>\n";
    }
}


function render_preview($photo) {
    print "<div id=\"image\">\n";
    print "<div id=\"preview\"><img " . $photo->get_preview_size()[3] . " src=\"". $photo->preview;
    print "\" alt=\"$photo->caption\"></div>\n";
}


function render_caption($photo) {
    print "<div class=\"comment\">";
    print "<span>" . $photo->name . "</span>";
    if ($photo->caption) {
        print " &ndash; ";
        print $photo->caption;
        print "</div>";
    }
}
